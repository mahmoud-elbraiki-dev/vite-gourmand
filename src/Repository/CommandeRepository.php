<?php
declare(strict_types=1);

namespace App\Repository;

use App\Core\Database;
use App\Service\OrderStatus;
use App\Service\StatsStore;

final class CommandeRepository
{
    private const SELECT = 'SELECT c.*, m.titre AS menu_titre, m.prix_par_personne, m.nombre_personne_minimum,
            u.nom, u.prenom, u.email, u.telephone,
            (c.prix_menu + c.prix_livraison) AS total,
            (SELECT a.avis_id FROM avis a WHERE a.numero_commande = c.numero_commande) AS avis_id
        FROM commande c JOIN menu m ON m.menu_id = c.menu_id JOIN utilisateur u ON u.utilisateur_id = c.utilisateur_id';

    public function find(string $numero): ?array
    {
        $st = Database::pdo()->prepare(self::SELECT . ' WHERE c.numero_commande = ?');
        $st->execute([$numero]);
        return $st->fetch() ?: null;
    }

    public function forUser(int $userId): array
    {
        $st = Database::pdo()->prepare(self::SELECT . ' WHERE c.utilisateur_id = ? ORDER BY c.date_commande DESC');
        $st->execute([$userId]);
        return $st->fetchAll();
    }

    /** Filtre employé : par statut et/ou par client (nom, prénom ou e-mail). */
    public function filter(string $statut = '', string $client = ''): array
    {
        $where = [];
        $p = [];
        if ($statut !== '') {
            $where[] = 'c.statut = :statut';
            $p['statut'] = $statut;
        }
        if ($client !== '') {
            $where[] = '(u.nom LIKE :c1 OR u.prenom LIKE :c2 OR u.email LIKE :c3 OR CONCAT(u.prenom, \' \', u.nom) LIKE :c4)';
            $like = '%' . addcslashes($client, '%_') . '%';
            $p += ['c1' => $like, 'c2' => $like, 'c3' => $like, 'c4' => $like];
        }
        $sql = self::SELECT . ($where ? ' WHERE ' . implode(' AND ', $where) : '') . ' ORDER BY c.date_prestation ASC, c.date_commande DESC';
        $st = Database::pdo()->prepare($sql);
        $st->execute($p);
        return $st->fetchAll();
    }

    public function history(string $numero): array
    {
        $st = Database::pdo()->prepare('SELECT * FROM commande_suivi WHERE numero_commande = ? ORDER BY date_modif, suivi_id');
        $st->execute([$numero]);
        return $st->fetchAll();
    }

    /**
     * Création d'une commande en transaction :
     * verrouillage du menu (FOR UPDATE) pour éviter de vendre un stock épuisé
     * lorsque deux clients commandent au même moment.
     */
    public function create(int $userId, array $menu, array $d, array $price): string
    {
        $pdo = Database::pdo();
        $pdo->beginTransaction();
        try {
            $st = $pdo->prepare('SELECT quantite_restante FROM menu WHERE menu_id = ? AND actif = 1 FOR UPDATE');
            $st->execute([$menu['menu_id']]);
            $stock = $st->fetchColumn();
            if ($stock === false || (int) $stock <= 0) {
                throw new \RuntimeException('Ce menu n\'est plus disponible (stock épuisé).');
            }
            $numero = 'VG-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(2)));
            $pdo->prepare('INSERT INTO commande (numero_commande, utilisateur_id, menu_id, date_prestation, heure_livraison,
                    adresse_prestation, ville_prestation, distance_km, nombre_personne, prix_menu, reduction, prix_livraison, statut)
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)')->execute([
                $numero, $userId, $menu['menu_id'], $d['date_prestation'], $d['heure_livraison'],
                $d['adresse_prestation'], $d['ville_prestation'], $d['distance_km'], $d['nombre_personne'],
                $price['menu'], $price['reduction'], $price['livraison'], OrderStatus::EN_ATTENTE,
            ]);
            $pdo->prepare('UPDATE menu SET quantite_restante = quantite_restante - 1 WHERE menu_id = ?')->execute([$menu['menu_id']]);
            $this->addHistory($numero, OrderStatus::EN_ATTENTE, $userId);
            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }

        StatsStore::instance()->upsert([
            'numero' => $numero,
            'menu_id' => $menu['menu_id'],
            'menu_titre' => $menu['titre'],
            'montant' => $price['menu'],
            'nombre_personne' => $d['nombre_personne'],
            'date_commande' => date('Y-m-d H:i:s'),
            'statut' => OrderStatus::EN_ATTENTE,
        ]);
        return $numero;
    }

    /** Modification des informations de prestation (le menu n'est jamais modifiable). */
    public function updateDetails(string $numero, array $d, array $price): void
    {
        Database::pdo()->prepare('UPDATE commande SET date_prestation=?, heure_livraison=?, adresse_prestation=?,
                ville_prestation=?, distance_km=?, nombre_personne=?, prix_menu=?, reduction=?, prix_livraison=?
            WHERE numero_commande = ?')->execute([
            $d['date_prestation'], $d['heure_livraison'], $d['adresse_prestation'], $d['ville_prestation'],
            $d['distance_km'], $d['nombre_personne'], $price['menu'], $price['reduction'], $price['livraison'], $numero,
        ]);
        $c = $this->find($numero);
        StatsStore::instance()->upsert([
            'numero' => $numero, 'menu_id' => $c['menu_id'], 'menu_titre' => $c['menu_titre'],
            'montant' => $price['menu'], 'nombre_personne' => $d['nombre_personne'],
            'date_commande' => $c['date_commande'], 'statut' => $c['statut'],
        ]);
    }

    public function updateStatus(string $numero, string $statut, int $authorId, array $extra = []): void
    {
        $pdo = Database::pdo();
        $sets = ['statut = :statut'];
        $params = ['statut' => $statut, 'numero' => $numero];
        foreach (['pret_materiel', 'restitution_materiel', 'motif_annulation', 'mode_contact'] as $col) {
            if (array_key_exists($col, $extra)) {
                $sets[] = "$col = :$col";
                $params[$col] = $extra[$col];
            }
        }
        $pdo->prepare('UPDATE commande SET ' . implode(', ', $sets) . ' WHERE numero_commande = :numero')->execute($params);
        $this->addHistory($numero, $statut, $authorId);
        StatsStore::instance()->setStatus($numero, $statut);
    }

    /** Annulation : la commande est conservée (traçabilité) et le stock est restitué. */
    public function cancel(string $numero, int $authorId, ?string $motif = null, ?string $mode = null): void
    {
        $pdo = Database::pdo();
        $pdo->beginTransaction();
        try {
            $c = $this->find($numero);
            $this->updateStatus($numero, OrderStatus::ANNULEE, $authorId, ['motif_annulation' => $motif, 'mode_contact' => $mode]);
            $pdo->prepare('UPDATE menu SET quantite_restante = quantite_restante + 1 WHERE menu_id = ?')->execute([$c['menu_id']]);
            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public function setContact(string $numero, string $mode, string $motif): void
    {
        Database::pdo()->prepare('UPDATE commande SET mode_contact = ?, motif_annulation = ? WHERE numero_commande = ?')
            ->execute([$mode, $motif, $numero]);
    }

    private function addHistory(string $numero, string $statut, int $authorId): void
    {
        Database::pdo()->prepare('INSERT INTO commande_suivi (numero_commande, statut, auteur_id) VALUES (?, ?, ?)')
            ->execute([$numero, $statut, $authorId]);
    }

    /** Ré-alimente la base NoSQL depuis MySQL (commande de maintenance / init). */
    public function syncAllToNoSql(): int
    {
        $rows = Database::pdo()->query(self::SELECT)->fetchAll();
        foreach ($rows as $c) {
            StatsStore::instance()->upsert([
                'numero' => $c['numero_commande'], 'menu_id' => $c['menu_id'], 'menu_titre' => $c['menu_titre'],
                'montant' => $c['prix_menu'], 'nombre_personne' => $c['nombre_personne'],
                'date_commande' => $c['date_commande'], 'statut' => $c['statut'],
            ]);
        }
        return count($rows);
    }

    public function countByStatus(): array
    {
        return Database::pdo()->query('SELECT statut, COUNT(*) n FROM commande GROUP BY statut')->fetchAll(\PDO::FETCH_KEY_PAIR);
    }
}
