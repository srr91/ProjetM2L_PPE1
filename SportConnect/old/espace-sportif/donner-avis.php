<?php
require_once __DIR__ . '/../../configuration/database.php';
require_once __DIR__ . '/../../configuration/session.php';
requireRole('sportif');

$seance_id = $_GET['seance_id'] ?? 0;
$conn = getConnection();
$error = '';
$success = '';

// Récupérer la séance
$stmt = $conn->prepare("
    SELECT s.*, u.prenom, u.nom, pc.specialite
    FROM seances s
    JOIN utilisateurs u ON s.coach_id = u.id
    JOIN profils_coachs pc ON u.id = pc.user_id
    WHERE s.id = ? AND s.user_id = ? AND s.statut = 'terminée'
");
$stmt->execute([$seance_id, $_SESSION['user_id']]);
$seance = $stmt->fetch();

if (!$seance) {
    header('Location: dashboard.php');
    exit();
}

// Vérifier si un avis existe déjà
$stmt = $conn->prepare("SELECT id FROM avis WHERE seance_id = ? AND user_id = ?");
$stmt->execute([$seance_id, $_SESSION['user_id']]);
if ($stmt->fetch()) {
    header('Location: dashboard.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $note = $_POST['note'] ?? 0;
    $commentaire = trim($_POST['commentaire']);
    
    if ($note < 1 || $note > 5) {
        $error = "Veuillez sélectionner une note";
    } elseif (empty($commentaire)) {
        $error = "Veuillez écrire un commentaire";
    } else {
        $stmt = $conn->prepare("
            INSERT INTO avis (user_id, coach_id, seance_id, note, commentaire)
            VALUES (?, ?, ?, ?, ?)
        ");
        
        if ($stmt->execute([$_SESSION['user_id'], $seance['coach_id'], $seance_id, $note, $commentaire])) {
            $success = "Merci pour votre avis !";
            header("refresh:2;url=dashboard.php");
        } else {
            $error = "Une erreur est survenue";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donner un avis - SportConnect</title>
    <link rel="stylesheet" href="/SportConnect/ressources/styles/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        .star-rating {
            display: flex;
            gap: 0.5rem;
            font-size: 2rem;
            justify-content: center;
            margin: 2rem 0;
        }
        .star-rating input {
            display: none;
        }
        .star-rating label {
            cursor: pointer;
            color: #ddd;
            transition: color 0.2s;
        }
        .star-rating input:checked ~ label,
        .star-rating label:hover,
        .star-rating label:hover ~ label {
            color: var(--warning);
        }
    </style>
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

    <div class="container" style="max-width: 600px; margin-top: 2rem;">
        <div class="card">
            <div class="card-header">
                <h1 class="card-title">Donner un avis</h1>
            </div>

            <div style="background: var(--light); padding: 1.5rem; border-radius: 0.5rem; margin-bottom: 2rem; text-align: center;">
                <h3><?= htmlspecialchars($seance['prenom'] . ' ' . $seance['nom']) ?></h3>
                <div style="color: var(--primary); font-weight: 600;"><?= htmlspecialchars($seance['specialite']) ?></div>
                <div style="color: var(--gray); margin-top: 0.5rem;">
                    Séance du <?= date('d/m/Y', strtotime($seance['date_seance'])) ?>
                </div>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label class="form-label" style="text-align: center; display: block;">Votre note *</label>
                    <div class="star-rating">
                        <input type="radio" name="note" value="5" id="star5" required>
                        <label for="star5">★</label>
                        <input type="radio" name="note" value="4" id="star4">
                        <label for="star4">★</label>
                        <input type="radio" name="note" value="3" id="star3">
                        <label for="star3">★</label>
                        <input type="radio" name="note" value="2" id="star2">
                        <label for="star2">★</label>
                        <input type="radio" name="note" value="1" id="star1">
                        <label for="star1">★</label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Votre commentaire *</label>
                    <textarea name="commentaire" class="form-control" rows="6" required placeholder="Partagez votre expérience avec ce coach..."></textarea>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%;">Publier mon avis</button>
                <a href="dashboard.php" class="btn btn-outline" style="width: 100%; margin-top: 1rem;">Annuler</a>
            </form>
        </div>
    </div>
</body>
</html>
