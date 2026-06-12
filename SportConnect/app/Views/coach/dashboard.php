<?php
/** @var array $demandes */
/** @var array $seances_confirmees */
/** @var array $stats */
/** @var string $chiffre_affaires_affiche */
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

    <h1 style="margin-bottom: 2rem;">Bonjour Coach <?= htmlspecialchars($_SESSION['prenom'] ?? '') ?> 👋</h1>

    <!-- Statistiques -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value"><?= (int)($stats['nb_clients'] ?? 0) ?></div>
            <div class="stat-label">Clients</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= (int)($stats['nb_seances'] ?? 0) ?></div>
            <div class="stat-label">Séances Ce mois ci</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= number_format((float)($stats['moyenne_note'] ?? 0), 1) ?> ★</div>
            <div class="stat-label">Note moyenne</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= (int)($stats['nb_avis'] ?? 0) ?></div>
            <div class="stat-label">Avis reçus</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= htmlspecialchars($chiffre_affaires_affiche) ?>€</div>
            <div class="stat-label">Chiffre d'affaires (terminées)</div>
        </div>
    </div>

    <!-- Demandes en attente -->
    <?php if (!empty($demandes)): ?>
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Demandes de réservation (<?= count($demandes) ?>)</h2>
        </div>

        <table class="table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Heure</th>
                    <th>Sportif</th>
                    <th>Contact</th>
                    <th>Notes</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($demandes as $demande): ?>
                    <tr>
                        <td><?= date('d/m/Y', strtotime((string)$demande['date_seance'])) ?></td>
                        <td><?= date('H:i', strtotime((string)$demande['heure_debut'])) ?> - <?= date('H:i', strtotime((string)$demande['heure_fin'])) ?></td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                <?php
                                $avatarFile = $demande['photo_profil'] ?? null;
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
                                    <img src="<?= $avatarBaseUrl . rawurlencode($avatarFileSafe) ?>" alt="" style="width: 34px; height: 34px; border-radius: 50%; object-fit: cover; border: 1px solid rgba(255, 255, 255, 0.18);">
                                <?php else: ?>
                                    <div style="width: 34px; height: 34px; border-radius: 50%; background: rgb(255, 255, 255); border: 1px solid rgba(255,255,255,0.12); display: flex; align-items: center; justify-content: center; font-weight: 900; color: rgba(255, 255, 255, 0.92);">
                                        <?= strtoupper(substr($demande['prenom'] ?? 'S', 0, 1)) ?>
                                    </div>
                                <?php endif; ?>
                                <span><?= htmlspecialchars(($demande['prenom'] ?? '') . ' ' . ($demande['nom'] ?? '')) ?></span>
                            </div>
                        </td>
                        <td><?= htmlspecialchars((string)($demande['telephone'] ?? 'N/A')) ?></td>
                        <td><?= htmlspecialchars(substr((string)($demande['notes'] ?? ''), 0, 50)) ?><?= strlen((string)($demande['notes'] ?? '')) > 50 ? '...' : '' ?></td>
                        <td>
                            <form method="POST" action="<?= BASE_PATH ?>/index.php?route=coach/dashboard" style="display: inline;">
                                <input type="hidden" name="seance_id" value="<?= (int)$demande['id'] ?>">
                                <button type="submit" name="action" value="confirmer" class="btn btn-sm btn-primary" style="background: var(--accent-blue); border-color: var(--accent-blue); color: #fff;">✓ Accepter</button>
                                <button type="submit" name="action" value="refuser" class="btn btn-sm btn-primary" style="background: var(--accent-blue); border-color: var(--accent-blue); color: #fff;">✗ Refuser</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <!-- Séances confirmées -->
    <div class="card" style="margin-top: 2rem;">
        <div class="card-header">
            <h2 class="card-title">Mes prochaines séances</h2>
        </div>

        <?php if (empty($seances_confirmees)): ?>
            <div style="text-align: center; padding: 3rem; color: var(--gray);">
                <div style="font-size: 3rem; margin-bottom: 1rem;">📅</div>
                <p>Aucune séance confirmée</p>
            </div>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Heure</th>
                        <th>Sportif</th>
                        <th>Contact</th>
                        <th>Lieu</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($seances_confirmees as $seance): ?>
                        <tr>
                            <td><?= date('d/m/Y', strtotime((string)$seance['date_seance'])) ?></td>
                            <td><?= date('H:i', strtotime((string)$seance['heure_debut'])) ?> - <?= date('H:i', strtotime((string)$seance['heure_fin'])) ?></td>
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
                            <td><?= htmlspecialchars((string)($seance['lieu'] ?? '')) ?></td>
                            <td>
                                <form method="POST" action="<?= BASE_PATH ?>/index.php?route=coach/dashboard" style="display: inline;">
                                    <input type="hidden" name="seance_id" value="<?= (int)$seance['id'] ?>">
                                    <button type="submit" name="seance_action" value="terminer" class="btn btn-sm btn-primary" style="background: var(--accent-blue); border-color: var(--accent-blue); color: #fff;">✓ Terminer</button>
                                    <button type="submit" name="seance_action" value="annuler" class="btn btn-sm btn-primary" style="background: var(--accent-blue); border-color: var(--accent-blue); color: #fff;">✗ Annuler</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <!-- Actions rapides -->
    <div class="grid grid-3" style="margin-top: 2rem;">
        <a href="<?= BASE_PATH ?>/espace-coach/profil.php" class="card" style="text-decoration: none; color: inherit; text-align: center;">
            <div style="font-size: 3rem; margin-bottom: 1rem;">👤</div>
            <h3>Profil utilisateur</h3>
            <p style="color: var(--gray);">Gérer mes informations</p>
        </a>
        <a href="<?= BASE_PATH ?>/index.php?route=coach/seances" class="card" style="text-decoration: none; color: inherit; text-align: center;">
            <div style="font-size: 3rem; margin-bottom: 1rem;">📅</div>
            <h3>Mes séances</h3>
            <p style="color: var(--gray);">Historique complet</p>
        </a>
        <a href="<?= BASE_PATH ?>/espace-coach/avis.php" class="card" style="text-decoration: none; color: inherit; text-align: center;">
            <div style="font-size: 3rem; margin-bottom: 1rem;">⭐</div>
            <h3>Mes avis</h3>
            <p style="color: var(--gray);">Voir les retours</p>
        </a>
    </div>
</div>

<?php require_once __DIR__ . '/../layout/pied-de-page.php'; ?>

