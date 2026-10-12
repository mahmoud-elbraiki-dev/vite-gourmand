<?php use App\Service\OrderStatus; ?>
<section class="page-head">
    <div class="container">
        <p class="eyebrow">Mon espace</p>
        <h1 class="page-title">Bonjour <?= e(App\Core\Auth::user()['prenom']) ?></h1>
        <?php App\Core\View::partial('partials/account-nav'); ?>
    </div>
</section>
<section class="section pt-4">
    <div class="container">
        <h2 class="section-title">Mes commandes</h2>
        <?php if (!$commandes): ?>
            <div class="empty-state">
                <p>Vous n'avez pas encore passé de commande.</p>
                <a class="btn btn-brand" href="<?= url('/menus') ?>">Découvrir nos menus</a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-brand align-middle">
                    <caption class="visually-hidden">Liste de mes commandes</caption>
                    <thead><tr><th scope="col">N°</th><th scope="col">Menu</th><th scope="col">Prestation</th><th scope="col">Total</th><th scope="col">Statut</th><th scope="col"><span class="visually-hidden">Actions</span></th></tr></thead>
                    <tbody>
                    <?php foreach ($commandes as $c): ?>
                        <tr>
                            <td><?= e($c['numero_commande']) ?></td>
                            <td><?= e($c['menu_titre']) ?><br><small><?= (int) $c['nombre_personne'] ?> pers.</small></td>
                            <td><?= date_fr($c['date_prestation']) ?></td>
                            <td><?= euros($c['total']) ?></td>
                            <td><span class="status <?= OrderStatus::badge($c['statut']) ?>"><?= e($c['statut']) ?></span></td>
                            <td class="text-end"><a class="btn btn-sm btn-outline-brand" href="<?= url('/espace/commande', ['numero' => $c['numero_commande']]) ?>">Détail<span class="visually-hidden"> de la commande <?= e($c['numero_commande']) ?></span></a></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</section>
