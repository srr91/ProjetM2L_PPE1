<?php

namespace App\Models;

use PDO;

/**
 * Modèle utilisateur.
 *
 * Contient toutes les opérations en base de données pour l'authentification et la création de profil.
 */
final class UserModel
{
    /**
     * Retourne une instance PDO depuis le système de configuration existant.
     */
    private static function pdo(): PDO
    {
        // On utilise le helper existant dans configuration/database.php
        return getConnection();
    }

    /**
     * Récupère un utilisateur actif par email.
     */
    public static function findActiveByEmail(string $email): ?array
    {
        $stmt = self::pdo()->prepare("SELECT * FROM utilisateurs WHERE email = ? AND actif = 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }

    /**
     * Vérifie si un email existe déjà.
     */
    public static function emailExists(string $email): bool
    {
        $stmt = self::pdo()->prepare("SELECT id FROM utilisateurs WHERE email = ?");
        $stmt->execute([$email]);
        return (bool)$stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Crée un utilisateur et retourne l'id créé.
     */
    public static function createUser(array $data): int
    {
        $pdo = self::pdo();
        $stmt = $pdo->prepare(
            "INSERT INTO utilisateurs
             (nom, prenom, email, telephone, mot_de_passe, role, actif)
             VALUES (?, ?, ?, ?, ?, ?, 1)"
        );
        $stmt->execute([
            $data['nom'],
            $data['prenom'],
            $data['email'],
            $data['telephone'],
            $data['mot_de_passe'],
            $data['role'],
        ]);

        return (int)$pdo->lastInsertId();
    }

    /**
     * Crée le profil sportif lié à un utilisateur.
     */
    public static function createProfileSportif(int $userId, array $data): bool
    {
        $stmt = self::pdo()->prepare(
            "INSERT INTO profils_sportifs
             (user_id, date_naissance, sexe, adresse, ville, code_postal, sport_pratique, niveau, frequence_entrainement, objectifs)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        return $stmt->execute([
            $userId,
            $data['date_naissance'] ?? null,
            $data['sexe'] ?? null,
            $data['adresse'] ?? null,
            $data['ville'] ?? null,
            $data['code_postal'] ?? null,
            $data['sport_pratique'] ?? null,
            $data['niveau'] ?? null,
            $data['frequence_entrainement'] ?? null,
            $data['objectifs'] ?? null,
        ]);
    }

    /**
     * Crée le profil coach lié à un utilisateur.
     */
    public static function createProfileCoach(int $userId, array $data): bool
    {
        $stmt = self::pdo()->prepare(
            "INSERT INTO profils_coachs
             (user_id, specialite, tarif_horaire, diplomes, experience, description, localisation, valide)
             VALUES (?, ?, ?, ?, ?, ?, ?, 1)"
        );
        return $stmt->execute([
            $userId,
            $data['specialite'] ?? 'À compléter',
            $data['tarif'] ?? 0,
            $data['diplomes'] ?? null,
            $data['experience'] ?? 0,
            $data['description'] ?? 'Profil en cours de complétion.',
            $data['localisation'] ?? 'À préciser',
        ]);
    }

    public static function findByEmail(string $email): ?array
    {
        $stmt = self::pdo()->prepare("SELECT * FROM utilisateurs WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }

    public static function updatePasswordByEmail(string $email, string $passwordHash): bool
    {
        $stmt = self::pdo()->prepare("UPDATE utilisateurs SET mot_de_passe = ? WHERE email = ?");
        return $stmt->execute([$passwordHash, $email]);
    }
}
