<?php
require_once __DIR__ . '/../../configuration/database.php';
require_once __DIR__ . '/../../configuration/session.php';
requireRole('sportif');

$conn = getConnection();
$user_id = $_SESSION['user_id'];
$success = '';

// Récupérer les progressions
$progressions = $conn->prepare("
    SELECT * FROM progressions 
    WHERE user_id = ? 
    ORDER BY date_mesure DESC
");
$progressions->execute([$user_id]);
$progressions = $progressions->fetchAll();

// Suppression ou ajout selon le bouton utilisé
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_selected'])) {
    $ids = isset($_POST['ids']) && is_array($_POST['ids']) ? array_map('intval', $_POST['ids']) : [];
    if (!empty($ids)) {
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $params = array_merge([$user_id], $ids);
        $delete = $conn->prepare("DELETE FROM progressions WHERE user_id = ? AND id IN ($placeholders)");
        $delete->execute($params);
        $success = "Éléments supprimés de l'historique.";
    }

    // Recharger
    $progressions = $conn->prepare("
        SELECT * FROM progressions 
        WHERE user_id = ? 
        ORDER BY date_mesure DESC
    ");
    $progressions->execute([$user_id]);
    $progressions = $progressions->fetchAll();
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date_mesure = $_POST['date_mesure'];
    $poids = $_POST['poids'] ?? null;
    $temps_performance = $_POST['temps_performance'] ?? null;
    $notes = trim($_POST['notes']);
    
    $stmt = $conn->prepare("
        INSERT INTO progressions (user_id, date_mesure, poids, temps_performance, notes)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([$user_id, $date_mesure, $poids, $temps_performance, $notes]);
    
    $success = "Progression enregistrée !";
    
    // Recharger
    $progressions = $conn->prepare("
        SELECT * FROM progressions 
        WHERE user_id = ? 
        ORDER BY date_mesure DESC
    ");
    $progressions->execute([$user_id]);
    $progressions = $progressions->fetchAll();
}

$titre = 'Ma progression - SportConnect';
require_once __DIR__ . '/../vues/mise-en-page/entete-sous-dossier.php';
?>

<div class="container" style="margin-top: 2rem;">
        <h1 style="margin-bottom: 2rem;">Ma progression</h1>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <div class="grid" style="grid-template-columns: 1fr 2fr; gap: 2rem;">
            <!-- Formulaire d'ajout -->
            <div class="card">
                <h2 style="margin-bottom: 1.5rem;">Ajouter une mesure</h2>
                
                <form method="POST">
                    <div class="form-group">
                        <label class="form-label">Date</label>
                        <input type="date" name="date_mesure" class="form-control" required value="<?= date('Y-m-d') ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Poids (kg)</label>
                        <input type="number" name="poids" class="form-control" step="0.1" min="0" placeholder="Ex: 75.5">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Performance (temps)</label>
                        <input type="time" name="temps_performance" class="form-control" placeholder="Ex: 00:25:30">
                        <small style="color: var(--gray);">Pour mesurer vos temps (course, etc.)</small>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Comment vous sentez-vous ? Remarques..."></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%;">Enregistrer</button>
                </form>
            </div>

            <!-- Historique -->
            <div class="card">
                <h2 style="margin-bottom: 1.5rem;">Historique</h2>

                <?php if (empty($progressions)): ?>
                    <div style="text-align: center; padding: 3rem; color: var(--gray);">
                        <div style="font-size: 3rem; margin-bottom: 1rem;">📊</div>
                        <p>Aucune mesure enregistrée</p>
                        <p style="font-size: 0.875rem;">Commencez à suivre votre progression !</p>
                    </div>
                <?php else: ?>
                    <form method="POST">
                        <input type="hidden" name="delete_selected" value="1">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <input type="checkbox" id="check_all" onclick="document.querySelectorAll('input[name=\'ids[]\']').forEach(cb=>cb.checked=this.checked);">
                                <label for="check_all" style="cursor: pointer;">Tout sélectionner</label>
                            </div>
                            <button type="submit" class="btn btn-danger" onclick="return confirm('Supprimer les éléments sélectionnés ?');">Supprimer la sélection</button>
                        </div>

                        <div style="max-height: 600px; overflow-y: auto;">
                            <?php foreach ($progressions as $index => $prog): ?>
                                <div style="padding: 1.5rem; <?= $index > 0 ? 'border-top: 1px solid var(--border);' : '' ?>">
                                    <div style="display: flex; justify-content: space-between; margin-bottom: 1rem; align-items: center;">
                                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                                            <input type="checkbox" name="ids[]" value="<?= (int)$prog['id'] ?>">
                                            <strong><?= date('d/m/Y', strtotime($prog['date_mesure'])) ?></strong>
                                        </div>
                                    </div>

                                    <div style="display: flex; gap: 2rem; margin-bottom: 1rem;">
                                        <?php if ($prog['poids']): ?>
                                            <div>
                                                <div style="color: var(--gray); font-size: 0.875rem;">Poids</div>
                                                <div style="font-size: 1.5rem; font-weight: 700; color: var(--primary);">
                                                    <?= number_format($prog['poids'], 1) ?> kg
                                                </div>
                                            </div>
                                        <?php endif; ?>

                                        <?php if ($prog['temps_performance']): ?>
                                            <div>
                                                <div style="color: var(--gray); font-size: 0.875rem;">Performance</div>
                                                <div style="font-size: 1.5rem; font-weight: 700; color: var(--secondary);">
                                                    <?= $prog['temps_performance'] ?>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <?php if ($prog['notes']): ?>
                                        <p style="color: var(--gray); font-style: italic;">
                                            "<?= htmlspecialchars($prog['notes']) ?>"
                                        </p>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

<?php require_once __DIR__ . '/../vues/mise-en-page/pied-de-page-sous-dossier.php'; ?>
