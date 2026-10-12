<?php
declare(strict_types=1);

namespace App\Controller;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Mailer;
use App\Core\View;
use App\Repository\UserRepository;

final class AuthController
{
    public function login(): void
    {
        if (Auth::check()) {
            redirect($this->home());
        }
        if (is_post()) {
            Csrf::check();
            $result = Auth::attempt(post('email'), (string) ($_POST['password'] ?? ''));
            if ($result === true) {
                flash('success', 'Bienvenue ' . Auth::user()['prenom'] . ' !');
                $to = $_SESSION['intended'] ?? $this->home();
                unset($_SESSION['intended']);
                // N'autorise que les redirections internes (pas d'open redirect)
                if (!str_starts_with($to, '/') || str_starts_with($to, '//')) {
                    $to = '/';
                }
                header('Location: ' . $to);
                exit;
            }
            keep_old($_POST);
            flash('error', is_string($result) ? $result : 'Identifiants incorrects.');
            redirect('/connexion');
        }
        View::render('pages/auth/login', ['title' => 'Connexion']);
    }

    private function home(): string
    {
        return Auth::isAdmin() ? '/admin' : (Auth::isStaff() ? '/gestion' : '/espace');
    }

    public function register(): void
    {
        if (Auth::check()) {
            redirect('/espace');
        }
        if (is_post()) {
            Csrf::check();
            $d = [
                'nom' => post('nom'), 'prenom' => post('prenom'), 'telephone' => post('telephone'),
                'email' => mb_strtolower(post('email')), 'adresse_postale' => post('adresse_postale'),
                'ville' => post('ville'), 'pays' => post('pays', 'France') ?: 'France',
                'password' => (string) ($_POST['password'] ?? ''),
            ];
            $errors = self::validateIdentity($d);
            $pwdErrors = Auth::passwordErrors($d['password']);
            if ($pwdErrors) {
                $errors[] = 'Mot de passe : ' . implode(', ', $pwdErrors) . '.';
            }
            if ($d['password'] !== ($_POST['password_confirm'] ?? '')) {
                $errors[] = 'Les deux mots de passe ne correspondent pas.';
            }
            if (!isset($_POST['rgpd'])) {
                $errors[] = 'Vous devez accepter la politique de confidentialité.';
            }
            $repo = new UserRepository();
            if (!$errors && $repo->emailExists($d['email'])) {
                $errors[] = 'Un compte existe déjà avec cette adresse e-mail.';
            }
            if ($errors) {
                keep_old($_POST);
                foreach ($errors as $err) {
                    flash('error', $err);
                }
                redirect('/inscription');
            }
            $repo->create($d, Auth::ROLE_USER);
            clear_old();
            Mailer::send($d['email'], 'Bienvenue chez Vite & Gourmand',
                '<p>Bonjour ' . e($d['prenom']) . ',</p><p>Votre compte a bien été créé. Vous pouvez dès à présent '
                . 'découvrir nos menus et passer commande en ligne.</p><p>À très vite,<br>Julie et José</p>');
            Auth::attempt($d['email'], $d['password']);
            flash('success', 'Votre compte a été créé. Un e-mail de bienvenue vous a été envoyé.');
            redirect('/espace');
        }
        View::render('pages/auth/register', ['title' => 'Créer un compte']);
    }

    /** Règles de validation communes inscription / profil. */
    public static function validateIdentity(array $d): array
    {
        $errors = [];
        if ($d['nom'] === '' || mb_strlen($d['nom']) > 50) {
            $errors[] = 'Le nom est obligatoire (50 caractères max.).';
        }
        if ($d['prenom'] === '' || mb_strlen($d['prenom']) > 50) {
            $errors[] = 'Le prénom est obligatoire (50 caractères max.).';
        }
        if (!preg_match('/^\+?[0-9 .-]{10,20}$/', $d['telephone'])) {
            $errors[] = 'Le numéro de GSM est invalide.';
        }
        if (!filter_var($d['email'], FILTER_VALIDATE_EMAIL) || mb_strlen($d['email']) > 180) {
            $errors[] = 'L\'adresse e-mail est invalide.';
        }
        if ($d['adresse_postale'] === '' || $d['ville'] === '') {
            $errors[] = 'L\'adresse postale et la ville sont obligatoires.';
        }
        return $errors;
    }

    public function logout(): void
    {
        if (is_post()) {
            Csrf::check();
            Auth::logout();
            session_start();
            flash('success', 'Vous êtes déconnecté(e).');
        }
        redirect('/');
    }

    public function forgot(): void
    {
        if (is_post()) {
            Csrf::check();
            $email = post('email');
            $user = (new UserRepository())->findByEmail($email);
            if ($user && (int) $user['actif'] === 1) {
                $token = (new UserRepository())->createResetToken((int) $user['utilisateur_id']);
                $link = rtrim(env('APP_URL', ''), '/') . url('/reinitialiser', ['token' => $token]);
                Mailer::send($user['email'], 'Réinitialisation de votre mot de passe',
                    '<p>Bonjour ' . e($user['prenom']) . ',</p><p>Pour choisir un nouveau mot de passe, cliquez sur le lien '
                    . 'ci-dessous (valable 1 heure) :</p><p><a href="' . e($link) . '">' . e($link) . '</a></p>'
                    . '<p>Si vous n\'êtes pas à l\'origine de cette demande, ignorez cet e-mail.</p>');
            }
            // Message identique que le compte existe ou non (pas d'énumération des comptes)
            flash('success', 'Si un compte correspond à cette adresse, un lien de réinitialisation vient d\'être envoyé.');
            redirect('/connexion');
        }
        View::render('pages/auth/forgot', ['title' => 'Mot de passe oublié']);
    }

    public function reset(): void
    {
        $token = is_post() ? post('token') : query('token');
        $repo = new UserRepository();
        $reset = $token !== '' ? $repo->findValidReset($token) : null;
        if (!$reset) {
            flash('error', 'Ce lien de réinitialisation est invalide ou a expiré.');
            redirect('/mot-de-passe-oublie');
        }
        if (is_post()) {
            Csrf::check();
            $pwd = (string) ($_POST['password'] ?? '');
            $errors = Auth::passwordErrors($pwd);
            if ($errors || $pwd !== ($_POST['password_confirm'] ?? '')) {
                flash('error', $errors ? 'Mot de passe : ' . implode(', ', $errors) . '.' : 'Les mots de passe ne correspondent pas.');
                redirect('/reinitialiser', ['token' => $token]);
            }
            $repo->updatePassword((int) $reset['utilisateur_id'], $pwd);
            $repo->consumeReset((int) $reset['id']);
            flash('success', 'Votre mot de passe a été modifié. Vous pouvez vous connecter.');
            redirect('/connexion');
        }
        View::render('pages/auth/reset', ['title' => 'Nouveau mot de passe', 'token' => $token]);
    }
}
