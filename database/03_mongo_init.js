// =====================================================================
//  Vite & Gourmand — Initialisation MongoDB (exécuter avec mongosh)
//  mongosh "<MONGO_URI>" database/03_mongo_init.js
//  Les documents sont ensuite alimentés automatiquement par l'application
//  à chaque commande (ou via : php bin/sync-nosql.php).
// =====================================================================
db = db.getSiblingDB('vite_gourmand');

db.createCollection('commandes', {
  validator: {
    $jsonSchema: {
      bsonType: 'object',
      required: ['numero', 'menu_id', 'menu_titre', 'montant', 'date_commande', 'statut'],
      properties: {
        numero: { bsonType: 'string' },
        menu_id: { bsonType: ['int', 'long'] },
        menu_titre: { bsonType: 'string' },
        montant: { bsonType: ['double', 'int', 'decimal'] },
        nombre_personne: { bsonType: ['int', 'long'] },
        date_commande: { bsonType: 'string', description: 'AAAA-MM-JJ HH:MM:SS' },
        statut: { bsonType: 'string' }
      }
    }
  }
});

db.commandes.createIndex({ numero: 1 }, { unique: true });
db.commandes.createIndex({ menu_id: 1, date_commande: 1 });

// Jeu de données identique aux commandes de 02_data.sql
db.commandes.insertMany([
  { numero: 'VG-20260815-A1B2', menu_id: 3, menu_titre: 'Tradition bordelaise', montant: 177.0, nombre_personne: 6, date_commande: '2026-08-15 10:12:00', statut: 'terminée' },
  { numero: 'VG-20260902-C3D4', menu_id: 4, menu_titre: 'Jardin végétarien', montant: 234.0, nombre_personne: 10, date_commande: '2026-09-02 14:40:00', statut: 'terminée' },
  { numero: 'VG-20260918-E5F6', menu_id: 6, menu_titre: 'Grand événement', montant: 1012.5, nombre_personne: 25, date_commande: '2026-09-18 09:05:00', statut: 'en attente du retour de matériel' },
  { numero: 'VG-20261001-G7H8', menu_id: 2, menu_titre: 'Brunch de Pâques', montant: 204.0, nombre_personne: 6, date_commande: '2026-10-01 16:20:00', statut: 'accepté' },
  { numero: 'VG-20261008-J9K0', menu_id: 3, menu_titre: 'Tradition bordelaise', montant: 118.0, nombre_personne: 4, date_commande: '2026-10-08 11:00:00', statut: 'en attente' }
]);

// Exemple de requête utilisée par l'espace administrateur :
// db.commandes.aggregate([
//   { $match: { statut: { $ne: 'annulée' }, date_commande: { $gte: '2026-09-01 00:00:00' } } },
//   { $group: { _id: '$menu_id', menu_titre: { $first: '$menu_titre' }, nb_commandes: { $sum: 1 }, chiffre_affaires: { $sum: '$montant' } } },
//   { $sort: { nb_commandes: -1 } }
// ]);
