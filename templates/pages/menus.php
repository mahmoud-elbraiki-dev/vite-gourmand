<section class="page-head">
    <div class="container">
        <p class="eyebrow">La carte</p>
        <h1 class="page-title">Tous nos menus</h1>
        <p class="lead mb-0">Filtrez par budget, thème, régime ou nombre de convives : la liste se met à jour instantanément.</p>
    </div>
</section>

<section class="section pt-4">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-3">
                <form id="menu-filters" class="filters" action="<?= url('/menus') ?>" method="get" aria-label="Filtrer les menus">
                    <h2 class="filters-title">Filtres</h2>
                    <fieldset class="mb-3">
                        <legend class="form-label">Prix (€)</legend>
                        <div class="d-flex gap-2 align-items-center">
                            <label class="visually-hidden" for="prix_min">Prix minimum</label>
                            <input class="form-control" type="number" min="0" step="10" id="prix_min" name="prix_min" placeholder="De" value="<?= e($filters['prix_min']) ?>">
                            <span aria-hidden="true">–</span>
                            <label class="visually-hidden" for="prix_max">Prix maximum</label>
                            <input class="form-control" type="number" min="0" step="10" id="prix_max" name="prix_max" placeholder="À" value="<?= e($filters['prix_max']) ?>">
                        </div>
                        <p class="form-text">Prix maximum seul ou fourchette, calculé pour le minimum de personnes.</p>
                    </fieldset>
                    <div class="mb-3">
                        <label class="form-label" for="theme">Thème</label>
                        <select class="form-select" id="theme" name="theme">
                            <option value="">Tous les thèmes</option>
                            <?php foreach ($themes as $t): ?>
                                <option value="<?= (int) $t['theme_id'] ?>" <?= $filters['theme'] == $t['theme_id'] ? 'selected' : '' ?>><?= e($t['libelle']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="regime">Régime</label>
                        <select class="form-select" id="regime" name="regime">
                            <option value="">Tous les régimes</option>
                            <?php foreach ($regimes as $r): ?>
                                <option value="<?= (int) $r['regime_id'] ?>" <?= $filters['regime'] == $r['regime_id'] ? 'selected' : '' ?>><?= e($r['libelle']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="personnes">Nombre de convives</label>
                        <input class="form-control" type="number" min="1" id="personnes" name="personnes" value="<?= e($filters['personnes']) ?>">
                        <p class="form-text">Affiche les menus dont le minimum requis est compatible.</p>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-brand flex-grow-1 js-hide" type="submit">Filtrer</button>
                        <a class="btn btn-outline-brand flex-grow-1" href="<?= url('/menus') ?>" id="reset-filters">Réinitialiser</a>
                    </div>
                </form>
            </div>
            <div class="col-lg-9">
                <p id="menu-count" class="result-count" role="status" aria-live="polite"><?= count($menus) ?> menu<?= count($menus) > 1 ? 's' : '' ?> trouvé<?= count($menus) > 1 ? 's' : '' ?></p>
                <div id="menu-list" class="row g-4">
                    <?php foreach ($menus as $m): ?>
                        <div class="col-md-6 col-xl-4"><?php App\Core\View::partial('partials/menu-card', ['m' => $m]); ?></div>
                    <?php endforeach; ?>
                </div>
                <p id="menu-empty" class="empty-state" <?= $menus ? 'hidden' : '' ?>>Aucun menu ne correspond à ces critères. Essayez d'élargir votre recherche.</p>
            </div>
        </div>
    </div>
</section>
<?php $scripts = [asset('assets/js/menus.js')]; ?>
