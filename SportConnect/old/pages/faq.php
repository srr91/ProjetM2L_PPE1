<?php
/**
 * Page FAQ (Foire Aux Questions)
 */
require_once __DIR__ . '/../configuration/session.php';
$titre = 'FAQ - SportConnect';
require_once __DIR__ . '/../vues/mise-en-page/entete.php';
?>

<div class="container" style="margin-top: 3rem;">
    <h1 style="text-align: center; margin-bottom: 3rem;">❓ Foire Aux Questions</h1>

    <div class="card" style="max-width: 900px; margin: 0 auto;">
        
        <!-- Question 1 -->
        <div style="border-bottom: 1px solid var(--border); padding: 2rem 0;">
            <h3 style="color: var(--primary); margin-bottom: 1rem;">Comment créer un compte ?</h3>
            <p style="color: var(--gray);">
                Cliquez sur le bouton "Inscription" en haut à droite, choisissez votre profil (Sportif ou Coach), 
                remplissez le formulaire et validez. Vous recevrez un email de confirmation.
            </p>
        </div>

        <!-- Question 2 -->
        <div style="border-bottom: 1px solid var(--border); padding: 2rem 0;">
            <h3 style="color: var(--primary); margin-bottom: 1rem;">Comment trouver un coach ?</h3>
            <p style="color: var(--gray);">
                Allez sur la page "Nos Coachs", utilisez les filtres de recherche (spécialité, tarif, localisation) 
                pour trouver le coach qui vous correspond. Consultez leurs profils et réservez une séance.
            </p>
        </div>

        <!-- Question 3 -->
        <div style="border-bottom: 1px solid var(--border); padding: 2rem 0;">
            <h3 style="color: var(--primary); margin-bottom: 1rem;">Comment réserver une séance ?</h3>
            <p style="color: var(--gray);">
                Sur le profil du coach, cliquez sur "Réserver une séance", choisissez la date et l'heure, 
                puis confirmez votre réservation. Le coach recevra une notification et pourra accepter ou refuser.
            </p>
        </div>

        <!-- Question 4 -->
        <div style="border-bottom: 1px solid var(--border); padding: 2rem 0;">
            <h3 style="color: var(--primary); margin-bottom: 1rem;">Quels sont les modes de paiement acceptés ?</h3>
            <p style="color: var(--gray);">
                Le paiement se fait directement avec le coach lors de la séance. Vous pouvez payer en espèces, 
                par carte bancaire ou par virement selon les préférences du coach.
            </p>
        </div>

        <!-- Question 5 -->
        <div style="border-bottom: 1px solid var(--border); padding: 2rem 0;">
            <h3 style="color: var(--primary); margin-bottom: 1rem;">Puis-je annuler une séance ?</h3>
            <p style="color: var(--gray);">
                Oui, vous pouvez annuler une séance jusqu'à 24h avant l'heure prévue depuis votre tableau de bord. 
                Au-delà, contactez directement le coach.
            </p>
        </div>

        <!-- Question 6 -->
        <div style="border-bottom: 1px solid var(--border); padding: 2rem 0;">
            <h3 style="color: var(--primary); margin-bottom: 1rem;">Comment devenir coach sur SportConnect ?</h3>
            <p style="color: var(--gray);">
                Créez un compte en choisissant "Coach", remplissez votre profil avec vos diplômes, expériences et spécialités. 
                Notre équipe validera votre profil sous 48h.
            </p>
        </div>

        <!-- Question 7 -->
        <div style="border-bottom: 1px solid var(--border); padding: 2rem 0;">
            <h3 style="color: var(--primary); margin-bottom: 1rem;">Comment contacter le support ?</h3>
            <p style="color: var(--gray);">
                Utilisez notre <a href="/SportConnect/pages/contact.php" style="color: var(--primary);">formulaire de contact</a> 
                ou envoyez-nous un email à support@sportconnect.fr. Nous répondons sous 24h.
            </p>
        </div>

        <!-- Question 8 -->
        <div style="padding: 2rem 0;">
            <h3 style="color: var(--primary); margin-bottom: 1rem;">Mes données sont-elles sécurisées ?</h3>
            <p style="color: var(--gray);">
                Oui, toutes vos données sont cryptées et stockées de manière sécurisée. Nous ne partageons jamais 
                vos informations personnelles avec des tiers. Consultez nos 
                <a href="/SportConnect/pages/conditions-utilisation.php" style="color: var(--primary);">conditions d'utilisation</a>.
            </p>
        </div>

    </div>

    <div style="text-align: center; margin: 3rem 0;">
        <p style="color: var(--gray); margin-bottom: 1rem;">Vous ne trouvez pas la réponse à votre question ?</p>
        <a href="/SportConnect/pages/contact.php" class="btn btn-primary">Contactez-nous</a>
    </div>
</div>

<?php require_once __DIR__ . '/../vues/mise-en-page/pied-de-page.php'; ?>
