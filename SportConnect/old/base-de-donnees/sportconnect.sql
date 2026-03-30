-- Ce fichier est généré à partir du script fusionné
-- Voir contenu historique dans `sportconnect_merged.sql` (supprimé après validation)

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 1;

START TRANSACTION;

CREATE DATABASE IF NOT EXISTS sportconnect CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sportconnect;

-- Table des utilisateurs
CREATE TABLE IF NOT EXISTS utilisateurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    mot_de_passe VARCHAR(255) NOT NULL,
    role ENUM('sportif', 'coach', 'admin') DEFAULT 'sportif',
    telephone VARCHAR(20),
    photo_profil VARCHAR(255) DEFAULT 'default.jpg',
    banniere_profil VARCHAR(255) DEFAULT NULL,
    date_inscription DATETIME DEFAULT CURRENT_TIMESTAMP,
    actif TINYINT(1) DEFAULT 1,
    INDEX idx_email (email),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET @col_exists := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'utilisateurs' AND COLUMN_NAME = 'photo_profil');
SET @sql := IF(@col_exists = 0, 'ALTER TABLE utilisateurs ADD COLUMN photo_profil VARCHAR(255) AFTER email;', 'SELECT 1;');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'utilisateurs' AND COLUMN_NAME = 'banniere_profil');
SET @sql := IF(@col_exists = 0, "ALTER TABLE utilisateurs ADD COLUMN banniere_profil VARCHAR(255) DEFAULT NULL AFTER photo_profil;", 'SELECT 1;');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Table des profils sportifs
CREATE TABLE IF NOT EXISTS profils_sportifs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    photo_profil VARCHAR(255),
    date_naissance DATE,
    sexe ENUM('M', 'F', 'A'),
    adresse VARCHAR(255),
    ville VARCHAR(100),
    code_postal VARCHAR(10),
    age INT,
    sport_pratique VARCHAR(100),
    objectifs TEXT,
    niveau ENUM('débutant', 'intermédiaire', 'avancé') DEFAULT 'débutant',
    frequence_entrainement VARCHAR(50),
    poids DECIMAL(5,2),
    taille INT,
    blessures TEXT,
    disponibilites TEXT,
    CONSTRAINT fk_profils_sportifs_user FOREIGN KEY (user_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET @exists := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'profils_sportifs' AND COLUMN_NAME = 'photo_profil');
SET @sql := IF(@exists = 0, 'ALTER TABLE profils_sportifs ADD COLUMN photo_profil VARCHAR(255) AFTER user_id;', 'SELECT 1;');
PREPARE s1 FROM @sql; EXECUTE s1; DEALLOCATE PREPARE s1;

SET @exists := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'profils_sportifs' AND COLUMN_NAME = 'date_naissance');
SET @sql := IF(@exists = 0, 'ALTER TABLE profils_sportifs ADD COLUMN date_naissance DATE AFTER photo_profil;', 'SELECT 1;');
PREPARE s2 FROM @sql; EXECUTE s2; DEALLOCATE PREPARE s2;

SET @exists := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'profils_sportifs' AND COLUMN_NAME = 'sexe');
SET @sql := IF(@exists = 0, "ALTER TABLE profils_sportifs ADD COLUMN sexe ENUM('M','F','A') AFTER date_naissance;", 'SELECT 1;');
PREPARE s3 FROM @sql; EXECUTE s3; DEALLOCATE PREPARE s3;

SET @exists := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'profils_sportifs' AND COLUMN_NAME = 'adresse');
SET @sql := IF(@exists = 0, 'ALTER TABLE profils_sportifs ADD COLUMN adresse VARCHAR(255) AFTER sexe;', 'SELECT 1;');
PREPARE s4 FROM @sql; EXECUTE s4; DEALLOCATE PREPARE s4;

SET @exists := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'profils_sportifs' AND COLUMN_NAME = 'ville');
SET @sql := IF(@exists = 0, 'ALTER TABLE profils_sportifs ADD COLUMN ville VARCHAR(100) AFTER adresse;', 'SELECT 1;');
PREPARE s5 FROM @sql; EXECUTE s5; DEALLOCATE PREPARE s5;

SET @exists := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'profils_sportifs' AND COLUMN_NAME = 'code_postal');
SET @sql := IF(@exists = 0, 'ALTER TABLE profils_sportifs ADD COLUMN code_postal VARCHAR(10) AFTER ville;', 'SELECT 1;');
PREPARE s6 FROM @sql; EXECUTE s6; DEALLOCATE PREPARE s6;

SET @exists := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'profils_sportifs' AND COLUMN_NAME = 'frequence_entrainement');
SET @sql := IF(@exists = 0, 'ALTER TABLE profils_sportifs ADD COLUMN frequence_entrainement VARCHAR(50) AFTER niveau;', 'SELECT 1;');
PREPARE s7 FROM @sql; EXECUTE s7; DEALLOCATE PREPARE s7;

SET @exists := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'profils_sportifs' AND COLUMN_NAME = 'blessures');
SET @sql := IF(@exists = 0, 'ALTER TABLE profils_sportifs ADD COLUMN blessures TEXT AFTER taille;', 'SELECT 1;');
PREPARE s8 FROM @sql; EXECUTE s8; DEALLOCATE PREPARE s8;

SET @exists := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'profils_sportifs' AND COLUMN_NAME = 'disponibilites');
SET @sql := IF(@exists = 0, 'ALTER TABLE profils_sportifs ADD COLUMN disponibilites TEXT AFTER blessures;', 'SELECT 1;');
PREPARE s9 FROM @sql; EXECUTE s9; DEALLOCATE PREPARE s9;

-- Table des profils coachs
CREATE TABLE IF NOT EXISTS profils_coachs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    specialite VARCHAR(100) NOT NULL,
    description TEXT,
    experience INT DEFAULT 0,
    diplomes TEXT,
    tarif_horaire DECIMAL(10,2) NOT NULL,
    localisation VARCHAR(255),
    latitude DECIMAL(10,8),
    longitude DECIMAL(11,8),
    disponibilites TEXT,
    valide TINYINT(1) DEFAULT 0,
    CONSTRAINT fk_profils_coachs_user FOREIGN KEY (user_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    INDEX idx_specialite (specialite),
    INDEX idx_localisation (localisation)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET @exists := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'profils_coachs' AND COLUMN_NAME = 'diplomes');
SET @sql := IF(@exists = 0, 'ALTER TABLE profils_coachs ADD COLUMN diplomes TEXT AFTER tarif_horaire;', 'SELECT 1;');
PREPARE s10 FROM @sql; EXECUTE s10; DEALLOCATE PREPARE s10;

SET @exists := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'profils_coachs' AND COLUMN_NAME = 'experience');
SET @sql := IF(@exists = 0, 'ALTER TABLE profils_coachs ADD COLUMN experience INT DEFAULT 0 AFTER diplomes;', 'SELECT 1;');
PREPARE s11 FROM @sql; EXECUTE s11; DEALLOCATE PREPARE s11;

-- Table des séances
CREATE TABLE IF NOT EXISTS seances (
    id INT AUTO_INCREMENT PRIMARY KEY,
    coach_id INT NOT NULL,
    user_id INT NOT NULL,
    date_seance DATE NOT NULL,
    niveau_souhaitez ENUM('debutant','intermediaire','avance') DEFAULT NULL,
    heure_debut TIME NOT NULL,
    heure_fin TIME NOT NULL,
    statut ENUM('en_attente', 'confirmée', 'annulée', 'terminée') DEFAULT 'en_attente',
    lieu VARCHAR(255),
    notes TEXT,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_seances_coach FOREIGN KEY (coach_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    CONSTRAINT fk_seances_user FOREIGN KEY (user_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    INDEX idx_coach (coach_id),
    INDEX idx_user (user_id),
    INDEX idx_date (date_seance)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET @exists := (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'seances' AND COLUMN_NAME = 'niveau_souhaitez');
SET @sql := IF(@exists = 0, "ALTER TABLE seances ADD COLUMN niveau_souhaitez ENUM('debutant','intermediaire','avance') DEFAULT NULL AFTER date_seance;", 'SELECT 1;');
PREPARE s12 FROM @sql; EXECUTE s12; DEALLOCATE PREPARE s12;

-- Table des avis
CREATE TABLE IF NOT EXISTS avis (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    coach_id INT NOT NULL,
    seance_id INT,
    note INT CHECK (note BETWEEN 1 AND 5),
    commentaire TEXT,
    date_avis DATETIME DEFAULT CURRENT_TIMESTAMP,
    modere TINYINT(1) DEFAULT 1,
    CONSTRAINT fk_avis_user FOREIGN KEY (user_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    CONSTRAINT fk_avis_coach FOREIGN KEY (coach_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    CONSTRAINT fk_avis_seance FOREIGN KEY (seance_id) REFERENCES seances(id) ON DELETE SET NULL,
    INDEX idx_coach (coach_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des messages
CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    contenu TEXT NOT NULL,
    date_envoi DATETIME DEFAULT CURRENT_TIMESTAMP,
    lu TINYINT(1) DEFAULT 0,
    CONSTRAINT fk_messages_sender FOREIGN KEY (sender_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    CONSTRAINT fk_messages_receiver FOREIGN KEY (receiver_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    INDEX idx_sender (sender_id),
    INDEX idx_receiver (receiver_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table de suivi de progression
CREATE TABLE IF NOT EXISTS progressions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    date_mesure DATE NOT NULL,
    poids DECIMAL(5,2),
    temps_performance TIME,
    notes TEXT,
    CONSTRAINT fk_progressions_user FOREIGN KEY (user_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Données de base idempotentes
INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, role)
VALUES ('Admin', 'SportConnect', 'admin@sportconnect.fr', '$2y$10$WvYNEsP/WKZqTgHM24CTO.E5zdKam6.VR/6T2c7wluMlqyuNM3JBS', 'admin')
ON DUPLICATE KEY UPDATE email = VALUES(email);

INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, role, telephone) VALUES
('Dupont', 'Jean', 'jean.dupont@coach.fr', '$2y$10$WvYNEsP/WKZqTgHM24CTO.E5zdKam6.VR/6T2c7wluMlqyuNM3JBS', 'coach', '0612345678'),
('Martin', 'Sophie', 'sophie.martin@coach.fr', '$2y$10$WvYNEsP/WKZqTgHM24CTO.E5zdKam6.VR/6T2c7wluMlqyuNM3JBS', 'coach', '0623456789'),
('Bernard', 'Luc', 'luc.bernard@coach.fr', '$2y$10$WvYNEsP/WKZqTgHM24CTO.E5zdKam6.VR/6T2c7wluMlqyuNM3JBS', 'coach', '0634567890')
ON DUPLICATE KEY UPDATE email = VALUES(email);

INSERT IGNORE INTO profils_coachs (user_id, specialite, description, experience, tarif_horaire, localisation, valide)
SELECT u.id, 'Musculation', 'Coach certifié en musculation et préparation physique. Spécialisé dans la prise de masse et la remise en forme.', 5, 45.00, 'Paris 15e', 1 FROM utilisateurs u WHERE u.email = 'jean.dupont@coach.fr';

INSERT IGNORE INTO profils_coachs (user_id, specialite, description, experience, tarif_horaire, localisation, valide)
SELECT u.id, 'Yoga', 'Professeur de yoga diplômée. Cours pour tous niveaux, spécialisation en yoga thérapeutique.', 8, 40.00, 'Lyon 6e', 1 FROM utilisateurs u WHERE u.email = 'sophie.martin@coach.fr';

INSERT IGNORE INTO profils_coachs (user_id, specialite, description, experience, tarif_horaire, localisation, valide)
SELECT u.id, 'Running', "Entraîneur de course à pied. Préparation marathon et semi-marathon. Ancien athlète de haut niveau.", 10, 50.00, 'Marseille', 1 FROM utilisateurs u WHERE u.email = 'luc.bernard@coach.fr';

INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, role)
VALUES ('Durand', 'Marie', 'marie.durand@email.fr', '$2y$10$WvYNEsP/WKZqTgHM24CTO.E5zdKam6.VR/6T2c7wluMlqyuNM3JBS', 'sportif')
ON DUPLICATE KEY UPDATE email = VALUES(email);

INSERT IGNORE INTO profils_sportifs (user_id, age, sport_pratique, objectifs, niveau)
SELECT u.id, 28, 'Fitness', 'Perdre du poids et me tonifier', 'débutant' FROM utilisateurs u WHERE u.email = 'marie.durand@email.fr';

COMMIT;


