# Livrables

> Consigne : « Les livrables de ce sujet sont les suivants : le lien du (ou des) dépôt(s) GitHub PUBLIC, le lien de votre / vos applications déployées, le lien vers votre logiciel de gestion de projet. »

| Livrable | Lien |
|---|---|
| Dépôt GitHub public | https://github.com/**[à compléter]**/vite-gourmand |
| Application déployée | https://**[à compléter]**.fly.dev |
| Gestion de projet (Trello / Notion) | **[à compléter]** |

Contenu du dépôt Git, conformément à l'énoncé :

| Exigence | Emplacement dans le dépôt |
|---|---|
| README.md : démarche de déploiement en local | `README.md` |
| Bonnes pratiques Git : branche principale, branche de développement, une branche par fonctionnalité | `main`, `develop`, `feature/*` (fusions `--no-ff`, étiquette `v1.0.0`) |
| Fichiers SQL de création de la base et d'intégration des données | `database/01_schema.sql`, `database/02_data.sql` (+ `database/03_mongo_init.js` pour MongoDB) |
| Manuel d'utilisation (PDF) avec identifiants | `docs/Manuel_utilisation.pdf` |
| Charte graphique (PDF) : palette, police, 3 maquettes bureau et 3 mobiles (wireframes et mockups) | `docs/Charte_graphique.pdf` |
| Documentation de gestion de projet | `docs/Gestion_de_projet.pdf` |
| Documentation technique : réflexions initiales, environnement, MCD, diagrammes d'utilisation et de séquence, déploiement | `docs/Documentation_technique.pdf` |

# Identifiants de test

| Rôle | Identifiant | Mot de passe |
|---|---|---|
| Administrateur (José) | jose@vitegourmand.fr | Admin!Gourmand2026 |
| Employée (Julie) | julie@vitegourmand.fr | Employe!Gourmand2026 |
| Utilisateur | client@test.fr | Client!Gourmand2026 |

# Analyse des besoins et couverture de l'énoncé

> Consigne : « L'objectif de cet ECF est que vous passiez sur chaque étape nécessaire à l'élaboration d'une solution de qualité : analyse des besoins, maquettage, intégration, développement des règles de gestion ainsi que le déploiement. »

| Besoin exprimé dans l'énoncé | Réponse apportée |
|---|---|
| Page d'accueil : présentation, professionnalisme de l'équipe, avis validés | Page d'accueil en 4 sections ; seuls les avis au statut « valide » sont affichés, avec la note moyenne |
| Menu : accueil, tous les menus, connexion (employé, administrateur, utilisateur), contact | Barre de navigation commune ; liens contextuels selon le rôle connecté |
| Pied de page : horaires du lundi au dimanche, mentions légales, CGV | Horaires lus en base (modifiables par les employés) ; pages Mentions légales, CGV et Confidentialité |
| Vue globale : titre, description, minimum de personnes, prix, bouton détail, pour tous | Cartes de menus accessibles sans connexion |
| Filtres prix max, fourchette, thème, régime, nombre de personnes, sans rechargement | Formulaire de filtres + API JSON `/api/menus` appelée en `fetch`, URL mise à jour |
| Caractéristiques d'un menu (titre, galerie, description, thème, plats, minimum, prix, allergènes, conditions, régime, stock) | Tables `menu`, `menu_image`, `plat`, `menu_plat`, `allergene`, `plat_allergene`, `theme`, `regime` ; un plat peut appartenir à plusieurs menus |
| Création de compte : nom, prénom, GSM, e-mail, adresse, mot de passe sécurisé, rôle utilisateur, mail de bienvenue | Contrôle de la politique côté client (aide) et côté serveur ; rôle « utilisateur » forcé ; e-mail automatique |
| Connexion par e-mail + mot de passe, réinitialisation par lien e-mail | Jeton aléatoire, haché, valable 1 h, usage unique |
| Vue détaillée : toutes les informations, bouton commander, conditions bien en évidence, connexion exigée | Encadré « Conditions » contrasté + rappel et case à cocher obligatoire sur la commande ; redirection vers la connexion puis retour à la commande |
| Commande : infos pré-remplies, adresse / date / heure / lieu, menu pré-positionné, nombre de personnes ≥ minimum, −10 % dès min + 5, livraison 5 € + 0,59 €/km hors Bordeaux, détail du prix, mail de confirmation | `OrderController` + `PricingService` (recalcul serveur) ; estimation de la distance par la Base Adresse Nationale ; transaction avec contrôle du stock |
| Espace utilisateur : commandes détaillées, modification des informations, annulation / modification avant « accepté » (sauf menu), suivi daté, avis 1 à 5 après « terminée » | `AccountController`, table `commande_suivi`, e-mail d'invitation à l'avis |
| Espace employé : menus, plats, horaires ; modification / annulation après contact (mode + motif) ; filtre par statut et client ; statuts ; mail retour de matériel (600 €) ; validation des avis | `StaffController` ; règles de transition (retour de matériel, restitution avant « terminée ») |
| Espace administrateur : création d'employé (mail sans mot de passe), désactivation, pas de création d'admin, droits employé, graphique depuis une base NoSQL, CA par menu et par durée | `AdminController` + `StatsStore` (MongoDB, agrégation `$group`) + Chart.js |
| Contact : titre, description, e-mail, envoi par mail à l'entreprise | `ContactController` + pot de miel anti-spam |
| Déploiement exigé, accessibilité RGAA | Docker + Fly.io ; mesures RGAA détaillées dans la documentation technique |
