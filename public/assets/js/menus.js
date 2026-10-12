/* Vue globale des menus : filtrage dynamique sans rechargement (fetch + API JSON) */
(function () {
    'use strict';

    var form = document.getElementById('menu-filters');
    var list = document.getElementById('menu-list');
    var count = document.getElementById('menu-count');
    var empty = document.getElementById('menu-empty');
    if (!form || !list) { return; }

    // Avec JavaScript, le bouton « Filtrer » devient inutile (amélioration progressive)
    form.querySelectorAll('.js-hide').forEach(function (b) { b.hidden = true; });

    var euro = new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' });
    var timer = null;
    var controller = null;

    function el(tag, className, text) {
        var node = document.createElement(tag);
        if (className) { node.className = className; }
        if (text !== undefined) { node.textContent = text; } // textContent : aucune injection HTML possible
        return node;
    }

    function card(m) {
        var col = el('div', 'col-md-6 col-xl-4');
        var art = el('article', 'menu-card');
        var imgWrap = el('div', 'menu-card-img');
        if (m.image) {
            var img = el('img');
            img.src = m.image; img.alt = m.image_alt; img.loading = 'lazy'; img.width = 600; img.height = 400;
            imgWrap.appendChild(img);
        }
        imgWrap.appendChild(el('span', 'tag tag-theme', m.theme));
        var body = el('div', 'menu-card-body');
        var meta = el('p', 'menu-card-meta');
        meta.appendChild(el('span', 'tag tag-regime', m.regime));
        if (m.stock <= 5) {
            meta.appendChild(el('span', 'stock-low', m.stock === 0 ? 'Épuisé' : 'Plus que ' + m.stock + ' commandes'));
        }
        body.appendChild(meta);
        body.appendChild(el('h3', 'menu-card-title', m.titre));
        body.appendChild(el('p', 'menu-card-desc', m.description));
        var dl = el('dl', 'menu-card-facts');
        [['Minimum', m.minimum + ' pers.'], ['À partir de', euro.format(m.prix_minimum)]].forEach(function (f) {
            var d = el('div'); d.appendChild(el('dt', '', f[0])); d.appendChild(el('dd', '', f[1])); dl.appendChild(d);
        });
        body.appendChild(dl);
        var a = el('a', 'btn btn-brand w-100', 'Voir le détail ');
        a.href = m.url;
        a.appendChild(el('span', 'visually-hidden', 'du menu ' + m.titre));
        body.appendChild(a);
        art.appendChild(imgWrap); art.appendChild(body); col.appendChild(art);
        return col;
    }

    function params() {
        var p = new URLSearchParams();
        new FormData(form).forEach(function (v, k) { if (String(v).trim() !== '') { p.append(k, v); } });
        return p;
    }

    function refresh() {
        var p = params();
        if (controller) { controller.abort(); }
        controller = new AbortController();
        list.setAttribute('aria-busy', 'true');
        fetch('/api/menus?' + p.toString(), { headers: { 'Accept': 'application/json' }, signal: controller.signal })
            .then(function (r) { if (!r.ok) { throw new Error(r.status); } return r.json(); })
            .then(function (menus) {
                list.replaceChildren.apply(list, menus.map(card));
                count.textContent = menus.length + ' menu' + (menus.length > 1 ? 's' : '') + ' trouvé' + (menus.length > 1 ? 's' : '');
                empty.hidden = menus.length > 0;
                // L'URL reflète les filtres (partage, bouton retour) sans recharger la page
                history.replaceState(null, '', '/menus' + (p.toString() ? '?' + p.toString() : ''));
            })
            .catch(function (e) { if (e.name !== 'AbortError') { count.textContent = 'Erreur de chargement, réessayez.'; } })
            .finally(function () { list.removeAttribute('aria-busy'); });
    }

    form.addEventListener('input', function () { clearTimeout(timer); timer = setTimeout(refresh, 300); });
    form.addEventListener('change', function () { clearTimeout(timer); refresh(); });
    form.addEventListener('submit', function (e) { e.preventDefault(); refresh(); });
    document.getElementById('reset-filters').addEventListener('click', function (e) {
        e.preventDefault(); form.reset();
        form.querySelectorAll('input').forEach(function (i) { i.value = ''; });
        form.querySelectorAll('select').forEach(function (s) { s.value = ''; });
        refresh();
    });
})();
