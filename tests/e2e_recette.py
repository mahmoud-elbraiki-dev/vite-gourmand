"""Recette de bout en bout (Playwright) : pip install playwright && playwright install chromium
Lancer l'application sur http://127.0.0.1:8000 avec une base fraîchement importée, puis : python tests/e2e_recette.py"""
import json, os, re, sys
from datetime import date, timedelta
from playwright.sync_api import sync_playwright

BASE = 'http://127.0.0.1:8000'
SHOTS = os.path.join(os.path.dirname(__file__), '..', 'docs', 'captures')
os.makedirs(SHOTS, exist_ok=True)

SHOT_CSS = "document.addEventListener('DOMContentLoaded',()=>{const s=document.createElement('style');s.textContent='.site-header{position:static!important}';document.head.appendChild(s);});"
errors = []
results = []


def ok(cond, label):
    results.append(('OK ' if cond else 'KO ') + label)
    if not cond:
        errors.append(label)


def flash(page):
    return ' | '.join(page.locator('.alert').all_inner_texts())


def login(page, email, pwd):
    page.goto(BASE + '/connexion')
    page.fill('#email', email)
    page.fill('#password', pwd)
    page.click('button[type=submit]:has-text("Se connecter")')
    page.wait_for_load_state('networkidle')


def logout(page):
    page.click('button:has-text("Déconnexion")')
    page.wait_for_load_state('networkidle')


with sync_playwright() as p:
    b = p.chromium.launch()
    ctx = b.new_context(viewport={'width': 1440, 'height': 900}, locale='fr-FR', bypass_csp=True)
    ctx.add_init_script(SHOT_CSS)
    page = ctx.new_page()
    console = []
    page.on('console', lambda m: console.append(m.type + ': ' + m.text) if m.type in ('error', 'warning') else None)
    page.on('pageerror', lambda e: console.append('pageerror: ' + str(e)))

    # --- Visiteur : accueil, menus, filtres dynamiques
    page.goto(BASE + '/')
    ok(page.locator('.review').count() == 2, 'Accueil : 2 avis validés affichés')
    ok('Dimanche' in page.locator('.hours').inner_text() and 'Fermé' in page.locator('.hours').inner_text(), 'Pied de page : horaires lundi→dimanche')
    page.screenshot(path=f'{SHOTS}/desktop-accueil.png', full_page=True)

    page.goto(BASE + '/menus')
    ok(page.locator('#menu-list .menu-card').count() == 6, 'Vue globale : 6 menus')
    url_before = page.url
    page.select_option('#regime', label='Vegan')
    page.wait_for_timeout(700)
    ok(page.locator('#menu-list .menu-card').count() == 1, 'Filtre régime vegan sans rechargement')
    page.select_option('#regime', value='')
    page.fill('#prix_max', '200')
    page.wait_for_timeout(800)
    ok(page.locator('#menu-list .menu-card').count() == 3, 'Filtre prix max 200 €')
    page.fill('#prix_min', '100')
    page.wait_for_timeout(800)
    ok(page.locator('#menu-list .menu-card').count() == 2, 'Filtre fourchette 100–200 €')
    page.fill('#prix_min', ''); page.fill('#prix_max', '')
    page.select_option('#theme', label='Noël')
    page.wait_for_timeout(700)
    ok(page.locator('#menu-list .menu-card').count() == 1, 'Filtre thème Noël')
    page.select_option('#theme', value='')
    page.fill('#personnes', '6')
    page.wait_for_timeout(800)
    ok(page.locator('#menu-list .menu-card').count() == 4, 'Filtre nombre de personnes (6)')
    page.click('#reset-filters'); page.wait_for_timeout(700)
    page.screenshot(path=f'{SHOTS}/desktop-menus.png', full_page=True)

    page.goto(BASE + '/menu?id=1')
    ok(page.locator('.conditions').count() == 1, 'Détail : conditions mises en évidence')
    ok(page.locator('.allergen').count() > 0, 'Détail : allergènes affichés')
    page.screenshot(path=f'{SHOTS}/desktop-detail.png', full_page=True)
    page.click('a:has-text("Commander ce menu")')
    page.wait_for_load_state('networkidle')
    ok('/connexion' in page.url, 'Visiteur redirigé vers la connexion avant commande')

    # --- Inscription
    page.goto(BASE + '/inscription')
    for k, v in {'#prenom': 'Inès', '#nom': 'Test', '#email': 'ines@test.fr', '#telephone': '0611223344',
                 '#adresse_postale': '5 rue Judaïque', '#ville': 'Bordeaux'}.items():
        page.fill(k, v)
    page.fill('#password', 'faible')
    page.fill('#password_confirm', 'faible')
    page.check('#rgpd')
    page.click('button:has-text("Créer mon compte")')
    page.wait_for_load_state('networkidle')
    ok('Mot de passe' in flash(page), 'Inscription refusée avec mot de passe faible')
    page.fill('#password', 'Gourmand!2026x')
    page.fill('#password_confirm', 'Gourmand!2026x')
    page.check('#rgpd')
    page.screenshot(path=f'{SHOTS}/desktop-inscription.png', full_page=True)
    page.click('button:has-text("Créer mon compte")')
    page.wait_for_load_state('networkidle')
    ok('/espace' in page.url, 'Inscription OK + connexion auto (rôle utilisateur)')

    # --- Commande (hors Bordeaux, réduction)
    page.goto(BASE + '/commande?menu=3')
    ok(page.input_value('#o-email') == 'ines@test.fr', 'Commande : e-mail pré-rempli')
    page.fill('#ville_prestation', 'Mérignac')
    page.fill('#distance_km', '10')
    page.fill('#date_prestation', (date.today() + timedelta(days=12)).isoformat())
    page.fill('#heure_livraison', '19:30')
    page.fill('#nombre_personne', '9')
    page.dispatch_event('#distance_km', 'input')
    page.wait_for_timeout(300)
    total_js = page.inner_text('#s-total')
    ok('11,10' in page.inner_text('#s-livraison') and '-' in page.inner_text('#s-reduc') or '−' in page.inner_text('#s-reduc'), 'Prix JS : livraison 11,10 € et réduction')
    page.check('#conditions_lues')
    page.screenshot(path=f'{SHOTS}/desktop-commande.png', full_page=True)
    page.click('button:has-text("Valider ma commande")')
    page.wait_for_load_state('networkidle')
    ok('/espace/commande' in page.url, 'Commande enregistrée')
    txt = page.inner_text('main')
    # 29.50*9=265.50, -26.55 = 238.95 ; livraison 5+5.9=10.90 ? distance 10 km => 5 + 0.59*10 = 10.90
    ok('238,95' in txt and '10,90' in txt and '249,85' in txt, 'Prix serveur : 238,95 € + 10,90 € = 249,85 €')
    numero = re.search(r'VG-\d{8}-[0-9A-F]{4}', txt).group(0)

    # Modification puis annulation par l'utilisateur
    page.click('a:has-text("Modifier")')
    page.fill('#nombre_personne', '4')
    page.click('button:has-text("Enregistrer les modifications")')
    page.wait_for_load_state('networkidle')
    ok('modifiée' in flash(page), 'Utilisateur : modification commande en attente')
    page.screenshot(path=f'{SHOTS}/desktop-espace-commande.png', full_page=True)
    page.on('dialog', lambda d: d.accept())
    page.click('button:has-text("Annuler la commande")')
    page.wait_for_load_state('networkidle')
    ok('annulée' in flash(page), 'Utilisateur : annulation commande en attente')

    # Nouvelle commande qui ira jusqu'au bout
    page.goto(BASE + '/commande?menu=6')
    page.fill('#date_prestation', (date.today() + timedelta(days=40)).isoformat())
    page.fill('#nombre_personne', '20')
    page.check('#conditions_lues')
    page.click('button:has-text("Valider ma commande")')
    page.wait_for_load_state('networkidle')
    numero2 = re.search(r'VG-\d{8}-[0-9A-F]{4}', page.inner_text('main')).group(0)
    page.goto(BASE + '/espace')
    page.screenshot(path=f'{SHOTS}/desktop-espace.png', full_page=True)
    page.goto(BASE + '/gestion')
    ok(page.locator('h1').inner_text().startswith('Erreur') or '403' in page.content(), 'Utilisateur : accès espace employé refusé (403)')
    logout(page)

    # --- Employé
    login(page, 'julie@vitegourmand.fr', 'Employe!Gourmand2026')
    ok('/gestion' in page.url, 'Connexion employé → espace employé')
    page.goto(BASE + '/gestion?client=Inès')
    ok(page.locator('main table tbody tr').count() == 2, 'Filtre commandes par client')
    page.goto(BASE + '/gestion?statut=en+attente')
    page.screenshot(path=f'{SHOTS}/desktop-gestion.png', full_page=True)
    page.goto(BASE + '/gestion/commande?numero=' + numero2)
    # annulation sans contact refusée (bypass HTML5 validation)
    page.evaluate("document.querySelectorAll('form').forEach(f => f.noValidate = true)")
    page.click('summary:has-text("Annuler la commande")')
    page.click('button.btn-danger-soft:has-text("Annuler la commande")')
    page.wait_for_load_state('networkidle')
    ok('mode de contact' in flash(page), 'Employé : annulation impossible sans contact client')
    for st in ['accepté', 'en préparation', 'en cours de livraison', 'livré']:
        page.select_option('#statut', label=st[0].upper() + st[1:])
        if st == 'livré':
            page.check('#pret_materiel')
        page.click('button:has-text("Mettre à jour")')
        page.wait_for_load_state('networkidle')
    page.select_option('#statut', label='En attente du retour de matériel')
    page.click('button:has-text("Mettre à jour")'); page.wait_for_load_state('networkidle')
    page.select_option('#statut', label='Terminée')
    page.click('button:has-text("Mettre à jour")'); page.wait_for_load_state('networkidle')
    ok('restitué' in flash(page), 'Employé : terminer sans restitution du matériel refusé')
    page.check('#restitution_materiel')
    page.select_option('#statut', label='Terminée')
    page.click('button:has-text("Mettre à jour")'); page.wait_for_load_state('networkidle')
    ok('terminée' in flash(page), 'Employé : commande terminée')
    page.screenshot(path=f'{SHOTS}/desktop-gestion-commande.png', full_page=True)

    # Horaires
    page.goto(BASE + '/gestion/horaires')
    page.fill('input[name="horaires[6][fermeture]"]', '18:00')
    page.click('button:has-text("Enregistrer les horaires")'); page.wait_for_load_state('networkidle')
    ok('10:00 – 18:00' in page.locator('.hours').inner_text(), 'Employé : horaires modifiés et visibles en pied de page')
    # Menu : édition
    page.goto(BASE + '/gestion/menu?id=5')
    page.fill('#quantite_restante', '7')
    page.click('button:has-text("Enregistrer le menu")'); page.wait_for_load_state('networkidle')
    ok('Menu enregistré' in flash(page), 'Employé : modification menu')
    page.screenshot(path=f'{SHOTS}/desktop-gestion-menu.png', full_page=True)
    page.goto(BASE + '/gestion/plats')
    page.screenshot(path=f'{SHOTS}/desktop-gestion-plats.png', full_page=True)
    page.goto(BASE + '/admin/employes')
    ok('Accès refusé' in page.locator('h1').inner_text(), 'Employé : accès administration refusé')
    logout(page)

    # --- Client : avis
    login(page, 'ines@test.fr', 'Gourmand!2026x')
    page.goto(BASE + '/espace/commande?numero=' + numero2)
    ok(page.locator('.timeline li').count() >= 7, 'Utilisateur : suivi avec historique daté')
    page.click('label[for=note-5]')
    page.fill('#description', 'Service impeccable pour notre séminaire, merci !')
    page.click('button:has-text("Publier mon avis")'); page.wait_for_load_state('networkidle')
    ok('Merci pour votre avis' in flash(page), 'Utilisateur : dépôt d\'avis (note 1-5 + commentaire)')
    logout(page)

    # --- Admin
    login(page, 'jose@vitegourmand.fr', 'Admin!Gourmand2026')
    ok('/admin' in page.url, 'Connexion admin → statistiques')
    page.goto(BASE + '/gestion/avis')
    page.locator('article:has-text("séminaire") button:has-text("Valider")').click()
    page.wait_for_load_state('networkidle')
    page.screenshot(path=f'{SHOTS}/desktop-gestion-avis.png', full_page=True)
    page.goto(BASE + '/')
    ok(page.locator('.review').count() == 3, 'Avis validé visible sur l\'accueil')
    page.goto(BASE + '/admin/employes')
    page.fill('#email', 'marc@vitegourmand.fr'); page.fill('#password', 'Employe!Marc2026')
    page.fill('#prenom', 'Marc'); page.fill('#nom', 'Lopez')
    page.click('button:has-text("Créer le compte")'); page.wait_for_load_state('networkidle')
    ok('Compte employé créé' in flash(page), 'Admin : création compte employé')
    page.locator('tr:has-text("marc@vitegourmand.fr") button:has-text("Désactiver")').click()
    page.wait_for_load_state('networkidle')
    page.screenshot(path=f'{SHOTS}/desktop-admin-employes.png', full_page=True)
    page.goto(BASE + '/admin/statistiques')
    page.wait_for_timeout(1200)
    ok(page.inner_text('#k-commandes').strip() not in ('—', '0'), 'Admin : statistiques NoSQL chargées')
    page.screenshot(path=f'{SHOTS}/desktop-admin-stats.png', full_page=True)
    page.select_option('#s-menu', label='Grand événement')
    page.wait_for_timeout(800)
    ok(page.locator('#stats-body tr').count() == 1, 'Admin : filtre CA par menu')
    logout(page)

    login(page, 'marc@vitegourmand.fr', 'Employe!Marc2026')
    ok('désactivé' in flash(page), 'Compte employé désactivé : connexion bloquée')

    # --- Mobile
    m = b.new_context(viewport={'width': 390, 'height': 844}, device_scale_factor=2, is_mobile=True, has_touch=True, locale='fr-FR', bypass_csp=True)
    m.add_init_script(SHOT_CSS)
    mp = m.new_page()
    mp.goto(BASE + '/'); mp.screenshot(path=f'{SHOTS}/mobile-accueil.png', full_page=True)
    mp.goto(BASE + '/menus'); mp.wait_for_timeout(300); mp.screenshot(path=f'{SHOTS}/mobile-menus.png', full_page=True)
    mp.goto(BASE + '/menu?id=1'); mp.screenshot(path=f'{SHOTS}/mobile-detail.png', full_page=True)
    mp.click('.navbar-toggler'); mp.wait_for_timeout(500); mp.screenshot(path=f'{SHOTS}/mobile-nav.png')
    login(mp, 'client@test.fr', 'Client!Gourmand2026')
    mp.goto(BASE + '/commande?menu=3'); mp.screenshot(path=f'{SHOTS}/mobile-commande.png', full_page=True)
    mp.goto(BASE + '/espace/commande?numero=VG-20260815-A1B2'); mp.screenshot(path=f'{SHOTS}/mobile-suivi.png', full_page=True)
    # Pas de défilement horizontal
    for u in ['/', '/menus', '/menu?id=1', '/commande?menu=3', '/espace', '/espace/commande?numero=VG-20260815-A1B2', '/contact', '/espace/profil']:
        mp.goto(BASE + u)
        sw = mp.evaluate('document.documentElement.scrollWidth')
        ok(sw <= 391, f'Mobile {u} : pas de défilement horizontal ({sw}px)')

    b.close()

print('\n'.join(results))
print('\nConsole :', console[:10])
print('ÉCHECS :', errors)
