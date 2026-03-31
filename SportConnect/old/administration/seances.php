<?php
require_once __DIR__ . '/../../configuration/database.php';
require_once __DIR__ . '/../../configuration/session.php';
requireRole('admin');

$conn = getConnection();

// Récupérer toutes les séances
$seances = $conn->query("
    SELECT s.*, 
           u1.prenom as sportif_prenom, u1.nom as sportif_nom,
           u2.prenom as coach_prenom, u2.nom as coach_nom,
           pc.specialite
    FROM seances s
    JOIN utilisateurs u1 ON s.user_id = u1.id
    JOIN utilisateurs u2 ON s.coach_id = u2.id
    JOIN profils_coachs pc ON u2.id = pc.user_id
    ORDER BY s.date_seance DESC, s.heure_debut DESC
    LIMIT 100
")->fetchAll();

$titre = 'Gestion des séances - SportConnect';
require_once __DIR__ . '/../vues/mise-en-page/entete-sous-dossier.php';
?>

<div class="container" style="margin-top: 2rem;">
        <h1 style="margin-bottom: 2rem;">Gestion des séances</h1>

        <div class="card">
            <p style="color: var(--gray); margin-bottom: 1rem;">
                <?= count($seances) ?> dernières séances
            </p>

            <table class="table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Heure</th>
                        <th>Sportif</th>
                        <th>Coach</th>
                        <th>Spécialité</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($seances as $seance): ?>
                        <tr>
                            <td><?= date('d/m/Y', strtotime($seance['date_seance'])) ?></td>
                            <td><?= date('H:i', strtotime($seance['heure_debut'])) ?> - <?= date('H:i', strtotime($seance['heure_fin'])) ?></td>
                            <td><?= htmlspecialchars($seance['sportif_prenom'] . ' ' . $seance['sportif_nom']) ?></td>
                            <td><?= htmlspecialchars($seance['coach_prenom'] . ' ' . $seance['coach_nom']) ?></td>
                            <td><?= htmlspecialchars($seance['specialite']) ?></td>
                            <td>
                                <?php if ($seance['statut'] === 'confirmée'): ?>
                                    <span class="badge badge-success">Confirmée</span>
                                <?php elseif ($seance['statut'] === 'en_attente'): ?>
                                    <span class="badge badge-warning">En attente</span>
                                <?php elseif ($seance['statut'] === 'terminée'): ?>
                                    <span class="badge badge-info">Terminée</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">Annulée</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

<?php require_once '../vues/mise-en-page/pied-de-page-sous-dossier.php'; ?>
