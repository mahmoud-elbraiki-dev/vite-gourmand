<section class="hero">
    <div class="container">
        <div class="row align-items-center g-4 g-lg-5">
            <div class="col-lg-6">
                <p class="eyebrow">Traiteur à Bordeaux depuis 25 ans</p>
                <h1 class="display-title">Des repas de fête faits maison, livrés chez vous.</h1>
                <p class="lead">Noël, Pâques, anniversaires ou séminaires : Julie et José composent des menus
                    généreux, de saison, et s'occupent de tout jusqu'à votre table.</p>
                <div class="d-flex flex-wrap gap-3 mt-4">
                    <a class="btn btn-brand btn-lg" href="<?= url('/menus') ?>">Découvrir nos menus</a>
                    <a class="btn btn-outline-brand btn-lg" href="<?= url('/contact') ?>">Demander un devis</a>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="hero-visual">
                    <img src="<?= asset('assets/img/accueil-chef.jpg') ?>" alt="Le chef dresse un pavé de poisson et ses légumes avant le service" width="600" height="400">
                    <?php if ($moyenne): ?>
                        <div class="hero-badge"><strong><?= e(number_format($moyenne, 1, ',', '')) ?>/5</strong><span>note moyenne de nos clients</span></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section" aria-labelledby="titre-entreprise">
    <div class="container">
        <div class="row g-4 g-lg-5">
            <div class="col-lg-5">
                <p class="eyebrow">Notre histoire</p>
                <h2 id="titre-entreprise" class="section-title">Une petite maison, un grand savoir-faire</h2>
            </div>
            <div class="col-lg-7">
                <p>Depuis 25 ans, <strong>Vite &amp; Gourmand</strong> accompagne les Bordelais dans leurs moments
                    importants. Ce qui a commencé par quelques menus envoyés par e-mail à nos habitués est aujourd'hui
                    une carte en constante évolution, pensée au fil des saisons et des fêtes.</p>
                <p>Chaque menu est préparé dans notre cuisine du centre de Bordeaux, avec des produits du Sud-Ouest,
                    puis livré à l'heure qui vous convient — gratuitement dans Bordeaux.</p>
            </div>
        </div>
    </div>
</section>

<section class="section section-alt" aria-labelledby="titre-equipe">
    <div class="container">
        <p class="eyebrow">Notre équipe</p>
        <h2 id="titre-equipe" class="section-title mb-5">Le professionnalisme, à chaque étape</h2>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="feature">
                    <span class="feature-num" aria-hidden="true">01</span>
                    <h3>Une cuisine maîtrisée</h3>
                    <p>José, chef de formation, élabore chaque recette et veille à la traçabilité et aux allergènes de chaque plat.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature">
                    <span class="feature-num" aria-hidden="true">02</span>
                    <h3>Une organisation sans faille</h3>
                    <p>Julie et son équipe logistique assurent une livraison ponctuelle, le prêt de matériel et le suivi en temps réel de votre commande.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature">
                    <span class="feature-num" aria-hidden="true">03</span>
                    <h3>Des normes respectées</h3>
                    <p>Chaîne du froid, hygiène HACCP et conditions de conservation précisées pour chaque menu : vous recevez en toute sérénité.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php if ($menus): ?>
<section class="section" aria-labelledby="titre-menus">
    <div class="container">
        <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
            <div>
                <p class="eyebrow">À la carte en ce moment</p>
                <h2 id="titre-menus" class="section-title mb-0">Nos menus du moment</h2>
            </div>
            <a class="link-arrow" href="<?= url('/menus') ?>">Voir tous les menus</a>
        </div>
        <div class="row g-4">
            <?php foreach ($menus as $m): ?>
                <div class="col-md-6 col-lg-4"><?php App\Core\View::partial('partials/menu-card', ['m' => $m]); ?></div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="section section-alt" aria-labelledby="titre-avis">
    <div class="container">
        <p class="eyebrow">Ils nous ont fait confiance</p>
        <h2 id="titre-avis" class="section-title mb-5">Avis de nos clients</h2>
        <?php if (!$avis): ?>
            <p>Les premiers avis arrivent bientôt.</p>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($avis as $a): ?>
                    <div class="col-md-6 col-lg-4">
                        <figure class="review">
                            <p class="stars" aria-label="Note : <?= (int) $a['note'] ?> sur 5">
                                <?= str_repeat('★', (int) $a['note']) ?><span class="stars-off"><?= str_repeat('★', 5 - (int) $a['note']) ?></span>
                            </p>
                            <blockquote><p><?= e($a['description']) ?></p></blockquote>
                            <figcaption><?= e($a['prenom']) ?> <?= e($a['initiale']) ?>. — <span><?= e($a['menu_titre']) ?></span></figcaption>
                        </figure>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
