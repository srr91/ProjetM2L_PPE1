<?php

namespace App\Models;

use App\Core\Database;
use PDO;
use Throwable;

final class CoachModel
{
    public static function getValidatedCoachsLimited(int $limit): array
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare("
            SELECT u.*, pc.specialite, pc.description, pc.tarif_horaire, pc.localisation,
                   COALESCE(AVG(a.note), 0) as moyenne_note,
                   COUNT(DISTINCT a.id) as nb_avis
            FROM utilisateurs u
            JOIN profils_coachs pc ON u.id = pc.user_id
            LEFT JOIN avis a ON u.id = a.coach_id
            WHERE u.role = 'coach' AND u.actif = 1
            GROUP BY u.id
            LIMIT ?
        ");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function listCoachs(array $filters): array
    {
        $pdo = Database::pdo();

        $specialite = (string)($filters['specialite'] ?? '');
        $localisation = (string)($filters['localisation'] ?? '');
        $tarifMax = (string)($filters['tarif_max'] ?? '');

        $experienceSelect = self::resolveExperienceSelect();

        $sql = "
            SELECT u.*, pc.specialite, pc.description, pc.tarif_horaire, pc.localisation, {$experienceSelect} AS experience,
                   COALESCE(AVG(a.note), 0) as moyenne_note,
                   COUNT(DISTINCT a.id) as nb_avis
            FROM utilisateurs u
            JOIN profils_coachs pc ON u.id = pc.user_id
            LEFT JOIN avis a ON u.id = a.coach_id
            WHERE u.role = 'coach' AND u.actif = 1
        ";

        $params = [];
        if ($specialite !== '') {
            $sql .= " AND pc.specialite LIKE ?";
            $params[] = "%{$specialite}%";
        }
        if ($localisation !== '') {
            $sql .= " AND pc.localisation LIKE ?";
            $params[] = "%{$localisation}%";
        }
        if ($tarifMax !== '') {
            $sql .= " AND pc.tarif_horaire <= ?";
            $params[] = $tarifMax;
        }

        $sql .= " GROUP BY u.id ORDER BY moyenne_note DESC, nb_avis DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function listSpecialites(): array
    {
        $pdo = Database::pdo();
        return $pdo->query("SELECT DISTINCT specialite FROM profils_coachs ORDER BY specialite")->fetchAll(PDO::FETCH_COLUMN);
    }

    public static function findCoachForReservation(int $coachId): ?array
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare("
            SELECT u.*, pc.specialite, pc.tarif_horaire, pc.localisation
            FROM utilisateurs u
            JOIN profils_coachs pc ON u.id = pc.user_id
            WHERE u.id = ? AND u.role = 'coach' AND u.actif = 1
        ");
        $stmt->execute([$coachId]);
        $coach = $stmt->fetch();
        return $coach ?: null;
    }

    public static function findPublicProfile(int $coachId): ?array
    {
        $pdo = Database::pdo();

        $experienceSelect = self::resolveAnneesExperienceSelect();

        $stmt = $pdo->prepare("
            SELECT u.id, u.nom, u.prenom, u.email, u.role, u.photo_profil,
                   u.banniere_profil,
                   pc.specialite, pc.description, pc.tarif_horaire, pc.localisation,
                   {$experienceSelect} AS annees_experience, pc.diplomes, pc.valide,
                   COALESCE(AVG(a.note), 0) as moyenne_note,
                   COUNT(DISTINCT a.id) as nb_avis,
                   COUNT(DISTINCT s.id) as nb_seances
            FROM utilisateurs u
            JOIN profils_coachs pc ON u.id = pc.user_id
            LEFT JOIN avis a ON u.id = a.coach_id
            LEFT JOIN seances s ON u.id = s.coach_id AND s.statut = 'terminée'
            WHERE u.id = ? AND u.role = 'coach' AND u.actif = 1
            GROUP BY u.id
        ");
        $stmt->execute([$coachId]);
        $coach = $stmt->fetch();
        return $coach ?: null;
    }

    private static function resolveExperienceSelect(): string
    {
        $pdo = Database::pdo();
        try {
            $colStmt = $pdo->prepare(
                "SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'profils_coachs' AND COLUMN_NAME = 'annees_experience' LIMIT 1"
            );
            $colStmt->execute();
            $hasAnneesExperience = (bool)$colStmt->fetchColumn();
        } catch (Throwable) {
            $hasAnneesExperience = false;
        }

        return $hasAnneesExperience ? 'pc.annees_experience' : 'pc.experience';
    }

    private static function resolveAnneesExperienceSelect(): string
    {
        $pdo = Database::pdo();
        try {
            $colStmt = $pdo->prepare(
                "SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'profils_coachs' AND COLUMN_NAME = 'annees_experience' LIMIT 1"
            );
            $colStmt->execute();
            $hasAnneesExperience = (bool)$colStmt->fetchColumn();
        } catch (Throwable) {
            $hasAnneesExperience = false;
        }

        return $hasAnneesExperience ? 'pc.annees_experience' : 'pc.experience';
    }
}

