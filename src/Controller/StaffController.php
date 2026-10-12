<?php
declare(strict_types=1);

namespace App\Controller;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Mailer;
use App\Core\Upload;
use App\Core\View;
use App\Repository\AvisRepository;
use App\Repository\CommandeRepository;
use App\Repository\MenuRepository;
use App\Repository\PlatRepository;
use App\Repository\RefRepository;
use App\Service\OrderStatus;
use App\Service\PricingService;

/** Espace employé (également accessible à l'administrateur). */
final class StaffController
{
    private function guard(): void
    {
        Auth::requireRole(Auth::ROLE_EMPLOYE, Auth::ROLE_ADMIN);
    }

    // ------------------------------------------------------------------ Commandes

    public function orders(): void
    {
        $this->guard();
        $repo = new CommandeRepository();
        View::render('pages/staff/orders', [
            'title' => 'Gestion des commandes',
            'commandes' => $repo->filter(query('statut'), query('client')),
            'counts' => $repo->countByStatus(),
            'statut' => query('statut'),
            'client' => query('client'),
        ]);
    }

    public function order(): void
    {
        $this->guard();
        $repo = new CommandeRepository();
        $c = $repo->find(query('numero'));
        if (!$c) {
            abort(404, 'Commande introuvable.');
        }
        View::render('pages/staff/order', [
            'title' => 'Commande ' . $c['numero_commande'],
            'c' => $c,
            'history' => $repo->history($c['numero_commande']),
        ]);
    }

    public function updateStatus(): void
    {
        $this->guard();
        Csrf::check();
        $repo = new CommandeRepository();
        $c = $repo->find(post('numero'));
        if (!$c) {
            abort(404);
        }
        $target = post('statut');
        $pret = isset($_POST['pret_materiel']) ? 1 : 0;
        $restit = isset($_POST['restitution_materiel']) ? 1 : 0;
        $back = fn () => redirect('/gestion/commande', ['numero' => $c['numero_commande']]);

        if (in_array($c['statut'], [OrderStatus::ANNULEE, OrderStatus::TERMINEE], true)) {
            flash('error', 'Cette commande est clôturée.');
            $back();
        }
        if (!in_array($target, OrderStatus::WORKFLOW, true)) {
            flash('error', 'Statut invalide.');
            $back();
        }
        if ($target === OrderStatus::RETOUR_MATERIEL && !$pret) {
            flash('error', 'Le statut « en attente du retour de matériel » nécessite un prêt de matériel.');
            $back();
        }
        if ($target === OrderStatus::TERMINEE && $pret && !$restit) {
            flash('error', 'Du matériel a été prêté : cochez « matériel restitué » avant de terminer la commande.');
            $back();
        }

        $repo->updateStatus($c['numero_commande'], $target, Auth::id(), [
            'pret_materiel' => $pret, 'restitution_materiel' => $restit,
        ]);

        if ($target === OrderStatus::RETOUR_MATERIEL) {
            Mailer::send($c['email'], 'Restitution du matériel prêté — commande ' . $c['numero_commande'],
                '<p>Bonjour ' . e($c['prenom']) . ',</p><p>Du matériel vous a été prêté pour votre prestation. '
                . 'Merci de prendre contact avec nous pour organiser sa restitution.</p>'
                . '<p><strong>Sans restitution sous 10 jours ouvrés, des frais de 600 € vous seront facturés</strong>, '
                . 'conformément à nos conditions générales de vente.</p><p>Téléphone : 05 56 00 00 00 — contact@vitegourmand.fr</p>');
        }
        if ($target === OrderStatus::TERMINEE) {
            Mailer::send($c['email'], 'Votre avis compte — commande ' . $c['numero_commande'],
                '<p>Bonjour ' . e($c['prenom']) . ',</p><p>Votre commande est terminée. Nous espérons que la prestation vous a plu !</p>'
                . '<p>Connectez-vous à votre espace pour nous laisser une note et un commentaire depuis votre commande.</p>');
        }
        flash('success', 'Statut mis à jour : « ' . $target . ' ».');
        $back();
    }

    /** Contrôle commun : l'employé doit avoir contacté le client (mode + motif). */
    private function requireContact(): array
    {
        $mode = post('mode_contact');
        $motif = post('motif');
        if (!in_array($mode, ['GSM', 'mail'], true) || $motif === '' || mb_strlen($motif) > 255) {
            return [null, null];
        }
        return [$mode, $motif];
    }

    public function cancel(): void
    {
        $this->guard();
        Csrf::check();
        $repo = new CommandeRepository();
        $c = $repo->find(post('numero'));
        if (!$c) {
            abort(404);
        }
        [$mode, $motif] = $this->requireContact();
        if (!$mode) {
            flash('error', 'Avant d\'annuler, indiquez le mode de contact du client (GSM ou mail) et le motif.');
        } elseif (in_array($c['statut'], [OrderStatus::ANNULEE, OrderStatus::TERMINEE], true)) {
            flash('error', 'Cette commande est déjà clôturée.');
        } else {
            $repo->cancel($c['numero_commande'], Auth::id(), $motif, $mode);
            Mailer::send($c['email'], 'Annulation de votre commande ' . $c['numero_commande'],
                '<p>Bonjour ' . e($c['prenom']) . ',</p><p>Suite à notre échange (' . e($mode) . '), votre commande a été annulée.</p>'
                . '<p>Motif : ' . e($motif) . '</p>');
            flash('success', 'Commande annulée.');
        }
        redirect('/gestion/commande', ['numero' => $c['numero_commande']]);
    }

    public function edit(): void
    {
        $this->guard();
        Csrf::check();
        $repo = new CommandeRepository();
        $c = $repo->find(post('numero'));
        if (!$c) {
            abort(404);
        }
        $back = fn () => redirect('/gestion/commande', ['numero' => $c['numero_commande']]);
        [$mode, $motif] = $this->requireContact();
        if (!$mode) {
            flash('error', 'Avant de modifier, indiquez le mode de contact du client (GSM ou mail) et le motif.');
            $back();
        }
        if (in_array($c['statut'], [OrderStatus::ANNULEE, OrderStatus::TERMINEE], true)) {
            flash('error', 'Cette commande est clôturée.');
            $back();
        }
        [$d, $errors] = OrderController::validateDetails($_POST, (int) $c['nombre_personne_minimum']);
        // Côté employé on autorise une date du jour (livraison du jour même déjà convenue)
        $errors = array_filter($errors, fn ($e) => !str_contains($e, 'postérieure'));
        if ($errors) {
            foreach ($errors as $err) {
                flash('error', $err);
            }
            $back();
        }
        $price = PricingService::compute((float) $c['prix_par_personne'], (int) $c['nombre_personne_minimum'],
            $d['nombre_personne'], $d['ville_prestation'], $d['distance_km']);
        $repo->updateDetails($c['numero_commande'], $d, $price);
        $repo->setContact($c['numero_commande'], $mode, 'Modification : ' . $motif);
        flash('success', 'Commande modifiée (contact client : ' . $mode . ').');
        $back();
    }

    // ------------------------------------------------------------------ Menus

    public function menus(): void
    {
        $this->guard();
        View::render('pages/staff/menus', ['title' => 'Gestion des menus', 'menus' => (new MenuRepository())->all()]);
    }

    public function menuForm(): void
    {
        $this->guard();
        $repo = new MenuRepository();
        $id = (int) (is_post() ? post('menu_id') : query('id'));
        $menu = $id ? $repo->find($id) : null;
        if ($id && !$menu) {
            abort(404);
        }

        if (is_post()) {
            Csrf::check();
            $d = [
                'titre' => post('titre'), 'description' => post('description'), 'conditions' => post('conditions'),
                'nombre_personne_minimum' => (int) post('nombre_personne_minimum'),
                'prix_par_personne' => (float) str_replace(',', '.', post('prix_par_personne')),
                'quantite_restante' => (int) post('quantite_restante'),
                'regime_id' => (int) post('regime_id'), 'theme_id' => (int) post('theme_id'),
                'actif' => isset($_POST['actif']) ? 1 : 0,
            ];
            $errors = [];
            if ($d['titre'] === '' || mb_strlen($d['titre']) > 100) {
                $errors[] = 'Titre obligatoire (100 caractères max.).';
            }
            if ($d['description'] === '' || $d['conditions'] === '') {
                $errors[] = 'Description et conditions sont obligatoires.';
            }
            if ($d['nombre_personne_minimum'] < 1 || $d['prix_par_personne'] <= 0 || $d['quantite_restante'] < 0) {
                $errors[] = 'Minimum de personnes, prix et stock doivent être des valeurs positives.';
            }
            $plats = array_map('intval', (array) ($_POST['plats'] ?? []));
            if (!$plats) {
                $errors[] = 'Sélectionnez au moins un plat.';
            }
            if ($errors) {
                foreach ($errors as $err) {
                    flash('error', $err);
                }
                redirect('/gestion/menu', $id ? ['id' => $id] : []);
            }
            $id = $repo->save($id ?: null, $d, $plats);
            try {
                if ($path = Upload::image('image', 'menus')) {
                    $repo->addImage($id, $path, post('image_alt') ?: $d['titre']);
                }
            } catch (\RuntimeException $e) {
                flash('error', $e->getMessage());
            }
            flash('success', 'Menu enregistré.');
            redirect('/gestion/menu', ['id' => $id]);
        }

        $ref = new RefRepository();
        View::render('pages/staff/menu-form', [
            'title' => $menu ? 'Modifier « ' . $menu['titre'] . ' »' : 'Nouveau menu',
            'menu' => $menu,
            'plats' => (new PlatRepository())->all(),
            'themes' => $ref->themes(),
            'regimes' => $ref->regimes(),
        ]);
    }

    public function menuDelete(): void
    {
        $this->guard();
        Csrf::check();
        $r = (new MenuRepository())->delete((int) post('menu_id'));
        flash('success', $r === 'archived'
            ? 'Ce menu a déjà été commandé : il a été désactivé (historique conservé).'
            : 'Menu supprimé.');
        redirect('/gestion/menus');
    }

    public function imageDelete(): void
    {
        $this->guard();
        Csrf::check();
        Upload::delete((new MenuRepository())->deleteImage((int) post('image_id')));
        flash('success', 'Image supprimée.');
        redirect('/gestion/menu', ['id' => (int) post('menu_id')]);
    }

    public function addRef(): void
    {
        $this->guard();
        Csrf::check();
        $lib = post('libelle');
        if ($lib !== '' && mb_strlen($lib) <= 50) {
            post('type') === 'theme' ? (new RefRepository())->addTheme($lib) : (new RefRepository())->addRegime($lib);
            flash('success', 'Ajouté : ' . $lib);
        }
        redirect('/gestion/menus');
    }

    // ------------------------------------------------------------------ Plats

    public function plats(): void
    {
        $this->guard();
        $repo = new PlatRepository();
        if (is_post()) {
            Csrf::check();
            if (post('action') === 'delete') {
                $repo->delete((int) post('plat_id'));
                flash('success', 'Plat supprimé.');
                redirect('/gestion/plats');
            }
            $id = (int) post('plat_id') ?: null;
            $titre = post('titre_plat');
            $type = post('type_plat');
            if ($titre === '' || mb_strlen($titre) > 100 || !in_array($type, ['entree', 'plat', 'dessert'], true)) {
                flash('error', 'Titre et type de plat obligatoires.');
                redirect('/gestion/plats', $id ? ['id' => $id] : []);
            }
            try {
                $photo = Upload::image('photo', 'plats');
            } catch (\RuntimeException $e) {
                flash('error', $e->getMessage());
                $photo = null;
            }
            $repo->save($id, $titre, $type, $photo, (array) ($_POST['allergenes'] ?? []));
            flash('success', 'Plat enregistré.');
            redirect('/gestion/plats');
        }
        $editId = (int) query('id');
        View::render('pages/staff/plats', [
            'title' => 'Gestion des plats',
            'plats' => $repo->all(),
            'edit' => $editId ? $repo->find($editId) : null,
            'allergenes' => (new RefRepository())->allergenes(),
        ]);
    }

    // ------------------------------------------------------------------ Horaires

    public function horaires(): void
    {
        $this->guard();
        $ref = new RefRepository();
        if (is_post()) {
            Csrf::check();
            foreach ((array) ($_POST['horaires'] ?? []) as $id => $h) {
                $closed = isset($h['ferme']);
                $o = $closed ? null : trim((string) ($h['ouverture'] ?? ''));
                $f = $closed ? null : trim((string) ($h['fermeture'] ?? ''));
                $re = '/^([01]\d|2[0-3]):[0-5]\d$/';
                if (!$closed && (!preg_match($re, (string) $o) || !preg_match($re, (string) $f) || $o >= $f)) {
                    flash('error', 'Horaire invalide (l\'ouverture doit précéder la fermeture).');
                    redirect('/gestion/horaires');
                }
                $ref->updateHoraire((int) $id, $o, $f);
            }
            flash('success', 'Horaires mis à jour.');
            redirect('/gestion/horaires');
        }
        View::render('pages/staff/horaires', ['title' => 'Horaires', 'horaires' => $ref->horaires()]);
    }

    // ------------------------------------------------------------------ Avis

    public function avis(): void
    {
        $this->guard();
        $repo = new AvisRepository();
        if (is_post()) {
            Csrf::check();
            $statut = post('statut');
            if (in_array($statut, ['valide', 'refuse'], true)) {
                $repo->setStatus((int) post('avis_id'), $statut);
                flash('success', $statut === 'valide' ? 'Avis validé : il est visible sur l\'accueil.' : 'Avis refusé.');
            }
            redirect('/gestion/avis');
        }
        View::render('pages/staff/avis', ['title' => 'Modération des avis', 'avis' => $repo->byStatus()]);
    }
}
