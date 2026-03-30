<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\CoachModel;

final class CoachsController extends Controller
{
    public function index(): void
    {
        $specialite = $_GET['specialite'] ?? '';
        $localisation = $_GET['localisation'] ?? '';
        $tarif_max = $_GET['tarif_max'] ?? '';

        $coachs = CoachModel::listCoachs($_GET);
        $specialites = CoachModel::listSpecialites();

        $titre = 'Nos coachs - SportConnect';

        $this->render('public/coachs/index.php', compact('coachs', 'specialites', 'specialite', 'localisation', 'tarif_max', 'titre'));
    }
}

