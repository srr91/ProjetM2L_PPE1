<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($titre ?? 'SportConnect') ?></title>
    <link rel="stylesheet" href="<?= BASE_PATH ?>/ressources/styles/style.css?v=<?= time() ?>">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body style="padding-top: 80px;">
    <nav class="navbar" style="position: fixed; top: 0; left: 0; right: 0; z-index: 1000; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <div class="navbar-container">
            <?php if (isset($_SESSION['user_id'])): ?>
                <?php
                // Photo profil (fallback old/)
                $photo_profil = $_SESSION['photo_profil'] ?? null;
                if ($photo_profil === null) {
                    try {
                        if (function_exists('getConnection')) {
                            $conn = getConnection();
                            $stmt = $conn->prepare("SELECT photo_profil FROM utilisateurs WHERE id = ?");
                            $stmt->execute([$_SESSION['user_id']]);
                            $user_data = $stmt->fetch();
                            $photo_profil = $user_data['photo_profil'] ?? null;
                        }
                    } catch (Throwable $e) {
                        $photo_profil = null;
                    }
                }

                $photoSafe = is_string($photo_profil) ? basename($photo_profil) : '';
                $photoOk = (!empty($photoSafe) && preg_match('/^[a-zA-Z0-9._-]+$/', $photoSafe));
                $mainPath = __DIR__ . '/../../../telechargements/profils/' . $photoSafe;
                $oldPath = __DIR__ . '/../../../old/telechargements/profils/' . $photoSafe;
                $hasMain = $photoOk && $photoSafe !== 'default.jpg' && file_exists($mainPath);
                $hasOld = $photoOk && $photoSafe !== 'default.jpg' && !$hasMain && file_exists($oldPath);
                $hasPhoto = $hasMain || $hasOld;
                $photoUrl = ($hasMain ? (BASE_PATH . '/telechargements/profils/') : (BASE_PATH . '/old/telechargements/profils/')) . rawurlencode($photoSafe);
                ?>

                <div class="logo" style="display:flex; align-items:center; gap:1rem;">
                    <?php if ($hasPhoto): ?>
                        <img src="<?= $photoUrl ?>" alt="Photo de profil" style="width: 45px; height: 45px; border-radius: 50%; object-fit: cover; border: 3px solid #FFD700; box-shadow: 0 2px 8px rgba(0,0,0,0.2);">
                    <?php else: ?>
                        <div style="width: 45px; height: 45px; border-radius: 50%; background: #FFD700; display:flex; align-items:center; justify-content:center; font-weight:900; color:#1E1E1E; font-size:1.3rem; border: 3px solid rgba(255,215,0,0.3);">
                            <?= strtoupper(substr((string)($_SESSION['prenom'] ?? 'U'), 0, 1)) ?>
                        </div>
                    <?php endif; ?>
                    <div>
                        <div style="font-size: 1.1rem; font-weight: 900; color: #FFD700; line-height: 1.2;">
                            <?= htmlspecialchars((string)(($_SESSION['prenom'] ?? '') . ' ' . ($_SESSION['nom'] ?? ''))) ?>
                        </div>
                        <div style="font-size: 0.75rem; color: #E8E8E8; text-transform: uppercase; letter-spacing: 1px; font-weight: 600;">
                            <?= htmlspecialchars((string)($_SESSION['role'] ?? '')) ?>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <a href="<?= BASE_PATH ?>/index.php" class="logo">⚡ SportConnect</a>
            <?php endif; ?>

            <ul class="nav-links">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="<?= BASE_PATH ?>/index.php">ACCUEIL</a></li>
                    <li><a href="<?= BASE_PATH ?>/index.php?route=coachs">NOS COACHS</a></li>
                    <li><a href="<?= BASE_PATH ?>/pages/faq.php">FAQ</a></li>
                    <li><a href="<?= BASE_PATH ?>/pages/contact.php">CONTACT</a></li>

                    <li class="profile-dropdown">
                        <a href="#" class="profile-trigger">
                            <span class="profile-avatar">
                                <?= strtoupper(substr((string)($_SESSION['prenom'] ?? 'U'), 0, 1)) ?>
                                <span class="sc-user-unread-badge" data-sc-user-unread></span>
                            </span>
                            <span><?= htmlspecialchars((string)($_SESSION['prenom'] ?? '')) ?></span>
                            <span class="dropdown-arrow">▼</span>
                        </a>
                        <div class="dropdown-menu">
                            <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
                                <a href="<?= BASE_PATH ?>/old/administration/dashboard.php" class="dropdown-item"><span class="dropdown-icon">📊</span> Tableau de bord</a>
                                <a href="<?= BASE_PATH ?>/old/administration/utilisateurs.php" class="dropdown-item"><span class="dropdown-icon">👥</span> Utilisateurs</a>
                                <a href="<?= BASE_PATH ?>/old/administration/seances.php" class="dropdown-item"><span class="dropdown-icon">📅</span> Séances</a>
                                <a href="<?= BASE_PATH ?>/old/administration/avis.php" class="dropdown-item"><span class="dropdown-icon">⭐</span> Avis</a>
                            <?php elseif (($_SESSION['role'] ?? '') === 'coach'): ?>
                                <a href="<?= BASE_PATH ?>/index.php?route=coach/dashboard" class="dropdown-item"><span class="dropdown-icon">📊</span> Tableau de bord</a>
                                <a href="<?= BASE_PATH ?>/old/espace-coach/profil.php" class="dropdown-item"><span class="dropdown-icon">👤</span> Profil utilisateur</a>
                                <a href="<?= BASE_PATH ?>/index.php?route=coach/seances" class="dropdown-item"><span class="dropdown-icon">📅</span> Mes séances</a>
                                <a href="<?= BASE_PATH ?>/old/espace-coach/avis.php" class="dropdown-item"><span class="dropdown-icon">⭐</span> Mes avis</a>
                                <a href="<?= BASE_PATH ?>/old/espace-coach/messages.php" class="dropdown-item">
                                    <span class="dropdown-icon">💬</span> Messages
                                    <span data-sc-messages-badge style="display:none; margin-left: 8px; min-width: 22px; height: 22px; padding: 0 7px; border-radius: 999px; background: #DC3545; color: #fff; font-weight: 800; font-size: 0.8rem; align-items: center; justify-content: center;"></span>
                                </a>
                            <?php else: ?>
                                <a href="<?= BASE_PATH ?>/old/espace-sportif/dashboard.php" class="dropdown-item"><span class="dropdown-icon">📊</span> Tableau de bord</a>
                                <a href="<?= BASE_PATH ?>/old/espace-sportif/profil.php" class="dropdown-item"><span class="dropdown-icon">👤</span> Profil utilisateur</a>
                                <a href="<?= BASE_PATH ?>/old/espace-sportif/progression.php" class="dropdown-item"><span class="dropdown-icon">📈</span> Ma progression</a>
                                <a href="<?= BASE_PATH ?>/index.php?route=coachs" class="dropdown-item"><span class="dropdown-icon">🔍</span> Trouver un coach</a>
                                <a href="<?= BASE_PATH ?>/old/espace-sportif/messages.php" class="dropdown-item">
                                    <span class="dropdown-icon">💬</span> Messages
                                    <span data-sc-messages-badge style="display:none; margin-left: 8px; min-width: 22px; height: 22px; padding: 0 7px; border-radius: 999px; background: #DC3545; color: #fff; font-weight: 800; font-size: 0.8rem; align-items: center; justify-content: center;"></span>
                                </a>
                            <?php endif; ?>

                            <div class="dropdown-divider"></div>
                            <a href="<?= BASE_PATH ?>/index.php?route=auth/logout" class="dropdown-item logout"><span class="dropdown-icon">🚪</span> Déconnexion</a>
                        </div>
                    </li>
                <?php else: ?>
                    <li><a href="<?= BASE_PATH ?>/index.php">ACCUEIL</a></li>
                    <li><a href="<?= BASE_PATH ?>/index.php?route=coachs">NOS COACHS</a></li>
                    <li><a href="<?= BASE_PATH ?>/pages/faq.php">FAQ</a></li>
                    <li><a href="<?= BASE_PATH ?>/pages/contact.php">CONTACT</a></li>
                    <li><a href="<?= BASE_PATH ?>/index.php?route=auth/login">CONNEXION</a></li>
                    <li><a href="<?= BASE_PATH ?>/index.php?route=auth/register" class="btn-primary" style="background: #FFD700; color: #1E1E1E; padding: 0.75rem 1.5rem; border-radius: 0; font-weight: 700;">INSCRIPTION</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const profileDropdown = document.querySelector('.profile-dropdown');
        const profileTrigger = document.querySelector('.profile-trigger');
        const dropdownMenu = document.querySelector('.dropdown-menu');
        const scRole = <?= isset($_SESSION['role']) ? json_encode($_SESSION['role']) : "null" ?>;
        const scIsLoggedIn = <?= isset($_SESSION['user_id']) ? 'true' : 'false' ?>;

        if (profileTrigger && dropdownMenu) {
            profileTrigger.addEventListener('click', function(e) {
                e.preventDefault();
                profileDropdown.classList.toggle('active');
            });
            document.addEventListener('click', function(e) {
                if (!profileDropdown.contains(e.target)) {
                    profileDropdown.classList.remove('active');
                }
            });
        }

        if (!scIsLoggedIn) return;

        function scUpdateBadge(count) {
            const label = (count > 9) ? '9+' : String(count);
            const badges = document.querySelectorAll('[data-sc-messages-badge]');
            for (let i = 0; i < badges.length; i++) {
                const el = badges[i];
                if (count > 0) {
                    el.style.display = 'inline-flex';
                    el.textContent = label;
                } else {
                    el.style.display = 'none';
                    el.textContent = '';
                }
            }
            const userBadges = document.querySelectorAll('[data-sc-user-unread]');
            for (let i = 0; i < userBadges.length; i++) {
                const el = userBadges[i];
                if (count > 0) {
                    el.style.display = 'inline-flex';
                    el.textContent = label;
                } else {
                    el.style.display = 'none';
                    el.textContent = '';
                }
            }
        }

        // Poll messages (API legacy déjà existante)
        let scLastId = parseInt(sessionStorage.getItem('sc_msg_last_id') || '0', 10);
        let scInFlight = false;
        async function scPoll() {
            if (scInFlight) return;
            scInFlight = true;
            try {
                const url = <?= json_encode(BASE_PATH) ?> + '/api/actualisation-messages.php?last_id=' + encodeURIComponent(scLastId);
                const res = await fetch(url, { credentials: 'same-origin', cache: 'no-store' });
                if (!res.ok) throw new Error('poll');
                const data = await res.json();
                if (typeof data.unread_count === 'number') {
                    scUpdateBadge(data.unread_count);
                }
                if (typeof data.max_id === 'number' && data.max_id > scLastId) {
                    scLastId = data.max_id;
                    sessionStorage.setItem('sc_msg_last_id', String(scLastId));
                }
            } catch (e) {
            } finally {
                scInFlight = false;
            }
        }

        scPoll();
        setInterval(scPoll, 5000);
    });
    </script>

    <main style="margin-top: 100px;">
