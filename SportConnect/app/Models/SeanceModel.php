<?php

namespace App\Models;

use App\Core\Database;
use Throwable;

final class SeanceModel
{
    public static function demandesEnAttente(int $coachId): array
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare("
            SELECT s.*, u.prenom, u.nom, u.telephone, u.photo_profil
            FROM seances s
            JOIN utilisateurs u ON s.user_id = u.id
            WHERE s.coach_id = ? AND s.statut = 'en_attente'
            ORDER BY s.date_seance ASC, s.heure_debut ASC
        ");
        $stmt->execute([$coachId]);
        return $stmt->fetchAll();
    }

    public static function seancesConfirmeesAVenir(int $coachId, int $limit = 10): array
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare("
            SELECT s.*, u.prenom, u.nom, u.telephone, u.photo_profil
            FROM seances s
            JOIN utilisateurs u ON s.user_id = u.id
            WHERE s.coach_id = ? AND s.statut = 'confirmée' AND s.date_seance >= CURDATE()
            ORDER BY s.date_seance ASC, s.heure_debut ASC
            LIMIT ?
        ");
        $stmt->bindValue(1, $coachId, \PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function statsCoach(int $coachId): array
    {
        $pdo = Database::pdo();
        $statsStmt = $pdo->prepare("
            SELECT 
                COUNT(DISTINCT CASE WHEN s.statut = 'terminée' THEN s.user_id END) as nb_clients,
                COUNT(CASE WHEN s.statut = 'terminée' THEN 1 END) as nb_seances,
                COALESCE(AVG(a.note), 0) as moyenne_note,
                COUNT(DISTINCT a.id) as nb_avis
            FROM seances s
            LEFT JOIN avis a ON s.coach_id = a.coach_id
            WHERE s.coach_id = ?
        ");
        $statsStmt->execute([$coachId]);
        $stats = $statsStmt->fetch();
        return $stats ?: ['nb_clients' => 0, 'nb_seances' => 0, 'moyenne_note' => 0, 'nb_avis' => 0];
    }

    public static function coachTarifHoraire(int $coachId): float
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare("SELECT tarif_horaire FROM profils_coachs WHERE user_id = ? LIMIT 1");
        $stmt->execute([$coachId]);
        return (float)($stmt->fetchColumn() ?: 0);
    }

    public static function chiffreAffairesTerminees(int $coachId, float $tarifHoraire): float
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare("
            SELECT COALESCE(SUM((GREATEST(TIME_TO_SEC(TIMEDIFF(s.heure_fin, s.heure_debut)), 0) / 3600) * ?), 0) AS chiffre_affaires
            FROM seances s
            WHERE s.coach_id = ? AND s.statut = 'terminée'
        ");
        $stmt->execute([$tarifHoraire, $coachId]);
        return (float)($stmt->fetchColumn() ?: 0);
    }

    public static function confirmerDemande(int $seanceId, int $coachId): void
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare("UPDATE seances SET statut = 'confirmée' WHERE id = ? AND coach_id = ?");
        $stmt->execute([$seanceId, $coachId]);
    }

    public static function refuserDemande(int $seanceId, int $coachId): void
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare("UPDATE seances SET statut = 'annulée' WHERE id = ? AND coach_id = ?");
        $stmt->execute([$seanceId, $coachId]);
    }

    public static function findConfirmeeDate(int $seanceId, int $coachId): ?array
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare("SELECT date_seance FROM seances WHERE id = ? AND coach_id = ? AND statut = 'confirmée'");
        $stmt->execute([$seanceId, $coachId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function terminerOuAnnulerConfirmee(int $seanceId, int $coachId, string $newStatus): void
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare("UPDATE seances SET statut = ? WHERE id = ? AND coach_id = ? AND statut = 'confirmée'");
        $stmt->execute([$newStatus, $seanceId, $coachId]);
    }

    public static function findSeanceTimes(int $seanceId, int $coachId): ?array
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare("SELECT date_seance, heure_debut, heure_fin FROM seances WHERE id = ? AND coach_id = ?");
        $stmt->execute([$seanceId, $coachId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function listForCoach(int $coachId, string $statutFilter = ''): array
    {
        $pdo = Database::pdo();
        $sql = "
            SELECT s.*, u.prenom, u.nom, u.telephone, u.photo_profil
            FROM seances s
            JOIN utilisateurs u ON s.user_id = u.id
            WHERE s.coach_id = ?
        ";
        $params = [$coachId];
        if ($statutFilter !== '') {
            $sql .= " AND s.statut = ?";
            $params[] = $statutFilter;
        }
        $sql .= " ORDER BY s.date_seance DESC, s.heure_debut DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function marquerTerminee(int $seanceId, int $coachId): void
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare("UPDATE seances SET statut = 'terminée' WHERE id = ? AND coach_id = ?");
        $stmt->execute([$seanceId, $coachId]);
    }

    public static function hasNiveauSouhaitezColumn(): bool
    {
        $pdo = Database::pdo();
        try {
            $colStmt = $pdo->prepare(
                "SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'seances' AND COLUMN_NAME = 'niveau_souhaitez' LIMIT 1"
            );
            $colStmt->execute();
            return (bool)$colStmt->fetchColumn();
        } catch (Throwable) {
            return false;
        }
    }

    public static function creneauPris(int $coachId, string $dateSeance, string $heureDebut, string $heureFin): bool
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM seances 
            WHERE coach_id = ? 
            AND date_seance = ? 
            AND statut != 'annulée'
            AND (
                (heure_debut <= ? AND heure_fin > ?) OR
                (heure_debut < ? AND heure_fin >= ?) OR
                (heure_debut >= ? AND heure_fin <= ?)
            )
        ");
        $stmt->execute([$coachId, $dateSeance, $heureDebut, $heureDebut, $heureFin, $heureFin, $heureDebut, $heureFin]);
        return ((int)$stmt->fetchColumn()) > 0;
    }

    public static function creerReservation(array $data): bool
    {
        $pdo = Database::pdo();
        $hasNiveau = self::hasNiveauSouhaitezColumn();

        if ($hasNiveau) {
            $stmt = $pdo->prepare("
                INSERT INTO seances (coach_id, user_id, date_seance, niveau_souhaitez, heure_debut, heure_fin, lieu, notes, statut)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'en_attente')
            ");
            return $stmt->execute([
                $data['coach_id'],
                $data['user_id'],
                $data['date_seance'],
                $data['niveau_souhaitez'],
                $data['heure_debut'],
                $data['heure_fin'],
                $data['lieu'],
                $data['notes'],
            ]);
        }

        $stmt = $pdo->prepare("
            INSERT INTO seances (coach_id, user_id, date_seance, heure_debut, heure_fin, lieu, notes, statut)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'en_attente')
        ");
        return $stmt->execute([
            $data['coach_id'],
            $data['user_id'],
            $data['date_seance'],
            $data['heure_debut'],
            $data['heure_fin'],
            $data['lieu'],
            $data['notes'],
        ]);
    }
}

