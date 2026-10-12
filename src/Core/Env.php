<?php
declare(strict_types=1);

namespace App\Core;

/** Chargeur minimal de fichier .env (clé=valeur, commentaires #). */
final class Env
{
    public static function load(string $file): void
    {
        if (!is_file($file)) {
            return;
        }
        foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#' || !str_contains($line, '=')) {
                continue;
            }
            [$key, $value] = array_map('trim', explode('=', $line, 2));
            // Retire un commentaire en fin de ligne (hors guillemets)
            if ($value !== '' && $value[0] !== '"' && str_contains($value, ' #')) {
                $value = trim(substr($value, 0, strpos($value, ' #')));
            }
            $value = trim($value, '"\'');
            // Les variables réelles de l'environnement (hébergeur) priment
            if (getenv($key) === false) {
                $_ENV[$key] = $value;
            } else {
                $_ENV[$key] = getenv($key);
            }
        }
    }
}
