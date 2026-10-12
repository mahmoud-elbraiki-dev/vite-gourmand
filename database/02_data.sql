-- =====================================================================
--  Vite & Gourmand — Jeu de données (intégration)
--  Comptes de démonstration (mots de passe hachés en bcrypt) :
--    Administrateur : jose@vitegourmand.fr   / Admin!Gourmand2026
--    Employé        : julie@vitegourmand.fr  / Employe!Gourmand2026
--    Utilisateur    : client@test.fr         / Client!Gourmand2026
-- =====================================================================
USE vite_gourmand;
SET NAMES utf8mb4;

INSERT INTO role (role_id, libelle) VALUES
 (1, 'utilisateur'), (2, 'employe'), (3, 'administrateur');

INSERT INTO theme (libelle) VALUES ('Noël'), ('Pâques'), ('Classique'), ('Événement');
INSERT INTO regime (libelle) VALUES ('Classique'), ('Végétarien'), ('Vegan'), ('Sans gluten');

INSERT INTO allergene (libelle) VALUES
 ('Gluten'), ('Crustacés'), ('Œufs'), ('Poissons'), ('Arachides'), ('Soja'), ('Lait'),
 ('Fruits à coque'), ('Céleri'), ('Moutarde'), ('Sésame'), ('Sulfites'), ('Lupin'), ('Mollusques');

INSERT INTO horaire (jour, ordre, heure_ouverture, heure_fermeture) VALUES
 ('Lundi', 1, '09:00', '18:00'), ('Mardi', 2, '09:00', '18:00'), ('Mercredi', 3, '09:00', '18:00'),
 ('Jeudi', 4, '09:00', '18:00'), ('Vendredi', 5, '09:00', '19:00'), ('Samedi', 6, '10:00', '17:00'),
 ('Dimanche', 7, NULL, NULL);

-- Le compte administrateur est créé ici (et uniquement ici) : il est impossible
-- de créer un administrateur depuis l'application (exigence de José).
INSERT INTO utilisateur (utilisateur_id, email, password, nom, prenom, telephone, ville, pays, adresse_postale, actif, role_id) VALUES
 (1, 'jose@vitegourmand.fr',  '$2y$12$k6Bs6rrk/HiJK94dkk2/LO/WUgeSlFl6HDceiEgZm6AmNXeVtOrfG', 'Martin', 'José',  '0556000001', 'Bordeaux', 'France', '12 rue Sainte-Catherine', 1, 3),
 (2, 'julie@vitegourmand.fr', '$2y$12$I/dFxJZipwiC.qm4fCfs5.JiP0KPcCQpQ2ozXguM12Yc3L391Lori', 'Martin', 'Julie', '0556000002', 'Bordeaux', 'France', '12 rue Sainte-Catherine', 1, 2),
 (3, 'client@test.fr',        '$2y$12$MS1TIhXWzb3XOm3OmCzxs.FiXn3vDlhRCucMq4U.XlsMpxOhipcbm', 'Durand', 'Camille', '0612345678', 'Bordeaux', 'France', '8 cours de l''Intendance', 1, 1),
 (4, 'lucas@test.fr',         '$2y$12$MS1TIhXWzb3XOm3OmCzxs.FiXn3vDlhRCucMq4U.XlsMpxOhipcbm', 'Bernard', 'Lucas', '0698765432', 'Mérignac', 'France', '3 avenue de l''Yser', 1, 1),
 (5, 'ancien.employe@vitegourmand.fr', '$2y$12$I/dFxJZipwiC.qm4fCfs5.JiP0KPcCQpQ2ozXguM12Yc3L391Lori', 'Petit', 'Hugo', '0556000003', 'Bordeaux', 'France', '', 0, 2);

-- Plats ----------------------------------------------------------------
INSERT INTO plat (plat_id, titre_plat, type_plat, photo) VALUES
 (1,  'Foie gras mi-cuit, chutney de figues', 'entree', 'uploads/plats/plat-1.jpg'),
 (2,  'Velouté de châtaignes', 'entree', 'uploads/plats/plat-2.jpg'),
 (3,  'Œuf parfait, asperges vertes', 'entree', 'uploads/plats/plat-3.jpg'),
 (4,  'Tartare de légumes croquants', 'entree', 'uploads/plats/plat-4.jpg'),
 (5,  'Chapon farci aux marrons', 'plat', 'uploads/plats/plat-5.jpg'),
 (6,  'Gigot d''agneau de sept heures', 'plat', 'uploads/plats/plat-6.jpg'),
 (7,  'Entrecôte à la bordelaise', 'plat', 'uploads/plats/plat-7.jpg'),
 (8,  'Risotto aux cèpes', 'plat', 'uploads/plats/plat-8.jpg'),
 (9,  'Curry de légumes au lait de coco', 'plat', 'uploads/plats/plat-9.jpg'),
 (10, 'Dos de cabillaud, beurre blanc', 'plat', 'uploads/plats/plat-10.jpg'),
 (11, 'Bûche chocolat-praliné', 'dessert', 'uploads/plats/plat-11.jpg'),
 (12, 'Nid de Pâques chocolat', 'dessert', 'uploads/plats/plat-12.jpg'),
 (13, 'Cannelés bordelais', 'dessert', 'uploads/plats/plat-13.jpg'),
 (14, 'Salade de fruits frais à la menthe', 'dessert', 'uploads/plats/plat-14.jpg'),
 (15, 'Pièce montée de choux', 'dessert', 'uploads/plats/plat-15.jpg');

INSERT INTO plat_allergene (plat_id, allergene_id) VALUES
 (1,1),(1,12),(2,7),(2,9),(3,3),(3,7),(5,1),(5,7),(5,8),(6,12),(7,12),(7,7),(8,7),(8,12),
 (10,4),(10,7),(11,1),(11,3),(11,7),(11,8),(12,3),(12,7),(12,6),(13,1),(13,3),(13,7),(15,1),(15,3),(15,7);

-- Menus ----------------------------------------------------------------
INSERT INTO menu (menu_id, titre, nombre_personne_minimum, prix_par_personne, description, conditions, quantite_restante, regime_id, theme_id) VALUES
 (1, 'Réveillon de Noël', 8, 52.00,
  'Un repas de fête généreux : foie gras maison, chapon farci aux marrons et bûche chocolat-praliné pour réunir toute la famille.',
  'À commander au minimum 14 jours avant la prestation. Conserver au réfrigérateur entre 0 et 4 °C et consommer sous 48 h. Matériel de service prêté (à restituer).',
  12, 1, 1),
 (2, 'Brunch de Pâques', 6, 34.00,
  'Œufs parfaits, gigot d''agneau de sept heures et nids en chocolat : un menu printanier pour célébrer Pâques en douceur.',
  'À commander au minimum 7 jours avant la prestation. Conserver au frais (0-4 °C).',
  8, 1, 2),
 (3, 'Tradition bordelaise', 4, 29.50,
  'Le meilleur du Sud-Ouest : entrecôte à la bordelaise, velouté de châtaignes et cannelés maison.',
  'À commander au minimum 3 jours avant la prestation. Réchauffer la viande 10 min à 160 °C.',
  20, 1, 3),
 (4, 'Jardin végétarien', 4, 26.00,
  'Une cuisine de saison, colorée et gourmande, sans viande ni poisson : risotto aux cèpes et tartare de légumes croquants.',
  'À commander au minimum 3 jours avant la prestation. Conserver au frais et consommer sous 24 h.',
  15, 2, 3),
 (5, 'Instant vegan', 4, 24.00,
  'Un menu 100 % végétal : curry de légumes au lait de coco, tartare croquant et salade de fruits à la menthe.',
  'À commander au minimum 3 jours avant la prestation. Sans produit d''origine animale.',
  5, 3, 3),
 (6, 'Grand événement', 20, 45.00,
  'Mariages, anniversaires, séminaires : un menu complet servi avec matériel, finale en pièce montée de choux.',
  'À commander au minimum 4 semaines avant la prestation. Matériel (plats, chafing-dish) prêté : restitution sous 10 jours ouvrés, faute de quoi 600 € de frais seront facturés (voir CGV).',
  3, 1, 4);

INSERT INTO menu_plat (menu_id, plat_id) VALUES
 (1,1),(1,2),(1,5),(1,11),
 (2,3),(2,6),(2,12),(2,14),
 (3,2),(3,7),(3,13),
 (4,4),(4,8),(4,13),
 (5,4),(5,9),(5,14),
 (6,1),(6,3),(6,6),(6,10),(6,15);

INSERT INTO menu_image (menu_id, chemin, alt, ordre) VALUES
 (1, 'uploads/menus/menu-1-a.jpg', 'Table de réveillon dressée avec foie gras', 1),
 (1, 'uploads/menus/menu-1-b.jpg', 'Chapon farci aux marrons', 2),
 (2, 'uploads/menus/menu-2-a.jpg', 'Brunch de Pâques printanier', 1),
 (2, 'uploads/menus/menu-2-b.jpg', 'Nids en chocolat de Pâques', 2),
 (3, 'uploads/menus/menu-3-a.jpg', 'Entrecôte à la bordelaise', 1),
 (3, 'uploads/menus/menu-3-b.jpg', 'Cannelés bordelais', 2),
 (4, 'uploads/menus/menu-4-a.jpg', 'Risotto aux cèpes', 1),
 (5, 'uploads/menus/menu-5-a.jpg', 'Curry de légumes', 1),
 (6, 'uploads/menus/menu-6-a.jpg', 'Buffet de réception', 1),
 (6, 'uploads/menus/menu-6-b.jpg', 'Pièce montée de choux', 2);

-- Commandes de démonstration -------------------------------------------
INSERT INTO commande (numero_commande, utilisateur_id, menu_id, date_commande, date_prestation, heure_livraison,
  adresse_prestation, ville_prestation, distance_km, nombre_personne, prix_menu, reduction, prix_livraison, statut, pret_materiel, restitution_materiel) VALUES
 ('VG-20260815-A1B2', 3, 3, '2026-08-15 10:12:00', '2026-08-29', '12:00', '8 cours de l''Intendance', 'Bordeaux', 0, 6, 177.00, 0, 0, 'terminée', 0, 0),
 ('VG-20260902-C3D4', 4, 4, '2026-09-02 14:40:00', '2026-09-12', '19:30', '3 avenue de l''Yser', 'Mérignac', 9.5, 10, 234.00, 26.00, 10.61, 'terminée', 0, 0),
 ('VG-20260918-E5F6', 3, 6, '2026-09-18 09:05:00', '2026-10-03', '18:00', 'Château Pape Clément', 'Pessac', 7.0, 25, 1012.50, 112.50, 9.13, 'en attente du retour de matériel', 1, 0),
 ('VG-20261001-G7H8', 4, 2, '2026-10-01 16:20:00', '2026-10-20', '11:00', '3 avenue de l''Yser', 'Mérignac', 9.5, 6, 204.00, 0, 10.61, 'accepté', 0, 0),
 ('VG-20261008-J9K0', 3, 3, '2026-10-08 11:00:00', '2026-10-25', '20:00', '8 cours de l''Intendance', 'Bordeaux', 0, 4, 118.00, 0, 0, 'en attente', 0, 0);

INSERT INTO commande_suivi (numero_commande, statut, date_modif, auteur_id) VALUES
 ('VG-20260815-A1B2', 'en attente', '2026-08-15 10:12:00', 3),
 ('VG-20260815-A1B2', 'accepté', '2026-08-15 15:00:00', 2),
 ('VG-20260815-A1B2', 'en préparation', '2026-08-29 08:00:00', 2),
 ('VG-20260815-A1B2', 'en cours de livraison', '2026-08-29 11:15:00', 2),
 ('VG-20260815-A1B2', 'livré', '2026-08-29 11:55:00', 2),
 ('VG-20260815-A1B2', 'terminée', '2026-08-29 12:00:00', 2),
 ('VG-20260902-C3D4', 'en attente', '2026-09-02 14:40:00', 4),
 ('VG-20260902-C3D4', 'accepté', '2026-09-03 09:00:00', 2),
 ('VG-20260902-C3D4', 'terminée', '2026-09-12 21:00:00', 2),
 ('VG-20260918-E5F6', 'en attente', '2026-09-18 09:05:00', 3),
 ('VG-20260918-E5F6', 'accepté', '2026-09-18 12:00:00', 1),
 ('VG-20260918-E5F6', 'livré', '2026-10-03 17:50:00', 2),
 ('VG-20260918-E5F6', 'en attente du retour de matériel', '2026-10-04 09:00:00', 2),
 ('VG-20261001-G7H8', 'en attente', '2026-10-01 16:20:00', 4),
 ('VG-20261001-G7H8', 'accepté', '2026-10-02 10:00:00', 2),
 ('VG-20261008-J9K0', 'en attente', '2026-10-08 11:00:00', 3);

INSERT INTO avis (note, description, statut, utilisateur_id, numero_commande, date_avis) VALUES
 (5, 'Entrecôte parfaite et cannelés incroyables, livrés à l''heure. Nos invités ont adoré !', 'valide', 3, 'VG-20260815-A1B2', '2026-08-30 10:00:00'),
 (4, 'Très bon risotto, portions généreuses. Livraison un peu juste mais l''équipe a prévenu.', 'valide', 4, 'VG-20260902-C3D4', '2026-09-13 18:00:00');
