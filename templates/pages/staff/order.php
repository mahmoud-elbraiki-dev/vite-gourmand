<?php
use App\Core\Csrf;
use App\Service\OrderStatus;
App\Core\View::partial('partials/staff-head', ['heading' => 'Commande ' . $c['numero_commande']]);
$closed = in_array($c['statut'], [OrderStatus::ANNULEE, OrderStatus::TERMINEE], true);
?>
<section class="section pt-4">
    <div class="container">
        <p><a class="link-back" href="<?= url('/gestion') ?>">Retour aux commandes</a></p>
        <div class="row g-4">
            <div class="col-lg-7">
                <div class="form-card">
                    <h2 class="form-card-title">Client</h2>
                    <p class="mb-0"><strong><?= e($c['prenom'] . ' ' . $c['nom']) ?></strong><br>
                        GSM : <a href="tel:<?= e(preg_replace('/\s+/', '', $c['telephone'])) ?>"><?= e($c['telephone']) ?></a> ·
                        <a href="mailto:<?= e($c['email']) ?>"><?= e($c['email']) ?></a></p>
                </div>
                <div class="form-card">
                    <h2 class="form-card-title">Commande</h2>
                    <?php App\Core\View::partial('partials/order-summary', ['c' => $c]); ?>
                </div>

                <?php if (!$closed): ?>
                    <details class="form-card">
                        <summary class="form-card-title">Modifier la commande (après contact client)</summary>
                        <form method="post" action="<?= url('/gestion/commande/modifier') ?>" class="mt-3">
                            <?= Csrf::field() ?><input type="hidden" name="numero" value="<?= e($c['numero_commande']) ?>">
                            <?php App\Core\View::partial('partials/order-fields', ['c' => $c]); ?>
                            <?php App\Core\View::partial('partials/contact-fields', ['prefix' => 'm']); ?>
                            <button class="btn btn-brand mt-3" type="submit">Enregistrer la modification</button>
                        </form>
                    </details>
                    <details class="form-card">
                        <summary class="form-card-title text-danger-brand">Annuler la commande (après contact client)</summary>
                        <form method="post" action="<?= url('/gestion/commande/annuler') ?>" class="mt-3 js-confirm" data-confirm="Confirmer l'annulation de cette commande ?">
                            <?= Csrf::field() ?><input type="hidden" name="numero" value="<?= e($c['numero_commande']) ?>">
                            <?php App\Core\View::partial('partials/contact-fields', ['prefix' => 'a']); ?>
                            <button class="btn btn-danger-soft mt-3" type="submit">Annuler la commande</button>
                        </form>
                    </details>
                <?php endif; ?>
            </div>

            <div class="col-lg-5">
                <?php if (!$closed): ?>
                    <form method="post" action="<?= url('/gestion/commande/statut') ?>" class="form-card">
                        <?= Csrf::field() ?><input type="hidden" name="numero" value="<?= e($c['numero_commande']) ?>">
                        <h2 class="form-card-title">Mettre à jour le statut</h2>
                        <label class="form-label" for="statut">Nouveau statut</label>
                        <select class="form-select mb-3" id="statut" name="statut" required>
                            <?php foreach (OrderStatus::WORKFLOW as $s): ?>
                                <option value="<?= e($s) ?>" <?= $c['statut'] === $s ? 'selected' : '' ?>><?= e(ucfirst($s)) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="pret_materiel" name="pret_materiel" <?= (int) $c['pret_materiel'] ? 'checked' : '' ?>>
                            <label class="form-check-label" for="pret_materiel">Du matériel a été prêté au client</label>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="restitution_materiel" name="restitution_materiel" <?= (int) $c['restitution_materiel'] ? 'checked' : '' ?>>
                            <label class="form-check-label" for="restitution_materiel">Le matériel a été restitué</label>
                        </div>
                        <p class="small">« En attente du retour de matériel » envoie automatiquement au client l'e-mail des 600 € de frais. « Terminée » l'invite à donner son avis.</p>
                        <button class="btn btn-brand w-100" type="submit">Mettre à jour</button>
                    </form>
                <?php endif; ?>
                <div class="form-card">
                    <h2 class="form-card-title">Historique</h2>
                    <?php App\Core\View::partial('partials/timeline', ['history' => $history]); ?>
                </div>
            </div>
        </div>
    </div>
</section>
