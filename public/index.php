<?php
declare(strict_types=1);

/**
 * Contrôleur frontal : toutes les requêtes passent par ce fichier.
 * Le dossier /public est la racine web : le code (src/), la configuration (.env)
 * et les scripts SQL ne sont jamais exposés.
 */

require dirname(__DIR__) . '/src/bootstrap.php';

use App\Controller;

$routes = [
    '/'                          => [Controller\HomeController::class, 'index'],
    '/menus'                     => [Controller\MenuController::class, 'index'],
    '/api/menus'                 => [Controller\MenuController::class, 'api'],
    '/menu'                      => [Controller\MenuController::class, 'show'],
    '/commande'                  => [Controller\OrderController::class, 'create'],
    '/connexion'                 => [Controller\AuthController::class, 'login'],
    '/inscription'               => [Controller\AuthController::class, 'register'],
    '/deconnexion'               => [Controller\AuthController::class, 'logout'],
    '/mot-de-passe-oublie'       => [Controller\AuthController::class, 'forgot'],
    '/reinitialiser'             => [Controller\AuthController::class, 'reset'],
    '/contact'                   => [Controller\ContactController::class, 'index'],
    '/mentions-legales'          => [Controller\PageController::class, 'mentions'],
    '/cgv'                       => [Controller\PageController::class, 'cgv'],
    '/confidentialite'           => [Controller\PageController::class, 'privacy'],
    // Espace utilisateur
    '/espace'                    => [Controller\AccountController::class, 'index'],
    '/espace/commande'           => [Controller\AccountController::class, 'order'],
    '/espace/commande/modifier'  => [Controller\AccountController::class, 'edit'],
    '/espace/commande/annuler'   => [Controller\AccountController::class, 'cancel'],
    '/espace/avis'               => [Controller\AccountController::class, 'review'],
    '/espace/profil'             => [Controller\AccountController::class, 'profile'],
    // Espace employé
    '/gestion'                   => [Controller\StaffController::class, 'orders'],
    '/gestion/commande'          => [Controller\StaffController::class, 'order'],
    '/gestion/commande/statut'   => [Controller\StaffController::class, 'updateStatus'],
    '/gestion/commande/annuler'  => [Controller\StaffController::class, 'cancel'],
    '/gestion/commande/modifier' => [Controller\StaffController::class, 'edit'],
    '/gestion/menus'             => [Controller\StaffController::class, 'menus'],
    '/gestion/menu'              => [Controller\StaffController::class, 'menuForm'],
    '/gestion/menu/supprimer'    => [Controller\StaffController::class, 'menuDelete'],
    '/gestion/image/supprimer'   => [Controller\StaffController::class, 'imageDelete'],
    '/gestion/referentiel'       => [Controller\StaffController::class, 'addRef'],
    '/gestion/plats'             => [Controller\StaffController::class, 'plats'],
    '/gestion/horaires'          => [Controller\StaffController::class, 'horaires'],
    '/gestion/avis'              => [Controller\StaffController::class, 'avis'],
    // Espace administrateur
    '/admin'                     => [Controller\AdminController::class, 'index'],
    '/admin/employes'            => [Controller\AdminController::class, 'employees'],
    '/admin/statistiques'        => [Controller\AdminController::class, 'stats'],
    '/api/statistiques'          => [Controller\AdminController::class, 'statsApi'],
];

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$path = rtrim($path, '/') ?: '/';

// Serveur PHP intégré : laisse passer les fichiers statiques
if (PHP_SAPI === 'cli-server' && $path !== '/' && is_file(__DIR__ . $path)) {
    return false;
}

try {
    if (!isset($routes[$path])) {
        abort(404);
    }
    [$class, $method] = $routes[$path];
    (new $class())->$method();
} catch (PDOException $e) {
    error_log('[DB] ' . $e->getMessage());
    http_response_code(500);
    App\Core\View::render('pages/error', [
        'title' => 'Erreur',
        'code' => 500,
        'message' => env('APP_ENV') === 'prod'
            ? 'Une erreur technique est survenue. Merci de réessayer plus tard.'
            : 'Erreur base de données : ' . $e->getMessage(),
    ]);
}
