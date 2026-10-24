/* Vue globale des menus : filtrage dynamique sans rechargement (fetch + API JSON) — module ES */
import { el, euro, formToParams, plural } from './utils.js';

const DEBOUNCE_MS = 300;

/**
 * Construit la carte d'un menu à partir de la réponse de l'API /api/menus.
 * @param {object} menu
 * @returns {HTMLElement}
 */
const createMenuCard = ({
    image, image_alt: imageAlt, theme, regime, stock,
    titre, description, minimum, prix_minimum: prixMinimum, url,
}) => {
    const col = el('div', 'col-md-6 col-xl-4');
    const article = el('article', 'menu-card');

    const imgWrap = el('div', 'menu-card-img');
    if (image) {
        const img = el('img');
        Object.assign(img, { src: image, alt: imageAlt, loading: 'lazy', width: 600, height: 400 });
        imgWrap.append(img);
    }
    imgWrap.append(el('span', 'tag tag-theme', theme));

    const meta = el('p', 'menu-card-meta');
    meta.append(el('span', 'tag tag-regime', regime));
    if (stock <= 5) {
        meta.append(el('span', 'stock-low', stock === 0 ? 'Épuisé' : `Plus que ${stock} commandes`));
    }

    const facts = el('dl', 'menu-card-facts');
    const rows = [['Minimum', `${minimum} pers.`], ['À partir de', euro.format(prixMinimum)]];
    for (const [label, value] of rows) {
        const row = el('div');
        row.append(el('dt', '', label), el('dd', '', value));
        facts.append(row);
    }

    const link = el('a', 'btn btn-brand w-100', 'Voir le détail ');
    link.href = url;
    link.append(el('span', 'visually-hidden', `du menu ${titre}`));

    const body = el('div', 'menu-card-body');
    body.append(meta, el('h3', 'menu-card-title', titre), el('p', 'menu-card-desc', description), facts, link);

    article.append(imgWrap, body);
    col.append(article);
    return col;
};

/** Gère le formulaire de filtres et le rafraîchissement de la liste des menus. */
class MenuFilter {
    #timer = null;
    #controller = null;

    constructor({ form, list, count, empty, resetButton }) {
        Object.assign(this, { form, list, count, empty, resetButton });
    }

    init() {
        // Avec JavaScript, le bouton « Filtrer » devient inutile (amélioration progressive)
        this.form.querySelectorAll('.js-hide').forEach((button) => { button.hidden = true; });

        this.form.addEventListener('input', () => this.#schedule());
        this.form.addEventListener('change', () => this.refresh());
        this.form.addEventListener('submit', (event) => {
            event.preventDefault();
            this.refresh();
        });
        this.resetButton?.addEventListener('click', (event) => {
            event.preventDefault();
            this.reset();
        });
    }

    #schedule() {
        clearTimeout(this.#timer);
        this.#timer = setTimeout(() => this.refresh(), DEBOUNCE_MS);
    }

    reset() {
        this.form.reset();
        this.form.querySelectorAll('input, select').forEach((field) => { field.value = ''; });
        this.refresh();
    }

    async refresh() {
        clearTimeout(this.#timer);
        const params = formToParams(this.form);
        const query = params.toString();

        // Annule la requête précédente si l'utilisateur modifie encore les filtres
        this.#controller?.abort();
        this.#controller = new AbortController();
        this.list.setAttribute('aria-busy', 'true');

        try {
            const response = await fetch(`/api/menus?${query}`, {
                headers: { Accept: 'application/json' },
                signal: this.#controller.signal,
            });
            if (!response.ok) throw new Error(`HTTP ${response.status}`);

            const menus = await response.json();
            this.render(menus);
            // L'URL reflète les filtres (partage, bouton retour) sans recharger la page
            history.replaceState(null, '', query ? `/menus?${query}` : '/menus');
        } catch (error) {
            if (error.name !== 'AbortError') this.count.textContent = 'Erreur de chargement, réessayez.';
        } finally {
            this.list.removeAttribute('aria-busy');
        }
    }

    /** @param {object[]} menus */
    render(menus) {
        const { length } = menus;
        this.list.replaceChildren(...menus.map(createMenuCard));
        this.count.textContent = `${length} ${plural(length, 'menu')} ${plural(length, 'trouvé')}`;
        this.empty.hidden = length > 0;
    }
}

const form = document.getElementById('menu-filters');
const list = document.getElementById('menu-list');

if (form && list) {
    new MenuFilter({
        form,
        list,
        count: document.getElementById('menu-count'),
        empty: document.getElementById('menu-empty'),
        resetButton: document.getElementById('reset-filters'),
    }).init();
}
