/* Vite & Gourmand — comportements communes à toutes les pages */
(function () {
    'use strict';

    // Confirmation des actions destructrices
    document.querySelectorAll('form.js-confirm').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            if (!window.confirm(form.dataset.confirm || 'Confirmer ?')) {
                e.preventDefault();
            }
        });
    });

    // Vérification en direct de la politique de mot de passe (aide à la saisie, contrôle réel côté serveur)
    var rules = {
        len: function (v) { return v.length >= 10; },
        upper: function (v) { return /[A-Z]/.test(v); },
        lower: function (v) { return /[a-z]/.test(v); },
        digit: function (v) { return /\d/.test(v); },
        special: function (v) { return /[^A-Za-z0-9]/.test(v); }
    };
    document.querySelectorAll('.js-password').forEach(function (input) {
        var list = document.getElementById(input.getAttribute('aria-describedby'));
        if (!list) { return; }
        input.addEventListener('input', function () {
            list.querySelectorAll('[data-rule]').forEach(function (li) {
                var ok = rules[li.dataset.rule](input.value);
                li.classList.toggle('is-ok', ok);
                li.setAttribute('data-state', ok ? 'respectée' : 'manquante');
            });
        });
    });

    // Galerie du menu
    var main = document.getElementById('gallery-main');
    document.querySelectorAll('.gallery-thumb').forEach(function (btn, _, all) {
        btn.addEventListener('click', function () {
            main.src = btn.dataset.src;
            main.alt = btn.dataset.alt;
            all.forEach(function (b) { b.setAttribute('aria-pressed', b === btn ? 'true' : 'false'); });
        });
    });
})();
