<?php App\Core\View::partial('partials/staff-head', ['heading' => 'Statistiques']); ?>
<section class="section pt-4">
    <div class="container">
        <form id="stats-filters" class="toolbar" aria-label="Filtrer les statistiques">
            <div>
                <label class="form-label" for="s-menu">Menu</label>
                <select class="form-select" id="s-menu" name="menu">
                    <option value="">Tous les menus</option>
                    <?php foreach ($menus as $m): ?><option value="<?= (int) $m['menu_id'] ?>"><?= e($m['titre']) ?></option><?php endforeach; ?>
                </select>
            </div>
            <div><label class="form-label" for="s-du">Du</label><input class="form-control" type="date" id="s-du" name="du"></div>
            <div><label class="form-label" for="s-au">Au</label><input class="form-control" type="date" id="s-au" name="au"></div>
            <div class="d-flex align-items-end"><button class="btn btn-outline-brand" type="reset">Réinitialiser</button></div>
        </form>

        <div class="row g-4">
            <div class="col-md-6 col-lg-3"><div class="stat"><span class="stat-label">Commandes</span><span class="stat-value" id="k-commandes">—</span></div></div>
            <div class="col-md-6 col-lg-3"><div class="stat"><span class="stat-label">Chiffre d'affaires (menus)</span><span class="stat-value" id="k-ca">—</span></div></div>
            <div class="col-lg-6"><div class="stat stat-muted"><span class="stat-label">Source des données</span><span class="stat-source" id="k-source">Base NoSQL — <?= e($driver) ?></span></div></div>
        </div>

        <div class="form-card mt-4">
            <h2 class="form-card-title">Nombre de commandes par menu</h2>
            <div class="chart-wrap"><canvas id="chart-commandes" role="img" aria-label="Graphique comparant le nombre de commandes par menu"></canvas></div>
        </div>

        <div class="form-card">
            <h2 class="form-card-title">Chiffre d'affaires par menu</h2>
            <div class="table-responsive">
                <table class="table table-brand align-middle mb-0">
                    <caption class="visually-hidden">Commandes et chiffre d'affaires par menu (hors commandes annulées)</caption>
                    <thead><tr><th scope="col">Menu</th><th scope="col" class="text-end">Commandes</th><th scope="col" class="text-end">Chiffre d'affaires</th></tr></thead>
                    <tbody id="stats-body" aria-live="polite"></tbody>
                </table>
            </div>
            <p class="small mt-2 mb-0">Chiffre d'affaires calculé sur le prix des menus (après réduction, hors livraison), commandes annulées exclues.</p>
        </div>
    </div>
</section>
<?php $scripts = [asset('assets/vendor/chartjs/chart.umd.min.js'), asset('assets/js/stats.js')]; ?>
