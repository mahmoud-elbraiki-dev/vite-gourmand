/* Statistiques administrateur : données issues de la base NoSQL, graphique Chart.js */
(function () {
    'use strict';

    var form = document.getElementById('stats-filters');
    if (!form) { return; }
    var euro = new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' });
    var chart = null;
    var styles = getComputedStyle(document.documentElement);
    var brand = styles.getPropertyValue('--vg-wine').trim() || '#6B1E2E';
    var ink = styles.getPropertyValue('--vg-ink-2').trim() || '#6B5F5A';

    function render(data) {
        document.getElementById('k-commandes').textContent = data.total_commandes;
        document.getElementById('k-ca').textContent = euro.format(data.total_ca);
        document.getElementById('k-source').textContent = 'Base NoSQL — ' + data.source;

        var body = document.getElementById('stats-body');
        body.replaceChildren();
        if (!data.rows.length) {
            var tr = document.createElement('tr'), td = document.createElement('td');
            td.colSpan = 3; td.textContent = 'Aucune commande sur cette période.';
            tr.appendChild(td); body.appendChild(tr);
        }
        data.rows.forEach(function (r) {
            var tr = document.createElement('tr');
            [r.menu_titre, r.nb_commandes, euro.format(r.chiffre_affaires)].forEach(function (v, i) {
                var td = document.createElement(i === 0 ? 'th' : 'td');
                if (i === 0) { td.scope = 'row'; } else { td.className = 'text-end'; }
                td.textContent = v; tr.appendChild(td);
            });
            body.appendChild(tr);
        });

        var labels = data.rows.map(function (r) { return r.menu_titre; });
        var values = data.rows.map(function (r) { return r.nb_commandes; });
        if (!window.Chart) { return; }
        if (chart) {
            chart.data.labels = labels; chart.data.datasets[0].data = values; chart.update(); return;
        }
        chart = new Chart(document.getElementById('chart-commandes'), {
            type: 'bar',
            data: { labels: labels, datasets: [{ label: 'Commandes', data: values, backgroundColor: brand, borderRadius: 6, maxBarThickness: 56 }] },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { precision: 0, color: ink }, grid: { color: 'rgba(43,34,32,.08)' } },
                    x: { ticks: { color: ink }, grid: { display: false } }
                }
            }
        });
    }

    function load() {
        var p = new URLSearchParams();
        new FormData(form).forEach(function (v, k) { if (v) { p.append(k, v); } });
        fetch('/api/statistiques?' + p.toString(), { headers: { 'Accept': 'application/json' } })
            .then(function (r) { return r.json(); })
            .then(render);
    }

    form.addEventListener('change', load);
    form.addEventListener('reset', function () { setTimeout(load, 0); });
    form.addEventListener('submit', function (e) { e.preventDefault(); load(); });
    load();
})();
