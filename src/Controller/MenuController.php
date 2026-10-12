<?php
declare(strict_types=1);

namespace App\Controller;

use App\Core\View;
use App\Repository\MenuRepository;
use App\Repository\RefRepository;

final class MenuController
{
    private function filters(): array
    {
        $f = [];
        foreach (['prix_max', 'prix_min', 'theme', 'regime', 'personnes'] as $k) {
            $v = query($k);
            $f[$k] = ($v !== '' && is_numeric($v) && (float) $v >= 0) ? $v : '';
        }
        return $f;
    }

    /** Vue globale (rendu serveur initial, puis filtrage AJAX sans rechargement). */
    public function index(): void
    {
        $ref = new RefRepository();
        $filters = $this->filters();
        View::render('pages/menus', [
            'title' => 'Nos menus',
            'menus' => (new MenuRepository())->search($filters),
            'themes' => $ref->themes(),
            'regimes' => $ref->regimes(),
            'filters' => $filters,
        ]);
    }

    /** API JSON utilisée par le filtre dynamique (fetch). */
    public function api(): void
    {
        $menus = (new MenuRepository())->search($this->filters());
        View::json(array_map(fn ($m) => [
            'id' => (int) $m['menu_id'],
            'titre' => $m['titre'],
            'description' => $m['description'],
            'theme' => $m['theme'],
            'regime' => $m['regime'],
            'minimum' => (int) $m['nombre_personne_minimum'],
            'prix_minimum' => (float) $m['prix_minimum'],
            'prix_personne' => (float) $m['prix_par_personne'],
            'stock' => (int) $m['quantite_restante'],
            'image' => $m['image'] ? asset($m['image']) : null,
            'image_alt' => $m['image_alt'] ?? '',
            'url' => url('/menu', ['id' => $m['menu_id']]),
        ], $menus));
    }

    public function show(): void
    {
        $menu = (new MenuRepository())->find((int) query('id'), true);
        if (!$menu) {
            abort(404, 'Ce menu n\'existe pas ou n\'est plus proposé.');
        }
        View::render('pages/menu-detail', ['title' => $menu['titre'], 'menu' => $menu]);
    }
}
