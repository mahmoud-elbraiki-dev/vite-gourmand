<section class="section auth">
    <div class="container">
        <div class="auth-card">
            <h1 class="page-title">Connexion</h1>
            <p>Accès clients, employés et administrateur.</p>
            <form method="post" action="<?= url('/connexion') ?>" novalidate>
                <?= App\Core\Csrf::field() ?>
                <div class="mb-3">
                    <label class="form-label" for="email">Adresse e-mail</label>
                    <input class="form-control" type="email" id="email" name="email" required autocomplete="username" value="<?= e(old('email')) ?>">
                </div>
                <div class="mb-2">
                    <label class="form-label" for="password">Mot de passe</label>
                    <input class="form-control" type="password" id="password" name="password" required autocomplete="current-password">
                </div>
                <p class="text-end"><a href="<?= url('/mot-de-passe-oublie') ?>">Mot de passe oublié ?</a></p>
                <button class="btn btn-brand btn-lg w-100" type="submit">Se connecter</button>
            </form>
            <hr>
            <p class="mb-0 text-center">Pas encore de compte ? <a href="<?= url('/inscription') ?>">Créer un compte</a></p>
        </div>
    </div>
</section>
<?php clear_old(); ?>
