<?php
declare(strict_types=1);

namespace App\Controller;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Mailer;
use App\Core\View;
use App\Repository\MenuRepository;
use App\Repository\UserRepository;
use App\Service\StatsStore;

/** Espace administrateur (José) : comptes employés et statistiques NoSQL. */
final class AdminController
{
    private function guard(): void
    {
        Auth::requireRole(Auth::ROLE_ADMIN);
    }

    public function index(): void
    {
        $this->guard();
        redirect('/admin/statistiques');
    }

    public function employees(): void
    {
        $this->guard();
        $repo = new UserRepository();
        if (is_post()) {
            Csrf::check();
            if (post('action') === 'toggle') {
                $repo->setEmployeeActive((int) post('id'), post('actif') === '1');
                flash('success', post('actif') === '1' ? 'Compte réactivé.' : 'Compte désactivé : l\'employé ne peut plus se connecter.');
                redirect('/admin/employes');
            }
            $email = mb_strtolower(post('email'));
            $pwd = (string) ($_POST['password'] ?? '');
            $errors = [];
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'E-mail invalide.';
            } elseif ($repo->emailExists($email)) {
                $errors[] = 'Cet e-mail est déjà utilisé.';
            }
            if ($e = Auth::passwordErrors($pwd)) {
                $errors[] = 'Mot de passe : ' . implode(', ', $e) . '.';
            }
            if ($errors) {
                keep_old($_POST);
                foreach ($errors as $err) {
                    flash('error', $err);
                }
                redirect('/admin/employes');
            }
            // Le rôle est forcé à « employe » : impossible de créer un administrateur
            $repo->create([
                'email' => $email, 'password' => $pwd, 'nom' => post('nom'), 'prenom' => post('prenom'),
            ], Auth::ROLE_EMPLOYE);
            clear_old();
            // Le mot de passe n'est volontairement PAS communiqué dans l'e-mail
            Mailer::send($email, 'Votre compte employé Vite & Gourmand',
                '<p>Bonjour,</p><p>Un compte employé a été créé pour vous sur l\'application Vite &amp; Gourmand. '
                . 'Votre identifiant est votre adresse e-mail : <strong>' . e($email) . '</strong>.</p>'
                . '<p>Pour des raisons de sécurité, le mot de passe ne vous est pas communiqué par e-mail : '
                . 'rapprochez-vous de l\'administrateur pour l\'obtenir.</p>');
            flash('success', 'Compte employé créé. Un e-mail de notification (sans mot de passe) a été envoyé.');
            redirect('/admin/employes');
        }
        View::render('pages/admin/employees', ['title' => 'Comptes employés', 'employes' => $repo->employees()]);
    }

    public function stats(): void
    {
        $this->guard();
        View::render('pages/admin/stats', [
            'title' => 'Statistiques',
            'menus' => (new MenuRepository())->all(),
            'driver' => StatsStore::instance()->driver(),
        ]);
    }

    /** Données du graphique et du chiffre d'affaires (source : base NoSQL). */
    public function statsApi(): void
    {
        $this->guard();
        $menuId = (int) query('menu') ?: null;
        $from = preg_match('/^\d{4}-\d{2}-\d{2}$/', query('du')) ? query('du') : null;
        $to = preg_match('/^\d{4}-\d{2}-\d{2}$/', query('au')) ? query('au') : null;
        $rows = StatsStore::instance()->aggregateByMenu($menuId, $from, $to);
        View::json([
            'source' => StatsStore::instance()->driver(),
            'rows' => $rows,
            'total_commandes' => array_sum(array_column($rows, 'nb_commandes')),
            'total_ca' => round(array_sum(array_column($rows, 'chiffre_affaires')), 2),
        ]);
    }
}
