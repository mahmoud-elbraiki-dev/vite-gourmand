<div class="row g-3">
    <div class="col-12"><label class="form-label" for="adresse_prestation">Adresse de la prestation</label>
        <input class="form-control" id="adresse_prestation" name="adresse_prestation" required value="<?= e($c['adresse_prestation']) ?>"></div>
    <div class="col-sm-6"><label class="form-label" for="ville_prestation">Ville</label>
        <input class="form-control" id="ville_prestation" name="ville_prestation" required value="<?= e($c['ville_prestation']) ?>"></div>
    <div class="col-sm-6"><label class="form-label" for="distance_km">Distance depuis Bordeaux (km)</label>
        <input class="form-control" type="number" step="0.1" min="0" id="distance_km" name="distance_km" value="<?= e($c['distance_km']) ?>"></div>
    <div class="col-sm-4"><label class="form-label" for="date_prestation">Date</label>
        <input class="form-control" type="date" id="date_prestation" name="date_prestation" required value="<?= e($c['date_prestation']) ?>"></div>
    <div class="col-sm-4"><label class="form-label" for="heure_livraison">Heure</label>
        <input class="form-control" type="time" id="heure_livraison" name="heure_livraison" required value="<?= e($c['heure_livraison']) ?>"></div>
    <div class="col-sm-4"><label class="form-label" for="nombre_personne">Personnes (min. <?= (int) $c['nombre_personne_minimum'] ?>)</label>
        <input class="form-control" type="number" id="nombre_personne" name="nombre_personne" min="<?= (int) $c['nombre_personne_minimum'] ?>" required value="<?= (int) $c['nombre_personne'] ?>"></div>
</div>
