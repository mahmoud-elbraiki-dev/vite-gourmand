/* Vite & Gourmand — utilitaires partagés par les modules front (ES2020+) */

/** Formateur monétaire unique pour tout le site (ex. « 1 234,50 € »). */
export const euro = new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' });

/** Raccourci vers document.getElementById. */
export const $ = (id) => document.getElementById(id);

/**
 * Crée un élément DOM. Le texte passe par textContent : aucune injection HTML possible.
 * @param {string} tag
 * @param {string} [className]
 * @param {string|number} [text]
 */
export const el = (tag, className = '', text) => {
    const node = document.createElement(tag);
    if (className) node.className = className;
    if (text !== undefined) node.textContent = text;
    return node;
};

/** Accorde un mot au pluriel : plural(2, 'menu') → « menus ». */
export const plural = (count, word) => `${word}${count > 1 ? 's' : ''}`;

/**
 * Convertit un formulaire en paramètres d'URL en ignorant les champs vides.
 * @param {HTMLFormElement} form
 * @returns {URLSearchParams}
 */
export const formToParams = (form) => {
    const params = new URLSearchParams();
    for (const [key, value] of new FormData(form)) {
        if (String(value).trim() !== '') params.append(key, value);
    }
    return params;
};
