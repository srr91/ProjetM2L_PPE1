<?php
require_once '../configuration/database.php';
require_once '../configuration/session.php';
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
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes avis - SportConnect</title>
    <link rel="stylesheet" href="/SportConnect/ressources/styles/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <nav class="navbar">
        <div class="navbar-container">
            <a href="../index.php" class="logo">⚡ SportConnect</a>
            <ul class="nav-links">
                <li><a href="../index.php">Accueil</a></li>
                <li><a href="dashboard.php">Mon espace coach</a></li>
                <li><a href="profil.php">Profil utilisateur</a></li>
                <li><a href="../logout.php">Déconnexion</a></li>
            </ul>
        </div>
    </nav>

    <div class="container" style="margin-top: 2rem;">
        <h1 style="margin-bottom: 2rem;">Mes avis clients</h1>

        <!-- Statistiques -->
        <div class="card" style="margin-bottom: 2rem;">
            <div style="text-align: center; padding: 2rem;">
                <div style="font-size: 4rem; font-weight: 700; color: var(--primary); margin-bottom: 0.5rem;">
                    <?= number_format($stats['moyenne'], 1) ?> ★
                </div>
                <div style="color: var(--gray); font-size: 1.25rem;">
                    Basé sur <?= $stats['total'] ?> avis
                </div>

                <div style="max-width: 400px; margin: 2rem auto 0;">
                    <?php for ($i = 5; $i >= 1; $i--): ?>
                        <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 0.5rem;">
                            <span style="width: 60px;"><?= $i ?> étoiles</span>
                            <div style="flex: 1; background: var(--border); height: 8px; border-radius: 4px; overflow: hidden;">
                                <?php 
                                $count = $stats["note_$i"];
                                $percent = $stats['total'] > 0 ? ($count / $stats['total']) * 100 : 0;
                                ?>
                                <div style="width: <?= $percent ?>%; height: 100%; background: var(--warning);"></div>
                            </div>
                            <span style="width: 40px; text-align: right;"><?= $count ?></span>
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
                    <p>Aucun avis pour le moment</p>
                    <p style="font-size: 0.875rem;">Vos clients pourront vous évaluer après leurs séances</p>
                </div>
            <?php else: ?>
                <?php foreach ($avis as $index => $avisItem): ?>
                    <div style="padding: 1.5rem; <?= $index > 0 ? 'border-top: 1px solid var(--border);' : '' ?>">
                        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
                            <div>
                                <strong style="font-size: 1.1rem;"><?= htmlspecialchars($avisItem['prenom'] . ' ' . $avisItem['nom']) ?></strong>
                                <div style="color: var(--gray); font-size: 0.875rem; margin-top: 0.25rem;">
                                    <?= date('d/m/Y', strtotime($avisItem['date_avis'])) ?>
                                </div>
                            </div>
                            <div class="stars" style="color: var(--warning); font-size: 1.25rem;">
                                <?= str_repeat('★', $avisItem['note']) . str_repeat('☆', 5 - $avisItem['note']) ?>
                            </div>
                        </div>
                        <p style="color: var(--gray); line-height: 1.6;">
                            <?= nl2br(htmlspecialchars($avisItem['commentaire'])) ?>
                        </p>
                        <?php if (!$avisItem['modere']): ?>
                            <div style="margin-top: 1rem;">
                                <span class="badge badge-danger">Masqué par l'administrateur</span>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
