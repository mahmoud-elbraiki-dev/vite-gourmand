<?php
declare(strict_types=1);

namespace App\Controller;

use App\Core\View;

final class PageController
{
    public function mentions(): void
    {
        View::render('pages/legal/mentions', ['title' => 'Mentions légales']);
    }

    public function cgv(): void
    {
        View::render('pages/legal/cgv', ['title' => 'Conditions générales de vente']);
    }

    public function privacy(): void
    {
        View::render('pages/legal/privacy', ['title' => 'Politique de confidentialité']);
    }
}
