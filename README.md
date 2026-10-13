# Vite & Gourmand

Application web du traiteur bordelais **Vite & Gourmand** (Julie et José) : consultation des menus, filtres dynamiques, commande en ligne, suivi de commande, avis clients, espace employé et espace administrateur avec statistiques issues d'une base NoSQL.

Projet réalisé dans le cadre de l'ECF Studi — *TP Développeur Web et Web Mobile*.

| | |
|---|---|
| Front | HTML 5, CSS 3 (Bootstrap 5.3 + feuille de styles maison), JavaScript natif (fetch) |
| Back | PHP 8.1+ sans framework, architecture MVC légère, PDO |
| Base relationnelle | MySQL 8 / MariaDB 10.6+ |
| Base NoSQL | MongoDB (Atlas en production) — repli JSON automatique en local |
| Déploiement | alwaysdata (production : https://elbraiki.alwaysdata.net) — configuration Docker + Fly.io également fournie |

---

## 1. Prérequis

- PHP **8.1 ou plus** avec les extensions `pdo_mysql`, `mbstring`, `fileinfo`
- MySQL 8 ou MariaDB 10.6+ (XAMPP, MAMP, WAMP ou installation native)
- [Composer](https://getcomposer.org/) (pour PHPMailer et le client MongoDB)
- *Optionnel* : MongoDB 6+ et l'extension PHP `mongodb` (`pecl install mongodb`)
- Git

## 2. Installation en local

```bash
# 1. Cloner le dépôt
git clone https://github.com/mahmoud-elbraiki-dev/vite-gourmand.git
cd vite-gourmand

# 2. Dépendances PHP
composer install
#    (sans l'extension mongodb installée : composer install --ignore-platform-req=ext-mongodb)

# 3. Configuration
cp .env.example .env          # puis renseigner DB_USER / DB_PASS

# 4. Base de données relationnelle (création + données de démonstration)
mysql -u root -p < database/01_schema.sql
mysql -u root -p < database/02_data.sql

# 5. Base NoSQL
#    a) avec MongoDB : renseigner MONGO_URI dans .env puis
mongosh "mongodb://localhost:27017" database/03_mongo_init.js
#    b) sans MongoDB : laisser MONGO_URI vide, puis alimenter le stockage JSON de repli
php bin/sync-nosql.php

# 6. Lancer le serveur
php -S localhost:8000 -t public public/index.php
```

Ouvrir **http://localhost:8000**.

### Avec XAMPP / WAMP / MAMP

1. Copier le dossier dans `htdocs/` (ou `www/`).
2. Créer un hôte virtuel dont la racine (`DocumentRoot`) est le dossier **`public/`** du projet — le fichier `public/.htaccess` redirige toutes les URL vers `index.php` (module `mod_rewrite` requis).
3. Importer `database/01_schema.sql` puis `database/02_data.sql` dans phpMyAdmin (onglet *Importer*).
4. Renseigner `.env` (par défaut sous XAMPP : `DB_USER=root`, `DB_PASS=` vide).

> La base du projet s'appelle **`vite_gourmand_ecf`**. Les scripts ne touchent pas à une éventuelle autre base (par exemple une ancienne `vite_gourmand` créée par une version précédente du projet). Si le site affiche une erreur du type `Field '…' doesn't have a default value` ou `Unknown column`, c'est que l'application est branchée sur une base qui n'a pas le schéma de ce projet : vérifiez `DB_NAME` dans `.env` et importez `01_schema.sql` puis `02_data.sql`.

> Le dossier `public/` est la seule racine web : le code (`src/`), les gabarits, le fichier `.env` et les scripts SQL ne sont jamais accessibles depuis le navigateur.

## 3. Comptes de démonstration

| Rôle | Identifiant | Mot de passe |
|---|---|---|
| Administrateur (José) | `jose@vitegourmand.fr` | `Admin!Gourmand2026` |
| Employée (Julie) | `julie@vitegourmand.fr` | `Employe!Gourmand2026` |
| Utilisateur | `client@test.fr` | `Client!Gourmand2026` |
| Employé désactivé | `ancien.employe@vitegourmand.fr` | `Employe!Gourmand2026` (connexion refusée) |

Le compte administrateur n'existe que dans le script SQL : **aucun administrateur ne peut être créé depuis l'application**.

## 4. E-mails

Sans serveur SMTP configuré, chaque e-mail (bienvenue, confirmation de commande, réinitialisation de mot de passe, retour de matériel, invitation à donner son avis, création de compte employé, formulaire de contact) est écrit dans `storage/logs/mail.log`. En production, renseigner les variables `MAIL_*` (ex. Brevo, Mailjet, Gmail SMTP).

## 5. Structure du projet

```
vite-gourmand/
├── public/                 Racine web (seul dossier exposé)
│   ├── index.php           Contrôleur frontal + table de routage
│   ├── assets/             CSS, JS, polices et bibliothèques auto-hébergées
│   └── uploads/            Images des menus et des plats (exécution PHP interdite)
├── src/
│   ├── Core/               Database (PDO), Auth, Csrf, View, Mailer, Upload, Env
│   ├── Controller/         Un contrôleur par espace (Home, Menu, Order, Account, Staff, Admin…)
│   ├── Repository/         Accès aux données SQL (requêtes préparées uniquement)
│   └── Service/            Règles métier : PricingService, OrderStatus, StatsStore (NoSQL)
├── templates/              Gabarits PHP (layout, pages, partiels)
├── database/               01_schema.sql, 02_data.sql, 03_mongo_init.js
├── bin/sync-nosql.php      Resynchronisation MySQL → NoSQL
├── storage/                Journaux (mail.log) et stockage NoSQL de repli
├── Dockerfile, fly.toml    Déploiement
└── docs/                   Documentation (PDF) et captures
```

## 6. Règles de gestion principales

- Prix d'une commande = prix par personne × nombre de personnes (minimum imposé par le menu)
- **−10 %** dès que le nombre de personnes ≥ minimum + 5
- Livraison : gratuite à Bordeaux, sinon **5 € + 0,59 €/km**
- Le prix est **toujours recalculé côté serveur**
- Le client modifie / annule tant que la commande est « en attente » ; ensuite, seul un employé le peut, **après avoir contacté le client** (mode de contact + motif obligatoires)
- Cycle : en attente → accepté → en préparation → en cours de livraison → livré → (en attente du retour de matériel) → terminée
- Chaque changement de statut est historisé (date et heure) et affiché dans le suivi client

## 7. Workflow Git

- `main` : branche de production (déployée)
- `develop` : branche d'intégration
- `feature/<fonctionnalité>` : une branche par fonctionnalité, créée depuis `develop`, fusionnée dans `develop` après test, puis `develop` est fusionnée dans `main` une fois testée.

## 8. Déploiement

Voir la *Documentation technique* (`docs/`) — résumé :

```bash
fly launch --no-deploy            # crée l'application à partir de fly.toml
fly secrets set DB_HOST=... DB_USER=... DB_PASS=... MONGO_URI=... APP_URL=https://vite-gourmand.fly.dev
fly deploy
```
