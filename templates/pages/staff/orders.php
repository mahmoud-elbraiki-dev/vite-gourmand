<?php use App\Service\OrderStatus; App\Core\View::partial('partials/staff-head', ['heading' => 'Commandes']); ?>
<section class="section pt-4">
    <div class="container">
        <ul class="kpis" aria-label="Commandes par statut">
            <?php foreach (OrderStatus::ALL as $s): ?>
                <li><a href="<?= url('/gestion', ['statut' => $s]) ?>" class="kpi <?= $statut === $s ? 'is-active' : '' ?>">
                    <span class="kpi-value"><?= (int) ($counts[$s] ?? 0) ?></span><span class="kpi-label"><?= e($s) ?></span>
                </a></li>
            <?php endforeach; ?>
        </ul>

        <form class="toolbar" method="get" action="<?= url('/gestion') ?>" role="search" aria-label="Filtrer les commandes">
            <div>
                <label class="form-label" for="f-statut">Statut</label>
                <select class="form-select" id="f-statut" name="statut">
                    <option value="">Tous les statuts</option>
                    <?php foreach (OrderStatus::ALL as $s): ?>
                        <option value="<?= e($s) ?>" <?= $statut === $s ? 'selected' : '' ?>><?= e(ucfirst($s)) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="flex-grow-1">
                <label class="form-label" for="f-client">Client</label>
                <input class="form-control" type="search" id="f-client" name="client" placeholder="Nom, prénom ou e-mail" value="<?= e($client) ?>">
            </div>
            <div class="d-flex gap-2 align-items-end">
                <button class="btn btn-brand" type="submit">Filtrer</button>
                <a class="btn btn-outline-brand" href="<?= url('/gestion') ?>">Effacer</a>
            </div>
        </form>

        <p class="result-count" role="status"><?= count($commandes) ?> commande(s)</p>
        <div class="table-responsive">
            <table class="table table-brand align-middle">
                <caption class="visually-hidden">Commandes filtrées</caption>
                <thead><tr><th scope="col">N°</th><th scope="col">Client</th><th scope="col">Menu</th><th scope="col">Prestation</th><th scope="col">Total</th><th scope="col">Statut</th><th scope="col"><span class="visually-hidden">Actions</span></th></tr></thead>
                <tbody>
                <?php foreach ($commandes as $c): ?>
                    <tr>
                        <td><?= e($c['numero_commande']) ?></td>
                        <td><?= e($c['prenom'] . ' ' . $c['nom']) ?><br><small><?= e($c['telephone']) ?></small></td>
                        <td><?= e($c['menu_titre']) ?><br><small><?= (int) $c['nombre_personne'] ?> pers.</small></td>
                        <td><?= date_fr($c['date_prestation']) ?> · <?= e($c['heure_livraison']) ?><br><small><?= e($c['ville_prestation']) ?></small></td>
                        <td><?= euros($c['total']) ?></td>
                        <td><span class="status <?= OrderStatus::badge($c['statut']) ?>"><?= e($c['statut']) ?></span></td>
                        <td class="text-end"><a class="btn btn-sm btn-outline-brand" href="<?= url('/gestion/commande', ['numero' => $c['numero_commande']]) ?>">Gérer<span class="visually-hidden"> la commande <?= e($c['numero_commande']) ?></span></a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
