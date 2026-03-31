<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\SeanceModel;

final class CoachAreaController extends Controller
{
    public function dashboard(): void
    {
        Auth::requireRole('coach', BASE_PATH . '/index.php?route=auth/login', BASE_PATH . '/index.php');

        $connCoachId = (int)($_SESSION['user_id'] ?? 0);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleDashboardPost($connCoachId);
            return;
        }

        $demandes = SeanceModel::demandesEnAttente($connCoachId);
        $seances_confirmees = SeanceModel::seancesConfirmeesAVenir($connCoachId, 10);
        $stats = SeanceModel::statsCoach($connCoachId);

        $coach_tarif = SeanceModel::coachTarifHoraire($connCoachId);
        $chiffre_affaires = SeanceModel::chiffreAffairesTerminees($connCoachId, $coach_tarif);

        $chiffre_affaires_affiche = number_format($chiffre_affaires, 2, ',', ' ');
        $chiffre_affaires_affiche = trim($chiffre_affaires_affiche);
        $chiffre_affaires_affiche = preg_replace('/^[\s\x{00A0}]*0+(?=\d)/u', '', $chiffre_affaires_affiche);

        $titre = 'Espace Coach - SportConnect';

        $this->render('coach/dashboard.php', compact(
            'demandes',
            'seances_confirmees',
            'stats',
            'chiffre_affaires_affiche',
            'titre'
        ));
    }

    public function seances(): void
    {
        Auth::requireRole('coach', BASE_PATH . '/index.php?route=auth/login', BASE_PATH . '/index.php');

        $coachId = (int)($_SESSION['user_id'] ?? 0);
        $statut_filter = (string)($_GET['statut'] ?? '');

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['terminer'])) {
            $seance_id = (int)($_POST['seance_id'] ?? 0);
            if ($seance_id > 0) {
                SeanceModel::marquerTerminee($seance_id, $coachId);
            }
            $this->redirect('coach/seances', $statut_filter !== '' ? ['statut' => $statut_filter] : []);
        }

        $seances = SeanceModel::listForCoach($coachId, $statut_filter);
        $titre = 'Mes séances - SportConnect';

        $this->render('coach/seances.php', compact('seances', 'statut_filter', 'titre'));
    }

    private function handleDashboardPost(int $coachId): void
    {
        // Actions sur les séances confirmées (terminer/annuler)
        if (isset($_POST['seance_action'])) {
            $seance_id = (int)($_POST['seance_id'] ?? 0);
            $action = (string)($_POST['seance_action'] ?? '');

            if ($seance_id > 0 && ($action === 'terminer' || $action === 'annuler')) {
                $seance = SeanceModel::findConfirmeeDate($seance_id, $coachId);

                if (!$seance) {
                    $_SESSION['flash_error'] = "Séance introuvable.";
                } elseif ($action === 'terminer' && strtotime((string)$seance['date_seance']) > strtotime('today')) {
                    $_SESSION['flash_error'] = "Impossible : vous ne pouvez pas terminer une séance avant sa date.";
                } else {
                    $newStatus = ($action === 'terminer') ? 'terminée' : 'annulée';
                    SeanceModel::terminerOuAnnulerConfirmee($seance_id, $coachId, $newStatus);
                    $_SESSION['flash_success'] = ($action === 'terminer') ? "Séance marquée comme terminée." : "Séance annulée.";
                }
            }

            $this->redirect('coach/dashboard');
        }

        // Actions sur les demandes (confirmer/refuser)
        if (isset($_POST['action'])) {
            $seance_id = (int)($_POST['seance_id'] ?? 0);
            $action = (string)($_POST['action'] ?? '');

            if ($seance_id > 0 && $action === 'confirmer') {
                $seance = SeanceModel::findSeanceTimes($seance_id, $coachId);

                $minutes = 0;
                if ($seance && !empty($seance['date_seance']) && !empty($seance['heure_debut']) && !empty($seance['heure_fin'])) {
                    $start = strtotime($seance['date_seance'] . ' ' . $seance['heure_debut']);
                    $end = strtotime($seance['date_seance'] . ' ' . $seance['heure_fin']);
                    if ($start !== false && $end !== false) {
                        $minutes = (int)(($end - $start) / 60);
                    }
                }

                if ($minutes < 30) {
                    $_SESSION['flash_error'] = "Impossible : une séance doit durer au minimum 30 minutes.";
                } else {
                    SeanceModel::confirmerDemande($seance_id, $coachId);
                    $_SESSION['flash_success'] = "Réservation confirmée.";
                }
            } elseif ($seance_id > 0 && $action === 'refuser') {
                SeanceModel::refuserDemande($seance_id, $coachId);
                $_SESSION['flash_success'] = "Réservation refusée.";
            }

            // Historiquement tu redirigeais vers seances.php : on garde la même intention
            $this->redirect('coach/seances');
        }

        $this->redirect('coach/dashboard');
    }
}

