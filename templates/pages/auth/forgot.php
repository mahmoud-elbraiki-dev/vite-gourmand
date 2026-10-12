<section class="section auth">
    <div class="container">
        <div class="auth-card">
            <h1 class="page-title">Mot de passe oublié</h1>
            <p>Saisissez votre adresse e-mail : nous vous enverrons un lien pour choisir un nouveau mot de passe.</p>
            <form method="post" action="<?= url('/mot-de-passe-oublie') ?>">
                <?= App\Core\Csrf::field() ?>
                <div class="mb-3">
                    <label class="form-label" for="email">Adresse e-mail</label>
                    <input class="form-control" type="email" id="email" name="email" required autocomplete="email">
                </div>
                <button class="btn btn-brand btn-lg w-100" type="submit">Recevoir le lien</button>
            </form>
            <p class="mt-3 mb-0 text-center"><a href="<?= url('/connexion') ?>">Retour à la connexion</a></p>
        </div>
    </div>
</section>
