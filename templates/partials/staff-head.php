<section class="page-head page-head-staff">
    <div class="container">
        <p class="eyebrow"><?= App\Core\Auth::isAdmin() ? 'Administration' : 'Espace employé' ?></p>
        <h1 class="page-title"><?= e($heading) ?></h1>
        <?php App\Core\View::partial('partials/staff-nav'); ?>
    </div>
</section>
