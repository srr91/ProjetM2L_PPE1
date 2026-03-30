<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\AvisModel;
use App\Models\CoachModel;

final class PublicCoachController extends Controller
{
    public function show(): void
    {
        $coach_id = (int)($_GET['id'] ?? 0);
        $coach = CoachModel::findPublicProfile($coach_id);

        if (!$coach) {
            header('Location: ' . BASE_PATH . '/index.php?route=coachs');
            exit();
        }

        $avis = AvisModel::listPublicForCoach($coach_id, 10);
        $titre = htmlspecialchars(($coach['prenom'] ?? '') . ' ' . ($coach['nom'] ?? '')) . ' - SportConnect';

        $this->render('public/coach/show.php', compact('coach', 'avis', 'titre'));
    }
}

