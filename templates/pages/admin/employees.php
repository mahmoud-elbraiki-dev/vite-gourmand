<?php use App\Core\Csrf; App\Core\View::partial('partials/staff-head', ['heading' => 'Comptes employés']); ?>
<section class="section pt-4">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-7">
                <div class="table-responsive">
                    <table class="table table-brand align-middle">
                        <caption class="visually-hidden">Comptes employés</caption>
                        <thead><tr><th scope="col">Employé</th><th scope="col">E-mail (identifiant)</th><th scope="col">État</th><th scope="col"><span class="visually-hidden">Actions</span></th></tr></thead>
                        <tbody>
                        <?php foreach ($employes as $u): $on = (int) $u['actif'] === 1; ?>
                            <tr>
                                <td><?= e(trim($u['prenom'] . ' ' . $u['nom'])) ?: '—' ?></td>
                                <td><?= e($u['email']) ?></td>
                                <td><span class="status <?= $on ? 'badge-done' : 'badge-cancel' ?>"><?= $on ? 'Actif' : 'Désactivé' ?></span></td>
                                <td class="text-end">
                                    <form method="post" action="<?= url('/admin/employes') ?>" class="js-confirm" data-confirm="<?= $on ? 'Désactiver ce compte ? L\'employé ne pourra plus se connecter.' : 'Réactiver ce compte ?' ?>">
                                        <?= Csrf::field() ?><input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="<?= (int) $u['utilisateur_id'] ?>"><input type="hidden" name="actif" value="<?= $on ? '0' : '1' ?>">
                                        <button class="btn btn-sm <?= $on ? 'btn-danger-soft' : 'btn-outline-brand' ?>" type="submit"><?= $on ? 'Désactiver' : 'Réactiver' ?><span class="visually-hidden"> <?= e($u['email']) ?></span></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="col-lg-5">
                <form method="post" action="<?= url('/admin/employes') ?>" class="form-card" novalidate>
                    <?= Csrf::field() ?>
                    <h2 class="form-card-title">Créer un compte employé</h2>
                    <div class="row g-3">
                        <div class="col-sm-6"><label class="form-label" for="prenom">Prénom</label><input class="form-control" id="prenom" name="prenom" value="<?= e(old('prenom')) ?>"></div>
                        <div class="col-sm-6"><label class="form-label" for="nom">Nom</label><input class="form-control" id="nom" name="nom" value="<?= e(old('nom')) ?>"></div>
                        <div class="col-12"><label class="form-label" for="email">E-mail (identifiant) *</label><input class="form-control" type="email" id="email" name="email" required value="<?= e(old('email')) ?>"></div>
                        <div class="col-12"><label class="form-label" for="password">Mot de passe *</label><input class="form-control js-password" type="password" id="password" name="password" required autocomplete="new-password" aria-describedby="pwd-rules"></div>
                    </div>
                    <ul class="pwd-rules mt-2" id="pwd-rules" aria-live="polite">
                        <li data-rule="len">10 caractères minimum</li><li data-rule="upper">Une majuscule</li>
                        <li data-rule="lower">Une minuscule</li><li data-rule="digit">Un chiffre</li><li data-rule="special">Un caractère spécial</li>
                    </ul>
                    <p class="small">L'employé reçoit un e-mail l'informant de la création de son compte, <strong>sans le mot de passe</strong> : communiquez-le-lui en main propre. Aucun compte administrateur ne peut être créé depuis l'application.</p>
                    <button class="btn btn-brand" type="submit">Créer le compte</button>
                </form>
            </div>
        </div>
    </div>
</section>
<?php clear_old(); ?>
