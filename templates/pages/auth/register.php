<section class="section auth">
    <div class="container">
        <div class="auth-card auth-card-wide">
            <h1 class="page-title">Créer un compte</h1>
            <p>Les champs marqués d'un astérisque (*) sont obligatoires.</p>
            <form method="post" action="<?= url('/inscription') ?>" novalidate>
                <?= App\Core\Csrf::field() ?>
                <div class="row g-3">
                    <div class="col-sm-6"><label class="form-label" for="prenom">Prénom *</label><input class="form-control" id="prenom" name="prenom" required autocomplete="given-name" value="<?= e(old('prenom')) ?>"></div>
                    <div class="col-sm-6"><label class="form-label" for="nom">Nom *</label><input class="form-control" id="nom" name="nom" required autocomplete="family-name" value="<?= e(old('nom')) ?>"></div>
                    <div class="col-sm-6"><label class="form-label" for="email">Adresse e-mail *</label><input class="form-control" type="email" id="email" name="email" required autocomplete="email" value="<?= e(old('email')) ?>"></div>
                    <div class="col-sm-6"><label class="form-label" for="telephone">Numéro de GSM *</label><input class="form-control" type="tel" id="telephone" name="telephone" required autocomplete="tel" value="<?= e(old('telephone')) ?>"></div>
                    <div class="col-12"><label class="form-label" for="adresse_postale">Adresse postale *</label><input class="form-control" id="adresse_postale" name="adresse_postale" required autocomplete="street-address" value="<?= e(old('adresse_postale')) ?>"></div>
                    <div class="col-sm-6"><label class="form-label" for="ville">Ville *</label><input class="form-control" id="ville" name="ville" required autocomplete="address-level2" value="<?= e(old('ville')) ?>"></div>
                    <div class="col-sm-6"><label class="form-label" for="pays">Pays</label><input class="form-control" id="pays" name="pays" autocomplete="country-name" value="<?= e(old('pays', 'France')) ?>"></div>
                    <div class="col-sm-6">
                        <label class="form-label" for="password">Mot de passe *</label>
                        <input class="form-control js-password" type="password" id="password" name="password" required minlength="10" autocomplete="new-password" aria-describedby="pwd-rules">
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label" for="password_confirm">Confirmation *</label>
                        <input class="form-control" type="password" id="password_confirm" name="password_confirm" required autocomplete="new-password">
                    </div>
                    <div class="col-12">
                        <ul class="pwd-rules" id="pwd-rules" aria-live="polite">
                            <li data-rule="len">10 caractères minimum</li>
                            <li data-rule="upper">Une majuscule</li>
                            <li data-rule="lower">Une minuscule</li>
                            <li data-rule="digit">Un chiffre</li>
                            <li data-rule="special">Un caractère spécial</li>
                        </ul>
                    </div>
                    <div class="col-12 form-check ms-1">
                        <input class="form-check-input" type="checkbox" id="rgpd" name="rgpd" required>
                        <label class="form-check-label" for="rgpd">J'accepte que mes données soient utilisées pour gérer mes commandes, conformément à la <a href="<?= url('/confidentialite') ?>">politique de confidentialité</a>.</label>
                    </div>
                </div>
                <button class="btn btn-brand btn-lg w-100 mt-4" type="submit">Créer mon compte</button>
            </form>
            <p class="mt-3 mb-0 text-center">Déjà inscrit(e) ? <a href="<?= url('/connexion') ?>">Se connecter</a></p>
        </div>
    </div>
</section>
<?php clear_old(); ?>
