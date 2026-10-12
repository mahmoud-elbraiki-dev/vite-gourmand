<?php
declare(strict_types=1);

namespace App\Core;

use App\Repository\UserRepository;

/**
 * Authentification et contrôle d'accès par rôle.
 * Rôles : utilisateur (1), employe (2), administrateur (3).
 */
final class Auth
{
    public const ROLE_USER = 'utilisateur';
    public const ROLE_EMPLOYE = 'employe';
    public const ROLE_ADMIN = 'administrateur';

    private const MAX_ATTEMPTS = 5;
    private const LOCK_SECONDS = 300;

    public static function attempt(string $email, string $password): bool|string
    {
        // Limitation des tentatives (anti force brute) par session et par e-mail
        $key = 'login_' . hash('sha256', strtolower($email));
        $state = $_SESSION[$key] ?? ['count' => 0, 'until' => 0];
        if ($state['until'] > time()) {
            return 'Trop de tentatives. Réessayez dans quelques minutes.';
        }

        $user = (new UserRepository())->findByEmail($email);
        // password_verify même si l'utilisateur n'existe pas : temps de réponse homogène
        $hash = $user['password'] ?? '$2y$12$XqAEQZ/kT3GYEipUlff/yOWk5FN7YtYj6UKy8BHoEwvBURboizkMC';
        $valid = password_verify($password, $hash);

        if (!$user || !$valid) {
            $state['count']++;
            if ($state['count'] >= self::MAX_ATTEMPTS) {
                $state = ['count' => 0, 'until' => time() + self::LOCK_SECONDS];
            }
            $_SESSION[$key] = $state;
            return false;
        }
        if ((int) $user['actif'] !== 1) {
            return 'Ce compte a été désactivé. Contactez l\'administrateur.';
        }

        unset($_SESSION[$key]);
        if (password_needs_rehash($user['password'], PASSWORD_BCRYPT, ['cost' => 12])) {
            (new UserRepository())->updatePassword((int) $user['utilisateur_id'], $password);
        }
        session_regenerate_id(true); // protection contre la fixation de session
        $_SESSION['user'] = [
            'id'     => (int) $user['utilisateur_id'],
            'email'  => $user['email'],
            'prenom' => $user['prenom'],
            'role'   => $user['role'],
        ];
        return true;
    }

    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
    }

    public static function user(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    public static function id(): ?int
    {
        return isset($_SESSION['user']) ? (int) $_SESSION['user']['id'] : null;
    }

    public static function check(): bool
    {
        return isset($_SESSION['user']);
    }

    public static function role(): ?string
    {
        return $_SESSION['user']['role'] ?? null;
    }

    public static function isStaff(): bool
    {
        return in_array(self::role(), [self::ROLE_EMPLOYE, self::ROLE_ADMIN], true);
    }

    public static function isAdmin(): bool
    {
        return self::role() === self::ROLE_ADMIN;
    }

    /** Exige une connexion ; mémorise la page demandée pour y revenir. */
    public static function requireLogin(): void
    {
        if (!self::check()) {
            $_SESSION['intended'] = $_SERVER['REQUEST_URI'] ?? '/';
            flash('info', 'Connectez-vous ou créez un compte pour continuer.');
            redirect('/connexion');
        }
        // Un compte désactivé pendant la session est déconnecté immédiatement
        $u = (new UserRepository())->find(self::id());
        if (!$u || (int) $u['actif'] !== 1) {
            self::logout();
            redirect('/connexion');
        }
    }

    public static function requireRole(string ...$roles): void
    {
        self::requireLogin();
        if (!in_array(self::role(), $roles, true)) {
            abort(403);
        }
    }

    /** Politique de mot de passe imposée par l'énoncé. */
    public static function passwordErrors(string $password): array
    {
        $errors = [];
        if (mb_strlen($password) < 10) {
            $errors[] = '10 caractères minimum';
        }
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'une majuscule';
        }
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'une minuscule';
        }
        if (!preg_match('/\d/', $password)) {
            $errors[] = 'un chiffre';
        }
        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            $errors[] = 'un caractère spécial';
        }
        return $errors;
    }
}
