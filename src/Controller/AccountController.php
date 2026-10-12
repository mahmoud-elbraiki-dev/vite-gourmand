<?php
declare(strict_types=1);

namespace App\Controller;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\View;
use App\Repository\AvisRepository;
use App\Repository\CommandeRepository;
use App\Repository\UserRepository;
use App\Service\OrderStatus;
use App\Service\PricingService;

/** Espace utilisateur : commandes, suivi, modification, annulation, avis, profil. */
final class AccountController
{
    public function index(): void
    {
        Auth::requireLogin();
        View::render('pages/account/index', [
            'title' => 'Mon espace',
            'commandes' => (new CommandeRepository())->forUser(Auth::id()),
        ]);
    }

    /** Charge une commande en vérifiant qu'elle appartient bien à l'utilisateur (anti-IDOR). */
    private function ownOrder(string $numero): array
    {
        $c = (new CommandeRepository())->find($numero);
        if (!$c || (int) $c['utilisateur_id'] !== Auth::id()) {
            abort(404, 'Commande introuvable.');
        }
        return $c;
    }

    public function order(): void
    {
        Auth::requireLogin();
        $c = $this->ownOrder(query('numero'));
        View::render('pages/account/order', [
            'title' => 'Commande ' . $c['numero_commande'],
            'c' => $c,
            'history' => (new CommandeRepository())->history($c['numero_commande']),
        ]);
    }

    public function edit(): void
    {
        Auth::requireLogin();
        $numero = is_post() ? post('numero') : query('numero');
        $c = $this->ownOrder($numero);
        if (!OrderStatus::editableByClient($c['statut'])) {
            flash('error', 'Cette commande a été acceptée : elle ne peut plus être modifiée. Contactez-nous si besoin.');
            redirect('/espace/commande', ['numero' => $numero]);
        }
        if (is_post()) {
            Csrf::check();
            [$d, $errors] = OrderController::validateDetails($_POST, (int) $c['nombre_personne_minimum']);
            if ($errors) {
                foreach ($errors as $err) {
                    flash('error', $err);
                }
                redirect('/espace/commande/modifier', ['numero' => $numero]);
            }
            $price = PricingService::compute((float) $c['prix_par_personne'], (int) $c['nombre_personne_minimum'],
                $d['nombre_personne'], $d['ville_prestation'], $d['distance_km']);
            (new CommandeRepository())->updateDetails($numero, $d, $price);
            flash('success', 'Votre commande a été modifiée.');
            redirect('/espace/commande', ['numero' => $numero]);
        }
        View::render('pages/account/edit', ['title' => 'Modifier la commande', 'c' => $c]);
    }

    public function cancel(): void
    {
        Auth::requireLogin();
        if (!is_post()) {
            redirect('/espace');
        }
        Csrf::check();
        $c = $this->ownOrder(post('numero'));
        if (!OrderStatus::editableByClient($c['statut'])) {
            flash('error', 'Cette commande ne peut plus être annulée en ligne.');
        } else {
            (new CommandeRepository())->cancel($c['numero_commande'], Auth::id(), 'Annulée par le client', null);
            flash('success', 'La commande ' . $c['numero_commande'] . ' a été annulée.');
        }
        redirect('/espace');
    }

    public function review(): void
    {
        Auth::requireLogin();
        if (!is_post()) {
            redirect('/espace');
        }
        Csrf::check();
        $c = $this->ownOrder(post('numero'));
        $note = (int) post('note');
        $txt = post('description');
        if ($c['statut'] !== OrderStatus::TERMINEE || $c['avis_id']) {
            flash('error', 'Un avis ne peut être déposé qu\'une fois, sur une commande terminée.');
        } elseif ($note < 1 || $note > 5 || $txt === '' || mb_strlen($txt) > 500) {
            flash('error', 'Merci d\'indiquer une note de 1 à 5 et un commentaire (500 caractères max.).');
        } else {
            (new AvisRepository())->create(Auth::id(), $c['numero_commande'], $note, $txt);
            flash('success', 'Merci pour votre avis ! Il sera publié après validation par notre équipe.');
        }
        redirect('/espace/commande', ['numero' => $c['numero_commande']]);
    }

    public function profile(): void
    {
        Auth::requireLogin();
        $repo = new UserRepository();
        $user = $repo->find(Auth::id());
        if (is_post()) {
            Csrf::check();
            if (post('action') === 'password') {
                $pwd = (string) ($_POST['password'] ?? '');
                if (!password_verify((string) ($_POST['current_password'] ?? ''), $user['password'])) {
                    flash('error', 'Mot de passe actuel incorrect.');
                } elseif ($errs = Auth::passwordErrors($pwd)) {
                    flash('error', 'Mot de passe : ' . implode(', ', $errs) . '.');
                } elseif ($pwd !== ($_POST['password_confirm'] ?? '')) {
                    flash('error', 'Les mots de passe ne correspondent pas.');
                } else {
                    $repo->updatePassword(Auth::id(), $pwd);
                    flash('success', 'Mot de passe mis à jour.');
                }
                redirect('/espace/profil');
            }
            $d = [
                'nom' => post('nom'), 'prenom' => post('prenom'), 'telephone' => post('telephone'),
                'email' => mb_strtolower(post('email')), 'adresse_postale' => post('adresse_postale'),
                'ville' => post('ville'), 'pays' => post('pays') ?: 'France',
            ];
            $errors = AuthController::validateIdentity($d);
            if (!$errors && $repo->emailExists($d['email'], Auth::id())) {
                $errors[] = 'Cette adresse e-mail est déjà utilisée.';
            }
            if ($errors) {
                foreach ($errors as $err) {
                    flash('error', $err);
                }
            } else {
                $repo->updateProfile(Auth::id(), $d);
                $_SESSION['user']['prenom'] = $d['prenom'];
                $_SESSION['user']['email'] = $d['email'];
                flash('success', 'Vos informations ont été mises à jour.');
            }
            redirect('/espace/profil');
        }
        View::render('pages/account/profile', ['title' => 'Mes informations', 'user' => $user]);
    }
}
