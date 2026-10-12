-- =====================================================================
--  Vite & Gourmand — Mise à jour des photos (base déjà importée)
--  À exécuter dans phpMyAdmin (onglet SQL ou Importer) si 02_data.sql
--  a été importé AVANT l'ajout des vraies photos.
--  Ne touche qu'aux images de démonstration des menus 1 à 6.
-- =====================================================================
USE vite_gourmand_ecf;
SET NAMES utf8mb4;

DELETE FROM menu_image
WHERE menu_id BETWEEN 1 AND 6
  AND chemin REGEXP '^uploads/menus/menu-[1-6]-[a-z0-9]+\\.jpg$';

INSERT INTO menu_image (menu_id, chemin, alt, ordre) VALUES
 (1, 'uploads/menus/menu-1-1.jpg', 'Velouté de châtaignes servi sur une table de fête', 1),
 (2, 'uploads/menus/menu-2-1.jpg', 'Gigot d''agneau de sept heures en cocotte', 1),
 (2, 'uploads/menus/menu-2-2.jpg', 'Œuf parfait et asperges vertes', 2),
 (2, 'uploads/menus/menu-2-3.jpg', 'Nid de Pâques au chocolat', 3),
 (2, 'uploads/menus/menu-2-4.jpg', 'Salade de fruits frais à la menthe', 4),
 (3, 'uploads/menus/menu-3-1.jpg', 'Entrecôte à la bordelaise tranchée', 1),
 (3, 'uploads/menus/menu-3-2.jpg', 'Velouté de châtaignes', 2),
 (3, 'uploads/menus/menu-3-3.jpg', 'Cannelés bordelais', 3),
 (4, 'uploads/menus/menu-4-1.jpg', 'Risotto aux cèpes', 1),
 (4, 'uploads/menus/menu-4-2.jpg', 'Tartare de légumes croquants', 2),
 (4, 'uploads/menus/menu-4-3.jpg', 'Cannelés bordelais', 3),
 (5, 'uploads/menus/menu-5-1.jpg', 'Curry de légumes au lait de coco', 1),
 (5, 'uploads/menus/menu-5-2.jpg', 'Tartare de légumes croquants', 2),
 (5, 'uploads/menus/menu-5-3.jpg', 'Salade de fruits frais à la menthe', 3),
 (6, 'uploads/menus/menu-6-1.jpg', 'Pièce montée de choux', 1),
 (6, 'uploads/menus/menu-6-2.jpg', 'Gigot d''agneau de sept heures', 2),
 (6, 'uploads/menus/menu-6-3.jpg', 'Dos de cabillaud, beurre blanc', 3),
 (6, 'uploads/menus/menu-6-4.jpg', 'Œuf parfait et asperges vertes', 4);
