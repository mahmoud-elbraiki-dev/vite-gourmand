<section class="page-head">
    <div class="container">
        <p class="eyebrow"><a href="<?= url('/espace/commande', ['numero' => $c['numero_commande']]) ?>">Commande <?= e($c['numero_commande']) ?></a></p>
        <h1 class="page-title">Modifier ma commande</h1>
    </div>
</section>
<section class="section pt-4">
    <div class="container container-narrow">
        <form method="post" action="<?= url('/espace/commande/modifier') ?>" class="form-card">
            <?= App\Core\Csrf::field() ?>
            <input type="hidden" name="numero" value="<?= e($c['numero_commande']) ?>">
            <p>Menu : <strong><?= e($c['menu_titre']) ?></strong> <small>(non modifiable)</small></p>
            <?php App\Core\View::partial('partials/order-fields', ['c' => $c]); ?>
            <p class="small mt-3">Le prix est recalculé automatiquement (réduction de 10 % dès <?= (int) $c['nombre_personne_minimum'] + 5 ?> personnes, livraison hors Bordeaux : 5 € + 0,59 €/km).</p>
            <button class="btn btn-brand" type="submit">Enregistrer les modifications</button>
        </form>
    </div>
</section>
