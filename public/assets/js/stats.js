/* Statistiques administrateur : données issues de la base NoSQL, graphique Chart.js — module ES */
import { $, euro, formToParams } from './utils.js';

/** Lit une variable CSS de la charte graphique, avec une valeur de secours. */
const cssVar = (name, fallback) =>
    getComputedStyle(document.documentElement).getPropertyValue(name).trim() || fallback;

/** Tableau de bord : indicateurs clés, tableau accessible et graphique en barres. */
class StatsDashboard {
    #chart = null;

    constructor(form) {
        this.form = form;
        this.colors = {
            brand: cssVar('--vg-wine', '#6B1E2E'),
            ink: cssVar('--vg-ink-2', '#6B5F5A'),
        };
    }

    init() {
        this.form.addEventListener('change', () => this.load());
        // L'événement reset est déclenché avant la remise à zéro des champs
        this.form.addEventListener('reset', () => setTimeout(() => this.load(), 0));
        this.form.addEventListener('submit', (event) => {
            event.preventDefault();
            this.load();
        });
        this.load();
    }

    async load() {
        const query = formToParams(this.form).toString();
        try {
            const response = await fetch(`/api/statistiques?${query}`, { headers: { Accept: 'application/json' } });
            if (!response.ok) throw new Error(`HTTP ${response.status}`);
            this.render(await response.json());
        } catch (error) {
            $('k-source').textContent = 'Erreur de chargement des statistiques.';
            console.error(error);
        }
    }

    render({ total_commandes: totalCommandes, total_ca: totalCa, source, rows }) {
        $('k-commandes').textContent = totalCommandes;
        $('k-ca').textContent = euro.format(totalCa);
        $('k-source').textContent = `Base NoSQL — ${source}`;

        this.#renderTable(rows);
        this.#renderChart(
            rows.map(({ menu_titre: titre }) => titre),
            rows.map(({ nb_commandes: nb }) => nb),
        );
    }

    #renderTable(rows) {
        const body = $('stats-body');

        if (!rows.length) {
            const tr = document.createElement('tr');
            const td = document.createElement('td');
            td.colSpan = 3;
            td.textContent = 'Aucune commande sur cette période.';
            tr.append(td);
            body.replaceChildren(tr);
            return;
        }

        const lines = rows.map(({ menu_titre: titre, nb_commandes: nb, chiffre_affaires: ca }) => {
            const tr = document.createElement('tr');
            const th = document.createElement('th');
            th.scope = 'row';
            th.textContent = titre;

            const cells = [nb, euro.format(ca)].map((value) => {
                const td = document.createElement('td');
                td.className = 'text-end';
                td.textContent = value;
                return td;
            });

            tr.append(th, ...cells);
            return tr;
        });
        body.replaceChildren(...lines);
    }

    #renderChart(labels, values) {
        if (!window.Chart) return;

        if (this.#chart) {
            this.#chart.data.labels = labels;
            this.#chart.data.datasets[0].data = values;
            this.#chart.update();
            return;
        }

        const { brand, ink } = this.colors;
        this.#chart = new window.Chart($('chart-commandes'), {
            type: 'bar',
            data: {
                labels,
                datasets: [{ label: 'Commandes', data: values, backgroundColor: brand, borderRadius: 6, maxBarThickness: 56 }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { precision: 0, color: ink }, grid: { color: 'rgba(43,34,32,.08)' } },
                    x: { ticks: { color: ink }, grid: { display: false } },
                },
            },
        });
    }
}

const form = $('stats-filters');
if (form) new StatsDashboard(form).init();
