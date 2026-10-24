/* Vite & Gourmand — comportements communs à toutes les pages (module ES) */

/** Confirmation des actions destructrices (formulaires marqués .js-confirm). */
const initConfirmForms = () => {
    document.querySelectorAll('form.js-confirm').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (!window.confirm(form.dataset.confirm || 'Confirmer ?')) event.preventDefault();
        });
    });
};

/**
 * Vérification en direct de la politique de mot de passe.
 * Simple aide à la saisie : le contrôle réel est fait côté serveur.
 */
class PasswordChecker {
    static RULES = {
        len: (value) => value.length >= 10,
        upper: (value) => /[A-Z]/.test(value),
        lower: (value) => /[a-z]/.test(value),
        digit: (value) => /\d/.test(value),
        special: (value) => /[^A-Za-z0-9]/.test(value),
    };

    /** @param {HTMLInputElement} input */
    constructor(input) {
        this.input = input;
        this.list = document.getElementById(input.getAttribute('aria-describedby'));
    }

    bind() {
        if (!this.list) return;
        this.input.addEventListener('input', () => this.check());
    }

    check() {
        const { value } = this.input;
        this.list.querySelectorAll('[data-rule]').forEach((item) => {
            const ok = PasswordChecker.RULES[item.dataset.rule]?.(value) ?? false;
            item.classList.toggle('is-ok', ok);
            item.setAttribute('data-state', ok ? 'respectée' : 'manquante');
        });
    }
}

/** Galerie de la page détail d'un menu : la vignette cliquée remplace l'image principale. */
const initGallery = () => {
    const main = document.getElementById('gallery-main');
    const thumbs = document.querySelectorAll('.gallery-thumb');
    if (!main || !thumbs.length) return;

    thumbs.forEach((thumb) => {
        thumb.addEventListener('click', () => {
            const { src, alt } = thumb.dataset;
            main.src = src;
            main.alt = alt;
            thumbs.forEach((other) => other.setAttribute('aria-pressed', String(other === thumb)));
        });
    });
};

initConfirmForms();
document.querySelectorAll('.js-password').forEach((input) => new PasswordChecker(input).bind());
initGallery();
