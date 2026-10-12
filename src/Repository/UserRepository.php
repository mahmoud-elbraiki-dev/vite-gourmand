<?php
declare(strict_types=1);

namespace App\Repository;

use App\Core\Database;

final class UserRepository
{
    private const SELECT = 'SELECT u.*, r.libelle AS role FROM utilisateur u JOIN role r ON r.role_id = u.role_id';

    public function find(?int $id): ?array
    {
        if (!$id) {
            return null;
        }
        $st = Database::pdo()->prepare(self::SELECT . ' WHERE u.utilisateur_id = ?');
        $st->execute([$id]);
        return $st->fetch() ?: null;
    }

    public function findByEmail(string $email): ?array
    {
        $st = Database::pdo()->prepare(self::SELECT . ' WHERE u.email = ?');
        $st->execute([mb_strtolower(trim($email))]);
        return $st->fetch() ?: null;
    }

    public function emailExists(string $email, ?int $exceptId = null): bool
    {
        $st = Database::pdo()->prepare('SELECT COUNT(*) FROM utilisateur WHERE email = ? AND utilisateur_id <> ?');
        $st->execute([mb_strtolower(trim($email)), $exceptId ?? 0]);
        return (int) $st->fetchColumn() > 0;
    }

    /** Création d'un compte « utilisateur » (inscription) ou « employe » (par l'admin). */
    public function create(array $d, string $role): int
    {
        if (!in_array($role, ['utilisateur', 'employe'], true)) {
            // Garde-fou : aucun administrateur ne peut être créé depuis l'application
            throw new \InvalidArgumentException('Rôle non autorisé');
        }
        $st = Database::pdo()->prepare(
            'INSERT INTO utilisateur (email, password, nom, prenom, telephone, ville, pays, adresse_postale, role_id)
             VALUES (:email, :password, :nom, :prenom, :telephone, :ville, :pays, :adresse,
                     (SELECT role_id FROM role WHERE libelle = :role))'
        );
        $st->execute([
            'email'     => mb_strtolower(trim($d['email'])),
            'password'  => password_hash($d['password'], PASSWORD_BCRYPT, ['cost' => 12]),
            'nom'       => $d['nom'] ?? '',
            'prenom'    => $d['prenom'] ?? '',
            'telephone' => $d['telephone'] ?? '',
            'ville'     => $d['ville'] ?? '',
            'pays'      => $d['pays'] ?? 'France',
            'adresse'   => $d['adresse_postale'] ?? '',
            'role'      => $role,
        ]);
        return (int) Database::pdo()->lastInsertId();
    }

    public function updateProfile(int $id, array $d): void
    {
        $st = Database::pdo()->prepare(
            'UPDATE utilisateur SET nom = ?, prenom = ?, telephone = ?, email = ?, adresse_postale = ?, ville = ?, pays = ?
             WHERE utilisateur_id = ?'
        );
        $st->execute([$d['nom'], $d['prenom'], $d['telephone'], mb_strtolower($d['email']),
            $d['adresse_postale'], $d['ville'], $d['pays'], $id]);
    }

    public function updatePassword(int $id, string $password): void
    {
        $st = Database::pdo()->prepare('UPDATE utilisateur SET password = ? WHERE utilisateur_id = ?');
        $st->execute([password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]), $id]);
    }

    public function employees(): array
    {
        return Database::pdo()->query(self::SELECT . " WHERE r.libelle = 'employe' ORDER BY u.actif DESC, u.nom")->fetchAll();
    }

    public function clients(): array
    {
        return Database::pdo()->query(self::SELECT . " WHERE r.libelle = 'utilisateur' ORDER BY u.nom, u.prenom")->fetchAll();
    }

    /** Active / désactive uniquement un compte employé. */
    public function setEmployeeActive(int $id, bool $active): void
    {
        $st = Database::pdo()->prepare(
            "UPDATE utilisateur SET actif = ? WHERE utilisateur_id = ?
             AND role_id = (SELECT role_id FROM role WHERE libelle = 'employe')"
        );
        $st->execute([$active ? 1 : 0, $id]);
    }

    // --- Réinitialisation de mot de passe -----------------------------

    public function createResetToken(int $userId): string
    {
        $token = bin2hex(random_bytes(32));
        $pdo = Database::pdo();
        $pdo->prepare('UPDATE reinitialisation_mdp SET utilise = 1 WHERE utilisateur_id = ?')->execute([$userId]);
        $pdo->prepare('INSERT INTO reinitialisation_mdp (utilisateur_id, token_hash, expire_le) VALUES (?, ?, ?)')
            ->execute([$userId, hash('sha256', $token), date('Y-m-d H:i:s', time() + 3600)]);
        return $token;
    }

    public function findValidReset(string $token): ?array
    {
        $st = Database::pdo()->prepare(
            'SELECT * FROM reinitialisation_mdp WHERE token_hash = ? AND utilise = 0 AND expire_le > NOW()'
        );
        $st->execute([hash('sha256', $token)]);
        return $st->fetch() ?: null;
    }

    public function consumeReset(int $resetId): void
    {
        Database::pdo()->prepare('UPDATE reinitialisation_mdp SET utilise = 1 WHERE id = ?')->execute([$resetId]);
    }
}
