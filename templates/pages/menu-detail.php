<?php
use App\Core\Auth;
$labels = ['entree' => 'Entrées', 'plat' => 'Plats', 'dessert' => 'Desserts'];
$byType = [];
foreach ($menu['plats'] as $p) {
    $byType[$p['type_plat']][] = $p;
}
?>
<nav class="container pt-4" aria-label="Fil d'Ariane">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= url('/menus') ?>">Nos menus</a></li>
        <li class="breadcrumb-item active" aria-current="page"><?= e($menu['titre']) ?></li>
    </ol>
</nav>

<section class="section pt-2">
    <div class="container">
        <div class="row g-4 g-lg-5">
            <div class="col-lg-7">
                <?php if ($menu['images']): ?>
                    <div class="gallery">
                        <img class="gallery-main" id="gallery-main" src="<?= asset($menu['images'][0]['chemin']) ?>" alt="<?= e($menu['images'][0]['alt']) ?>" width="800" height="533">
                        <?php if (count($menu['images']) > 1): ?>
                            <ul class="gallery-thumbs" aria-label="Galerie d'images">
                                <?php foreach ($menu['images'] as $i => $img): ?>
                                    <li><button type="button" class="gallery-thumb" data-src="<?= asset($img['chemin']) ?>" data-alt="<?= e($img['alt']) ?>" aria-pressed="<?= $i === 0 ? 'true' : 'false' ?>">
                                        <img src="<?= asset($img['chemin']) ?>" alt="" width="120" height="80"><span class="visually-hidden">Afficher : <?= e($img['alt']) ?></span>
                                    </button></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <h2 class="section-title mt-5">Composition du menu</h2>
                <?php foreach ($labels as $type => $label): if (empty($byType[$type])) continue; ?>
                    <h3 class="course-title"><?= $label ?></h3>
                    <ul class="dish-list">
                        <?php foreach ($byType[$type] as $p): ?>
                            <li class="dish">
                                <?php if ($p['photo']): ?><img src="<?= asset($p['photo']) ?>" alt="" width="72" height="72" loading="lazy"><?php endif; ?>
                                <div>
                                    <p class="dish-name"><?= e($p['titre_plat']) ?></p>
                                    <p class="dish-allergens">
                                        <?php if ($p['allergenes']): ?>
                                            <span class="visually-hidden">Allergènes :</span>
                                            <?php foreach ($p['allergenes'] as $a): ?><span class="allergen"><?= e($a['libelle']) ?></span><?php endforeach; ?>
                                        <?php else: ?>
                                            <span class="text-muted-2">Aucun allergène majeur</span>
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endforeach; ?>
            </div>

            <div class="col-lg-5">
                <div class="detail-panel">
                    <p class="d-flex gap-2 flex-wrap mb-3"><span class="tag tag-theme-inline"><?= e($menu['theme']) ?></span><span class="tag tag-regime"><?= e($menu['regime']) ?></span></p>
                    <h1 class="page-title"><?= e($menu['titre']) ?></h1>
                    <p><?= e($menu['description']) ?></p>
                    <dl class="facts">
                        <div><dt>Minimum</dt><dd><?= (int) $menu['nombre_personne_minimum'] ?> personnes</dd></div>
                        <div><dt>Prix minimum</dt><dd><?= euros($menu['prix_minimum']) ?></dd></div>
                        <div><dt>Par personne</dt><dd><?= euros($menu['prix_par_personne']) ?></dd></div>
                        <div><dt>Disponibilité</dt><dd><?= (int) $menu['quantite_restante'] > 0 ? (int) $menu['quantite_restante'] . ' commande(s) possible(s)' : 'Épuisé' ?></dd></div>
                    </dl>

                    <div class="conditions" role="note" aria-labelledby="titre-conditions">
                        <h2 id="titre-conditions" class="conditions-title">
                            <svg aria-hidden="true" width="20" height="20" viewBox="0 0 24 24"><path fill="currentColor" d="M12 2 1 21h22L12 2Zm0 6 1 7h-2l1-7Zm0 9.5a1.25 1.25 0 1 1 0 2.5 1.25 1.25 0 0 1 0-2.5Z"/></svg>
                            Conditions importantes de ce menu
                        </h2>
                        <p class="mb-0"><?= nl2br(e($menu['conditions'])) ?></p>
                    </div>

                    <p class="small mt-3">-10 % dès <?= (int) $menu['nombre_personne_minimum'] + 5 ?> personnes · Livraison offerte dans Bordeaux</p>

                    <?php if ((int) $menu['quantite_restante'] > 0): ?>
                        <a class="btn btn-brand btn-lg w-100" href="<?= url('/commande', ['menu' => $menu['menu_id']]) ?>">Commander ce menu</a>
                        <?php if (!Auth::check()): ?>
                            <p class="small text-center mt-2">Vous serez invité(e) à vous connecter ou à créer un compte.</p>
                        <?php endif; ?>
                    <?php else: ?>
                        <p class="alert alert-warning">Ce menu est momentanément épuisé.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>
