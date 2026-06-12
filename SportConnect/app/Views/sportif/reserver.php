<?php
/** @var array $coach */
/** @var int $coach_id */
/** @var string $error */
/** @var string $success */
/** @var string $titre */
require_once __DIR__ . '/../layout/entete.php';
?>

<div class="container" style="max-width: 800px; margin-top: 2rem;">
    <a href="<?= BASE_PATH ?>/index.php?route=coach/show&id=<?= (int)$coach['id'] ?>" style="color: var(--primary); text-decoration: none; display: inline-block; margin-bottom: 1rem;">← Retour au profil</a>

    <div class="card">
        <div class="card-header">
            <h1 class="card-title">Réserver une séance</h1>
        </div>

        <!-- Info coach -->
        <div style="background: var(--light); padding: 1.5rem; border-radius: 0.5rem; margin-bottom: 2rem;">
            <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 0.75rem;">
                <?php
                $avatarFile = $coach['photo_profil'] ?? null;
                $avatarFileSafe = is_string($avatarFile) ? basename($avatarFile) : '';
                $avatarFileOk = (!empty($avatarFileSafe) && preg_match('/^[a-zA-Z0-9._-]+$/', $avatarFileSafe));
                $avatarMainDir = __DIR__ . '/../../../telechargements/profils/';
                $avatarOldDir = __DIR__ . '/../../../old/telechargements/profils/';
                $avatarMainUrl = BASE_PATH . '/telechargements/profils/';
                $avatarOldUrl = BASE_PATH . '/old/telechargements/profils/';
                $hasAvatarMain = $avatarFileOk && $avatarFileSafe !== 'default.jpg' && file_exists($avatarMainDir . $avatarFileSafe);
                $hasAvatarOld = $avatarFileOk && $avatarFileSafe !== 'default.jpg' && !$hasAvatarMain && file_exists($avatarOldDir . $avatarFileSafe);
                $hasAvatar = $hasAvatarMain || $hasAvatarOld;
                $avatarBaseUrl = $hasAvatarMain ? $avatarMainUrl : $avatarOldUrl;
                ?>
                <?php if ($hasAvatar): ?>
                    <img src="<?= $avatarBaseUrl . rawurlencode($avatarFileSafe) ?>" alt="Photo de profil" style="width: 60px; height: 60px; border-radius: 50%; object-fit: cover; border: 1px solid rgba(255,255,255,0.18); box-shadow: 0 12px 30px rgba(0,0,0,0.35);">
                <?php else: ?>
                    <div style="width: 60px; height: 60px; border-radius: 50%; background: rgba(255,255,255,0.08); display: flex; align-items: center; justify-content: center; font-weight: 900; color: rgba(255,255,255,0.92); font-size: 1.25rem; border: 1px solid rgba(255,255,255,0.14); box-shadow: 0 12px 30px rgba(0,0,0,0.35);">
                        <?= strtoupper(substr($coach['prenom'] ?? 'C', 0, 1)) ?>
                    </div>
                <?php endif; ?>

                <div>
                    <h3 style="margin-bottom: 0.25rem;"><?= htmlspecialchars(($coach['prenom'] ?? '') . ' ' . ($coach['nom'] ?? '')) ?></h3>
                    <div style="color: var(--primary); font-weight: 600;">
                        <?= htmlspecialchars((string)($coach['specialite'] ?? '')) ?>
                    </div>
                </div>
            </div>

            <div style="color: var(--gray);">
                📍 <?= htmlspecialchars((string)($coach['localisation'] ?? '')) ?>
            </div>
            <div style="font-size: 1.5rem; font-weight: 700; color: var(--primary); margin-top: 1rem;">
                <?= number_format((float)($coach['tarif_horaire'] ?? 0), 0) ?>€/heure
            </div>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="POST" action="<?= BASE_PATH ?>/index.php?route=sportif/reserver&coach_id=<?= (int)$coach_id ?>">
            <div class="form-group">
                <label class="form-label">Date de la séance *</label>
                <input type="date" name="date_seance" class="form-control" required min="<?= date('Y-m-d') ?>">
            </div>

            <div class="form-group">
                <label class="form-label">Niveau souhaité *</label>
                <select name="niveau_souhaitez" class="form-control" required>
                    <option value="">Sélectionner...</option>
                    <option value="debutant">Débutant</option>
                    <option value="intermediaire">Intermédiaire</option>
                    <option value="avance">Avancé</option>
                </select>
            </div>

            <div class="grid grid-2">
                <div class="form-group">
                    <label class="form-label">Heure de début *</label>
                    <input type="time" name="heure_debut" class="form-control" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Heure de fin *</label>
                    <input type="time" name="heure_fin" class="form-control" required>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Notes / Objectifs de la séance</label>
                <textarea name="notes" class="form-control" rows="4" placeholder="Décrivez vos objectifs pour cette séance..."></textarea>
            </div>

            <div style="background: #eff6ff; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem;">
                <strong style="color: var(--primary);">ℹ️ Informations importantes :</strong>
                <ul style="margin-top: 0.5rem; margin-left: 1.5rem; color: var(--gray);">
                    <li>Votre demande sera envoyée au coach pour validation</li>
                    <li>Vous recevrez une notification une fois la séance confirmée</li>
                    <li>Annulation gratuite jusqu'à 24h avant la séance</li>
                </ul>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%;">Envoyer la demande de réservation</button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../layout/pied-de-page.php'; ?>

