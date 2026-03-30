<?php
require_once '../configuration/database.php';
require_once '../configuration/session.php';
requireRole('admin');

$conn = getConnection();

// Traitement des actions
$message_succes = '';
$message_erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['toggle_actif'])) {
        $user_id = $_POST['user_id'];
        $stmt = $conn->prepare("SELECT nom, prenom, actif FROM utilisateurs WHERE id = ?");
        $stmt->execute([$user_id]);
        $user_info = $stmt->fetch();
        
        $stmt = $conn->prepare("UPDATE utilisateurs SET actif = NOT actif WHERE id = ?");
        if ($stmt->execute([$user_id])) {
            if ($user_info['actif'] == 1) {
                $message_succes = "Le compte de {$user_info['prenom']} {$user_info['nom']} a été désactivé.";
            } else {
                $message_succes = "Le compte de {$user_info['prenom']} {$user_info['nom']} a été réactivé.";
            }
        }
    }
    
    if (isset($_POST['supprimer_user'])) {
        $user_id = $_POST['user_id'];
        $stmt = $conn->prepare("SELECT nom, prenom FROM utilisateurs WHERE id = ?");
        $stmt->execute([$user_id]);
        $user_info = $stmt->fetch();
        
        $stmt = $conn->prepare("DELETE FROM utilisateurs WHERE id = ? AND role != 'admin'");
        if ($stmt->execute([$user_id]) && $stmt->rowCount() > 0) {
            $message_succes = "Le compte de {$user_info['prenom']} {$user_info['nom']} a été supprimé.";
        } else {
            $message_erreur = "Impossible de supprimer ce compte.";
        }
    }
}

// Filtres
$role_filter = $_GET['role'] ?? '';
$search = $_GET['search'] ?? '';
$statut_filter = $_GET['statut'] ?? '';

// Construction de la requête
$sql = "SELECT * FROM utilisateurs WHERE 1=1";
$params = [];

if ($role_filter) {
    $sql .= " AND role = ?";
    $params[] = $role_filter;
}

if ($search) {
    $sql .= " AND (nom LIKE ? OR prenom LIKE ? OR email LIKE ?)";
    $searchParam = "%$search%";
    $params = array_merge($params, [$searchParam, $searchParam, $searchParam]);
}

if ($statut_filter !== '') {
    $sql .= " AND actif = ?";
    $params[] = $statut_filter;
}

$sql .= " ORDER BY date_inscription DESC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$utilisateurs = $stmt->fetchAll();

// Statistiques
$stats = $conn->query("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN role = 'sportif' THEN 1 ELSE 0 END) as sportifs,
        SUM(CASE WHEN role = 'coach' THEN 1 ELSE 0 END) as coachs,
        SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) as admins,
        SUM(CASE WHEN actif = 1 THEN 1 ELSE 0 END) as actifs
    FROM utilisateurs
")->fetch();

$titre = 'Gestion des utilisateurs - SportConnect';
require_once '../vues/mise-en-page/entete-sous-dossier.php';
?>

<div class="container" style="margin-top: 2rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1 style="margin: 0;">👥 Gestion des utilisateurs</h1>
        <a href="dashboard.php" class="btn btn-outline">← Retour au dashboard</a>
    </div>

    <!-- Messages -->
    <?php if ($message_succes): ?>
        <div class="alert alert-success">
            ✅ <?= htmlspecialchars($message_succes) ?>
        </div>
    <?php endif; ?>
    
    <?php if ($message_erreur): ?>
        <div class="alert alert-danger">
            ❌ <?= htmlspecialchars($message_erreur) ?>
        </div>
    <?php endif; ?>

    <!-- Statistiques rapides -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
        <div style="background: white; padding: 1.5rem; border-left: 4px solid #FFD700; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
            <div style="font-size: 2rem; font-weight: 900; color: #1E1E1E;"><?= $stats['total'] ?></div>
            <div style="color: #6B6B6B; font-weight: 600; text-transform: uppercase; font-size: 0.85rem; margin-top: 0.5rem;">Total utilisateurs</div>
        </div>
        <div style="background: white; padding: 1.5rem; border-left: 4px solid #28A745; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
            <div style="font-size: 2rem; font-weight: 900; color: #1E1E1E;"><?= $stats['sportifs'] ?></div>
            <div style="color: #6B6B6B; font-weight: 600; text-transform: uppercase; font-size: 0.85rem; margin-top: 0.5rem;">Sportifs</div>
        </div>
        <div style="background: white; padding: 1.5rem; border-left: 4px solid #007BFF; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
            <div style="font-size: 2rem; font-weight: 900; color: #1E1E1E;"><?= $stats['coachs'] ?></div>
            <div style="color: #6B6B6B; font-weight: 600; text-transform: uppercase; font-size: 0.85rem; margin-top: 0.5rem;">Coachs</div>
        </div>
        <div style="background: white; padding: 1.5rem; border-left: 4px solid #28A745; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
            <div style="font-size: 2rem; font-weight: 900; color: #1E1E1E;"><?= $stats['actifs'] ?></div>
            <div style="color: #6B6B6B; font-weight: 600; text-transform: uppercase; font-size: 0.85rem; margin-top: 0.5rem;">Comptes actifs</div>
        </div>
    </div>

    <!-- Filtres modernes -->
    <div class="card" style="margin-bottom: 2rem;">
        <form method="GET" style="display: grid; grid-template-columns: 2fr 1fr 1fr auto auto; gap: 1rem; align-items: end;">
            <div class="form-group" style="margin: 0;">
                <label class="form-label">🔍 Rechercher</label>
                <input type="text" name="search" class="form-control" placeholder="Nom, prénom, email..." value="<?= htmlspecialchars($search) ?>">
            </div>
            
            <div class="form-group" style="margin: 0;">
                <label class="form-label">👤 Rôle</label>
                <select name="role" class="form-control">
                    <option value="">Tous</option>
                    <option value="sportif" <?= $role_filter === 'sportif' ? 'selected' : '' ?>>Sportifs</option>
                    <option value="coach" <?= $role_filter === 'coach' ? 'selected' : '' ?>>Coachs</option>
                    <option value="admin" <?= $role_filter === 'admin' ? 'selected' : '' ?>>Admins</option>
                </select>
            </div>

            <div class="form-group" style="margin: 0;">
                <label class="form-label">📊 Statut</label>
                <select name="statut" class="form-control">
                    <option value="">Tous</option>
                    <option value="1" <?= $statut_filter === '1' ? 'selected' : '' ?>>Actifs</option>
                    <option value="0" <?= $statut_filter === '0' ? 'selected' : '' ?>>Désactivés</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary" style="padding: 0.75rem 1.5rem;">Filtrer</button>
            
            <?php if ($search || $role_filter || $statut_filter !== ''): ?>
                <a href="utilisateurs-new.php" class="btn btn-outline" style="padding: 0.75rem 1.5rem;">Réinitialiser</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Liste des utilisateurs avec design moderne -->
    <div style="background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); border-left: 4px solid #FFD700;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 2px solid #FFD700;">
            <h2 style="margin: 0; font-size: 1.3rem; font-weight: 700;">Liste des utilisateurs</h2>
            <span style="color: #6B6B6B; font-weight: 600;">
                <?= count($utilisateurs) ?> résultat<?= count($utilisateurs) > 1 ? 's' : '' ?>
            </span>
        </div>

        <?php if (empty($utilisateurs)): ?>
            <div style="text-align: center; padding: 4rem 2rem; color: #6B6B6B;">
                <div style="font-size: 4rem; margin-bottom: 1rem;">🔍</div>
                <h3>Aucun utilisateur trouvé</h3>
                <p>Essayez de modifier vos critères de recherche</p>
            </div>
        <?php else: ?>
            <div style="overflow-x: auto; margin: -2rem; padding: 2rem;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #2C2C2C; color: white;">
                            <th style="padding: 1rem; text-align: left; font-weight: 700; text-transform: uppercase; font-size: 0.85rem; letter-spacing: 0.5px;">Nom</th>
                            <th style="padding: 1rem; text-align: left; font-weight: 700; text-transform: uppercase; font-size: 0.85rem; letter-spacing: 0.5px;">Email</th>
                            <th style="padding: 1rem; text-align: center; font-weight: 700; text-transform: uppercase; font-size: 0.85rem; letter-spacing: 0.5px;">Rôle</th>
                            <th style="padding: 1rem; text-align: center; font-weight: 700; text-transform: uppercase; font-size: 0.85rem; letter-spacing: 0.5px;">Date d'inscription</th>
                            <th style="padding: 1rem; text-align: center; font-weight: 700; text-transform: uppercase; font-size: 0.85rem; letter-spacing: 0.5px;">Statut</th>
                            <th style="padding: 1rem; text-align: center; font-weight: 700; text-transform: uppercase; font-size: 0.85rem; letter-spacing: 0.5px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($utilisateurs as $user): ?>
                            <tr style="border-bottom: 1px solid #E8E8E8; transition: background 0.2s;" onmouseover="this.style.background='#F5F5F5'" onmouseout="this.style.background='white'">
                                <td style="padding: 1.25rem 1rem;">
                                    <div style="display: flex; align-items: center; gap: 1rem;">
                                        <?php if (!empty($user['photo_profil']) && file_exists('../telechargements/profils/' . $user['photo_profil'])): ?>
                                            <img src="../telechargements/profils/<?= htmlspecialchars($user['photo_profil']) ?>" 
                                                 alt="Photo de profil" 
                                                 style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 2px solid #FFD700;">
                                        <?php else: ?>
                                            <div style="width: 40px; height: 40px; border-radius: 50%; background: #FFD700; display: flex; align-items: center; justify-content: center; font-weight: 900; color: #1E1E1E; font-size: 1.1rem;">
                                                <?= strtoupper(substr($user['prenom'], 0, 1)) ?>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <div style="font-weight: 700; color: #1E1E1E;"><?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?></div>
                                            <div style="font-size: 0.85rem; color: #6B6B6B;">#<?= $user['id'] ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td style="padding: 1.25rem 1rem; color: #6B6B6B; font-weight: 500;">
                                    <?= htmlspecialchars($user['email']) ?>
                                </td>
                                <td style="padding: 1.25rem 1rem; text-align: center;">
                                    <?php if ($user['role'] === 'coach'): ?>
                                        <span style="display: inline-block; padding: 0.4rem 1rem; background: rgba(0, 123, 255, 0.1); color: #007BFF; border-radius: 20px; font-weight: 700; font-size: 0.85rem;">
                                            🏋️ Coach
                                        </span>
                                    <?php elseif ($user['role'] === 'admin'): ?>
                                        <span style="display: inline-block; padding: 0.4rem 1rem; background: rgba(220, 53, 69, 0.1); color: #DC3545; border-radius: 20px; font-weight: 700; font-size: 0.85rem;">
                                            👑 Admin
                                        </span>
                                    <?php else: ?>
                                        <span style="display: inline-block; padding: 0.4rem 1rem; background: rgba(40, 167, 69, 0.1); color: #28A745; border-radius: 20px; font-weight: 700; font-size: 0.85rem;">
                                            🏃 Sportif
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 1.25rem 1rem; text-align: center; color: #6B6B6B; font-weight: 500;">
                                    <?= date('d/m/Y H:i', strtotime($user['date_inscription'])) ?>
                                </td>
                                <td style="padding: 1.25rem 1rem; text-align: center;">
                                    <?php if ($user['actif']): ?>
                                        <span style="display: inline-block; padding: 0.4rem 1rem; background: rgba(40, 167, 69, 0.1); color: #28A745; border-radius: 20px; font-weight: 700; font-size: 0.85rem;">
                                            ✅ Actif
                                        </span>
                                    <?php else: ?>
                                        <span style="display: inline-block; padding: 0.4rem 1rem; background: rgba(220, 53, 69, 0.1); color: #DC3545; border-radius: 20px; font-weight: 700; font-size: 0.85rem;">
                                            ❌ Désactivé
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 1.25rem 1rem; text-align: center;">
                                    <div style="display: flex; gap: 0.5rem; justify-content: center;">
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir <?= $user['actif'] ? 'désactiver' : 'activer' ?> ce compte ?');">
                                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                            <button type="submit" name="toggle_actif" class="btn btn-sm" style="background: #FFD700; color: #1E1E1E; padding: 0.5rem 1rem; font-size: 0.85rem; font-weight: 700;">
                                                <?= $user['actif'] ? '🔒 DÉSACTIVER' : '🔓 ACTIVER' ?>
                                            </button>
                                        </form>
                                        
                                        <?php if ($user['role'] !== 'admin'): ?>
                                            <form method="POST" style="display: inline;" onsubmit="return confirm('⚠️ ATTENTION : Êtes-vous sûr de vouloir SUPPRIMER définitivement ce compte ?');">
                                                <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                                <button type="submit" name="supprimer_user" class="btn btn-sm" style="background: #DC3545; color: white; padding: 0.5rem 1rem; font-size: 0.85rem; font-weight: 700;">
                                                    🗑️ SUPPRIMER
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../vues/mise-en-page/pied-de-page-sous-dossier.php'; ?>
