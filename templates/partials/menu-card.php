<article class="menu-card">
    <div class="menu-card-img">
        <?php if ($m['image']): ?>
            <img src="<?= asset($m['image']) ?>" alt="<?= e($m['image_alt']) ?>" loading="lazy" width="600" height="400">
        <?php endif; ?>
        <span class="tag tag-theme"><?= e($m['theme']) ?></span>
    </div>
    <div class="menu-card-body">
        <p class="menu-card-meta"><span class="tag tag-regime"><?= e($m['regime']) ?></span>
            <?php if ((int) $m['quantite_restante'] <= 5): ?>
                <span class="stock-low"><?= (int) $m['quantite_restante'] === 0 ? 'Épuisé' : 'Plus que ' . (int) $m['quantite_restante'] . ' commandes' ?></span>
            <?php endif; ?>
        </p>
        <h3 class="menu-card-title"><?= e($m['titre']) ?></h3>
        <p class="menu-card-desc"><?= e($m['description']) ?></p>
        <dl class="menu-card-facts">
            <div><dt>Minimum</dt><dd><?= (int) $m['nombre_personne_minimum'] ?> pers.</dd></div>
            <div><dt>À partir de</dt><dd><?= euros($m['prix_minimum']) ?></dd></div>
        </dl>
        <a class="btn btn-brand w-100" href="<?= url('/menu', ['id' => $m['menu_id']]) ?>">
            Voir le détail <span class="visually-hidden">du menu <?= e($m['titre']) ?></span>
        </a>
    </div>
</article>
