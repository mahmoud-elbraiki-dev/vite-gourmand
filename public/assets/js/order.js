/* Page de commande : calcul du prix en direct (le serveur recalcule toujours le prix final) — module ES */
import { $, euro } from './utils.js';

const BORDEAUX = { lat: 44.8378, lon: -0.5792 };
const GEOCODE_DELAY_MS = 500;
const ROAD_FACTOR = 1.25; // vol d'oiseau → approximation de la distance routière

/** Bordeaux, Bordeaux Cedex, Bordeaux 33000… → livraison offerte. */
const isBordeaux = (ville) => /^bordeaux( cedex)?( \d+)?$/.test(ville.trim().toLowerCase().replace(/-/g, ' '));

/** Arrondi à n décimales. */
const round = (value, decimals = 2) => Math.round(value * 10 ** decimals) / 10 ** decimals;

/**
 * Règles de prix (miroir de PricingService côté serveur) :
 * -10 % dès « minimum + 5 » personnes, livraison 5 € + 0,59 €/km hors Bordeaux.
 */
const computePrice = ({ unitPrice, guests, minimum, inBordeaux, km }) => {
    const brut = unitPrice * guests;
    const reduction = guests >= minimum + 5 ? Math.round(brut * 10) / 100 : 0;
    const livraison = inBordeaux ? 0 : round(5 + 0.59 * km);
    return { brut, reduction, livraison, total: brut - reduction + livraison };
};

/** Distance à vol d'oiseau (formule de haversine), en km. */
const haversine = (a, b) => {
    const R = 6371;
    const rad = Math.PI / 180;
    const dLat = (b.lat - a.lat) * rad;
    const dLon = (b.lon - a.lon) * rad;
    const h = Math.sin(dLat / 2) ** 2 + Math.cos(a.lat * rad) * Math.cos(b.lat * rad) * Math.sin(dLon / 2) ** 2;
    return 2 * R * Math.asin(Math.sqrt(h));
};

/** Estimation du prix d'une commande et géocodage de l'adresse via la Base Adresse Nationale. */
class OrderEstimator {
    #geoTimer = null;

    constructor() {
        this.menu = $('menu_id');
        this.guests = $('nombre_personne');
        this.ville = $('ville_prestation');
        this.adresse = $('adresse_prestation');
        this.distance = $('distance_km');
    }

    get selectedOption() {
        return this.menu.options[this.menu.selectedIndex];
    }

    init() {
        this.menu.addEventListener('change', () => this.onMenuChange());
        [this.guests, this.distance].forEach((input) => input.addEventListener('input', () => this.update()));
        [this.ville, this.adresse].forEach((input) => input.addEventListener('input', () => {
            this.update();
            this.geocode();
        }));
        this.update();
        this.geocode();
    }

    /** Remonte le nombre de convives au minimum du menu choisi si nécessaire. */
    onMenuChange() {
        const min = parseInt(this.selectedOption?.dataset.min ?? '', 10);
        if (min && (parseInt(this.guests.value, 10) || 0) < min) this.guests.value = min;
        this.update();
    }

    update() {
        const option = this.selectedOption;
        const { prix, min: minAttr, titre, conditions } = option?.dataset ?? {};
        const hasMenu = Boolean(option?.value);

        const unitPrice = prix ? parseFloat(prix) : 0;
        const minimum = minAttr ? parseInt(minAttr, 10) : 1;
        const guests = parseInt(this.guests.value, 10) || 0;
        const inBordeaux = isBordeaux(this.ville.value);
        const km = parseFloat(String(this.distance.value).replace(',', '.')) || 0;

        const { brut, reduction, livraison, total } = computePrice({ unitPrice, guests, minimum, inBordeaux, km });

        this.guests.min = minimum;
        this.guests.setCustomValidity(guests && guests < minimum ? `Minimum ${minimum} personnes pour ce menu.` : '');
        $('min-help').textContent = hasMenu ? `Minimum ${minimum} personnes. -10 % dès ${minimum + 5}.` : '';
        $('distance-group').hidden = inBordeaux;

        // Récapitulatif
        $('s-nb').textContent = guests;
        $('s-pu').textContent = euro.format(unitPrice);
        $('s-brut').textContent = euro.format(brut);
        $('s-reduc-line').hidden = reduction === 0;
        $('s-reduc').textContent = `−${euro.format(reduction)}`;
        $('s-livraison').textContent = inBordeaux ? 'Offerte' : euro.format(livraison);
        $('s-total').textContent = euro.format(total);

        // Conditions du menu choisi
        const conditionsBlock = $('order-conditions');
        conditionsBlock.hidden = !hasMenu;
        if (hasMenu) {
            $('cond-titre').textContent = titre;
            $('cond-text').textContent = conditions;
        }
    }

    geocode() {
        const ville = this.ville.value.trim();
        if (isBordeaux(ville) || ville.length < 2) {
            this.update();
            return;
        }
        clearTimeout(this.#geoTimer);
        this.#geoTimer = setTimeout(() => this.#fetchDistance(), GEOCODE_DELAY_MS);
    }

    async #fetchDistance() {
        const query = encodeURIComponent(`${this.adresse.value} ${this.ville.value}`.trim());
        try {
            const response = await fetch(`https://api-adresse.data.gouv.fr/search/?limit=1&q=${query}`);
            const { features = [] } = await response.json();
            if (!features.length) return;

            const [lon, lat] = features[0].geometry.coordinates;
            const km = haversine(BORDEAUX, { lat, lon }) * ROAD_FACTOR;
            this.distance.value = round(km, 1).toFixed(1);
            this.update();
        } catch {
            // API indisponible : l'utilisateur saisit la distance manuellement
        }
    }
}

if ($('order-form')) new OrderEstimator().init();
