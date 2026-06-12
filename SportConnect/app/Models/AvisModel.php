<?php

namespace App\Models;

use App\Core\Database;

final class AvisModel
{
    public static function listPublicForCoach(int $coachId, int $limit = 10): array
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare("
            SELECT a.*, u.prenom, u.nom
            FROM avis a
            JOIN utilisateurs u ON a.user_id = u.id
            WHERE a.coach_id = ? AND a.modere = 1
            ORDER BY a.date_avis DESC
            LIMIT ?
        ");
        $stmt->bindValue(1, $coachId, \PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}

