<?php
/**
 * Page de réinitialisation de mot de passe
 */
require_once '../configuration/database.php';
require_once '../configuration/session.php';

$succes = '';
$erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    
    if (empty($email)) {
        $erreur = "Veuillez entrer votre adresse email";
    } else {
        $conn = getConnection();
        $stmt = $conn->prepare("SELECT id, nom, prenom FROM utilisateurs WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user) {
            // Générer un nouveau mot de passe temporaire
            $nouveauMotDePasse = 'SportConnect' . rand(1000, 9999);
            $hash = password_hash($nouveauMotDePasse, PASSWORD_DEFAULT);
            
            // Mettre à jour dans la base
            $stmt = $conn->prepare("UPDATE utilisateurs SET mot_de_passe = ? WHERE email = ?");
            $stmt->execute([$hash, $email]);
            
            $succes = "Votre nouveau mot de passe temporaire est : <strong>$nouveauMotDePasse</strong><br>
                      Veuillez le noter et le changer après votre connexion.";
        } else {
            $erreur = "Aucun compte trouvé avec cet email";
        }
    }
}

$titre = 'Mot de passe oublié - SportConnect';
require_once '../vues/mise-en-page/entete.php';
?>

<div class="container" style="max-width: 500px; margin-top: 5rem;">
    <div class="card">
        <div class="card-header">
            <h1 class="card-title">Mot de passe oublié</h1>
            <p style="color: var(--gray); margin-top: 0.5rem;">
                Entrez votre email pour recevoir un nouveau mot de passe
            </p>
        </div>

        <?php if (!empty($erreur)): ?>
            <div class="alert alert-danger"><?= $erreur ?></div>
        <?php endif; ?>

        <?php if (!empty($succes)): ?>
            <div class="alert alert-success"><?= $succes ?></div>
            <a href="/SportConnect/connexion.php" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">
                Se connecter
            </a>
        <?php else: ?>
            <form method="POST">
                <div class="form-group">
                    <label class="form-label">Adresse email</label>
                    <input type="email" name="email" class="form-control" required 
                           placeholder="votre.email@exemple.fr"
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    Réinitialiser le mot de passe
                </button>
            </form>

            <p style="text-align: center; margin-top: 1.5rem; color: var(--gray);">
                Vous vous souvenez de votre mot de passe ? 
                <a href="/SportConnect/connexion.php" style="color: var(--primary); font-weight: 600;">
                    Se connecter
                </a>
            </p>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../vues/mise-en-page/pied-de-page.php'; ?>
