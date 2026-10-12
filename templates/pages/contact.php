<section class="page-head">
    <div class="container">
        <p class="eyebrow">Une question, un devis ?</p>
        <h1 class="page-title">Contactez-nous</h1>
    </div>
</section>
<section class="section pt-4">
    <div class="container">
        <div class="row g-4 g-lg-5">
            <div class="col-lg-7">
                <form method="post" action="<?= url('/contact') ?>" class="form-card" novalidate>
                    <?= App\Core\Csrf::field() ?>
                    <div class="hp" aria-hidden="true"><label for="site_web">Ne pas remplir</label><input id="site_web" name="site_web" tabindex="-1" autocomplete="off"></div>
                    <div class="mb-3">
                        <label class="form-label" for="titre">Titre de votre demande *</label>
                        <input class="form-control" id="titre" name="titre" required maxlength="120" value="<?= e(old('titre')) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="description">Description *</label>
                        <textarea class="form-control" id="description" name="description" rows="6" required minlength="10" maxlength="3000"><?= e(old('description')) ?></textarea>
                    </div>
                    <div class="mb-4">
                        <label class="form-label" for="email">Votre e-mail (pour vous répondre) *</label>
                        <input class="form-control" type="email" id="email" name="email" required autocomplete="email"
                               value="<?= e(old('email', App\Core\Auth::user()['email'] ?? '')) ?>">
                    </div>
                    <button class="btn btn-brand btn-lg" type="submit">Envoyer le message</button>
                </form>
            </div>
            <div class="col-lg-5">
                <div class="detail-panel">
                    <h2 class="h4">Vite &amp; Gourmand</h2>
                    <p>12 rue Sainte-Catherine<br>33000 Bordeaux</p>
                    <p><a href="tel:+33556000000">05 56 00 00 00</a><br><a href="mailto:contact@vitegourmand.fr">contact@vitegourmand.fr</a></p>
                    <p class="mb-0">Nous répondons sous 24 h ouvrées. Pour les événements de plus de 50 personnes, précisez la date et le lieu dans votre message.</p>
                </div>
            </div>
        </div>
    </div>
</section>
<?php clear_old(); ?>
