<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Téléversement d'images sécurisé :
 * - type MIME réel vérifié (finfo), pas l'extension déclarée
 * - taille limitée à 2 Mo
 * - nom de fichier aléatoire (aucun nom fourni par l'utilisateur n'est réutilisé)
 */
final class Upload
{
    private const MIMES = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    private const MAX = 2 * 1024 * 1024;

    /** @return string|null chemin relatif à /public, ou null si aucun fichier */
    public static function image(string $field, string $folder): ?string
    {
        $f = $_FILES[$field] ?? null;
        if (!$f || ($f['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        if ($f['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($f['tmp_name'])) {
            throw new \RuntimeException('Échec du téléversement de l\'image.');
        }
        if ($f['size'] > self::MAX) {
            throw new \RuntimeException('Image trop lourde (2 Mo maximum).');
        }
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
        if (!isset(self::MIMES[$mime]) || @getimagesize($f['tmp_name']) === false) {
            throw new \RuntimeException('Format d\'image non autorisé (JPG, PNG ou WebP).');
        }
        $dir = ROOT_PATH . '/public/uploads/' . $folder;
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $name = bin2hex(random_bytes(12)) . '.' . self::MIMES[$mime];
        if (!move_uploaded_file($f['tmp_name'], $dir . '/' . $name)) {
            throw new \RuntimeException('Impossible d\'enregistrer l\'image.');
        }
        return 'uploads/' . $folder . '/' . $name;
    }

    public static function delete(?string $relative): void
    {
        if ($relative && str_starts_with($relative, 'uploads/')) {
            $path = realpath(ROOT_PATH . '/public/' . $relative);
            $base = realpath(ROOT_PATH . '/public/uploads');
            if ($path && $base && str_starts_with($path, $base)) {
                @unlink($path);
            }
        }
    }
}
