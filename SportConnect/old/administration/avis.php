<?php
require_once '../configuration/database.php';
require_once '../configuration/session.php';
requireRole('admin');

$conn = getConnection();

// Récupérer tous les avis
$avis = $conn->query("
    SELECT a.*, 
           u1.prenom as sportif_prenom, u1.nom as sportif_nom,
           u2.prenom as coach_prenom, u2.nom as coach_nom
    FROM avis a
    JOIN utilisateurs u1 ON a.user_id = u1.id
    JOIN utilisateurs u2 ON a.coach_id = u2.id
    ORDER BY a.date_avis DESC
")->fetchAll();

// Traitement des actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['toggle_modere'])) {
        $avis_id = $_POST['avis_id'];
        $stmt = $conn->prepare("UPDATE avis SET modere = NOT modere WHERE id = ?");
        $stmt->execute([$avis_id]);
        header('Location: avis.php');
        exit();
    }
    
    if (isset($_POST['supprimer_avis'])) {
        $avis_id = $_POST['avis_id'];
        $stmt = $conn->prepare("DELETE FROM avis WHERE id = ?");
        $stmt->execute([$avis_id]);
        header('Location: avis.php');
        exit();
    }
}

$titre = 'Gestion des avis - SportConnect';
require_once '../vues/mise-en-page/entete-sous-dossier.php';
?>

<div class="container" style="margin-top: 2rem;">
        <h1 style="margin-bottom: 2rem;">Gestion des avis</h1>

        <div class="card">
            <p style="color: var(--gray); margin-bottom: 1rem;">
                <?= count($avis) ?> avis au total
            </p>

            <table class="table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Sportif</th>
                        <th>Coach</th>
                        <th>Note</th>
                        <th>Commentaire</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($avis as $avisItem): ?>
                        <tr>
                            <td><?= date('d/m/Y', strtotime($avisItem['date_avis'])) ?></td>
                            <td><?= htmlspecialchars($avisItem['sportif_prenom'] . ' ' . $avisItem['sportif_nom']) ?></td>
                            <td><?= htmlspecialchars($avisItem['coach_prenom'] . ' ' . $avisItem['coach_nom']) ?></td>
                            <td>
                                <span style="color: var(--warning);">
                                    <?= str_repeat('★', $avisItem['note']) . str_repeat('☆', 5 - $avisItem['note']) ?>
                                </span>
                            </td>
                            <td style="max-width: 300px;">
                                <?= htmlspecialchars(substr($avisItem['commentaire'], 0, 100)) ?>
                                <?= strlen($avisItem['commentaire']) > 100 ? '...' : '' ?>
                            </td>
                            <td>
                                <?php if ($avisItem['modere']): ?>
                                    <span class="badge badge-success">Publié</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">Masqué</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="avis_id" value="<?= $avisItem['id'] ?>">
                                    <button type="submit" name="toggle_modere" class="btn btn-sm btn-outline">
                                        <?= $avisItem['modere'] ? 'Masquer' : 'Publier' ?>
                                    </button>
                                    <button type="submit" name="supprimer_avis" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer cet avis ?')">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

<?php require_once '../vues/mise-en-page/pied-de-page-sous-dossier.php'; ?>
