<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\UserModel;

final class AuthController extends Controller
{
    /**
     * Affiche / traite le formulaire de connexion.
     * En MVC, c'est le contrôleur qui orchestre la validation et la vue.
     */
    public function login(): void
    {
        $erreur = '';
        $succes = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            if ($email === '' || $password === '') {
                $erreur = 'Veuillez remplir tous les champs.';
            } else {
                $utilisateur = UserModel::findActiveByEmail($email);

                if ($utilisateur && password_verify($password, $utilisateur['mot_de_passe'])) {
                    // Stocker uniquement les informations nécessaires
                    $_SESSION['user_id'] = $utilisateur['id'];
                    $_SESSION['nom'] = $utilisateur['nom'];
                    $_SESSION['prenom'] = $utilisateur['prenom'];
                    $_SESSION['email'] = $utilisateur['email'];
                    $_SESSION['role'] = $utilisateur['role'];

                    // Redirection selon le rôle (mix MVC + legacy si nécessaire)
                    if ($utilisateur['role'] === 'admin') {
                        header('Location: ' . $this->basePath . '/old/administration/utilisateurs.php');
                        exit();
                    }
                    if ($utilisateur['role'] === 'coach') {
                        header('Location: ' . $this->basePath . '/index.php?route=coach/dashboard');
                        exit();
                    }
                    header('Location: ' . $this->basePath . '/old/espace-sportif/dashboard.php');
                    exit();
                }

                $erreur = 'Email ou mot de passe incorrect.';
            }
        }

        if (isset($_GET['registered']) && $_GET['registered'] === '1') {
            $succes = "Inscription réussie ! Vous pouvez maintenant vous connecter.";
        }

        // Vue de connexion
        require __DIR__ . '/../../app/Views/auth/login.php';
    }

    /**
     * Affiche / traite le formulaire d'inscription.
     */
    public function register(): void
    {
        $erreur = '';
        $succes = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom = trim($_POST['nom'] ?? '');
            $prenom = trim($_POST['prenom'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $telephone = trim($_POST['telephone'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirm = $_POST['confirm_password'] ?? '';
            $role = $_POST['role'] ?? 'sportif';

            if ($nom === '' || $prenom === '' || $email === '' || $telephone === '' || $password === '') {
                $erreur = 'Veuillez remplir tous les champs obligatoires.';
            } elseif ($password !== $confirm) {
                $erreur = 'Les mots de passe ne correspondent pas.';
            } elseif (strlen($password) < 6) {
                $erreur = 'Le mot de passe doit contenir au moins 6 caractères.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $erreur = 'L\'adresse email n\'est pas valide.';
            } elseif (UserModel::emailExists($email)) {
                $erreur = 'Cette adresse email est déjà utilisée.';
            } else {
                $userId = UserModel::createUser([
                    'nom' => $nom,
                    'prenom' => $prenom,
                    'email' => $email,
                    'telephone' => $telephone,
                    'mot_de_passe' => password_hash($password, PASSWORD_DEFAULT),
                    'role' => $role,
                ]);

                if ($role === 'sportif') {
                    UserModel::createProfileSportif($userId, [
                        'date_naissance' => $_POST['date_naissance'] ?? null,
                        'sexe' => $_POST['sexe'] ?? null,
                        'adresse' => trim($_POST['adresse'] ?? ''),
                        'ville' => trim($_POST['ville'] ?? ''),
                        'code_postal' => trim($_POST['code_postal'] ?? ''),
                        'sport_pratique' => trim($_POST['sport_pratique'] ?? ''),
                        'niveau' => $_POST['niveau'] ?? null,
                        'frequence_entrainement' => $_POST['frequence_entrainement'] ?? null,
                        'objectifs' => trim($_POST['objectifs'] ?? ''),
                    ]);
                } else {
                    UserModel::createProfileCoach($userId, [
                        'specialite' => trim($_POST['specialite'] ?? ''),
                        'tarif' => $_POST['tarif'] ?? 0,
                        'diplomes' => trim($_POST['diplomes'] ?? ''),
                        'experience' => $_POST['experience'] ?? 0,
                        'description' => trim($_POST['description'] ?? ''),
                        'localisation' => trim($_POST['localisation'] ?? ''),
                    ]);
                }

                header('Location: ' . $this->basePath . '/index.php?route=auth/login&registered=1');
                exit();
            }
        }

        require __DIR__ . '/../../app/Views/auth/register.php';
    }

    /**
     * Affiche / traite le formulaire "mot de passe oublié".
     * Version identique à l'ancien projet: génère un mot de passe temporaire et l'affiche.
     */
    public function forgot(): void
    {
        $succes = '';
        $erreur = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');

            if ($email === '') {
                $erreur = "Veuillez entrer votre adresse email";
            } else {
                $user = UserModel::findByEmail($email);
                if ($user) {
                    $nouveauMotDePasse = 'SportConnect' . random_int(1000, 9999);
                    $hash = password_hash($nouveauMotDePasse, PASSWORD_DEFAULT);

                    if (UserModel::updatePasswordByEmail($email, $hash)) {
                        $succes = "Votre nouveau mot de passe temporaire est : <strong>" . htmlspecialchars($nouveauMotDePasse) . "</strong><br>
                                   Veuillez le noter et le changer après votre connexion.";
                    } else {
                        $erreur = "Une erreur est survenue lors de la réinitialisation.";
                    }
                } else {
                    $erreur = "Aucun compte trouvé avec cet email";
                }
            }
        }

        require __DIR__ . '/../../app/Views/auth/forgot.php';
    }

    /**
     * Déconnexion utilisateur.
     */
    public function logout(): void
    {
        session_destroy();
        header('Location: ' . $this->basePath . '/index.php');
        exit();
    }
}
