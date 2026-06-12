<?php
/** @var array $coachs */
/** @var string $titre */
require_once __DIR__ . '/../layout/entete.php';
?>

<!-- Hero Section avec design noir/gris/jaune -->
<section style="position: relative; height: 90vh; min-height: 600px; overflow: hidden; background: linear-gradient(135deg, #1E1E1E 0%, #2C2C2C 100%);">
    <!-- Image de fond -->
    <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: url('https://images.unsplash.com/photo-1571019614242-c5c5dee9f50b?w=1920&q=60&fm=webp') center/cover; opacity: 0.15; will-change: transform;"></div>
    
    <!-- Overlay gradient noir/gris -->
    <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: linear-gradient(135deg, rgba(30, 30, 30, 0.92) 0%, rgba(44, 44, 44, 0.88) 100%);"></div>
    
    <!-- Contenu -->
    <div class="container" style="position: relative; height: 100%; display: flex; align-items: center;">
        <div style="max-width: 700px;">
            <h1 style="font-size: 4rem; font-weight: 900; line-height: 1.1; margin-bottom: 1.5rem; color: white; text-transform: uppercase; letter-spacing: 2px;">
                ATTEIGNEZ VOS <span style="color: #FFD700;">OBJECTIFS</span> SPORTIFS
            </h1>
            <p style="font-size: 1.4rem; margin-bottom: 2.5rem; color: #E8E8E8; line-height: 1.6;">
                Connectez-vous avec les meilleurs coachs sportifs professionnels près de chez vous
            </p>
            <div style="display: flex; gap: 1.5rem; flex-wrap: wrap;">
                <a href="<?= BASE_PATH ?>/index.php?route=coachs" class="btn btn-primary" style="padding: 1.2rem 2.5rem; font-size: 1.1rem; transition: transform 0.2s ease; will-change: transform;">
                    Trouver un coach
                </a>
                <a href="<?= BASE_PATH ?>/index.php?route=auth/register&role=coach" class="btn btn-outline" style="padding: 1.2rem 2.5rem; font-size: 1.1rem; transition: transform 0.2s ease; will-change: transform;">
                    Devenir coach
                </a>
            </div>
        </div>
    </div>
    
    <!-- Scroll indicator -->
    <div style="position: absolute; bottom: 30px; left: 50%; transform: translateX(-50%); color: #FFD700; text-align: center;">
        <div style="font-size: 2rem;">↓</div>
        <div style="font-size: 0.9rem; margin-top: 0.5rem; color: #E8E8E8;">Découvrir</div>
    </div>
</section>

<!-- Section Pourquoi nous choisir -->
<section class="why-section">
    <div class="container">
        <div class="why-section-header">
            <h2 class="why-section-title">Pourquoi choisir <span>SportConnect</span> ?</h2>
            <p class="why-section-subtitle">
                La plateforme de référence pour trouver votre coach sportif idéal
            </p>
        </div>
        
        <div class="why-grid">
            <div class="feature-card">
                <div class="icon-wrapper">🎯</div>
                <h3>Coachs qualifiés</h3>
                <p>
                    Tous nos coachs sont vérifiés et diplômés pour vous garantir un accompagnement de qualité
                </p>
            </div>
            
            <div class="feature-card">
                <div class="icon-wrapper">📍</div>
                <h3>Proche de vous</h3>
                <p>
                    Trouvez facilement un coach dans votre ville grâce à notre système de recherche avancé
                </p>
            </div>
            
            <div class="feature-card">
                <div class="icon-wrapper">⚡</div>
                <h3>Réservation simple</h3>
                <p>
                    Réservez vos séances en quelques clics et suivez votre progression en temps réel
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Section Nos coachs populaires -->
<section style="background: #f8f9fa; padding: 6rem 0;">
    <div class="container">
        <div style="text-align: center; margin-bottom: 4rem;">
            <h2 style="font-size: 2.5rem; font-weight: 700; margin-bottom: 1rem;">Nos coachs populaires</h2>
            <p style="font-size: 1.2rem; color: var(--gray);">
                Découvrez les coachs les mieux notés de notre plateforme
            </p>
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 2rem;">
            <?php foreach ($coachs as $coach): ?>
                <div class="card coach-card-hover" style="overflow: hidden; cursor: pointer;">
                    
                    <!-- Image du coach -->
                    <div style="height: 250px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); position: relative; overflow: hidden;">
                        <div style="position: absolute; bottom: 20px; left: 20px; color: white;">
                            <div style="font-size: 2rem; font-weight: 700;"><?= htmlspecialchars($coach['prenom']) ?></div>
                            <div style="font-size: 1.2rem; opacity: 0.9;"><?= htmlspecialchars($coach['nom']) ?></div>
                        </div>
                    </div>
                    
                    <!-- Contenu -->
                    <div style="padding: 1.5rem;">
                        <div style="display: inline-block; background: #667eea; color: white; padding: 0.4rem 1rem; border-radius: 20px; font-size: 0.9rem; font-weight: 600; margin-bottom: 1rem;">
                            <?= htmlspecialchars($coach['specialite']) ?>
                        </div>
                        
                        <p style="color: var(--gray); margin-bottom: 1.5rem; line-height: 1.6; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
                            <?= htmlspecialchars($coach['description']) ?>
                        </p>
                        
                        <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 1rem; border-top: 1px solid var(--border);">
                            <div>
                                <div style="color: var(--gray); font-size: 0.9rem;">Tarif</div>
                                <div style="font-size: 1.5rem; font-weight: 700; color: var(--primary);">
                                    <?= number_format($coach['tarif_horaire'], 0) ?>€<span style="font-size: 1rem; font-weight: 400;">/h</span>
                                </div>
                            </div>
                            <a href="<?= BASE_PATH ?>/index.php?route=coach/show&id=<?= (int)$coach['id'] ?>" class="btn btn-primary" style="padding: 0.6rem 1.5rem;">
                                Voir le profil
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div style="text-align: center; margin-top: 3rem;">
            <a href="<?= BASE_PATH ?>/index.php?route=coachs" class="btn btn-outline" style="padding: 1rem 2.5rem; font-size: 1.1rem;">
                Voir tous les coachs
            </a>
        </div>
    </div>
</section>

<!-- Section CTA -->
<section style="background: linear-gradient(135deg, #1E1E1E 0%, #2C2C2C 100%); padding: 6rem 0; color: white; text-align: center; position: relative; overflow: hidden;">
    <!-- Effet de fond -->
    <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: repeating-linear-gradient(45deg, transparent, transparent 10px, rgba(255, 215, 0, 0.03) 10px, rgba(255, 215, 0, 0.03) 20px);"></div>
    
    <div class="container" style="position: relative; z-index: 1;">
        <h2 style="font-size: 3rem; font-weight: 900; margin-bottom: 1.5rem; color: white; text-transform: uppercase; letter-spacing: 2px;">
            Prêt à commencer votre <span style="color: #FFD700;">transformation</span> ?
        </h2>
        <p style="font-size: 1.3rem; margin-bottom: 2.5rem; color: #E8E8E8;">
            Rejoignez des milliers de sportifs qui ont déjà trouvé leur coach idéal
        </p>
        <a href="<?= BASE_PATH ?>/index.php?route=auth/register" class="btn btn-primary" style="padding: 1.2rem 3rem; font-size: 1.2rem;">
            Commencer maintenant
        </a>
    </div>
</section>

<style>
@keyframes bounce {
    0%, 20%, 50%, 80%, 100% {
        transform: translateX(-50%) translateY(0);
    }
    40% {
        transform: translateX(-50%) translateY(-10px);
    }
    60% {
        transform: translateX(-50%) translateY(-5px);
    }
}

/* Optimisation hover cartes coachs */
.coach-card-hover {
    transition: transform 0.1s cubic-bezier(0.25, 0.46, 0.45, 0.94), box-shadow 0.1s ease;
    will-change: transform;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    transform: translateZ(0); /* Force GPU */
}

.coach-card-hover:hover {
    transform: translateY(-4px) translateZ(0);
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
}

/* Optimisation GPU sur l'élément animé */
div[style*="animation: bounce"] {
    will-change: transform;
    backface-visibility: hidden;
}
</style>

<?php require_once __DIR__ . '/../layout/pied-de-page.php'; ?>

