<?php
/** @var array $coach */
/** @var array $avis */
/** @var string $titre */
require_once __DIR__ . '/../../layout/entete.php';
?>

<div class="container" style="margin-top: 2rem;">
    <a href="<?= BASE_PATH ?>/index.php?route=coachs" style="color: var(--primary); text-decoration: none; display: inline-block; margin-bottom: 1rem;">← Retour à la liste</a>

    <?php
    $bannerFile = $coach['banniere_profil'] ?? null;
    $bannerFileSafe = is_string($bannerFile) ? basename($bannerFile) : '';
    $bannerFileOk = (!empty($bannerFileSafe) && preg_match('/^[a-zA-Z0-9._-]+$/', $bannerFileSafe));
    $bannerMainDir = __DIR__ . '/../../../../telechargements/banners/';
    $bannerOldDir = __DIR__ . '/../../../../old/telechargements/banners/';
    $bannerMainUrl = BASE_PATH . '/telechargements/banners/';
    $bannerOldUrl = BASE_PATH . '/old/telechargements/banners/';

    $hasBannerMain = $bannerFileOk && file_exists($bannerMainDir . $bannerFileSafe);
    $hasBannerOld = $bannerFileOk && !$hasBannerMain && file_exists($bannerOldDir . $bannerFileSafe);
    $hasBanner = $hasBannerMain || $hasBannerOld;
    $bannerClass = $hasBanner ? 'sc-profile-banner--image' : 'sc-profile-banner--g-default';
    $bannerStyle = '';
    if ($hasBanner) {
        $bannerBaseUrl = $hasBannerMain ? $bannerMainUrl : $bannerOldUrl;
        $bannerStyle = "--sc-banner-url: url(" . $bannerBaseUrl . rawurlencode($bannerFileSafe) . ");";
    }
    ?>

    <div class="sc-profile-banner <?= $bannerClass ?>" style="<?= $bannerStyle ?> margin-bottom: 2rem;">
        <div class="sc-profile-banner__avatar">
            <?php
            $avatarFile = $coach['photo_profil'] ?? null;
            $avatarFileSafe = is_string($avatarFile) ? basename($avatarFile) : '';
            $avatarFileOk = (!empty($avatarFileSafe) && preg_match('/^[a-zA-Z0-9._-]+$/', $avatarFileSafe));
            $avatarMainDir = __DIR__ . '/../../../../telechargements/profils/';
            $avatarOldDir = __DIR__ . '/../../../../old/telechargements/profils/';
            $avatarMainUrl = BASE_PATH . '/telechargements/profils/';
            $avatarOldUrl = BASE_PATH . '/old/telechargements/profils/';

            $hasAvatarMain = $avatarFileOk && $avatarFileSafe !== 'default.jpg' && file_exists($avatarMainDir . $avatarFileSafe);
            $hasAvatarOld = $avatarFileOk && $avatarFileSafe !== 'default.jpg' && !$hasAvatarMain && file_exists($avatarOldDir . $avatarFileSafe);
            $hasAvatar = $hasAvatarMain || $hasAvatarOld;
            $avatarBaseUrl = $hasAvatarMain ? $avatarMainUrl : $avatarOldUrl;
            ?>
            <?php if ($hasAvatar): ?>
                <img src="<?= $avatarBaseUrl . rawurlencode($avatarFileSafe) ?>" alt="">
            <?php else: ?>
                <?= strtoupper(substr($coach['prenom'] ?? 'C', 0, 1)) ?>
            <?php endif; ?>
        </div>

        <div class="sc-profile-banner__content">
            <div class="sc-profile-banner__name"><?= htmlspecialchars(($coach['prenom'] ?? '') . ' ' . ($coach['nom'] ?? '')) ?></div>
            <div class="sc-profile-banner__subtitle"><?= htmlspecialchars($coach['specialite'] ?? '') ?></div>
        </div>
    </div>

    <div class="grid" style="grid-template-columns: 2fr 1fr; gap: 2rem;">
        <!-- Profil principal -->
        <div>
            <div class="card">
                <div style="display: flex; gap: 2rem; flex-wrap: wrap; align-items: center; justify-content: space-between;">
                    <div class="coach-rating">
                        <span class="stars">★</span>
                        <span style="font-size: 1.5rem; font-weight: 900;">
                            <?= number_format((float)($coach['moyenne_note'] ?? 0), 1) ?>
                        </span>
                        <span style="color: var(--gray);">(<?= (int)($coach['nb_avis'] ?? 0) ?> avis)</span>
                    </div>
                    <div style="color: var(--gray); font-weight: 700;">
                        <strong><?= (int)($coach['nb_seances'] ?? 0) ?></strong> séances réalisées
                    </div>
                    <div style="color: var(--gray);">
                        <div>📍 <?= htmlspecialchars((string)($coach['localisation'] ?? '')) ?></div>
                        <div>💼 <?= (int)($coach['annees_experience'] ?? 0) ?> ans d'expérience</div>
                    </div>
                </div>
            </div>

            <!-- Description -->
            <div class="card" style="margin-top: 2rem;">
                <h2 style="margin-bottom: 1rem;">À propos</h2>
                <p style="line-height: 1.8; color: var(--gray);">
                    <?= nl2br(htmlspecialchars((string)($coach['description'] ?? ''))) ?>
                </p>
            </div>

            <?php if (!empty($coach['diplomes'])): ?>
            <div class="card" style="margin-top: 2rem;">
                <h2 style="margin-bottom: 1rem;">Diplômes et certifications</h2>
                <p style="line-height: 1.8; color: var(--gray);">
                    <?= nl2br(htmlspecialchars((string)$coach['diplomes'])) ?>
                </p>
            </div>
            <?php endif; ?>

            <!-- Avis -->
            <div class="card" style="margin-top: 2rem;">
                <h2 style="margin-bottom: 1.5rem;">Avis des sportifs (<?= (int)($coach['nb_avis'] ?? 0) ?>)</h2>
                
                <?php if (empty($avis)): ?>
                    <p style="color: var(--gray); text-align: center; padding: 2rem;">Aucun avis pour le moment</p>
                <?php else: ?>
                    <?php foreach ($avis as $index => $avisItem): ?>
                        <div style="padding: 1.5rem 0; <?= $index > 0 ? 'border-top: 1px solid var(--border);' : '' ?>">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                <strong><?= htmlspecialchars(($avisItem['prenom'] ?? '') . ' ' . substr((string)($avisItem['nom'] ?? ''), 0, 1)) ?>.</strong>
                                <div class="stars" style="color: var(--warning);">
                                    <?=
                                        str_repeat('★', (int)($avisItem['note'] ?? 0)) .
                                        str_repeat('☆', 5 - (int)($avisItem['note'] ?? 0))
                                    ?>
                                </div>
                            </div>
                            <p style="color: var(--gray); margin-bottom: 0.5rem;">
                                <?= htmlspecialchars((string)($avisItem['commentaire'] ?? '')) ?>
                            </p>
                            <small style="color: var(--gray);">
                                <?= !empty($avisItem['date_avis']) ? date('d/m/Y', strtotime((string)$avisItem['date_avis'])) : '' ?>
                            </small>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Sidebar réservation -->
        <div>
            <div class="card" style="position: sticky; top: 100px;">
                <div style="text-align: center; margin-bottom: 1.5rem;">
                    <div style="font-size: 3rem; font-weight: 700; color: var(--primary);">
                        <?= number_format((float)($coach['tarif_horaire'] ?? 0), 0) ?>€
                        <span style="font-size: 1rem; color: var(--gray);">/heure</span>
                    </div>
                </div>

                <?php if (\App\Core\Auth::isLoggedIn() && \App\Core\Auth::hasRole('sportif')): ?>
                    <a href="<?= BASE_PATH ?>/index.php?route=sportif/reserver&coach_id=<?= (int)$coach['id'] ?>" class="btn btn-primary" style="width: 100%; margin-bottom: 1rem;">
                        Réserver une séance
                    </a>
                    <a href="<?= BASE_PATH ?>/old/espace-sportif/messages.php?coach_id=<?= (int)$coach['id'] ?>" class="btn btn-outline" style="width: 100%;">
                        Contacter le coach
                    </a>
                <?php else: ?>
                    <a href="<?= BASE_PATH ?>/index.php?route=auth/login" class="btn btn-primary" style="width: 100%; margin-bottom: 1rem;">
                        Connectez-vous pour réserver
                    </a>
                    <p style="text-align: center; color: var(--gray); font-size: 0.875rem;">
                        Créez un compte sportif pour réserver une séance
                    </p>
                <?php endif; ?>

                <div style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid var(--border);">
                    <h3 style="margin-bottom: 1rem; font-size: 1rem;">Informations pratiques</h3>
                    <ul style="list-style: none; color: var(--gray); font-size: 0.875rem;">
                        <li style="margin-bottom: 0.5rem;">✓ Réponse rapide</li>
                        <li style="margin-bottom: 0.5rem;">✓ Annulation gratuite 24h avant</li>
                        <li style="margin-bottom: 0.5rem;">✓ Paiement sécurisé</li>
                        <li style="margin-bottom: 0.5rem;">✓ Satisfaction garantie</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../layout/pied-de-page.php'; ?>
