<?php
declare(strict_types=1);

/**
 * Point d'amorçage commun : autoload, variables d'environnement,
 * session sécurisée et en-têtes HTTP de sécurité.
 */

define('ROOT_PATH', dirname(__DIR__));

// Autoload Composer si présent, sinon autoloader PSR-4 minimal pour App\
if (is_file(ROOT_PATH . '/vendor/autoload.php')) {
    require ROOT_PATH . '/vendor/autoload.php';
}
spl_autoload_register(function (string $class): void {
    if (str_starts_with($class, 'App\\')) {
        $file = ROOT_PATH . '/src/' . str_replace('\\', '/', substr($class, 4)) . '.php';
        if (is_file($file)) {
            require $file;
        }
    }
});

require ROOT_PATH . '/src/helpers.php';

App\Core\Env::load(ROOT_PATH . '/.env');

$isProd = env('APP_ENV', 'dev') === 'prod';
ini_set('display_errors', $isProd ? '0' : '1');
error_reporting(E_ALL);
date_default_timezone_set('Europe/Paris');

// Session : cookie HttpOnly, SameSite=Lax, Secure en HTTPS, mode strict
$secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
session_name('VGSESSID');
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'secure'   => $secure,
    'httponly' => true,
    'samesite' => 'Lax',
]);
if (PHP_SAPI !== 'cli' && session_status() === PHP_SESSION_NONE) {
    session_start();
}

// En-têtes de sécurité
if (PHP_SAPI !== 'cli' && !headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
    header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self'; font-src 'self'; "
        . "img-src 'self' data:; connect-src 'self' https://api-adresse.data.gouv.fr; "
        . "frame-ancestors 'none'; form-action 'self'; base-uri 'self'; object-src 'none'");
    if ($secure) {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
}
