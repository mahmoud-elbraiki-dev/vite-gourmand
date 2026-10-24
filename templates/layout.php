<?php
use App\Core\Auth;
use App\Core\Csrf;
use App\Repository\RefRepository;

$current = rtrim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/', '/') ?: '/';
$isActive = fn (string $p) => ($p === '/' ? $current === '/' : str_starts_with($current, $p)) ? ' aria-current="page"' : '';
try {
    $horaires = (new RefRepository())->horaires();
} catch (\Throwable) {
    $horaires = [];
}
$authUser = Auth::user();
?><!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title ?? 'Vite & Gourmand') ?> — Vite &amp; Gourmand</title>
    <meta name="description" content="Vite & Gourmand, traiteur à Bordeaux depuis 25 ans : menus de fête, repas de famille et événements livrés chez vous.">
    <link href="<?= asset('assets/css/fonts.css') ?>" rel="stylesheet">
    <link href="<?= asset('assets/vendor/bootstrap/bootstrap.min.css') ?>" rel="stylesheet">
    <link href="<?= asset('assets/css/style.css') ?>" rel="stylesheet">
    <link rel="icon" href="<?= asset('assets/img/favicon.svg') ?>" type="image/svg+xml">
</head>
<body>
<a class="skip-link" href="#contenu">Aller au contenu principal</a>

<header class="site-header">
    <nav class="navbar navbar-expand-lg container" aria-label="Menu principal">
        <a class="brand" href="<?= url('/') ?>">
            <span class="brand-mark" aria-hidden="true">V&amp;G</span>
            <span class="brand-name">Vite <em>&amp;</em> Gourmand</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav"
                aria-controls="nav" aria-expanded="false" aria-label="Ouvrir le menu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="nav">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-1">
                <li class="nav-item"><a class="nav-link" href="<?= url('/') ?>"<?= $isActive('/') ?>>Accueil</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= url('/menus') ?>"<?= $isActive('/menu') ?>>Nos menus</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= url('/contact') ?>"<?= $isActive('/contact') ?>>Contact</a></li>
                <?php if ($authUser): ?>
                    <?php if (Auth::isAdmin()): ?>
                        <li class="nav-item"><a class="nav-link" href="<?= url('/admin') ?>"<?= $isActive('/admin') ?>>Administration</a></li>
                    <?php endif; ?>
                    <?php if (Auth::isStaff()): ?>
                        <li class="nav-item"><a class="nav-link" href="<?= url('/gestion') ?>"<?= $isActive('/gestion') ?>>Espace employé</a></li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link" href="<?= url('/espace') ?>"<?= $isActive('/espace') ?>>Mon espace</a></li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <form method="post" action="<?= url('/deconnexion') ?>" class="m-0">
                            <?= Csrf::field() ?>
                            <button class="btn btn-outline-brand btn-sm ms-lg-2" type="submit">
                                Déconnexion <span class="visually-hidden">de <?= e($authUser['prenom']) ?></span>
                            </button>
                        </form>
                    </li>
                <?php else: ?>
                    <li class="nav-item"><a class="btn btn-brand btn-sm ms-lg-2" href="<?= url('/connexion') ?>"<?= $isActive('/connexion') ?>>Connexion</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>
</header>

<main id="contenu" tabindex="-1">
    <?php $flashes = take_flashes(); if ($flashes): ?>
        <div class="container pt-4" role="status" aria-live="polite">
            <?php foreach ($flashes as $f): ?>
                <div class="alert alert-<?= $f['type'] === 'error' ? 'danger' : ($f['type'] === 'success' ? 'success' : 'info') ?>" <?= $f['type'] === 'error' ? 'role="alert"' : '' ?>>
                    <?= e($f['message']) ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php require $__template; ?>
</main>

<footer class="site-footer">
    <div class="container">
        <div class="row g-4 g-lg-5">
            <div class="col-lg-4">
                <p class="footer-brand">Vite <em>&amp;</em> Gourmand</p>
                <p>Julie et José cuisinent pour vos fêtes et événements depuis 25 ans, à Bordeaux et alentours.</p>
                <address class="mb-0">12 rue Sainte-Catherine, 33000 Bordeaux<br>
                    <a href="tel:+33556000000">05 56 00 00 00</a> · <a href="mailto:contact@vitegourmand.fr">contact@vitegourmand.fr</a>
                </address>
            </div>
            <div class="col-sm-6 col-lg-4">
                <h2 class="footer-title">Nos horaires</h2>
                <table class="hours">
                    <caption class="visually-hidden">Horaires d'ouverture du lundi au dimanche</caption>
                    <tbody>
                    <?php foreach ($horaires as $h): ?>
                        <tr>
                            <th scope="row"><?= e($h['jour']) ?></th>
                            <td><?= $h['heure_ouverture'] ? e($h['heure_ouverture']) . ' – ' . e($h['heure_fermeture']) : 'Fermé' ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="col-sm-6 col-lg-4">
                <h2 class="footer-title">Informations</h2>
                <ul class="list-unstyled footer-links">
                    <li><a href="<?= url('/mentions-legales') ?>">Mentions légales</a></li>
                    <li><a href="<?= url('/cgv') ?>">Conditions générales de vente</a></li>
                    <li><a href="<?= url('/confidentialite') ?>">Politique de confidentialité</a></li>
                    <li><a href="<?= url('/contact') ?>">Nous contacter</a></li>
                </ul>
            </div>
        </div>
        <p class="footer-copy">© <?= date('Y') ?> Vite &amp; Gourmand — Réalisé par FastDev</p>
    </div>
</footer>

<script src="<?= asset('assets/vendor/bootstrap/bootstrap.bundle.min.js') ?>"></script>
<script type="module" src="<?= asset('assets/js/app.js') ?>"></script>
<?php foreach (($scripts ?? []) as $s): ?>
    <?php /* Nos scripts sont des modules ES (import/export) ; les bibliothèques tierces restent des scripts classiques */ ?>
    <script<?= str_contains($s, '/assets/js/') ? ' type="module"' : '' ?> src="<?= e($s) ?>"></script>
<?php endforeach; ?>
</body>
</html>
