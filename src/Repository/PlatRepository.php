<?php
declare(strict_types=1);

namespace App\Repository;

use App\Core\Database;

final class PlatRepository
{
    public function all(): array
    {
        $plats = Database::pdo()->query('SELECT * FROM plat ORDER BY type_plat, titre_plat')->fetchAll();
        return $this->withAllergenes($plats);
    }

    public function find(int $id): ?array
    {
        $st = Database::pdo()->prepare('SELECT * FROM plat WHERE plat_id = ?');
        $st->execute([$id]);
        $p = $st->fetch();
        return $p ? $this->withAllergenes([$p])[0] : null;
    }

    public function forMenu(int $menuId): array
    {
        $st = Database::pdo()->prepare(
            'SELECT p.* FROM plat p JOIN menu_plat mp ON mp.plat_id = p.plat_id
             WHERE mp.menu_id = ? ORDER BY p.type_plat, p.titre_plat'
        );
        $st->execute([$menuId]);
        return $this->withAllergenes($st->fetchAll());
    }

    /** Ajoute la liste des allergènes à chaque plat (une seule requête). */
    private function withAllergenes(array $plats): array
    {
        if (!$plats) {
            return [];
        }
        $ids = array_column($plats, 'plat_id');
        $in = implode(',', array_fill(0, count($ids), '?'));
        $st = Database::pdo()->prepare(
            "SELECT pa.plat_id, a.allergene_id, a.libelle FROM plat_allergene pa
             JOIN allergene a ON a.allergene_id = pa.allergene_id WHERE pa.plat_id IN ($in) ORDER BY a.libelle"
        );
        $st->execute($ids);
        $map = [];
        foreach ($st->fetchAll() as $row) {
            $map[$row['plat_id']][] = $row;
        }
        foreach ($plats as &$p) {
            $p['allergenes'] = $map[$p['plat_id']] ?? [];
        }
        return $plats;
    }

    public function save(?int $id, string $titre, string $type, ?string $photo, array $allergeneIds): int
    {
        $pdo = Database::pdo();
        $pdo->beginTransaction();
        try {
            if ($id) {
                $sql = 'UPDATE plat SET titre_plat = ?, type_plat = ?' . ($photo ? ', photo = ?' : '') . ' WHERE plat_id = ?';
                $params = $photo ? [$titre, $type, $photo, $id] : [$titre, $type, $id];
                $pdo->prepare($sql)->execute($params);
            } else {
                $pdo->prepare('INSERT INTO plat (titre_plat, type_plat, photo) VALUES (?, ?, ?)')->execute([$titre, $type, $photo]);
                $id = (int) $pdo->lastInsertId();
            }
            $pdo->prepare('DELETE FROM plat_allergene WHERE plat_id = ?')->execute([$id]);
            $ins = $pdo->prepare('INSERT INTO plat_allergene (plat_id, allergene_id) VALUES (?, ?)');
            foreach (array_unique(array_map('intval', $allergeneIds)) as $aid) {
                $ins->execute([$id, $aid]);
            }
            $pdo->commit();
            return $id;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public function delete(int $id): void
    {
        Database::pdo()->prepare('DELETE FROM plat WHERE plat_id = ?')->execute([$id]);
    }
}
