<?php App\Core\View::partial('partials/staff-head', ['heading' => 'Horaires d\'ouverture']); ?>
<section class="section pt-4">
    <div class="container container-narrow">
        <form method="post" action="<?= url('/gestion/horaires') ?>" class="form-card">
            <?= App\Core\Csrf::field() ?>
            <p>Ces horaires sont affichés dans le pied de page de toutes les pages.</p>
            <table class="table table-brand align-middle">
                <caption class="visually-hidden">Horaires du lundi au dimanche</caption>
                <thead><tr><th scope="col">Jour</th><th scope="col">Ouverture</th><th scope="col">Fermeture</th><th scope="col">Fermé</th></tr></thead>
                <tbody>
                <?php foreach ($horaires as $h): $id = (int) $h['horaire_id']; ?>
                    <tr>
                        <th scope="row"><?= e($h['jour']) ?></th>
                        <td><label class="visually-hidden" for="o-<?= $id ?>">Ouverture <?= e($h['jour']) ?></label><input class="form-control" type="time" id="o-<?= $id ?>" name="horaires[<?= $id ?>][ouverture]" value="<?= e($h['heure_ouverture']) ?>"></td>
                        <td><label class="visually-hidden" for="f-<?= $id ?>">Fermeture <?= e($h['jour']) ?></label><input class="form-control" type="time" id="f-<?= $id ?>" name="horaires[<?= $id ?>][fermeture]" value="<?= e($h['heure_fermeture']) ?>"></td>
                        <td><input class="form-check-input" type="checkbox" id="c-<?= $id ?>" name="horaires[<?= $id ?>][ferme]" <?= $h['heure_ouverture'] ? '' : 'checked' ?>><label class="visually-hidden" for="c-<?= $id ?>"><?= e($h['jour']) ?> fermé</label></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <button class="btn btn-brand" type="submit">Enregistrer les horaires</button>
        </form>
    </div>
</section>
