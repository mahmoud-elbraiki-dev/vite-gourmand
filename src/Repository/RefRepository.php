<?php
declare(strict_types=1);

namespace App\Repository;

use App\Core\Database;

/** Référentiels : thèmes, régimes, allergènes, horaires. */
final class RefRepository
{
    public function themes(): array
    {
        return Database::pdo()->query('SELECT * FROM theme ORDER BY theme_id')->fetchAll();
    }

    public function regimes(): array
    {
        return Database::pdo()->query('SELECT * FROM regime ORDER BY regime_id')->fetchAll();
    }

    public function allergenes(): array
    {
        return Database::pdo()->query('SELECT * FROM allergene ORDER BY libelle')->fetchAll();
    }

    public function horaires(): array
    {
        return Database::pdo()->query('SELECT * FROM horaire ORDER BY ordre')->fetchAll();
    }

    public function updateHoraire(int $id, ?string $open, ?string $close): void
    {
        Database::pdo()->prepare('UPDATE horaire SET heure_ouverture = ?, heure_fermeture = ? WHERE horaire_id = ?')
            ->execute([$open ?: null, $close ?: null, $id]);
    }

    public function addRegime(string $libelle): void
    {
        Database::pdo()->prepare('INSERT IGNORE INTO regime (libelle) VALUES (?)')->execute([$libelle]);
    }

    public function addTheme(string $libelle): void
    {
        Database::pdo()->prepare('INSERT IGNORE INTO theme (libelle) VALUES (?)')->execute([$libelle]);
    }
}
