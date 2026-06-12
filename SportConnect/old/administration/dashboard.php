<?php
require_once '../configuration/database.php';
require_once '../configuration/session.php';
require_once '../configuration/check-account.php';
requireRole('admin');

$conn = getConnection();

// Statistiques globales
$stats = $conn->query("
    SELECT 
        (SELECT COUNT(*) FROM utilisateurs WHERE role = 'sportif') as nb_sportifs,
        (SELECT COUNT(*) FROM utilisateurs WHERE role = 'coach') as nb_coachs,
        (SELECT COUNT(*) FROM seances) as nb_seances,
        (SELECT COUNT(*) FROM avis) as nb_avis,
        (SELECT COUNT(*) FROM profils_coachs WHERE valide = 0) as coachs_en_attente
")->fetch();

// Derniers utilisateurs
$derniers_users = $conn->query("
    SELECT * FROM utilisateurs 
    ORDER BY date_inscription DESC 
    LIMIT 10
")->fetchAll();

// Coachs en attente de validation
$coachs_attente = $conn->query("
    SELECT u.*, pc.specialite, pc.tarif_horaire
    FROM utilisateurs u
    JOIN profils_coachs pc ON u.id = pc.user_id
    WHERE pc.valide = 0
    ORDER BY u.date_inscription DESC
")->fetchAll();

// Variable pour les messages
$message_succes = '';

// Traitement des actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['valider_coach'])) {
        $coach_id = $_POST['coach_id'];
        $stmt = $conn->prepare("UPDATE profils_coachs SET valide = 1 WHERE user_id = ?");
        $stmt->execute([$coach_id]);
        $message_succes = "Coach validé avec succès !";
    }
    
    if (isset($_POST['supprimer_user'])) {
        $user_id = $_POST['user_id'];
        
        // Récupérer les infos de l'utilisateur avant suppression
        $stmt = $conn->prepare("SELECT nom, prenom FROM utilisateurs WHERE id = ?");
        $stmt->execute([$user_id]);
        $user_info = $stmt->fetch();
        
        // Supprimer l'utilisateur
        $stmt = $conn->prepare("DELETE FROM utilisateurs WHERE id = ? AND role != 'admin'");
        if ($stmt->execute([$user_id]) && $stmt->rowCount() > 0) {
            $message_succes = "Le compte de {$user_info['prenom']} {$user_info['nom']} a été supprimé. L'utilisateur sera déconnecté automatiquement.";
        }
    }
    
    if (isset($_POST['toggle_actif'])) {
        $user_id = $_POST['user_id'];
        
        // Récupérer l'état actuel
        $stmt = $conn->prepare("SELECT nom, prenom, actif FROM utilisateurs WHERE id = ?");
        $stmt->execute([$user_id]);
        $user_info = $stmt->fetch();
        
        // Changer l'état
        $stmt = $conn->prepare("UPDATE utilisateurs SET actif = NOT actif WHERE id = ?");
        if ($stmt->execute([$user_id])) {
            if ($user_info['actif'] == 1) {
                $message_succes = "Le compte de {$user_info['prenom']} {$user_info['nom']} a été désactivé. L'utilisateur sera déconnecté automatiquement.";
            } else {
                $message_succes = "Le compte de {$user_info['prenom']} {$user_info['nom']} a été réactivé.";
            }
        }
    }
}

$titre = 'Administration - SportConnect';
require_once '../vues/mise-en-page/entete-sous-dossier.php';
?>

<div class="container" style="margin-top: 2rem;">
        <h1 style="margin-bottom: 2rem;">Tableau de bord administrateur</h1>

        <!-- Message de succès -->
        <?php if (!empty($message_succes)): ?>
        <div class="alert alert-success" style="background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 1rem; border-radius: 0.5rem; margin-bottom: 2rem;">
            ✅ <?= htmlspecialchars($message_succes) ?>
        </div>
        <?php endif; ?>

        <!-- Statistiques -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value"><?= $stats['nb_sportifs'] ?></div>
                <div class="stat-label">Sportifs inscrits</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?= $stats['nb_coachs'] ?></div>
                <div class="stat-label">Coachs</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?= $stats['nb_seances'] ?></div>
                <div class="stat-label">Séances réservées</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?= $stats['nb_avis'] ?></div>
                <div class="stat-label">Avis publiés</div>
            </div>
        </div>

        <!-- Alertes -->
        <?php if ($stats['coachs_en_attente'] > 0): ?>
        <div class="alert alert-warning">
            ⚠️ <?= $stats['coachs_en_attente'] ?> coach(s) en attente de validation
        </div>
        <?php endif; ?>

        <!-- Coachs en attente -->
        <?php if (!empty($coachs_attente)): ?>
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Coachs en attente de validation</h2>
            </div>

            <table class="table">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Spécialité</th>
                        <th>Tarif</th>
                        <th>Inscription</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($coachs_attente as $coach): ?>
                        <tr>
                            <td><?= htmlspecialchars($coach['prenom'] . ' ' . $coach['nom']) ?></td>
                            <td><?= htmlspecialchars($coach['email']) ?></td>
                            <td><?= htmlspecialchars($coach['specialite']) ?></td>
                            <td><?= number_format($coach['tarif_horaire'], 0) ?>€/h</td>
                            <td><?= date('d/m/Y', strtotime($coach['date_inscription'])) ?></td>
                            <td>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="coach_id" value="<?= $coach['id'] ?>">
                                    <button type="submit" name="valider_coach" class="btn btn-sm btn-secondary" onclick="return confirm('Valider ce coach ?')">✓ Valider</button>
                                </form>
                                <a href="../coach-profile.php?id=<?= $coach['id'] ?>" class="btn btn-sm btn-outline" target="_blank">Voir profil</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <!-- Derniers utilisateurs -->
        <div class="card" style="margin-top: 2rem;">
            <div class="card-header">
                <h2 class="card-title">Dernières inscriptions</h2>
            </div>

            <table class="table" style="width: 100%;">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Rôle</th>
                        <th>Date d'inscription</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($derniers_users as $user): ?>
                        <tr>
                            <td><?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td>
                                <?php if ($user['role'] === 'coach'): ?>
                                    <span class="badge badge-info">Coach</span>
                                <?php elseif ($user['role'] === 'admin'): ?>
                                    <span class="badge badge-danger">Admin</span>
                                <?php else: ?>
                                    <span class="badge badge-success">Sportif</span>
                                <?php endif; ?>
                            </td>
                            <td><?= date('d/m/Y H:i', strtotime($user['date_inscription'])) ?></td>
                            <td>
                                <?php if ($user['actif']): ?>
                                    <span class="badge badge-success">Actif</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">Désactivé</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($user['role'] !== 'admin'): ?>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                        <button type="submit" name="toggle_actif" class="btn btn-sm btn-outline">
                                            <?= $user['actif'] ? 'Désactiver' : 'Activer' ?>
                                        </button>
                                        <button type="submit" name="supprimer_user" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer cet utilisateur ?')">Supprimer</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Actions rapides -->
        <div class="grid grid-3" style="margin-top: 2rem;">
            <a href="utilisateurs.php" class="card" style="text-decoration: none; color: inherit; text-align: center;">
                <div style="font-size: 3rem; margin-bottom: 1rem;">👥</div>
                <h3>Utilisateurs</h3>
                <p style="color: var(--gray);">Gérer tous les comptes</p>
            </a>
            <a href="seances.php" class="card" style="text-decoration: none; color: inherit; text-align: center;">
                <div style="font-size: 3rem; margin-bottom: 1rem;">📅</div>
                <h3>Séances</h3>
                <p style="color: var(--gray);">Voir toutes les séances</p>
            </a>
            <a href="avis.php" class="card" style="text-decoration: none; color: inherit; text-align: center;">
                <div style="font-size: 3rem; margin-bottom: 1rem;">⭐</div>
                <h3>Avis</h3>
                <p style="color: var(--gray);">Modérer les avis</p>
            </a>
        </div>
    </div>

<?php require_once '../vues/mise-en-page/pied-de-page-sous-dossier.php'; ?>
