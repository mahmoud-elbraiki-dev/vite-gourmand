<?php use App\Core\Csrf; App\Core\View::partial('partials/staff-head', ['heading' => 'Menus']); ?>
<section class="section pt-4">
    <div class="container">
        <div class="d-flex flex-wrap justify-content-between gap-3 mb-3">
            <p class="result-count mb-0"><?= count($menus) ?> menu(s)</p>
            <a class="btn btn-brand" href="<?= url('/gestion/menu') ?>">+ Nouveau menu</a>
        </div>
        <div class="table-responsive">
            <table class="table table-brand align-middle">
                <caption class="visually-hidden">Liste des menus</caption>
                <thead><tr><th scope="col">Menu</th><th scope="col">Thème / régime</th><th scope="col">Min.</th><th scope="col">Prix/pers.</th><th scope="col">Stock</th><th scope="col">État</th><th scope="col"><span class="visually-hidden">Actions</span></th></tr></thead>
                <tbody>
                <?php foreach ($menus as $m): ?>
                    <tr>
                        <td><strong><?= e($m['titre']) ?></strong></td>
                        <td><?= e($m['theme']) ?> · <?= e($m['regime']) ?></td>
                        <td><?= (int) $m['nombre_personne_minimum'] ?></td>
                        <td><?= euros($m['prix_par_personne']) ?></td>
                        <td><?= (int) $m['quantite_restante'] ?></td>
                        <td><?= (int) $m['actif'] ? '<span class="status badge-done">En ligne</span>' : '<span class="status badge-cancel">Désactivé</span>' ?></td>
                        <td class="text-end text-nowrap">
                            <a class="btn btn-sm btn-outline-brand" href="<?= url('/gestion/menu', ['id' => $m['menu_id']]) ?>">Modifier<span class="visually-hidden"> <?= e($m['titre']) ?></span></a>
                            <form method="post" action="<?= url('/gestion/menu/supprimer') ?>" class="d-inline js-confirm" data-confirm="Supprimer ce menu ?">
                                <?= Csrf::field() ?><input type="hidden" name="menu_id" value="<?= (int) $m['menu_id'] ?>">
                                <button class="btn btn-sm btn-danger-soft" type="submit">Supprimer<span class="visually-hidden"> <?= e($m['titre']) ?></span></button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="row g-4 mt-2">
            <?php foreach (['regime' => 'Ajouter un régime', 'theme' => 'Ajouter un thème'] as $type => $label): ?>
                <div class="col-md-6">
                    <form method="post" action="<?= url('/gestion/referentiel') ?>" class="form-card d-flex gap-2 align-items-end">
                        <?= Csrf::field() ?><input type="hidden" name="type" value="<?= $type ?>">
                        <div class="flex-grow-1">
                            <label class="form-label" for="ref-<?= $type ?>"><?= $label ?></label>
                            <input class="form-control" id="ref-<?= $type ?>" name="libelle" maxlength="50" required>
                        </div>
                        <button class="btn btn-outline-brand" type="submit">Ajouter</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
