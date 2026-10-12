/* Page de commande : calcul du prix en direct (le serveur recalcule toujours le prix final) */
(function () {
    'use strict';

    var form = document.getElementById('order-form');
    if (!form) { return; }

    var BORDEAUX = { lat: 44.8378, lon: -0.5792 };
    var euro = new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' });
    var $ = function (id) { return document.getElementById(id); };
    var menu = $('menu_id'), nb = $('nombre_personne'), ville = $('ville_prestation'),
        adresse = $('adresse_prestation'), dist = $('distance_km');

    function isBordeaux(v) {
        return /^bordeaux( cedex)?( \d+)?$/.test(v.trim().toLowerCase().replace(/-/g, ' '));
    }

    function update() {
        var opt = menu.options[menu.selectedIndex];
        var pu = opt && opt.dataset.prix ? parseFloat(opt.dataset.prix) : 0;
        var min = opt && opt.dataset.min ? parseInt(opt.dataset.min, 10) : 1;
        nb.min = min;
        $('min-help').textContent = opt && opt.value ? 'Minimum ' + min + ' personnes. -10 % dès ' + (min + 5) + '.' : '';
        var n = parseInt(nb.value, 10) || 0;
        var brut = pu * n;
        var reduc = n >= min + 5 ? Math.round(brut * 10) / 100 : 0;
        var inBdx = isBordeaux(ville.value);
        var km = parseFloat(String(dist.value).replace(',', '.')) || 0;
        var livraison = inBdx ? 0 : Math.round((5 + 0.59 * km) * 100) / 100;
        $('distance-group').hidden = inBdx;
        $('s-nb').textContent = n;
        $('s-pu').textContent = euro.format(pu);
        $('s-brut').textContent = euro.format(brut);
        $('s-reduc-line').hidden = reduc === 0;
        $('s-reduc').textContent = '−' + euro.format(reduc);
        $('s-livraison').textContent = inBdx ? 'Offerte' : euro.format(livraison);
        $('s-total').textContent = euro.format(brut - reduc + livraison);
        nb.setCustomValidity(n && n < min ? 'Minimum ' + min + ' personnes pour ce menu.' : '');

        var cond = $('order-conditions');
        if (opt && opt.value) {
            cond.hidden = false;
            $('cond-titre').textContent = opt.dataset.titre;
            $('cond-text').textContent = opt.dataset.conditions;
        } else {
            cond.hidden = true;
        }
    }

    // Distance à vol d'oiseau × 1,25 (approximation route) depuis la Base Adresse Nationale (API publique)
    function haversine(a, b) {
        var R = 6371, r = Math.PI / 180;
        var dLat = (b.lat - a.lat) * r, dLon = (b.lon - a.lon) * r;
        var h = Math.sin(dLat / 2) ** 2 + Math.cos(a.lat * r) * Math.cos(b.lat * r) * Math.sin(dLon / 2) ** 2;
        return 2 * R * Math.asin(Math.sqrt(h));
    }
    var geoTimer = null;
    function geocode() {
        if (isBordeaux(ville.value) || ville.value.trim().length < 2) { update(); return; }
        clearTimeout(geoTimer);
        geoTimer = setTimeout(function () {
            var q = encodeURIComponent((adresse.value + ' ' + ville.value).trim());
            fetch('https://api-adresse.data.gouv.fr/search/?limit=1&q=' + q)
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (!data.features || !data.features.length) { return; }
                    var c = data.features[0].geometry.coordinates;
                    dist.value = (Math.round(haversine(BORDEAUX, { lat: c[1], lon: c[0] }) * 1.25 * 10) / 10).toFixed(1);
                    update();
                })
                .catch(function () { /* saisie manuelle de la distance */ });
        }, 500);
    }

    menu.addEventListener('change', function () {
        var opt = menu.options[menu.selectedIndex];
        if (opt && opt.dataset.min && (parseInt(nb.value, 10) || 0) < parseInt(opt.dataset.min, 10)) {
            nb.value = opt.dataset.min;
        }
        update();
    });
    [nb, dist].forEach(function (i) { i.addEventListener('input', update); });
    [ville, adresse].forEach(function (i) { i.addEventListener('input', function () { update(); geocode(); }); });
    update();
    geocode();
})();
