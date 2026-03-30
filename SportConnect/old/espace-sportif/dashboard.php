<?php
require_once __DIR__ . '/../../configuration/database.php';
require_once __DIR__ . '/../../configuration/session.php';
require_once __DIR__ . '/../../configuration/check-account.php';
requireRole('sportif');

$conn = getConnection();
$user_id = $_SESSION['user_id'];

// Récupérer les séances à venir
$stmt = $conn->prepare("
    SELECT s.*, u.prenom, u.nom, pc.specialite
    FROM seances s
    JOIN utilisateurs u ON s.coach_id = u.id
    JOIN profils_coachs pc ON u.id = pc.user_id
    WHERE s.user_id = ? AND s.date_seance >= CURDATE() AND s.statut != 'annulée'
    ORDER BY s.date_seance ASC, s.heure_debut ASC
    LIMIT 5
");
$stmt->execute([$user_id]);
$seances_a_venir = $stmt->fetchAll();

// Récupérer les séances passées
$stmt = $conn->prepare("
    SELECT s.*, u.prenom, u.nom, pc.specialite,
           a.id as avis_id
    FROM seances s
    JOIN utilisateurs u ON s.coach_id = u.id
    JOIN profils_coachs pc ON u.id = pc.user_id
    LEFT JOIN avis a ON s.id = a.seance_id AND a.user_id = ?
    WHERE s.user_id = ? AND s.statut = 'terminée'
    ORDER BY s.date_seance DESC
    LIMIT 5
");
$stmt->execute([$user_id, $user_id]);
$seances_passees = $stmt->fetchAll();

// Statistiques
$stats = $conn->prepare("
    SELECT 
        COUNT(*) as total_seances,
        COUNT(CASE WHEN statut = 'terminée' THEN 1 END) as seances_terminees,
        COUNT(CASE WHEN statut = 'en_attente' THEN 1 END) as seances_en_attente
    FROM seances
    WHERE user_id = ?
");
$stats->execute([$user_id]);
$stats = $stats->fetch();

// Profil sportif
$profil = $conn->prepare("SELECT * FROM profils_sportifs WHERE user_id = ?");
$profil->execute([$user_id]);
$profil = $profil->fetch();

$titre = 'Mon espace - SportConnect';
require_once __DIR__ . '/../vues/mise-en-page/entete-sous-dossier.php';
?>

<div class="container" style="margin-top: 2rem;">
        <h1 style="margin-bottom: 2rem;">Bonjour <?= htmlspecialchars($_SESSION['prenom']) ?> 👋</h1>

        <!-- Statistiques -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value"><?= $stats['total_seances'] ?></div>
                <div class="stat-label">Séances réservées</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?= $stats['seances_terminees'] ?></div>
                <div class="stat-label">Séances terminées</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?= $stats['seances_en_attente'] ?></div>
                <div class="stat-label">En attente</div>
            </div>
        </div>

        <!-- Séances à venir -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Mes prochaines séances</h2>
            </div>

            <?php if (empty($seances_a_venir)): ?>
                <div style="text-align: center; padding: 3rem; color: var(--gray);">
                    <div style="font-size: 3rem; margin-bottom: 1rem;">📅</div>
                    <p>Aucune séance prévue</p>
                    <a href="../coachs.php" class="btn btn-primary" style="margin-top: 1rem;">Réserver une séance</a>
                </div>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Heure</th>
                            <th>Coach</th>
                            <th>Spécialité</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($seances_a_venir as $seance): ?>
                            <tr>
                                <td><?= date('d/m/Y', strtotime($seance['date_seance'])) ?></td>
                                <td><?= date('H:i', strtotime($seance['heure_debut'])) ?></td>
                                <td><?= htmlspecialchars($seance['prenom'] . ' ' . $seance['nom']) ?></td>
                                <td><?= htmlspecialchars($seance['specialite']) ?></td>
                                <td>
                                    <?php if ($seance['statut'] === 'confirmée'): ?>
                                        <span class="badge badge-success">Confirmée</span>
                                    <?php else: ?>
                                        <span class="badge badge-warning">En attente</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="seance-details.php?id=<?= $seance['id'] ?>" class="btn btn-sm btn-outline">Détails</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- Séances passées -->
        <?php if (!empty($seances_passees)): ?>
        <div class="card" style="margin-top: 2rem;">
            <div class="card-header">
                <h2 class="card-title">Séances récentes</h2>
            </div>

            <table class="table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Coach</th>
                        <th>Spécialité</th>
                        <th>Avis</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($seances_passees as $seance): ?>
                        <tr>
                            <td><?= date('d/m/Y', strtotime($seance['date_seance'])) ?></td>
                            <td><?= htmlspecialchars($seance['prenom'] . ' ' . $seance['nom']) ?></td>
                            <td><?= htmlspecialchars($seance['specialite']) ?></td>
                            <td>
                                <?php if ($seance['avis_id']): ?>
                                    <span class="badge badge-success">Avis donné</span>
                                <?php else: ?>
                                    <a href="donner-avis.php?seance_id=<?= $seance['id'] ?>" class="btn btn-sm btn-primary">Donner un avis</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <!-- Actions rapides -->
        <div class="grid grid-3" style="margin-top: 2rem;">
            <a href="../coachs.php" class="card" style="text-decoration: none; color: inherit; text-align: center;">
                <div style="font-size: 3rem; margin-bottom: 1rem;">🔍</div>
                <h3>Trouver un coach</h3>
                <p style="color: var(--gray);">Recherchez le coach parfait</p>
            </a>
            <a href="profil.php" class="card" style="text-decoration: none; color: inherit; text-align: center;">
                <div style="font-size: 3rem; margin-bottom: 1rem;">👤</div>
                <h3>Profil utilisateur</h3>
                <p style="color: var(--gray);">Gérez vos informations</p>
            </a>
            <a href="progression.php" class="card" style="text-decoration: none; color: inherit; text-align: center;">
                <div style="font-size: 3rem; margin-bottom: 1rem;">📈</div>
                <h3>Ma progression</h3>
                <p style="color: var(--gray);">Suivez vos performances</p>
            </a>
        </div>
    </div>

<?php require_once __DIR__ . '/../vues/mise-en-page/pied-de-page-sous-dossier.php'; ?>
