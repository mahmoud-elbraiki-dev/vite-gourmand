<?php use App\Service\OrderStatus; ?>
<dl class="facts facts-2">
    <div><dt>Menu</dt><dd><?= e($c['menu_titre']) ?></dd></div>
    <div><dt>Statut</dt><dd><span class="status <?= OrderStatus::badge($c['statut']) ?>"><?= e($c['statut']) ?></span></dd></div>
    <div><dt>Prestation</dt><dd><?= date_fr($c['date_prestation']) ?> à <?= e($c['heure_livraison']) ?></dd></div>
    <div><dt>Personnes</dt><dd><?= (int) $c['nombre_personne'] ?></dd></div>
    <div class="span-2"><dt>Lieu</dt><dd><?= e($c['adresse_prestation']) ?>, <?= e($c['ville_prestation']) ?><?= (float) $c['distance_km'] > 0 ? ' (' . e($c['distance_km']) . ' km)' : '' ?></dd></div>
    <div><dt>Prix du menu</dt><dd><?= euros($c['prix_menu']) ?><?= (float) $c['reduction'] > 0 ? ' <small>(−' . euros($c['reduction']) . ')</small>' : '' ?></dd></div>
    <div><dt>Livraison</dt><dd><?= euros($c['prix_livraison']) ?></dd></div>
    <div><dt>Total</dt><dd><strong><?= euros($c['total']) ?></strong></dd></div>
    <div><dt>Commandé le</dt><dd><?= date_fr($c['date_commande'], true) ?></dd></div>
    <?php if ((int) $c['pret_materiel']): ?>
        <div class="span-2"><dt>Matériel prêté</dt><dd><?= (int) $c['restitution_materiel'] ? 'Restitué' : 'À restituer sous 10 jours ouvrés (600 € de frais sinon)' ?></dd></div>
    <?php endif; ?>
    <?php if ($c['motif_annulation']): ?>
        <div class="span-2"><dt>Note</dt><dd><?= e($c['motif_annulation']) ?><?= $c['mode_contact'] ? ' — contact par ' . e($c['mode_contact']) : '' ?></dd></div>
    <?php endif; ?>
</dl>
