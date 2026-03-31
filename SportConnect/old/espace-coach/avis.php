<?php
require_once __DIR__ . '/../../configuration/database.php';
require_once __DIR__ . '/../../configuration/session.php';
requireRole('coach');

$conn = getConnection();
$user_id = $_SESSION['user_id'];

// Récupérer les avis
$avis = $conn->prepare("
    SELECT a.*, u.prenom, u.nom
    FROM avis a
    JOIN utilisateurs u ON a.user_id = u.id
    WHERE a.coach_id = ?
    ORDER BY a.date_avis DESC
");
$avis->execute([$user_id]);
$avis = $avis->fetchAll();

// Statistiques
$stats = $conn->prepare("
    SELECT 
        COUNT(*) as total,
        AVG(note) as moyenne,
        COUNT(CASE WHEN note = 5 THEN 1 END) as note_5,
        COUNT(CASE WHEN note = 4 THEN 1 END) as note_4,
        COUNT(CASE WHEN note = 3 THEN 1 END) as note_3,
        COUNT(CASE WHEN note = 2 THEN 1 END) as note_2,
        COUNT(CASE WHEN note = 1 THEN 1 END) as note_1
    FROM avis
    WHERE coach_id = ?
");
$stats->execute([$user_id]);
$stats = $stats->fetch();
// Evite les warnings si la requête renvoie null (aucun avis)
if (!$stats) {
    $stats = [
        'total' => 0,
        'moyenne' => 0,
        'note_5' => 0,
        'note_4' => 0,
        'note_3' => 0,
        'note_2' => 0,
        'note_1' => 0,
    ];
}

$titre = 'Mes avis - SportConnect';
require_once __DIR__ . '/../vues/mise-en-page/entete-sous-dossier.php';
?>

<div class="container" style="margin-top: 2rem;">
    <h1 style="margin-bottom: 2rem;">Mes avis clients</h1>

    <!-- Statistiques -->
    <div class="card" style="margin-bottom: 2rem;">
        <div style="text-align: center; padding: 2rem;">
            <div style="font-size: 4rem; font-weight: 700; color: var(--primary); margin-bottom: 0.5rem;">
                <?= number_format((float)$stats['moyenne'], 1) ?> ★
            </div>
            <div style="color: var(--gray); font-size: 1.25rem;">
                Basé sur <?= (int)$stats['total'] ?> avis
            </div>

            <div style="max-width: 420px; margin: 2rem auto 0;">
                <?php for ($i = 5; $i >= 1; $i--): ?>
                    <?php
                    $count = (int)($stats["note_$i"] ?? 0);
                    $percent = ((int)$stats['total'] > 0) ? ($count / (int)$stats['total']) * 100 : 0;
                    ?>
                    <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 0.55rem;">
                        <span style="width: 64px; font-weight: 700; color: var(--gray);"><?= $i ?> étoiles</span>
                        <div style="flex: 1; background: var(--border); height: 8px; border-radius: 4px; overflow: hidden;">
                            <div style="width: <?= $percent ?>%; height: 100%; background: var(--warning);"></div>
                        </div>
                        <span style="width: 44px; text-align: right; color: var(--gray); font-weight: 700;"><?= $count ?></span>
                    </div>
                <?php endfor; ?>
            </div>
        </div>
    </div>

    <!-- Liste des avis -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Tous les avis (<?= count($avis) ?>)</h2>
        </div>

        <?php if (empty($avis)): ?>
            <div style="text-align: center; padding: 3rem; color: var(--gray);">
                <div style="font-size: 3rem; margin-bottom: 1rem;">⭐</div>
                <p style="margin: 0.2rem 0;">Aucun avis pour le moment</p>
                <p style="font-size: 0.875rem; margin: 0.6rem 0 0;">Vos clients pourront vous évaluer après leurs séances</p>
            </div>
        <?php else: ?>
            <?php foreach ($avis as $index => $avisItem): ?>
                <div style="padding: 1.5rem; <?= $index > 0 ? 'border-top: 1px solid var(--border);' : '' ?>">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem; gap: 1rem;">
                        <div>
                            <strong style="font-size: 1.1rem;"><?= htmlspecialchars(($avisItem['prenom'] ?? '') . ' ' . ($avisItem['nom'] ?? '')) ?></strong>
                            <div style="color: var(--gray); font-size: 0.875rem; margin-top: 0.25rem;">
                                <?= !empty($avisItem['date_avis']) ? date('d/m/Y', strtotime((string)$avisItem['date_avis'])) : '' ?>
                            </div>
                        </div>
                        <div style="color: var(--warning); font-size: 1.25rem; white-space: nowrap;">
                            <?php
                            $note = (int)($avisItem['note'] ?? 0);
                            echo str_repeat('★', $note) . str_repeat('☆', max(0, 5 - $note));
                            ?>
                        </div>
                    </div>
                    <p style="color: var(--gray); line-height: 1.6; margin: 0;">
                        <?= nl2br(htmlspecialchars((string)($avisItem['commentaire'] ?? ''))) ?>
                    </p>
                    <?php if (isset($avisItem['modere']) && !$avisItem['modere']): ?>
                        <div style="margin-top: 1rem;">
                            <span class="badge badge-danger">Masqué par l'administrateur</span>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../vues/mise-en-page/pied-de-page-sous-dossier.php'; ?>
