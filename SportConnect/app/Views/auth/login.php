<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - SportConnect</title>
    <link rel="stylesheet" href="<?= BASE_PATH ?>/ressources/styles/style.css?v=<?= time() ?>">
</head>
<body class="auth-page">
    <a href="<?= BASE_PATH ?>/index.php" class="btn btn-outline" style="position: fixed; top: 18px; left: 18px; z-index: 3000; padding: 10px 14px; font-size: 0.85rem;">
        ← Accueil
    </a>
    <div class="auth-card" style="max-width: 520px;">
        <div class="card-header" style="margin-bottom: 1rem;">
            <h1 class="card-title">Connexion</h1>
            <p style="color: #666; margin-top: 0.2rem;">Bienvenue sur SportConnect</p>
        </div>

        <?php if (!empty($succes)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($succes) ?></div>
        <?php endif; ?>

        <?php if (!empty($erreur)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($erreur) ?></div>
        <?php endif; ?>

        <form method="POST" action="<?= BASE_PATH ?>/index.php?route=auth/login">
            <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" required
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                       placeholder="votre.email@exemple.fr">
            </div>
            <div class="form-group">
                <label class="form-label">Mot de passe</label>
                <div style="position: relative;">
                    <input type="password" name="password" id="password" class="form-control" required
                           placeholder="Votre mot de passe" style="padding-right: 45px;">
                    <button type="button" onclick="togglePassword()"
                            style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; font-size: 1rem; color: #666;">
                        <span id="eye-icon">👁️</span>
                    </button>
                </div>
            </div>

            <div style="text-align: right; margin-bottom: 1rem;">
                <a href="<?= BASE_PATH ?>/index.php?route=auth/forgot" style="color: #1488ff; font-size: 0.9rem; text-decoration: none;">Mot de passe oublié ?</a>
            </div>

            <button type="submit" class="btn btn-primary" style="width:100%;">Se connecter</button>
        </form>

        <p style="text-align: center; margin-top: 1rem; color: #666;">Pas encore de compte ? <a href="<?= BASE_PATH ?>/index.php?route=auth/register" style="color: #1488ff; font-weight: 700;">S'inscrire</a></p>
    </div>

    <script>
    function togglePassword() {
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eye-icon');
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            eyeIcon.textContent = '🙈';
        } else {
            passwordInput.type = 'password';
            eyeIcon.textContent = '👁️';
        }
    }
    </script>
</body>
</html>
