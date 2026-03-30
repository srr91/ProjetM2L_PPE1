<?php
/**
 * Page Contact
 */
require_once __DIR__ . '/../configuration/session.php';
require_once __DIR__ . '/../configuration/database.php';

$succes = '';
$erreur = '';

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom']);
    $email = trim($_POST['email']);
    $sujet = trim($_POST['sujet']);
    $message = trim($_POST['message']);
    
    // Validation
    if (empty($nom) || empty($email) || empty($sujet) || empty($message)) {
        $erreur = "Tous les champs sont obligatoires";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erreur = "L'adresse email n'est pas valide";
    } else {
        // Ici vous pouvez envoyer un email ou enregistrer dans la base de données
        // Pour l'instant, on affiche juste un message de succès
        $succes = "Votre message a été envoyé avec succès ! Nous vous répondrons dans les plus brefs délais.";
        
        // Réinitialiser les champs
        $nom = $email = $sujet = $message = '';
    }
}

$titre = 'Contact - SportConnect';
require_once __DIR__ . '/../vues/mise-en-page/entete.php';
?>

<div class="container" style="margin-top: 3rem;">
    <h1 style="text-align: center; margin-bottom: 1rem;">📧 Contactez-nous</h1>
    <p style="text-align: center; color: var(--gray); margin-bottom: 3rem;">
        Une question ? Une suggestion ? N'hésitez pas à nous contacter !
    </p>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 3rem; max-width: 1200px; margin: 0 auto;">
        
        <!-- Formulaire de contact -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Envoyez-nous un message</h2>
            </div>

            <?php if (!empty($erreur)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($erreur) ?></div>
            <?php endif; ?>

            <?php if (!empty($succes)): ?>
                <div class="alert alert-success"><?= htmlspecialchars($succes) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label class="form-label">Nom complet</label>
                    <input type="text" name="nom" class="form-control" required 
                           value="<?= htmlspecialchars($nom ?? '') ?>"
                           placeholder="Votre nom">
                </div>

                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" required 
                           value="<?= htmlspecialchars($email ?? '') ?>"
                           placeholder="votre.email@exemple.fr">
                </div>

                <div class="form-group">
                    <label class="form-label">Sujet</label>
                    <select name="sujet" class="form-control" required>
                        <option value="">Choisissez un sujet</option>
                        <option value="Question générale">Question générale</option>
                        <option value="Problème technique">Problème technique</option>
                        <option value="Devenir coach">Devenir coach</option>
                        <option value="Signalement">Signalement</option>
                        <option value="Autre">Autre</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Message</label>
                    <textarea name="message" class="form-control" rows="6" required 
                              placeholder="Votre message..."><?= htmlspecialchars($message ?? '') ?></textarea>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    Envoyer le message
                </button>
            </form>
        </div>

        <!-- Informations de contact -->
        <div>
            <div class="card" style="margin-bottom: 2rem;">
                <h3 style="color: var(--primary); margin-bottom: 1.5rem;">📍 Nos coordonnées</h3>
                
                <div style="margin-bottom: 1.5rem;">
                    <h4 style="font-size: 1rem; margin-bottom: 0.5rem;">📧 Email</h4>
                    <p style="color: var(--gray);">support@sportconnect.fr</p>
                </div>

                <div style="margin-bottom: 1.5rem;">
                    <h4 style="font-size: 1rem; margin-bottom: 0.5rem;">📞 Téléphone</h4>
                    <p style="color: var(--gray);">01 23 45 67 89</p>
                </div>

                <div style="margin-bottom: 1.5rem;">
                    <h4 style="font-size: 1rem; margin-bottom: 0.5rem;">🏢 Adresse</h4>
                    <p style="color: var(--gray);">
                        123 Avenue du Sport<br>
                        75001 Paris, France
                    </p>
                </div>

                <div>
                    <h4 style="font-size: 1rem; margin-bottom: 0.5rem;">🕐 Horaires</h4>
                    <p style="color: var(--gray);">
                        Lundi - Vendredi : 9h - 18h<br>
                        Samedi : 10h - 16h<br>
                        Dimanche : Fermé
                    </p>
                </div>
            </div>

            <div class="card">
                <h3 style="color: var(--primary); margin-bottom: 1.5rem;">❓ Questions fréquentes</h3>
                <p style="color: var(--gray); margin-bottom: 1rem;">
                    Avant de nous contacter, consultez notre page FAQ, vous y trouverez peut-être la réponse à votre question.
                </p>
                <a href="/SportConnect/pages/faq.php" class="btn btn-secondary" style="width: 100%;">
                    Voir la FAQ
                </a>
            </div>
        </div>

    </div>
</div>

<?php require_once __DIR__ . '/../vues/mise-en-page/pied-de-page.php'; ?>
