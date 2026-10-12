<?php
$cur = (string) parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
$links = [
    '/gestion' => 'Commandes',
    '/gestion/menus' => 'Menus',
    '/gestion/plats' => 'Plats',
    '/gestion/horaires' => 'Horaires',
    '/gestion/avis' => 'Avis',
];
if (App\Core\Auth::isAdmin()) {
    $links['/admin/employes'] = 'Employés';
    $links['/admin/statistiques'] = 'Statistiques';
}
$active = function (string $href) use ($cur): bool {
    if ($href === '/gestion') {
        return $cur === '/gestion' || str_starts_with($cur, '/gestion/commande');
    }
    if ($href === '/gestion/menus') {
        return str_starts_with($cur, '/gestion/menu');
    }
    return str_starts_with($cur, $href);
};
?>
<nav class="subnav" aria-label="<?= App\Core\Auth::isAdmin() ? 'Administration' : 'Espace employé' ?>">
    <?php foreach ($links as $href => $label): ?>
        <a href="<?= url($href) ?>" <?= $active($href) ? 'aria-current="page"' : '' ?>><?= e($label) ?></a>
    <?php endforeach; ?>
</nav>
