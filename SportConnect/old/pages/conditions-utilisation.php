<?php
/**
 * Page Conditions d'utilisation
 */
require_once __DIR__ . '/../configuration/session.php';
$titre = 'Conditions d\'utilisation - SportConnect';
require_once __DIR__ . '/../vues/mise-en-page/entete.php';
?>

<div class="container" style="margin-top: 3rem;">
    <h1 style="text-align: center; margin-bottom: 1rem;">📜 Conditions d'utilisation</h1>
    <p style="text-align: center; color: var(--gray); margin-bottom: 3rem;">
        Dernière mise à jour : 14 octobre 2025
    </p>

    <div class="card" style="max-width: 900px; margin: 0 auto;">
        
        <!-- Section 1 -->
        <div style="margin-bottom: 3rem;">
            <h2 style="color: var(--primary); margin-bottom: 1rem;">1. Acceptation des conditions</h2>
            <p style="color: var(--gray); line-height: 1.8;">
                En accédant et en utilisant SportConnect, vous acceptez d'être lié par les présentes conditions d'utilisation. 
                Si vous n'acceptez pas ces conditions, veuillez ne pas utiliser notre plateforme.
            </p>
        </div>

        <!-- Section 2 -->
        <div style="margin-bottom: 3rem;">
            <h2 style="color: var(--primary); margin-bottom: 1rem;">2. Description du service</h2>
            <p style="color: var(--gray); line-height: 1.8;">
                SportConnect est une plateforme de mise en relation entre sportifs et coachs sportifs. 
                Nous facilitons la recherche, la réservation et le suivi de séances de coaching sportif.
            </p>
        </div>

        <!-- Section 3 -->
        <div style="margin-bottom: 3rem;">
            <h2 style="color: var(--primary); margin-bottom: 1rem;">3. Inscription et compte utilisateur</h2>
            <p style="color: var(--gray); line-height: 1.8; margin-bottom: 1rem;">
                Pour utiliser certaines fonctionnalités de SportConnect, vous devez créer un compte. Vous vous engagez à :
            </p>
            <ul style="color: var(--gray); line-height: 1.8;">
                <li>Fournir des informations exactes et à jour</li>
                <li>Maintenir la sécurité de votre mot de passe</li>
                <li>Ne pas partager votre compte avec d'autres personnes</li>
                <li>Nous informer immédiatement de toute utilisation non autorisée</li>
            </ul>
        </div>

        <!-- Section 4 -->
        <div style="margin-bottom: 3rem;">
            <h2 style="color: var(--primary); margin-bottom: 1rem;">4. Responsabilités des utilisateurs</h2>
            <p style="color: var(--gray); line-height: 1.8; margin-bottom: 1rem;">
                <strong>Pour les sportifs :</strong>
            </p>
            <ul style="color: var(--gray); line-height: 1.8; margin-bottom: 1rem;">
                <li>Respecter les horaires de réservation</li>
                <li>Annuler au moins 24h à l'avance en cas d'empêchement</li>
                <li>Payer les séances selon les modalités convenues</li>
            </ul>
            <p style="color: var(--gray); line-height: 1.8; margin-bottom: 1rem;">
                <strong>Pour les coachs :</strong>
            </p>
            <ul style="color: var(--gray); line-height: 1.8;">
                <li>Fournir des informations exactes sur vos qualifications</li>
                <li>Respecter les horaires de séances réservées</li>
                <li>Maintenir un comportement professionnel</li>
                <li>Disposer des assurances nécessaires</li>
            </ul>
        </div>

        <!-- Section 5 -->
        <div style="margin-bottom: 3rem;">
            <h2 style="color: var(--primary); margin-bottom: 1rem;">5. Paiements et remboursements</h2>
            <p style="color: var(--gray); line-height: 1.8;">
                Les paiements se font directement entre le sportif et le coach. SportConnect n'est pas responsable 
                des transactions financières. En cas de litige, contactez notre support.
            </p>
        </div>

        <!-- Section 6 -->
        <div style="margin-bottom: 3rem;">
            <h2 style="color: var(--primary); margin-bottom: 1rem;">6. Protection des données personnelles</h2>
            <p style="color: var(--gray); line-height: 1.8;">
                Nous nous engageons à protéger vos données personnelles conformément au RGPD. 
                Vos données sont utilisées uniquement pour le fonctionnement de la plateforme et ne sont jamais 
                vendues à des tiers. Vous pouvez demander l'accès, la modification ou la suppression de vos données 
                à tout moment.
            </p>
        </div>

        <!-- Section 7 -->
        <div style="margin-bottom: 3rem;">
            <h2 style="color: var(--primary); margin-bottom: 1rem;">7. Propriété intellectuelle</h2>
            <p style="color: var(--gray); line-height: 1.8;">
                Tous les contenus présents sur SportConnect (textes, images, logos, design) sont protégés par 
                le droit d'auteur et appartiennent à SportConnect ou à ses partenaires. Toute reproduction 
                non autorisée est interdite.
            </p>
        </div>

        <!-- Section 8 -->
        <div style="margin-bottom: 3rem;">
            <h2 style="color: var(--primary); margin-bottom: 1rem;">8. Limitation de responsabilité</h2>
            <p style="color: var(--gray); line-height: 1.8;">
                SportConnect agit uniquement comme intermédiaire. Nous ne sommes pas responsables :
            </p>
            <ul style="color: var(--gray); line-height: 1.8;">
                <li>De la qualité des séances de coaching</li>
                <li>Des blessures ou accidents survenant pendant les séances</li>
                <li>Des litiges entre sportifs et coachs</li>
                <li>Des pertes de données dues à des problèmes techniques</li>
            </ul>
        </div>

        <!-- Section 9 -->
        <div style="margin-bottom: 3rem;">
            <h2 style="color: var(--primary); margin-bottom: 1rem;">9. Résiliation</h2>
            <p style="color: var(--gray); line-height: 1.8;">
                Vous pouvez supprimer votre compte à tout moment depuis votre profil. 
                Nous nous réservons le droit de suspendre ou supprimer votre compte en cas de violation 
                des présentes conditions.
            </p>
        </div>

        <!-- Section 10 -->
        <div style="margin-bottom: 3rem;">
            <h2 style="color: var(--primary); margin-bottom: 1rem;">10. Modifications des conditions</h2>
            <p style="color: var(--gray); line-height: 1.8;">
                Nous nous réservons le droit de modifier ces conditions à tout moment. 
                Les modifications seront publiées sur cette page avec la date de mise à jour. 
                Votre utilisation continue de la plateforme après les modifications constitue votre acceptation.
            </p>
        </div>

        <!-- Section 11 -->
        <div>
            <h2 style="color: var(--primary); margin-bottom: 1rem;">11. Contact</h2>
            <p style="color: var(--gray); line-height: 1.8;">
                Pour toute question concernant ces conditions d'utilisation, contactez-nous à :
                <a href="mailto:legal@sportconnect.fr" style="color: var(--primary);">legal@sportconnect.fr</a>
            </p>
        </div>

    </div>

    <div style="text-align: center; margin: 3rem 0;">
        <a href="/SportConnect/index.php" class="btn btn-secondary">Retour à l'accueil</a>
    </div>
</div>

<?php require_once __DIR__ . '/../vues/mise-en-page/pied-de-page.php'; ?>
