<?php
declare(strict_types=1);

namespace App\Core;

use PDO;

/**
 * Connexion PDO unique (singleton).
 * - ERRMODE_EXCEPTION : aucune erreur SQL silencieuse
 * - EMULATE_PREPARES = false : vraies requêtes préparées côté serveur (anti-injection SQL)
 */
final class Database
{
    private static ?PDO $pdo = null;

    public static function pdo(): PDO
    {
        if (self::$pdo === null) {
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                env('DB_HOST', '127.0.0.1'),
                env('DB_PORT', '3306'),
                env('DB_NAME', 'vite_gourmand_ecf')
            );
            self::$pdo = new PDO($dsn, env('DB_USER', 'root'), env('DB_PASS', '') ?? '', [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        }
        return self::$pdo;
    }
}
