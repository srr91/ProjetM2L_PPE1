<?php
/** @var array $seances */
/** @var string $statut_filter */
/** @var string $titre */
require_once __DIR__ . '/../layout/entete.php';
?>

<div class="container" style="margin-top: 2rem;">
    <?php if (!empty($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger" style="margin-bottom: 1rem;">
            <?= htmlspecialchars($_SESSION['flash_error']) ?>
        </div>
        <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['flash_success'])): ?>
        <div class="alert alert-success" style="margin-bottom: 1rem;">
            <?= htmlspecialchars($_SESSION['flash_success']) ?>
        </div>
        <?php unset($_SESSION['flash_success']); ?>
    <?php endif; ?>

    <h1 style="margin-bottom: 2rem;">Mes séances</h1>

    <!-- Filtres -->
    <div class="card" style="margin-bottom: 2rem;">
        <form method="GET" action="<?= BASE_PATH ?>/index.php" class="search-bar">
            <input type="hidden" name="route" value="coach/seances">
            <select name="statut" class="form-control">
                <option value="">Tous les statuts</option>
                <option value="en_attente" <?= $statut_filter === 'en_attente' ? 'selected' : '' ?>>En attente</option>
                <option value="confirmée" <?= $statut_filter === 'confirmée' ? 'selected' : '' ?>>Confirmées</option>
                <option value="terminée" <?= $statut_filter === 'terminée' ? 'selected' : '' ?>>Terminées</option>
                <option value="annulée" <?= $statut_filter === 'annulée' ? 'selected' : '' ?>>Annulées</option>
            </select>

            <button type="submit" class="btn btn-primary">Filtrer</button>
            <?php if ($statut_filter): ?>
                <a href="<?= BASE_PATH ?>/index.php?route=coach/seances" class="btn btn-outline">Réinitialiser</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Liste des séances -->
    <div class="card">
        <p style="color: var(--gray); margin-bottom: 1rem;">
            <?= count($seances) ?> séance<?= count($seances) > 1 ? 's' : '' ?> trouvée<?= count($seances) > 1 ? 's' : '' ?>
        </p>

        <?php if (empty($seances)): ?>
            <div style="text-align: center; padding: 3rem; color: var(--gray);">
                <div style="font-size: 3rem; margin-bottom: 1rem;">📅</div>
                <p>Aucune séance trouvée</p>
            </div>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Heure</th>
                        <th>Niveau</th>
                        <th>Sportif</th>
                        <th>Contact</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($seances as $seance): ?>
                        <tr>
                            <td><?= date('d/m/Y', strtotime((string)$seance['date_seance'])) ?></td>
                            <td><?= date('H:i', strtotime((string)$seance['heure_debut'])) ?> - <?= date('H:i', strtotime((string)$seance['heure_fin'])) ?></td>
                            <td><?= htmlspecialchars((string)($seance['niveau_souhaitez'] ?? '-')) ?></td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 0.75rem;">
                                    <?php
                                    $avatarFile = $seance['photo_profil'] ?? null;
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
                                        <img src="<?= $avatarBaseUrl . rawurlencode($avatarFileSafe) ?>" alt="" style="width: 34px; height: 34px; border-radius: 50%; object-fit: cover; border: 1px solid rgba(255,255,255,0.18);">
                                    <?php else: ?>
                                        <div style="width: 34px; height: 34px; border-radius: 50%; background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.12); display: flex; align-items: center; justify-content: center; font-weight: 900; color: rgba(255,255,255,0.92);">
                                            <?= strtoupper(substr($seance['prenom'] ?? 'S', 0, 1)) ?>
                                        </div>
                                    <?php endif; ?>
                                    <span><?= htmlspecialchars(($seance['prenom'] ?? '') . ' ' . ($seance['nom'] ?? '')) ?></span>
                                </div>
                            </td>
                            <td><?= htmlspecialchars((string)($seance['telephone'] ?? 'N/A')) ?></td>
                            <td>
                                <?php if (($seance['statut'] ?? '') === 'confirmée'): ?>
                                    <span class="badge badge-success">Confirmée</span>
                                <?php elseif (($seance['statut'] ?? '') === 'en_attente'): ?>
                                    <span class="badge badge-warning">En attente</span>
                                <?php elseif (($seance['statut'] ?? '') === 'terminée'): ?>
                                    <span class="badge badge-info">Terminée</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">Annulée</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (($seance['statut'] ?? '') === 'confirmée' && strtotime((string)$seance['date_seance']) < strtotime('today')): ?>
                                    <form method="POST" action="<?= BASE_PATH ?>/index.php?route=coach/seances<?= $statut_filter ? '&statut=' . urlencode($statut_filter) : '' ?>" style="display: inline;">
                                        <input type="hidden" name="seance_id" value="<?= (int)$seance['id'] ?>">
                                        <button type="submit" name="terminer" class="btn btn-sm btn-secondary">Marquer terminée</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../layout/pied-de-page.php'; ?>

