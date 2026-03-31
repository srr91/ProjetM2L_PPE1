<?php
/** @var array $coachs */
/** @var array $specialites */
/** @var string $specialite */
/** @var string $localisation */
/** @var string $tarif_max */
/** @var string $titre */
require_once __DIR__ . '/../../layout/entete.php';
?>

<div class="coachs-section">
    <div class="container">
        <div class="coachs-header">
            <h1>Trouvez votre coach sportif</h1>
            <p><?= count($coachs) ?> coach<?= count($coachs) > 1 ? 's' : '' ?> disponible<?= count($coachs) > 1 ? 's' : '' ?></p>
        </div>

        <!-- Filtres -->
        <div class="card" style="margin-bottom: 2rem;">
            <form method="GET" action="<?= BASE_PATH ?>/index.php" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; align-items: end;">
                <input type="hidden" name="route" value="coachs">
                <div>
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Spécialité</label>
                    <select name="specialite" class="form-control">
                        <option value="">Toutes les spécialités</option>
                        <?php foreach ($specialites as $spec): ?>
                            <option value="<?= htmlspecialchars($spec) ?>" <?= $specialite === $spec ? 'selected' : '' ?>>
                                <?= htmlspecialchars($spec) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Localisation</label>
                    <input type="text" name="localisation" class="form-control" placeholder="Ville..." value="<?= htmlspecialchars($localisation) ?>">
                </div>

                <div>
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Tarif maximum (€/h)</label>
                    <input type="number" name="tarif_max" class="form-control" placeholder="Ex: 50" value="<?= htmlspecialchars($tarif_max) ?>">
                </div>

                <div style="display: flex; gap: 0.5rem;">
                    <button type="submit" class="btn btn-primary" style="flex: 1;">Rechercher</button>
                    <a href="<?= BASE_PATH ?>/index.php?route=coachs" class="btn btn-outline">Réinitialiser</a>
                </div>
            </form>
        </div>

        <!-- Liste des coachs -->
        <?php if (empty($coachs)): ?>
            <div class="card" style="text-align: center; padding: 3rem;">
                <p style="color: var(--gray); font-size: 1.2rem;">Aucun coach ne correspond à vos critères de recherche.</p>
                <a href="<?= BASE_PATH ?>/index.php?route=coachs" class="btn btn-primary" style="margin-top: 1rem;">Voir tous les coachs</a>
            </div>
        <?php else: ?>
            <div class="coachs-grid">
                <?php foreach ($coachs as $coach): ?>
                    <div class="coach-card">
                        <!-- Photo du coach -->
                        <div class="coach-photo" data-initial="<?= strtoupper(substr($coach['prenom'] ?? 'C', 0, 1)) ?>">
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
                                <img src="<?= $avatarBaseUrl . rawurlencode($avatarFileSafe) ?>" alt="Photo de profil" class="coach-avatar">
                            <?php else: ?>
                                <div class="coach-avatar-placeholder">
                                    <?= strtoupper(substr($coach['prenom'] ?? 'C', 0, 1)) ?>
                                </div>
                            <?php endif; ?>
                            <div class="coach-name">
                                <span class="coach-firstname"><?= htmlspecialchars($coach['prenom']) ?></span>
                                <span class="coach-lastname"><?= htmlspecialchars($coach['nom']) ?></span>
                            </div>
                        </div>
                        
                        <!-- Contenu -->
                        <div class="coach-content">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                                <div class="badge">
                                    <?= htmlspecialchars($coach['specialite']) ?>
                                </div>
                                <div class="coach-rating">
                                    <span class="stars">★</span>
                                    <span style="font-weight: 700;"><?= number_format($coach['moyenne_note'], 1) ?></span>
                                    <span>(<?= $coach['nb_avis'] ?>)</span>
                                </div>
                            </div>

                            <?php if (($coach['specialite'] ?? '') === 'À compléter' || ($coach['description'] ?? '') === 'Profil en cours de complétion.' || ($coach['localisation'] ?? '') === 'À préciser'): ?>
                                <div style="display: inline-block; background: rgba(255, 215, 0, 0.15); color: var(--dark); padding: 0.3rem 0.8rem; border: 1px solid var(--primary); font-size: 0.8rem; font-weight: 700; margin-bottom: 0.75rem;">
                                    Profil en cours de complétion
                                </div>
                            <?php endif; ?>
                            
                            <div class="coach-description">
                                <?= htmlspecialchars($coach['description']) ?>
                            </div>
                            
                            <div class="coach-meta">
                                <div>📍 <?= htmlspecialchars($coach['localisation']) ?></div>
                                <div>💼 <?= htmlspecialchars((string)($coach['experience'] ?? '-')) ?> ans</div>
                            </div>
                            
                            <div class="card-footer">
                                <div>
                                    <div class="tarif-label">Tarif</div>
                                    <div class="tarif-prix">
                                        <?= number_format($coach['tarif_horaire'], 0) ?>€<span style="font-size: 0.9rem; font-weight: 400;">/h</span>
                                    </div>
                                </div>
                                <a href="<?= BASE_PATH ?>/index.php?route=coach/show&id=<?= (int)$coach['id'] ?>" class="btn-profil">
                                    Voir le profil
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../../layout/pied-de-page.php'; ?>

