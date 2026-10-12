<?php
declare(strict_types=1);

namespace App\Controller;

use App\Core\View;
use App\Repository\AvisRepository;
use App\Repository\MenuRepository;

final class HomeController
{
    public function index(): void
    {
        $avisRepo = new AvisRepository();
        View::render('pages/home', [
            'title' => 'Traiteur à Bordeaux depuis 25 ans',
            'avis' => $avisRepo->validated(6),
            'moyenne' => $avisRepo->averageNote(),
            'menus' => array_slice((new MenuRepository())->search(), 0, 3),
        ]);
    }
}
