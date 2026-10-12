<?php
use App\Core\Csrf;
App\Core\View::partial('partials/staff-head', ['heading' => $menu ? 'Modifier un menu' : 'Nouveau menu']);
$selected = $menu ? array_column($menu['plats'], 'plat_id') : [];
$labels = ['entree' => 'Entrées', 'plat' => 'Plats', 'dessert' => 'Desserts'];
?>
<section class="section pt-4">
    <div class="container">
        <p><a class="link-back" href="<?= url('/gestion/menus') ?>">Retour aux menus</a></p>
        <form method="post" action="<?= url('/gestion/menu') ?>" enctype="multipart/form-data" class="row g-4">
            <?= Csrf::field() ?>
            <input type="hidden" name="menu_id" value="<?= (int) ($menu['menu_id'] ?? 0) ?>">
            <div class="col-lg-7">
                <div class="form-card">
                    <h2 class="form-card-title">Informations</h2>
                    <div class="row g-3">
                        <div class="col-12"><label class="form-label" for="titre">Titre *</label><input class="form-control" id="titre" name="titre" required maxlength="100" value="<?= e($menu['titre'] ?? '') ?>"></div>
                        <div class="col-12"><label class="form-label" for="description">Description *</label><textarea class="form-control" id="description" name="description" rows="3" required><?= e($menu['description'] ?? '') ?></textarea></div>
                        <div class="col-12"><label class="form-label" for="conditions">Conditions (délai de commande, conservation…) *</label><textarea class="form-control" id="conditions" name="conditions" rows="3" required><?= e($menu['conditions'] ?? '') ?></textarea></div>
                        <div class="col-sm-6"><label class="form-label" for="theme_id">Thème</label>
                            <select class="form-select" id="theme_id" name="theme_id"><?php foreach ($themes as $t): ?><option value="<?= (int) $t['theme_id'] ?>" <?= ($menu['theme_id'] ?? 0) == $t['theme_id'] ? 'selected' : '' ?>><?= e($t['libelle']) ?></option><?php endforeach; ?></select></div>
                        <div class="col-sm-6"><label class="form-label" for="regime_id">Régime</label>
                            <select class="form-select" id="regime_id" name="regime_id"><?php foreach ($regimes as $r): ?><option value="<?= (int) $r['regime_id'] ?>" <?= ($menu['regime_id'] ?? 0) == $r['regime_id'] ? 'selected' : '' ?>><?= e($r['libelle']) ?></option><?php endforeach; ?></select></div>
                        <div class="col-sm-4"><label class="form-label" for="nombre_personne_minimum">Personnes minimum *</label><input class="form-control" type="number" min="1" id="nombre_personne_minimum" name="nombre_personne_minimum" required value="<?= (int) ($menu['nombre_personne_minimum'] ?? 4) ?>"></div>
                        <div class="col-sm-4"><label class="form-label" for="prix_par_personne">Prix par personne (€) *</label><input class="form-control" type="number" min="0" step="0.01" id="prix_par_personne" name="prix_par_personne" required value="<?= e($menu['prix_par_personne'] ?? '') ?>"></div>
                        <div class="col-sm-4"><label class="form-label" for="quantite_restante">Stock (commandes) *</label><input class="form-control" type="number" min="0" id="quantite_restante" name="quantite_restante" required value="<?= (int) ($menu['quantite_restante'] ?? 10) ?>"></div>
                        <div class="col-12 form-check ms-1"><input class="form-check-input" type="checkbox" id="actif" name="actif" <?= !$menu || (int) $menu['actif'] ? 'checked' : '' ?>><label class="form-check-label" for="actif">Menu visible sur le site</label></div>
                    </div>
                </div>
                <div class="form-card">
                    <h2 class="form-card-title">Galerie d'images</h2>
                    <?php if ($menu && $menu['images']): ?>
                        <ul class="admin-gallery">
                            <?php foreach ($menu['images'] as $img): ?>
                                <li><img src="<?= asset($img['chemin']) ?>" alt="<?= e($img['alt']) ?>" width="140" height="94">
                                    <button class="btn btn-sm btn-danger-soft" type="submit" form="del-img-<?= (int) $img['image_id'] ?>">Retirer<span class="visually-hidden"> l'image <?= e($img['alt']) ?></span></button></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                    <div class="row g-3">
                        <div class="col-sm-6"><label class="form-label" for="image">Ajouter une image (JPG, PNG, WebP — 2 Mo max.)</label><input class="form-control" type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp"></div>
                        <div class="col-sm-6"><label class="form-label" for="image_alt">Texte alternatif de l'image</label><input class="form-control" id="image_alt" name="image_alt" maxlength="150"></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <fieldset class="form-card">
                    <legend class="form-card-title">Plats proposés *</legend>
                    <p class="small">Un même plat peut figurer dans plusieurs menus. <a href="<?= url('/gestion/plats') ?>">Gérer les plats</a></p>
                    <?php foreach ($labels as $type => $label): ?>
                        <p class="course-title mt-3 mb-1"><?= $label ?></p>
                        <?php foreach ($plats as $p): if ($p['type_plat'] !== $type) continue; ?>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="plats[]" value="<?= (int) $p['plat_id'] ?>" id="plat-<?= (int) $p['plat_id'] ?>" <?= in_array($p['plat_id'], $selected) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="plat-<?= (int) $p['plat_id'] ?>"><?= e($p['titre_plat']) ?></label>
                            </div>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </fieldset>
                <button class="btn btn-brand btn-lg w-100" type="submit">Enregistrer le menu</button>
            </div>
        </form>
        <?php if ($menu): foreach ($menu['images'] as $img): ?>
            <form method="post" action="<?= url('/gestion/image/supprimer') ?>" id="del-img-<?= (int) $img['image_id'] ?>" hidden>
                <?= Csrf::field() ?><input type="hidden" name="image_id" value="<?= (int) $img['image_id'] ?>"><input type="hidden" name="menu_id" value="<?= (int) $menu['menu_id'] ?>">
            </form>
        <?php endforeach; endif; ?>
    </div>
</section>
