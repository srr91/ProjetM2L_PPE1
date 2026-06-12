<?php
require_once __DIR__ . '/../../configuration/database.php';
require_once __DIR__ . '/../../configuration/session.php';
requireRole('sportif');

$seance_id = $_GET['id'] ?? 0;
$conn = getConnection();
$user_id = $_SESSION['user_id'];

// Récupérer la séance
$stmt = $conn->prepare("
    SELECT s.*, u.prenom, u.nom, u.telephone, pc.specialite, pc.tarif_horaire
    FROM seances s
    JOIN utilisateurs u ON s.coach_id = u.id
    JOIN profils_coachs pc ON u.id = pc.user_id
    WHERE s.id = ? AND s.user_id = ?
");
$stmt->execute([$seance_id, $user_id]);
$seance = $stmt->fetch();

if (!$seance) {
    header('Location: dashboard.php');
    exit();
}

// Annulation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['annuler'])) {
    $stmt = $conn->prepare("UPDATE seances SET statut = 'annulée' WHERE id = ? AND user_id = ?");
    $stmt->execute([$seance_id, $user_id]);
    header('Location: dashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails de la séance - SportConnect</title>
    <link rel="stylesheet" href="/SportConnect/ressources/styles/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <nav class="navbar">
        <div class="navbar-container">
            <a href="../index.php" class="logo">⚡ SportConnect</a>
            <ul class="nav-links">
                <li><a href="dashboard.php">Mon espace</a></li>
                <li><a href="../logout.php">Déconnexion</a></li>
            </ul>
        </div>
    </nav>

    <div class="container" style="max-width: 800px; margin-top: 2rem;">
        <a href="dashboard.php" style="color: var(--primary); text-decoration: none; display: inline-block; margin-bottom: 1rem;">← Retour</a>

        <div class="card">
            <div class="card-header">
                <h1 class="card-title">Détails de la séance</h1>
            </div>

            <div style="background: var(--light); padding: 1.5rem; border-radius: 0.5rem; margin-bottom: 2rem;">
                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
                    <div>
                        <h2 style="margin-bottom: 0.5rem;"><?= htmlspecialchars($seance['prenom'] . ' ' . $seance['nom']) ?></h2>
                        <div style="color: var(--primary); font-weight: 600;"><?= htmlspecialchars($seance['specialite']) ?></div>
                    </div>
                    <?php if ($seance['statut'] === 'confirmée'): ?>
                        <span class="badge badge-success">Confirmée</span>
                    <?php elseif ($seance['statut'] === 'en_attente'): ?>
                        <span class="badge badge-warning">En attente</span>
                    <?php elseif ($seance['statut'] === 'terminée'): ?>
                        <span class="badge badge-info">Terminée</span>
                    <?php else: ?>
                        <span class="badge badge-danger">Annulée</span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="grid grid-2" style="margin-bottom: 2rem;">
                <div>
                    <h3 style="margin-bottom: 1rem;">📅 Date et heure</h3>
                    <p><strong>Date :</strong> <?= date('d/m/Y', strtotime($seance['date_seance'])) ?></p>
                    <p><strong>Heure :</strong> <?= date('H:i', strtotime($seance['heure_debut'])) ?> - <?= date('H:i', strtotime($seance['heure_fin'])) ?></p>
                    <p><strong>Durée :</strong> 
                        <?php
                        $debut = new DateTime($seance['heure_debut']);
                        $fin = new DateTime($seance['heure_fin']);
                        $duree = $debut->diff($fin);
                        echo $duree->h . 'h' . ($duree->i > 0 ? $duree->i . 'min' : '');
                        ?>
                    </p>
                </div>

                <div>
                    <h3 style="margin-bottom: 1rem;">📍 Lieu</h3>
                    <p><?= htmlspecialchars($seance['lieu']) ?></p>
                    <h3 style="margin-top: 1.5rem; margin-bottom: 1rem;">💰 Tarif</h3>
                    <p style="font-size: 1.5rem; font-weight: 700; color: var(--primary);">
                        <?= number_format($seance['tarif_horaire'], 0) ?>€/h
                    </p>
                </div>
            </div>

            <?php if ($seance['notes']): ?>
            <div style="margin-bottom: 2rem;">
                <h3 style="margin-bottom: 1rem;">📝 Notes</h3>
                <p style="color: var(--gray);"><?= nl2br(htmlspecialchars($seance['notes'])) ?></p>
            </div>
            <?php endif; ?>

            <div style="margin-bottom: 2rem;">
                <h3 style="margin-bottom: 1rem;">📞 Contact</h3>
                <p><strong>Téléphone :</strong> <?= htmlspecialchars($seance['telephone'] ?? 'Non renseigné') ?></p>
            </div>

            <?php if ($seance['statut'] === 'en_attente' || $seance['statut'] === 'confirmée'): ?>
            <div style="border-top: 1px solid var(--border); padding-top: 2rem;">
                <form method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir annuler cette séance ?')">
                    <button type="submit" name="annuler" class="btn btn-danger">Annuler la séance</button>
                </form>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
