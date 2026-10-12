<?php $cur = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH); ?>
<nav class="subnav" aria-label="Mon espace">
    <a href="<?= url('/espace') ?>" <?= $cur === '/espace' || str_starts_with((string) $cur, '/espace/commande') ? 'aria-current="page"' : '' ?>>Mes commandes</a>
    <a href="<?= url('/espace/profil') ?>" <?= $cur === '/espace/profil' ? 'aria-current="page"' : '' ?>>Mes informations</a>
    <a href="<?= url('/menus') ?>">Commander</a>
</nav>
