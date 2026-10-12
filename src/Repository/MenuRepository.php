<?php
declare(strict_types=1);

namespace App\Repository;

use App\Core\Database;

final class MenuRepository
{
    private const SELECT = 'SELECT m.*, t.libelle AS theme, r.libelle AS regime,
            (m.prix_par_personne * m.nombre_personne_minimum) AS prix_minimum,
            (SELECT chemin FROM menu_image mi WHERE mi.menu_id = m.menu_id ORDER BY ordre LIMIT 1) AS image,
            (SELECT alt FROM menu_image mi WHERE mi.menu_id = m.menu_id ORDER BY ordre LIMIT 1) AS image_alt
        FROM menu m JOIN theme t ON t.theme_id = m.theme_id JOIN regime r ON r.regime_id = m.regime_id';

    /**
     * Recherche filtrée (vue globale + API AJAX).
     * Tous les filtres passent par des paramètres liés : aucune concaténation de saisie.
     */
    public function search(array $f = [], bool $onlyActive = true): array
    {
        $where = [];
        $params = [];
        if ($onlyActive) {
            $where[] = 'm.actif = 1';
        }
        if (($f['prix_max'] ?? '') !== '') {
            $where[] = '(m.prix_par_personne * m.nombre_personne_minimum) <= :prix_max';
            $params['prix_max'] = (float) $f['prix_max'];
        }
        if (($f['prix_min'] ?? '') !== '') {
            $where[] = '(m.prix_par_personne * m.nombre_personne_minimum) >= :prix_min';
            $params['prix_min'] = (float) $f['prix_min'];
        }
        if (($f['theme'] ?? '') !== '') {
            $where[] = 'm.theme_id = :theme';
            $params['theme'] = (int) $f['theme'];
        }
        if (($f['regime'] ?? '') !== '') {
            $where[] = 'm.regime_id = :regime';
            $params['regime'] = (int) $f['regime'];
        }
        if (($f['personnes'] ?? '') !== '') {
            // Menus commandables pour ce nombre de convives : minimum requis <= nombre saisi
            $where[] = 'm.nombre_personne_minimum <= :personnes';
            $params['personnes'] = (int) $f['personnes'];
        }
        $sql = self::SELECT . ($where ? ' WHERE ' . implode(' AND ', $where) : '') . ' ORDER BY m.menu_id';
        $st = Database::pdo()->prepare($sql);
        $st->execute($params);
        return $st->fetchAll();
    }

    public function find(int $id, bool $onlyActive = false): ?array
    {
        $st = Database::pdo()->prepare(self::SELECT . ' WHERE m.menu_id = ?' . ($onlyActive ? ' AND m.actif = 1' : ''));
        $st->execute([$id]);
        $menu = $st->fetch();
        if (!$menu) {
            return null;
        }
        $menu['images'] = $this->images($id);
        $menu['plats'] = (new PlatRepository())->forMenu($id);
        return $menu;
    }

    public function images(int $menuId): array
    {
        $st = Database::pdo()->prepare('SELECT * FROM menu_image WHERE menu_id = ? ORDER BY ordre, image_id');
        $st->execute([$menuId]);
        return $st->fetchAll();
    }

    public function save(?int $id, array $d, array $platIds): int
    {
        $pdo = Database::pdo();
        $pdo->beginTransaction();
        try {
            $params = [
                $d['titre'], (int) $d['nombre_personne_minimum'], (float) $d['prix_par_personne'],
                $d['description'], $d['conditions'], (int) $d['quantite_restante'],
                (int) $d['regime_id'], (int) $d['theme_id'], (int) ($d['actif'] ?? 1),
            ];
            if ($id) {
                $params[] = $id;
                $pdo->prepare('UPDATE menu SET titre=?, nombre_personne_minimum=?, prix_par_personne=?, description=?,
                    conditions=?, quantite_restante=?, regime_id=?, theme_id=?, actif=? WHERE menu_id=?')->execute($params);
            } else {
                $pdo->prepare('INSERT INTO menu (titre, nombre_personne_minimum, prix_par_personne, description, conditions,
                    quantite_restante, regime_id, theme_id, actif) VALUES (?,?,?,?,?,?,?,?,?)')->execute($params);
                $id = (int) $pdo->lastInsertId();
            }
            $pdo->prepare('DELETE FROM menu_plat WHERE menu_id = ?')->execute([$id]);
            $ins = $pdo->prepare('INSERT INTO menu_plat (menu_id, plat_id) VALUES (?, ?)');
            foreach (array_unique(array_map('intval', $platIds)) as $pid) {
                $ins->execute([$id, $pid]);
            }
            $pdo->commit();
            return $id;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public function addImage(int $menuId, string $path, string $alt): void
    {
        Database::pdo()->prepare('INSERT INTO menu_image (menu_id, chemin, alt, ordre)
            VALUES (?, ?, ?, (SELECT IFNULL(MAX(ordre),0)+1 FROM menu_image mi WHERE mi.menu_id = ?))')
            ->execute([$menuId, $path, $alt, $menuId]);
    }

    public function deleteImage(int $imageId): ?string
    {
        $st = Database::pdo()->prepare('SELECT chemin FROM menu_image WHERE image_id = ?');
        $st->execute([$imageId]);
        $path = $st->fetchColumn() ?: null;
        Database::pdo()->prepare('DELETE FROM menu_image WHERE image_id = ?')->execute([$imageId]);
        return $path;
    }

    /**
     * Suppression : si le menu a déjà été commandé, on le désactive
     * (l'historique des commandes et le chiffre d'affaires restent cohérents).
     */
    public function delete(int $id): string
    {
        $st = Database::pdo()->prepare('SELECT COUNT(*) FROM commande WHERE menu_id = ?');
        $st->execute([$id]);
        if ((int) $st->fetchColumn() > 0) {
            Database::pdo()->prepare('UPDATE menu SET actif = 0 WHERE menu_id = ?')->execute([$id]);
            return 'archived';
        }
        Database::pdo()->prepare('DELETE FROM menu WHERE menu_id = ?')->execute([$id]);
        return 'deleted';
    }

    public function all(): array
    {
        return $this->search([], false);
    }
}
