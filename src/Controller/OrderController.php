<?php
declare(strict_types=1);

namespace App\Controller;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Mailer;
use App\Core\View;
use App\Repository\CommandeRepository;
use App\Repository\MenuRepository;
use App\Repository\UserRepository;
use App\Service\PricingService;

final class OrderController
{
    /** Espace de commande : réservé aux personnes authentifiées. */
    public function create(): void
    {
        Auth::requireLogin();
        $menus = (new MenuRepository())->search();
        $menuId = (int) (is_post() ? post('menu_id') : query('menu'));
        $menu = $menuId ? (new MenuRepository())->find($menuId, true) : null;
        $user = (new UserRepository())->find(Auth::id());

        if (is_post()) {
            Csrf::check();
            if (!$menu) {
                flash('error', 'Veuillez choisir un menu.');
                redirect('/commande');
            }
            [$d, $errors] = self::validateDetails($_POST, (int) $menu['nombre_personne_minimum']);
            if (!isset($_POST['conditions_lues'])) {
                $errors[] = 'Vous devez confirmer avoir pris connaissance des conditions du menu.';
            }
            if ($errors) {
                keep_old($_POST);
                foreach ($errors as $err) {
                    flash('error', $err);
                }
                redirect('/commande', ['menu' => $menu['menu_id']]);
            }
            $price = PricingService::compute((float) $menu['prix_par_personne'], (int) $menu['nombre_personne_minimum'],
                $d['nombre_personne'], $d['ville_prestation'], $d['distance_km']);
            try {
                $numero = (new CommandeRepository())->create(Auth::id(), $menu, $d, $price);
            } catch (\RuntimeException $e) {
                flash('error', $e->getMessage());
                redirect('/menu', ['id' => $menu['menu_id']]);
            }
            clear_old();
            Mailer::send($user['email'], 'Confirmation de votre commande ' . $numero,
                '<p>Bonjour ' . e($user['prenom']) . ',</p><p>Nous avons bien reçu votre commande <strong>' . e($numero) . '</strong>.</p>'
                . '<ul><li>Menu : ' . e($menu['titre']) . ' pour ' . $d['nombre_personne'] . ' personnes</li>'
                . '<li>Prestation : le ' . date_fr($d['date_prestation']) . ' à ' . e($d['heure_livraison']) . '</li>'
                . '<li>Lieu : ' . e($d['adresse_prestation']) . ', ' . e($d['ville_prestation']) . '</li>'
                . '<li>Menu : ' . euros($price['menu']) . ($price['reduction'] > 0 ? ' (réduction de ' . euros($price['reduction']) . ' incluse)' : '') . '</li>'
                . '<li>Livraison : ' . euros($price['livraison']) . '</li>'
                . '<li><strong>Total : ' . euros($price['total']) . '</strong></li></ul>'
                . '<p>Notre équipe va valider votre commande très prochainement. Vous pouvez la suivre depuis votre espace.</p>');
            flash('success', 'Commande ' . $numero . ' enregistrée ! Un e-mail de confirmation vous a été envoyé.');
            redirect('/espace/commande', ['numero' => $numero]);
        }

        View::render('pages/order', [
            'title' => 'Commander un menu',
            'menus' => $menus,
            'menu' => $menu,
            'user' => $user,
        ]);
    }

    /**
     * Validation des informations de prestation (création et modification).
     * @return array{0: array, 1: string[]}
     */
    public static function validateDetails(array $in, int $minimum): array
    {
        $errors = [];
        $d = [
            'date_prestation' => trim((string) ($in['date_prestation'] ?? '')),
            'heure_livraison' => trim((string) ($in['heure_livraison'] ?? '')),
            'adresse_prestation' => trim((string) ($in['adresse_prestation'] ?? '')),
            'ville_prestation' => trim((string) ($in['ville_prestation'] ?? '')),
            'distance_km' => (float) str_replace(',', '.', (string) ($in['distance_km'] ?? '0')),
            'nombre_personne' => (int) ($in['nombre_personne'] ?? 0),
        ];
        $date = \DateTime::createFromFormat('Y-m-d', $d['date_prestation']);
        if (!$date || $date->format('Y-m-d') !== $d['date_prestation']) {
            $errors[] = 'La date de prestation est invalide.';
        } elseif ($d['date_prestation'] <= date('Y-m-d')) {
            $errors[] = 'La date de prestation doit être postérieure à aujourd\'hui.';
        }
        if (!preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $d['heure_livraison'])) {
            $errors[] = 'L\'heure de livraison est invalide.';
        }
        if ($d['adresse_prestation'] === '' || mb_strlen($d['adresse_prestation']) > 255) {
            $errors[] = 'L\'adresse de la prestation est obligatoire.';
        }
        if ($d['ville_prestation'] === '' || mb_strlen($d['ville_prestation']) > 100) {
            $errors[] = 'La ville de la prestation est obligatoire.';
        }
        if (PricingService::isBordeaux($d['ville_prestation'])) {
            $d['distance_km'] = 0.0;
        } elseif ($d['distance_km'] <= 0 || $d['distance_km'] > 300) {
            $errors[] = 'Hors Bordeaux, indiquez la distance depuis Bordeaux (entre 1 et 300 km).';
        }
        if ($d['nombre_personne'] < $minimum) {
            $errors[] = 'Ce menu se commande pour ' . $minimum . ' personnes minimum.';
        }
        if ($d['nombre_personne'] > 500) {
            $errors[] = 'Pour plus de 500 personnes, contactez-nous directement.';
        }
        return [$d, $errors];
    }
}
