<section class="section auth">
    <div class="container">
        <div class="auth-card">
            <h1 class="page-title">Nouveau mot de passe</h1>
            <form method="post" action="<?= url('/reinitialiser') ?>">
                <?= App\Core\Csrf::field() ?>
                <input type="hidden" name="token" value="<?= e($token) ?>">
                <div class="mb-3">
                    <label class="form-label" for="password">Nouveau mot de passe</label>
                    <input class="form-control js-password" type="password" id="password" name="password" required minlength="10" autocomplete="new-password" aria-describedby="pwd-rules">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="password_confirm">Confirmation</label>
                    <input class="form-control" type="password" id="password_confirm" name="password_confirm" required autocomplete="new-password">
                </div>
                <ul class="pwd-rules" id="pwd-rules" aria-live="polite">
                    <li data-rule="len">10 caractères minimum</li><li data-rule="upper">Une majuscule</li>
                    <li data-rule="lower">Une minuscule</li><li data-rule="digit">Un chiffre</li><li data-rule="special">Un caractère spécial</li>
                </ul>
                <button class="btn btn-brand btn-lg w-100" type="submit">Enregistrer</button>
            </form>
        </div>
    </div>
</section>
