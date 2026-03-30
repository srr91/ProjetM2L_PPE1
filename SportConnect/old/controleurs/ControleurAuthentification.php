<?php
/**
 * Contrôleur d'authentification
 * Gère la connexion, l'inscription et la déconnexion des utilisateurs
 */

require_once __DIR__ . '/../configuration/database.php';
require_once __DIR__ . '/../configuration/session.php';

class ControleurAuthentification {
    
    /**
     * Affiche la page de connexion
     */
    public function afficherConnexion() {
        $erreur = '';
        $titre = 'Connexion - SportConnect';
        require_once __DIR__ . '/../vues/authentification/connexion.php';
    }
    
    /**
     * Traite la soumission du formulaire de connexion
     */
    public function traiterConnexion() {
        $erreur = '';
        
        // Vérifier si un message est passé en paramètre
        if (isset($_GET['message'])) {
            if ($_GET['message'] === 'compte_desactive') {
                $erreur = "Votre compte a été désactivé ou supprimé par un administrateur. Veuillez contacter le support.";
            } elseif ($_GET['message'] === 'deconnexion') {
                $erreur = "Vous avez été déconnecté car votre compte a été modifié.";
            }
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email']);
            $motDePasse = $_POST['password'];
            
            // Validation des champs
            if (empty($email) || empty($motDePasse)) {
                $erreur = "Veuillez remplir tous les champs";
            } else {
                // Connexion à la base de données
                $conn = getConnection();
                $stmt = $conn->prepare("SELECT * FROM utilisateurs WHERE email = ? AND actif = 1");
                $stmt->execute([$email]);
                $utilisateur = $stmt->fetch();
                
                // Vérification du mot de passe
                if ($utilisateur && password_verify($motDePasse, $utilisateur['mot_de_passe'])) {
                    // Connexion réussie - Création de la session
                    $_SESSION['user_id'] = $utilisateur['id'];
                    $_SESSION['nom'] = $utilisateur['nom'];
                    $_SESSION['prenom'] = $utilisateur['prenom'];
                    $_SESSION['email'] = $utilisateur['email'];
                    $_SESSION['role'] = $utilisateur['role'];
                    
                    // Redirection selon le rôle avec URL absolue
                    $redirectUrl = '';
                    if ($utilisateur['role'] === 'admin') {
                        $redirectUrl = '/SportConnect/administration/utilisateurs.php';
                    } elseif ($utilisateur['role'] === 'coach') {
                        $redirectUrl = '/SportConnect/espace-coach/dashboard.php';
                    } else {
                        $redirectUrl = '/SportConnect/espace-sportif/dashboard.php';
                    }
                    
                    // Nettoyer le buffer de sortie avant la redirection
                    if (ob_get_level()) {
                        ob_end_clean();
                    }
                    
                    header('Location: ' . $redirectUrl, true, 302);
                    exit();
                } else {
                    $erreur = "Email ou mot de passe incorrect";
                }
            }
        }
        
        // Affichage de la page avec l'erreur
        $titre = 'Connexion - SportConnect';
        require_once __DIR__ . '/../vues/authentification/connexion.php';
    }
    
    /**
     * Affiche la page d'inscription
     */
    public function afficherInscription() {
        $erreur = '';
        $succes = '';
        $titre = 'Inscription - SportConnect';
        require_once __DIR__ . '/../vues/authentification/inscription.php';
    }
    
    /**
     * Traite la soumission du formulaire d'inscription
     */
    public function traiterInscription() {
        $erreur = '';
        $succes = '';
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom = trim($_POST['nom']);
            $prenom = trim($_POST['prenom']);
            $email = trim($_POST['email']);
            $telephone = trim($_POST['telephone']);
            $motDePasse = $_POST['password'];
            $confirmationMotDePasse = $_POST['confirm_password'];
            $role = $_POST['role'];
            
            // Champs supplémentaires
            $date_naissance = $_POST['date_naissance'] ?? null;
            $sexe = $_POST['sexe'] ?? null;
            $adresse = trim($_POST['adresse'] ?? '');
            $ville = trim($_POST['ville'] ?? '');
            $code_postal = trim($_POST['code_postal'] ?? '');
            
            // Validation des champs
            if (empty($nom) || empty($prenom) || empty($email) || empty($motDePasse)) {
                $erreur = "Veuillez remplir tous les champs obligatoires";
            } elseif ($motDePasse !== $confirmationMotDePasse) {
                $erreur = "Les mots de passe ne correspondent pas";
            } elseif (strlen($motDePasse) < 6) {
                $erreur = "Le mot de passe doit contenir au moins 6 caractères";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $erreur = "L'adresse email n'est pas valide";
            } else {
                // Connexion à la base de données
                $conn = getConnection();
                
                // Vérifier si l'email existe déjà
                $stmt = $conn->prepare("SELECT id FROM utilisateurs WHERE email = ?");
                $stmt->execute([$email]);
                
                if ($stmt->fetch()) {
                    $erreur = "Cette adresse email est déjà utilisée";
                } else {
                    // Hashage du mot de passe
                    $motDePasseHash = password_hash($motDePasse, PASSWORD_DEFAULT);
                    
                    // Insertion de l'utilisateur
                    $stmt = $conn->prepare("
                        INSERT INTO utilisateurs (nom, prenom, email, telephone, mot_de_passe, role) 
                        VALUES (?, ?, ?, ?, ?, ?)
                    ");
                    
                    if ($stmt->execute([$nom, $prenom, $email, $telephone, $motDePasseHash, $role])) {
                        $userId = $conn->lastInsertId();
                        
                        // Créer le profil selon le rôle
                        if ($role === 'sportif') {
                            $sport_pratique = trim($_POST['sport_pratique'] ?? '');
                            $niveau = $_POST['niveau'] ?? null;
                            $frequence = $_POST['frequence_entrainement'] ?? null;
                            $objectifs = trim($_POST['objectifs'] ?? '');
                            
                            $stmt = $conn->prepare("
                                INSERT INTO profils_sportifs 
                                (user_id, date_naissance, sexe, adresse, ville, code_postal, sport_pratique, niveau, frequence_entrainement, objectifs) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                            ");
                            $stmt->execute([$userId, $date_naissance, $sexe, $adresse, $ville, $code_postal, $sport_pratique, $niveau, $frequence, $objectifs]);
                            
                        } elseif ($role === 'coach') {
                            $specialite = trim($_POST['specialite'] ?? '');
                            $tarif = $_POST['tarif'] ?? 0;
                            $diplomes = trim($_POST['diplomes'] ?? '');
                            $experience = $_POST['experience'] ?? 0;
                            $description = trim($_POST['description'] ?? '');
                            $localisation = trim($_POST['localisation'] ?? '');

                            if ($specialite === '') {
                                $specialite = 'À compléter';
                            }
                            if ($description === '') {
                                $description = 'Profil en cours de complétion.';
                            }
                            if ($localisation === '') {
                                $localisation = 'À préciser';
                            }
                            
                            $stmt = $conn->prepare("
                                INSERT INTO profils_coachs 
                                (user_id, specialite, tarif_horaire, diplomes, experience, description, localisation, valide) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, 1)
                            ");
                            $stmt->execute([$userId, $specialite, $tarif, $diplomes, $experience, $description, $localisation]);
                        }
                        
                        $succes = "Inscription réussie ! Vous pouvez maintenant vous connecter.";
                        // Redirection après 2 secondes
                        header("refresh:2;url=/SportConnect/authentification/connexion.php");
                    } else {
                        $erreur = "Une erreur est survenue lors de l'inscription";
                    }
                }
            }
        }
        
        // Affichage de la page avec les messages
        $titre = 'Inscription - SportConnect';
        require_once __DIR__ . '/../vues/authentification/inscription.php';
    }
    
    /**
     * Déconnexion de l'utilisateur
     */
    public function deconnexion() {
        session_destroy();
        header('Location: /SportConnect/index.php');
        exit();
    }
}
?>
