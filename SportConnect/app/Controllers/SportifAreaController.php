<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\CoachModel;
use App\Models\SeanceModel;

final class SportifAreaController extends Controller
{
    public function reserver(): void
    {
        Auth::requireRole('sportif', BASE_PATH . '/index.php?route=auth/login', BASE_PATH . '/index.php');

        $coach_id = (int)($_GET['coach_id'] ?? 0);
        $coach = CoachModel::findCoachForReservation($coach_id);

        if (!$coach) {
            $this->redirect('coachs');
        }

        $error = '';
        $success = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $date_seance = (string)($_POST['date_seance'] ?? '');
            $niveau_souhaitez = (string)($_POST['niveau_souhaitez'] ?? '');
            $heure_debut = (string)($_POST['heure_debut'] ?? '');
            $heure_fin = (string)($_POST['heure_fin'] ?? '');
            $notes = (string)($_POST['notes'] ?? '');

            $niveaux_valides = ['debutant', 'intermediaire', 'avance'];

            if ($date_seance === '' || $heure_debut === '' || $heure_fin === '') {
                $error = "Veuillez remplir tous les champs obligatoires";
            } elseif (strtotime($date_seance) < strtotime('today')) {
                $error = "La date ne peut pas être dans le passé";
            } elseif ($niveau_souhaitez === '' || !in_array($niveau_souhaitez, $niveaux_valides, true)) {
                $error = "Veuillez sélectionner un niveau souhaité valide";
            } elseif (strtotime($date_seance . ' ' . $heure_fin) <= strtotime($date_seance . ' ' . $heure_debut)) {
                $error = "L'heure de fin doit être après l'heure de début";
            } elseif (((strtotime($date_seance . ' ' . $heure_fin) - strtotime($date_seance . ' ' . $heure_debut)) / 60) < 30) {
                $error = "La séance doit durer au minimum 30 minutes";
            } elseif (SeanceModel::creneauPris($coach_id, $date_seance, $heure_debut, $heure_fin)) {
                $error = "Ce créneau n'est pas disponible";
            } else {
                $ok = SeanceModel::creerReservation([
                    'coach_id' => $coach_id,
                    'user_id' => (int)$_SESSION['user_id'],
                    'date_seance' => $date_seance,
                    'niveau_souhaitez' => $niveau_souhaitez,
                    'heure_debut' => $heure_debut,
                    'heure_fin' => $heure_fin,
                    'lieu' => (string)($coach['localisation'] ?? ''),
                    'notes' => $notes,
                ]);

                if ($ok) {
                    $success = "Votre demande de réservation a été envoyée au coach !";
                    header("refresh:2;url=" . BASE_PATH . "/espace-sportif/dashboard.php");
                } else {
                    $error = "Une erreur est survenue";
                }
            }
        }

        $titre = 'Réserver une séance - SportConnect';

        $this->render('sportif/reserver.php', compact('coach', 'coach_id', 'error', 'success', 'titre'));
    }
}

