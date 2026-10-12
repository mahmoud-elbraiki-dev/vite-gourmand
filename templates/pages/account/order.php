<?php use App\Service\OrderStatus; use App\Core\Csrf; ?>
<section class="page-head">
    <div class="container">
        <p class="eyebrow"><a href="<?= url('/espace') ?>">Mes commandes</a></p>
        <h1 class="page-title">Commande <?= e($c['numero_commande']) ?></h1>
    </div>
</section>
<section class="section pt-4">
    <div class="container">
        <div class="row g-4 g-lg-5">
            <div class="col-lg-7">
                <div class="form-card">
                    <h2 class="form-card-title">Détail</h2>
                    <?php App\Core\View::partial('partials/order-summary', ['c' => $c]); ?>
                    <?php if (OrderStatus::editableByClient($c['statut'])): ?>
                        <div class="d-flex flex-wrap gap-2 mt-3">
                            <a class="btn btn-outline-brand" href="<?= url('/espace/commande/modifier', ['numero' => $c['numero_commande']]) ?>">Modifier</a>
                            <form method="post" action="<?= url('/espace/commande/annuler') ?>" class="js-confirm" data-confirm="Annuler définitivement cette commande ?">
                                <?= Csrf::field() ?><input type="hidden" name="numero" value="<?= e($c['numero_commande']) ?>">
                                <button class="btn btn-danger-soft" type="submit">Annuler la commande</button>
                            </form>
                        </div>
                        <p class="small mt-2 mb-0">Modification et annulation possibles tant que la commande n'a pas été acceptée par notre équipe. Le menu choisi ne peut pas être changé.</p>
                    <?php endif; ?>
                </div>

                <?php if ($c['statut'] === OrderStatus::TERMINEE): ?>
                    <div class="form-card">
                        <h2 class="form-card-title">Votre avis</h2>
                        <?php if ($c['avis_id']): ?>
                            <p class="mb-0">Merci, votre avis a bien été transmis.</p>
                        <?php else: ?>
                            <form method="post" action="<?= url('/espace/avis') ?>">
                                <?= Csrf::field() ?><input type="hidden" name="numero" value="<?= e($c['numero_commande']) ?>">
                                <fieldset class="rating mb-3">
                                    <legend class="form-label">Note *</legend>
                                    <?php for ($i = 5; $i >= 1; $i--): ?>
                                        <input type="radio" id="note-<?= $i ?>" name="note" value="<?= $i ?>" required>
                                        <label for="note-<?= $i ?>" title="<?= $i ?> sur 5">★<span class="visually-hidden"><?= $i ?> sur 5</span></label>
                                    <?php endfor; ?>
                                </fieldset>
                                <label class="form-label" for="description">Commentaire *</label>
                                <textarea class="form-control mb-3" id="description" name="description" rows="4" maxlength="500" required></textarea>
                                <button class="btn btn-brand" type="submit">Publier mon avis</button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="col-lg-5">
                <div class="form-card">
                    <h2 class="form-card-title">Suivi de la commande</h2>
                    <?php if ($c['statut'] === OrderStatus::EN_ATTENTE): ?>
                        <p>Votre commande est en attente de validation. Le suivi détaillé sera disponible dès qu'elle sera acceptée.</p>
                    <?php endif; ?>
                    <?php App\Core\View::partial('partials/timeline', ['history' => $history]); ?>
                </div>
            </div>
        </div>
    </div>
</section>
