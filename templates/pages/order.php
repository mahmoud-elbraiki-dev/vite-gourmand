<section class="page-head">
    <div class="container">
        <p class="eyebrow">Commande</p>
        <h1 class="page-title">Commander un menu</h1>
    </div>
</section>

<section class="section pt-4">
    <div class="container">
        <form method="post" action="<?= url('/commande') ?>" id="order-form" class="row g-4 g-lg-5" novalidate
              data-bordeaux-free="1">
            <?= App\Core\Csrf::field() ?>
            <div class="col-lg-7">
                <fieldset class="form-card">
                    <legend class="form-card-title"><span class="step">1</span> Vos informations</legend>
                    <p class="form-text mt-0">Pré-remplies depuis votre compte. <a href="<?= url('/espace/profil') ?>">Les modifier</a>.</p>
                    <div class="row g-3">
                        <div class="col-sm-6"><label class="form-label" for="o-prenom">Prénom</label><input class="form-control" id="o-prenom" value="<?= e($user['prenom']) ?>" readonly></div>
                        <div class="col-sm-6"><label class="form-label" for="o-nom">Nom</label><input class="form-control" id="o-nom" value="<?= e($user['nom']) ?>" readonly></div>
                        <div class="col-sm-6"><label class="form-label" for="o-email">E-mail</label><input class="form-control" id="o-email" value="<?= e($user['email']) ?>" readonly></div>
                        <div class="col-sm-6"><label class="form-label" for="o-gsm">GSM</label><input class="form-control" id="o-gsm" value="<?= e($user['telephone']) ?>" readonly></div>
                    </div>
                </fieldset>

                <fieldset class="form-card">
                    <legend class="form-card-title"><span class="step">2</span> La prestation</legend>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label" for="adresse_prestation">Adresse de la prestation <span aria-hidden="true">*</span></label>
                            <input class="form-control" id="adresse_prestation" name="adresse_prestation" required autocomplete="street-address"
                                   value="<?= e(old('adresse_prestation', $user['adresse_postale'])) ?>">
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label" for="ville_prestation">Ville <span aria-hidden="true">*</span></label>
                            <input class="form-control" id="ville_prestation" name="ville_prestation" required autocomplete="address-level2"
                                   value="<?= e(old('ville_prestation', $user['ville'])) ?>">
                        </div>
                        <div class="col-sm-6" id="distance-group">
                            <label class="form-label" for="distance_km">Distance depuis Bordeaux (km)</label>
                            <input class="form-control" type="number" step="0.1" min="0" id="distance_km" name="distance_km"
                                   value="<?= e(old('distance_km', '0')) ?>" aria-describedby="distance-help">
                            <p class="form-text" id="distance-help">Calculée automatiquement à partir de l'adresse, modifiable si besoin.</p>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label" for="date_prestation">Date de la prestation <span aria-hidden="true">*</span></label>
                            <input class="form-control" type="date" id="date_prestation" name="date_prestation" required
                                   min="<?= date('Y-m-d', strtotime('+1 day')) ?>" value="<?= e(old('date_prestation')) ?>">
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label" for="heure_livraison">Heure de livraison souhaitée <span aria-hidden="true">*</span></label>
                            <input class="form-control" type="time" id="heure_livraison" name="heure_livraison" required value="<?= e(old('heure_livraison', '12:00')) ?>">
                        </div>
                    </div>
                </fieldset>

                <fieldset class="form-card">
                    <legend class="form-card-title"><span class="step">3</span> Le menu</legend>
                    <div class="row g-3">
                        <div class="col-sm-8">
                            <label class="form-label" for="menu_id">Menu choisi <span aria-hidden="true">*</span></label>
                            <select class="form-select" id="menu_id" name="menu_id" required>
                                <option value="">Choisir un menu…</option>
                                <?php foreach ($menus as $m): if ((int) $m['quantite_restante'] <= 0) continue; ?>
                                    <option value="<?= (int) $m['menu_id'] ?>"
                                            data-prix="<?= e($m['prix_par_personne']) ?>"
                                            data-min="<?= (int) $m['nombre_personne_minimum'] ?>"
                                            data-titre="<?= e($m['titre']) ?>"
                                            data-conditions="<?= e($m['conditions']) ?>"
                                            <?= $menu && (int) $menu['menu_id'] === (int) $m['menu_id'] ? 'selected' : '' ?>>
                                        <?= e($m['titre']) ?> — <?= euros($m['prix_par_personne']) ?>/pers. (min. <?= (int) $m['nombre_personne_minimum'] ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-sm-4">
                            <label class="form-label" for="nombre_personne">Nombre de personnes <span aria-hidden="true">*</span></label>
                            <input class="form-control" type="number" id="nombre_personne" name="nombre_personne" required
                                   min="<?= $menu ? (int) $menu['nombre_personne_minimum'] : 1 ?>"
                                   value="<?= e(old('nombre_personne', $menu ? (string) $menu['nombre_personne_minimum'] : '')) ?>" aria-describedby="min-help">
                            <p class="form-text" id="min-help"></p>
                        </div>
                    </div>
                    <div class="conditions mt-3" role="note" id="order-conditions" <?= $menu ? '' : 'hidden' ?>>
                        <p class="conditions-title mb-1">Conditions du menu « <span id="cond-titre"><?= e($menu['titre'] ?? '') ?></span> »</p>
                        <p class="mb-0" id="cond-text"><?= $menu ? nl2br(e($menu['conditions'])) : '' ?></p>
                    </div>
                    <div class="form-check mt-3">
                        <input class="form-check-input" type="checkbox" id="conditions_lues" name="conditions_lues" required>
                        <label class="form-check-label" for="conditions_lues">J'ai lu et j'accepte les conditions de ce menu et les <a href="<?= url('/cgv') ?>" target="_blank" rel="noopener">conditions générales de vente</a> <span class="visually-hidden">(nouvel onglet)</span>.</label>
                    </div>
                </fieldset>
            </div>

            <div class="col-lg-5">
                <aside class="summary" aria-labelledby="titre-recap">
                    <h2 id="titre-recap" class="summary-title">Détail du prix</h2>
                    <dl class="summary-lines" aria-live="polite">
                        <div><dt>Menu (<span id="s-nb">0</span> pers. × <span id="s-pu">0,00 €</span>)</dt><dd id="s-brut">0,00 €</dd></div>
                        <div id="s-reduc-line" hidden><dt>Réduction 10 %</dt><dd id="s-reduc">−0,00 €</dd></div>
                        <div><dt>Livraison</dt><dd id="s-livraison">0,00 €</dd></div>
                        <div class="summary-total"><dt>Total TTC</dt><dd id="s-total">0,00 €</dd></div>
                    </dl>
                    <p class="small" id="s-hint">Livraison offerte dans Bordeaux, sinon 5 € + 0,59 €/km.</p>
                    <button class="btn btn-brand btn-lg w-100" type="submit">Valider ma commande</button>
                    <p class="small mt-2 mb-0">Le prix définitif est recalculé et confirmé par e-mail.</p>
                </aside>
            </div>
        </form>
    </div>
</section>
<?php $scripts = [asset('assets/js/order.js')]; ?>
