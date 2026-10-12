<?php
use App\Core\Csrf;
App\Core\View::partial('partials/staff-head', ['heading' => 'Avis clients']);
$labels = ['en attente' => ['À modérer', 'badge-wait'], 'valide' => ['Validé', 'badge-done'], 'refuse' => ['Refusé', 'badge-cancel']];
?>
<section class="section pt-4">
    <div class="container">
        <p>Seuls les avis validés apparaissent sur la page d'accueil.</p>
        <?php if (!$avis): ?><p class="empty-state">Aucun avis pour le moment.</p><?php endif; ?>
        <div class="row g-4">
            <?php foreach ($avis as $a): ?>
                <div class="col-md-6 col-lg-4">
                    <article class="review review-admin">
                        <p class="d-flex justify-content-between align-items-center">
                            <span class="stars" aria-label="Note : <?= (int) $a['note'] ?> sur 5"><?= str_repeat('★', (int) $a['note']) ?><span class="stars-off"><?= str_repeat('★', 5 - (int) $a['note']) ?></span></span>
                            <span class="status <?= $labels[$a['statut']][1] ?? '' ?>"><?= $labels[$a['statut']][0] ?? e($a['statut']) ?></span>
                        </p>
                        <p><?= e($a['description']) ?></p>
                        <p class="small mb-3"><?= e($a['prenom'] . ' ' . $a['nom']) ?> · <?= e($a['menu_titre']) ?> · <?= date_fr($a['date_avis']) ?></p>
                        <div class="d-flex gap-2">
                            <?php foreach (['valide' => 'Valider', 'refuse' => 'Refuser'] as $s => $l): if ($a['statut'] === $s) continue; ?>
                                <form method="post" action="<?= url('/gestion/avis') ?>">
                                    <?= Csrf::field() ?><input type="hidden" name="avis_id" value="<?= (int) $a['avis_id'] ?>"><input type="hidden" name="statut" value="<?= $s ?>">
                                    <button class="btn btn-sm <?= $s === 'valide' ? 'btn-brand' : 'btn-danger-soft' ?>" type="submit"><?= $l ?><span class="visually-hidden"> l'avis de <?= e($a['prenom']) ?></span></button>
                                </form>
                            <?php endforeach; ?>
                        </div>
                    </article>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
