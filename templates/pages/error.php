<section class="section auth">
    <div class="container text-center">
        <p class="eyebrow">Erreur <?= (int) $code ?></p>
        <h1 class="page-title"><?= e($message) ?></h1>
        <a class="btn btn-brand mt-3" href="<?= url('/') ?>">Retour à l'accueil</a>
    </div>
</section>
