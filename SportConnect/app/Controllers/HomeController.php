<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\CoachModel;

final class HomeController extends Controller
{
    public function index(): void
    {
        $coachs = CoachModel::getValidatedCoachsLimited(6);
        $titre = 'SportConnect - Trouvez votre coach sportif';

        $this->render('home/index.php', compact('coachs', 'titre'));
    }
}

