<?php
declare(strict_types=1);

namespace App\Core;

/** Jeton anti-CSRF par session, vérifié à temps constant sur chaque POST. */
final class Csrf
{
    public static function token(): string
    {
        if (empty($_SESSION['_csrf'])) {
            $_SESSION['_csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_csrf'];
    }

    public static function field(): string
    {
        return '<input type="hidden" name="_csrf" value="' . e(self::token()) . '">';
    }

    public static function check(): void
    {
        $sent = (string) ($_POST['_csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
        if (!hash_equals(self::token(), $sent)) {
            abort(403, 'Jeton de sécurité invalide. Merci de recharger la page.');
        }
    }
}
