<section class="page-head">
    <div class="container">
        <p class="eyebrow">Mon espace</p>
        <h1 class="page-title">Mes informations</h1>
        <?php App\Core\View::partial('partials/account-nav'); ?>
    </div>
</section>
<section class="section pt-4">
    <div class="container">
        <div class="row g-4 g-lg-5">
            <div class="col-lg-7">
                <form method="post" action="<?= url('/espace/profil') ?>" class="form-card">
                    <?= App\Core\Csrf::field() ?>
                    <h2 class="form-card-title">Informations personnelles</h2>
                    <div class="row g-3">
                        <div class="col-sm-6"><label class="form-label" for="prenom">Prénom</label><input class="form-control" id="prenom" name="prenom" required autocomplete="given-name" value="<?= e($user['prenom']) ?>"></div>
                        <div class="col-sm-6"><label class="form-label" for="nom">Nom</label><input class="form-control" id="nom" name="nom" required autocomplete="family-name" value="<?= e($user['nom']) ?>"></div>
                        <div class="col-sm-6"><label class="form-label" for="email">E-mail</label><input class="form-control" type="email" id="email" name="email" required autocomplete="email" value="<?= e($user['email']) ?>"></div>
                        <div class="col-sm-6"><label class="form-label" for="telephone">GSM</label><input class="form-control" type="tel" id="telephone" name="telephone" required autocomplete="tel" value="<?= e($user['telephone']) ?>"></div>
                        <div class="col-12"><label class="form-label" for="adresse_postale">Adresse postale</label><input class="form-control" id="adresse_postale" name="adresse_postale" required autocomplete="street-address" value="<?= e($user['adresse_postale']) ?>"></div>
                        <div class="col-sm-6"><label class="form-label" for="ville">Ville</label><input class="form-control" id="ville" name="ville" required autocomplete="address-level2" value="<?= e($user['ville']) ?>"></div>
                        <div class="col-sm-6"><label class="form-label" for="pays">Pays</label><input class="form-control" id="pays" name="pays" autocomplete="country-name" value="<?= e($user['pays']) ?>"></div>
                    </div>
                    <button class="btn btn-brand mt-4" type="submit">Enregistrer</button>
                </form>
            </div>
            <div class="col-lg-5">
                <form method="post" action="<?= url('/espace/profil') ?>" class="form-card">
                    <?= App\Core\Csrf::field() ?>
                    <input type="hidden" name="action" value="password">
                    <h2 class="form-card-title">Changer de mot de passe</h2>
                    <div class="mb-3"><label class="form-label" for="current_password">Mot de passe actuel</label><input class="form-control" type="password" id="current_password" name="current_password" required autocomplete="current-password"></div>
                    <div class="mb-3"><label class="form-label" for="password">Nouveau mot de passe</label><input class="form-control js-password" type="password" id="password" name="password" required autocomplete="new-password" aria-describedby="pwd-rules"></div>
                    <div class="mb-3"><label class="form-label" for="password_confirm">Confirmation</label><input class="form-control" type="password" id="password_confirm" name="password_confirm" required autocomplete="new-password"></div>
                    <ul class="pwd-rules" id="pwd-rules" aria-live="polite">
                        <li data-rule="len">10 caractères minimum</li><li data-rule="upper">Une majuscule</li>
                        <li data-rule="lower">Une minuscule</li><li data-rule="digit">Un chiffre</li><li data-rule="special">Un caractère spécial</li>
                    </ul>
                    <button class="btn btn-outline-brand" type="submit">Mettre à jour</button>
                </form>
            </div>
        </div>
    </div>
</section>
