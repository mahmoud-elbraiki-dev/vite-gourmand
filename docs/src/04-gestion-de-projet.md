# Gestion de projet

## 1. Méthode

Le projet a été mené selon une approche **agile de type Kanban**, adaptée à un développement en solo avec un client (Julie et José) et un chef de projet FastDev :

- un **tableau Kanban** (Trello / Notion) à cinq colonnes : *Backlog*, *À faire*, *En cours*, *En test*, *Terminé* ;
- chaque besoin de l'énoncé est découpé en **user stories** (« En tant que… je veux… afin de… ») avec des critères d'acceptation, une étiquette de priorité (MoSCoW) et une étiquette d'espace (visiteur, utilisateur, employé, admin, transverse) ;
- des **itérations d'une semaine** ; en fin d'itération, démonstration au chef de projet et réajustement du backlog ;
- règle « une carte = une branche Git » : chaque carte passée *En cours* correspond à une branche `feature/…` créée depuis `develop` ; elle passe *En test* à l'ouverture de la fusion et *Terminé* une fois fusionnée et testée.

## 2. Planning (≈ 70 h)

| Itération | Contenu | Charge |
|---|---|---|
| 0 — Cadrage | Lecture de l'énoncé, analyse des besoins, user stories, choix techniques, environnement | 6 h |
| 1 — Conception | MCD complété, diagrammes (cas d'utilisation, séquence), charte graphique, wireframes et maquettes | 10 h |
| 2 — Socle | Base de données, architecture MVC, sécurité (sessions, CSRF, PDO), layout responsive | 10 h |
| 3 — Visiteur | Accueil, vue globale et filtres dynamiques, détail d'un menu, contact, pages légales | 9 h |
| 4 — Utilisateur | Inscription, connexion, mot de passe oublié, commande et calcul du prix, espace client, avis | 12 h |
| 5 — Back-office | Espace employé (commandes, statuts, menus, plats, horaires, avis), espace administrateur, statistiques NoSQL | 12 h |
| 6 — Qualité et livraison | Recette, accessibilité (RGAA), corrections, déploiement, documentation | 11 h |

## 3. Backlog (extrait)

| ID | User story | Priorité | Espace |
|---|---|---|---|
| US01 | En tant que visiteur, je veux voir la présentation de l'entreprise et les avis validés afin d'avoir confiance | Must | Visiteur |
| US02 | En tant que visiteur, je veux filtrer les menus (prix, fourchette, thème, régime, personnes) sans rechargement | Must | Visiteur |
| US03 | En tant que visiteur, je veux voir le détail d'un menu, ses plats, allergènes et conditions bien visibles | Must | Visiteur |
| US04 | En tant que visiteur, je veux créer un compte avec un mot de passe sécurisé et recevoir un e-mail de bienvenue | Must | Visiteur |
| US05 | En tant qu'utilisateur, je veux me connecter et réinitialiser mon mot de passe par e-mail | Must | Utilisateur |
| US06 | En tant qu'utilisateur, je veux commander un menu avec mes informations pré-remplies et voir le détail du prix | Must | Utilisateur |
| US07 | En tant qu'utilisateur, je veux modifier ou annuler ma commande tant qu'elle n'est pas acceptée | Must | Utilisateur |
| US08 | En tant qu'utilisateur, je veux suivre les états de ma commande avec date et heure | Must | Utilisateur |
| US09 | En tant qu'utilisateur, je veux noter (1 à 5) et commenter une commande terminée | Must | Utilisateur |
| US10 | En tant qu'employé, je veux filtrer les commandes par statut ou client et mettre à jour leur statut | Must | Employé |
| US11 | En tant qu'employé, je dois indiquer le mode de contact et le motif avant de modifier ou annuler une commande | Must | Employé |
| US12 | En tant qu'employé, je veux gérer les menus, plats, allergènes et horaires | Must | Employé |
| US13 | En tant qu'employé, je veux valider ou refuser les avis | Must | Employé |
| US14 | En tant qu'administrateur, je veux créer et désactiver des comptes employés | Must | Admin |
| US15 | En tant qu'administrateur, je veux comparer les commandes par menu (graphique, NoSQL) et le chiffre d'affaires par période | Must | Admin |
| US16 | En tant que client, je veux une application accessible (RGAA) et conforme au RGPD | Must | Transverse |
| US17 | En tant que client, je veux l'application déployée et en ligne à la livraison | Must | Transverse |
| US18 | En tant qu'utilisateur, je veux que la distance de livraison soit estimée automatiquement depuis mon adresse | Should | Utilisateur |

Le backlog complet est fourni au format CSV (`docs/gestion-projet-notion.csv`), importable directement dans Notion ou dans un tableau Trello.

## 4. Gestion des versions (Git)

- `main` : version stable, déployée en production ; étiquette `v1.0.0` à la livraison.
- `develop` : branche d'intégration.
- `feature/<nom>` : une branche par fonctionnalité (`feature/base-de-donnees`, `feature/menus`, `feature/authentification`, `feature/commande`, `feature/espace-utilisateur`, `feature/espace-employe`, `feature/espace-admin`, `feature/contact-legal`, `feature/deploiement`…), fusionnée dans `develop` après test (`--no-ff` pour conserver l'historique), puis `develop` fusionnée dans `main`.
- Messages de commit conventionnels (`feat:`, `fix:`, `docs:`, `chore:`).

## 5. Suivi et qualité

- **Définition de « terminé »** : critères d'acceptation validés, tests de recette passés, aucune erreur console, contrôle clavier et contraste, code relu et fusionné.
- **Recette automatisée** (Playwright) rejouée avant chaque fusion dans `main`.
- **Risques identifiés** : délai (priorisation MoSCoW), hébergement (déploiement anticipé dès l'itération 2), absence de MongoDB sur certains postes (repli JSON), envoi d'e-mails (journal en développement, SMTP en production).
