<?php
use App\Core\Csrf;
App\Core\View::partial('partials/staff-head', ['heading' => 'Plats et allergènes']);
$types = ['entree' => 'Entrée', 'plat' => 'Plat', 'dessert' => 'Dessert'];
$editAll = $edit ? array_column($edit['allergenes'], 'allergene_id') : [];
?>
<section class="section pt-4">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-7">
                <div class="table-responsive">
                    <table class="table table-brand align-middle">
                        <caption class="visually-hidden">Liste des plats</caption>
                        <thead><tr><th scope="col">Plat</th><th scope="col">Type</th><th scope="col">Allergènes</th><th scope="col"><span class="visually-hidden">Actions</span></th></tr></thead>
                        <tbody>
                        <?php foreach ($plats as $p): ?>
                            <tr>
                                <td><?= e($p['titre_plat']) ?></td>
                                <td><?= $types[$p['type_plat']] ?></td>
                                <td><?= $p['allergenes'] ? e(implode(', ', array_column($p['allergenes'], 'libelle'))) : '—' ?></td>
                                <td class="text-end text-nowrap">
                                    <a class="btn btn-sm btn-outline-brand" href="<?= url('/gestion/plats', ['id' => $p['plat_id']]) ?>">Modifier<span class="visually-hidden"> <?= e($p['titre_plat']) ?></span></a>
                                    <form method="post" action="<?= url('/gestion/plats') ?>" class="d-inline js-confirm" data-confirm="Supprimer ce plat de tous les menus ?">
                                        <?= Csrf::field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="plat_id" value="<?= (int) $p['plat_id'] ?>">
                                        <button class="btn btn-sm btn-danger-soft" type="submit">Supprimer<span class="visually-hidden"> <?= e($p['titre_plat']) ?></span></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="col-lg-5">
                <form method="post" action="<?= url('/gestion/plats') ?>" enctype="multipart/form-data" class="form-card">
                    <?= Csrf::field() ?><input type="hidden" name="plat_id" value="<?= (int) ($edit['plat_id'] ?? 0) ?>">
                    <h2 class="form-card-title"><?= $edit ? 'Modifier le plat' : 'Nouveau plat' ?></h2>
                    <div class="mb-3"><label class="form-label" for="titre_plat">Titre *</label><input class="form-control" id="titre_plat" name="titre_plat" required maxlength="100" value="<?= e($edit['titre_plat'] ?? '') ?>"></div>
                    <div class="mb-3"><label class="form-label" for="type_plat">Type *</label>
                        <select class="form-select" id="type_plat" name="type_plat"><?php foreach ($types as $k => $v): ?><option value="<?= $k ?>" <?= ($edit['type_plat'] ?? '') === $k ? 'selected' : '' ?>><?= $v ?></option><?php endforeach; ?></select></div>
                    <div class="mb-3"><label class="form-label" for="photo">Photo (JPG, PNG, WebP — 2 Mo max.)</label><input class="form-control" type="file" id="photo" name="photo" accept="image/jpeg,image/png,image/webp"></div>
                    <fieldset class="mb-3">
                        <legend class="form-label">Allergènes</legend>
                        <div class="allergen-grid">
                            <?php foreach ($allergenes as $a): ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="allergenes[]" value="<?= (int) $a['allergene_id'] ?>" id="al-<?= (int) $a['allergene_id'] ?>" <?= in_array($a['allergene_id'], $editAll) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="al-<?= (int) $a['allergene_id'] ?>"><?= e($a['libelle']) ?></label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </fieldset>
                    <button class="btn btn-brand" type="submit">Enregistrer</button>
                    <?php if ($edit): ?><a class="btn btn-link" href="<?= url('/gestion/plats') ?>">Annuler</a><?php endif; ?>
                </form>
            </div>
        </div>
    </div>
</section>
