<?php
declare(strict_types=1);

namespace App\Repository;

use App\Core\Database;

final class AvisRepository
{
    public function validated(int $limit = 6): array
    {
        $st = Database::pdo()->prepare(
            "SELECT a.*, u.prenom, LEFT(u.nom, 1) AS initiale, m.titre AS menu_titre
             FROM avis a JOIN utilisateur u ON u.utilisateur_id = a.utilisateur_id
             JOIN commande c ON c.numero_commande = a.numero_commande JOIN menu m ON m.menu_id = c.menu_id
             WHERE a.statut = 'valide' ORDER BY a.date_avis DESC LIMIT ?"
        );
        $st->bindValue(1, $limit, \PDO::PARAM_INT);
        $st->execute();
        return $st->fetchAll();
    }

    public function byStatus(string $statut = ''): array
    {
        $sql = 'SELECT a.*, u.prenom, u.nom, u.email, m.titre AS menu_titre FROM avis a
                JOIN utilisateur u ON u.utilisateur_id = a.utilisateur_id
                JOIN commande c ON c.numero_commande = a.numero_commande JOIN menu m ON m.menu_id = c.menu_id';
        if ($statut !== '') {
            $st = Database::pdo()->prepare($sql . ' WHERE a.statut = ? ORDER BY a.date_avis DESC');
            $st->execute([$statut]);
            return $st->fetchAll();
        }
        return Database::pdo()->query($sql . ' ORDER BY FIELD(a.statut, \'en attente\', \'valide\', \'refuse\'), a.date_avis DESC')->fetchAll();
    }

    public function create(int $userId, string $numero, int $note, string $description): void
    {
        Database::pdo()->prepare('INSERT INTO avis (note, description, utilisateur_id, numero_commande) VALUES (?, ?, ?, ?)')
            ->execute([$note, $description, $userId, $numero]);
    }

    public function setStatus(int $id, string $statut): void
    {
        Database::pdo()->prepare('UPDATE avis SET statut = ? WHERE avis_id = ?')->execute([$statut, $id]);
    }

    public function averageNote(): ?float
    {
        $v = Database::pdo()->query("SELECT AVG(note) FROM avis WHERE statut = 'valide'")->fetchColumn();
        return $v !== null ? round((float) $v, 1) : null;
    }
}
