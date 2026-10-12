-- =====================================================================
--  Vite & Gourmand — Script de création de la base de données
--  SGBD : MySQL 8 / MariaDB 10.6+  (moteur InnoDB, utf8mb4)
--  Basé sur le MCD fourni en annexe 1 de l'énoncé, complété par les
--  tables nécessaires aux règles de gestion (galerie, suivi, reset mdp…)
-- =====================================================================

SET NAMES utf8mb4;

DROP DATABASE IF EXISTS vite_gourmand;
CREATE DATABASE vite_gourmand CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE vite_gourmand;

-- ---------------------------------------------------------------------
-- Référentiels
-- ---------------------------------------------------------------------
CREATE TABLE role (
    role_id   INT          NOT NULL AUTO_INCREMENT,
    libelle   VARCHAR(50)  NOT NULL UNIQUE,
    PRIMARY KEY (role_id)
) ENGINE=InnoDB;

CREATE TABLE theme (
    theme_id  INT          NOT NULL AUTO_INCREMENT,
    libelle   VARCHAR(50)  NOT NULL UNIQUE,
    PRIMARY KEY (theme_id)
) ENGINE=InnoDB;

CREATE TABLE regime (
    regime_id INT          NOT NULL AUTO_INCREMENT,
    libelle   VARCHAR(50)  NOT NULL UNIQUE,
    PRIMARY KEY (regime_id)
) ENGINE=InnoDB;

CREATE TABLE allergene (
    allergene_id INT         NOT NULL AUTO_INCREMENT,
    libelle      VARCHAR(50) NOT NULL UNIQUE,
    PRIMARY KEY (allergene_id)
) ENGINE=InnoDB;

CREATE TABLE horaire (
    horaire_id      INT         NOT NULL AUTO_INCREMENT,
    jour            VARCHAR(50) NOT NULL,
    ordre           TINYINT     NOT NULL DEFAULT 0,   -- 1 = lundi … 7 = dimanche (tri)
    heure_ouverture VARCHAR(50) NULL,                 -- NULL = fermé
    heure_fermeture VARCHAR(50) NULL,
    PRIMARY KEY (horaire_id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Utilisateurs  (utilisateur 1,1 — possède — 0,n role)
-- ---------------------------------------------------------------------
CREATE TABLE utilisateur (
    utilisateur_id  INT          NOT NULL AUTO_INCREMENT,
    email           VARCHAR(180) NOT NULL UNIQUE,
    password        VARCHAR(255) NOT NULL,            -- hash bcrypt (password_hash)
    nom             VARCHAR(50)  NOT NULL DEFAULT '',
    prenom          VARCHAR(50)  NOT NULL DEFAULT '',
    telephone       VARCHAR(50)  NOT NULL DEFAULT '',
    ville           VARCHAR(50)  NOT NULL DEFAULT '',
    pays            VARCHAR(50)  NOT NULL DEFAULT 'France',
    adresse_postale VARCHAR(255) NOT NULL DEFAULT '',
    actif           TINYINT(1)   NOT NULL DEFAULT 1,  -- 0 = compte désactivé (départ employé)
    role_id         INT          NOT NULL,
    date_creation   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (utilisateur_id),
    CONSTRAINT fk_utilisateur_role FOREIGN KEY (role_id) REFERENCES role(role_id)
) ENGINE=InnoDB;

CREATE TABLE reinitialisation_mdp (
    id             INT         NOT NULL AUTO_INCREMENT,
    utilisateur_id INT         NOT NULL,
    token_hash     CHAR(64)    NOT NULL,              -- SHA-256 du jeton (jamais le jeton en clair)
    expire_le      DATETIME    NOT NULL,
    utilise        TINYINT(1)  NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    INDEX idx_token (token_hash),
    CONSTRAINT fk_reset_user FOREIGN KEY (utilisateur_id) REFERENCES utilisateur(utilisateur_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Menus  (menu 1,1 — adapte — 0,n regime ; menu 1,1 — propose — 0,n theme)
-- ---------------------------------------------------------------------
CREATE TABLE menu (
    menu_id                  INT           NOT NULL AUTO_INCREMENT,
    titre                    VARCHAR(100)  NOT NULL,
    nombre_personne_minimum  INT           NOT NULL,
    prix_par_personne        DECIMAL(8,2)  NOT NULL,
    description              TEXT          NOT NULL,
    conditions               TEXT          NOT NULL,
    quantite_restante        INT           NOT NULL DEFAULT 0,
    regime_id                INT           NOT NULL,
    theme_id                 INT           NOT NULL,
    actif                    TINYINT(1)    NOT NULL DEFAULT 1,
    PRIMARY KEY (menu_id),
    CONSTRAINT fk_menu_regime FOREIGN KEY (regime_id) REFERENCES regime(regime_id),
    CONSTRAINT fk_menu_theme  FOREIGN KEY (theme_id)  REFERENCES theme(theme_id),
    CONSTRAINT chk_menu_min   CHECK (nombre_personne_minimum > 0),
    CONSTRAINT chk_menu_prix  CHECK (prix_par_personne >= 0),
    CONSTRAINT chk_menu_stock CHECK (quantite_restante >= 0)
) ENGINE=InnoDB;

-- Galerie d'images du menu
CREATE TABLE menu_image (
    image_id  INT          NOT NULL AUTO_INCREMENT,
    menu_id   INT          NOT NULL,
    chemin    VARCHAR(255) NOT NULL,
    alt       VARCHAR(150) NOT NULL DEFAULT '',
    ordre     TINYINT      NOT NULL DEFAULT 0,
    PRIMARY KEY (image_id),
    CONSTRAINT fk_image_menu FOREIGN KEY (menu_id) REFERENCES menu(menu_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Plats  (menu 1,n — propose — 0,n plat ; plat 0,n — contient — 0,n allergene)
-- ---------------------------------------------------------------------
CREATE TABLE plat (
    plat_id    INT          NOT NULL AUTO_INCREMENT,
    titre_plat VARCHAR(100) NOT NULL,
    type_plat  ENUM('entree','plat','dessert') NOT NULL,
    photo      VARCHAR(255) NULL,                     -- chemin du fichier (voir doc technique)
    PRIMARY KEY (plat_id)
) ENGINE=InnoDB;

CREATE TABLE menu_plat (                               -- association « propose » menu/plat
    menu_id INT NOT NULL,
    plat_id INT NOT NULL,
    PRIMARY KEY (menu_id, plat_id),
    CONSTRAINT fk_mp_menu FOREIGN KEY (menu_id) REFERENCES menu(menu_id) ON DELETE CASCADE,
    CONSTRAINT fk_mp_plat FOREIGN KEY (plat_id) REFERENCES plat(plat_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE plat_allergene (                          -- association « contient »
    plat_id      INT NOT NULL,
    allergene_id INT NOT NULL,
    PRIMARY KEY (plat_id, allergene_id),
    CONSTRAINT fk_pa_plat FOREIGN KEY (plat_id) REFERENCES plat(plat_id) ON DELETE CASCADE,
    CONSTRAINT fk_pa_all  FOREIGN KEY (allergene_id) REFERENCES allergene(allergene_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Commandes  (utilisateur 0,n — commande — 0,n menu)
-- ---------------------------------------------------------------------
CREATE TABLE commande (
    numero_commande      VARCHAR(50)   NOT NULL,      -- ex. VG-20261012-4F2A
    utilisateur_id       INT           NOT NULL,
    menu_id              INT           NOT NULL,
    date_commande        DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    date_prestation      DATE          NOT NULL,
    heure_livraison      VARCHAR(50)   NOT NULL,
    adresse_prestation   VARCHAR(255)  NOT NULL,
    ville_prestation     VARCHAR(100)  NOT NULL,
    distance_km          DECIMAL(6,1)  NOT NULL DEFAULT 0,
    nombre_personne      INT           NOT NULL,
    prix_menu            DECIMAL(10,2) NOT NULL,      -- après réduction éventuelle
    reduction            DECIMAL(10,2) NOT NULL DEFAULT 0,
    prix_livraison       DECIMAL(10,2) NOT NULL DEFAULT 0,
    statut               VARCHAR(50)   NOT NULL DEFAULT 'en attente',
    pret_materiel        TINYINT(1)    NOT NULL DEFAULT 0,
    restitution_materiel TINYINT(1)    NOT NULL DEFAULT 0,
    motif_annulation     VARCHAR(255)  NULL,
    mode_contact         VARCHAR(20)   NULL,          -- 'GSM' ou 'mail' (obligatoire avant modif/annulation employé)
    PRIMARY KEY (numero_commande),
    INDEX idx_commande_statut (statut),
    INDEX idx_commande_user (utilisateur_id),
    CONSTRAINT fk_commande_user FOREIGN KEY (utilisateur_id) REFERENCES utilisateur(utilisateur_id),
    CONSTRAINT fk_commande_menu FOREIGN KEY (menu_id) REFERENCES menu(menu_id),
    CONSTRAINT chk_commande_nb CHECK (nombre_personne > 0)
) ENGINE=InnoDB;

-- Suivi : historique de tous les états avec date et heure
CREATE TABLE commande_suivi (
    suivi_id        INT          NOT NULL AUTO_INCREMENT,
    numero_commande VARCHAR(50)  NOT NULL,
    statut          VARCHAR(50)  NOT NULL,
    date_modif      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    auteur_id       INT          NULL,
    PRIMARY KEY (suivi_id),
    CONSTRAINT fk_suivi_commande FOREIGN KEY (numero_commande) REFERENCES commande(numero_commande) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Avis  (utilisateur 1,1 — publie — 0,n avis)
-- ---------------------------------------------------------------------
CREATE TABLE avis (
    avis_id         INT          NOT NULL AUTO_INCREMENT,
    note            TINYINT      NOT NULL,
    description     VARCHAR(500) NOT NULL,
    statut          VARCHAR(50)  NOT NULL DEFAULT 'en attente',  -- en attente / valide / refuse
    utilisateur_id  INT          NOT NULL,
    numero_commande VARCHAR(50)  NOT NULL UNIQUE,                -- un avis par commande
    date_avis       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (avis_id),
    CONSTRAINT fk_avis_user FOREIGN KEY (utilisateur_id) REFERENCES utilisateur(utilisateur_id),
    CONSTRAINT fk_avis_commande FOREIGN KEY (numero_commande) REFERENCES commande(numero_commande) ON DELETE CASCADE,
    CONSTRAINT chk_avis_note CHECK (note BETWEEN 1 AND 5)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Utilisateur applicatif à privilèges restreints (principe du moindre privilège)
-- À adapter en production : mot de passe fort défini dans le .env
-- ---------------------------------------------------------------------
-- CREATE USER IF NOT EXISTS 'vg_app'@'localhost' IDENTIFIED BY 'ChangeMoi!2026';
-- GRANT SELECT, INSERT, UPDATE, DELETE ON vite_gourmand.* TO 'vg_app'@'localhost';
