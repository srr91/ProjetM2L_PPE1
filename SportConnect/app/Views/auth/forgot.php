<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mot de passe oublié - SportConnect</title>
    <link rel="stylesheet" href="<?= BASE_PATH ?>/ressources/styles/style.css?v=<?= time() ?>">
</head>
<body class="auth-page">
    <a href="<?= BASE_PATH ?>/index.php" class="btn btn-outline" style="position: fixed; top: 18px; left: 18px; z-index: 3000; padding: 10px 14px; font-size: 0.85rem;">
        ← Accueil
    </a>
    <div class="auth-card" style="max-width: 520px;">
        <div class="card-header" style="margin-bottom: 1rem;">
            <h1 class="card-title">Mot de passe oublié</h1>
            <p style="color: #666; margin-top: 0.2rem;">
                Entrez votre email pour générer un nouveau mot de passe temporaire
            </p>
        </div>

        <?php if (!empty($erreur)): ?>
            <div class="alert alert-danger"><?= $erreur ?></div>
        <?php endif; ?>

        <?php if (!empty($succes)): ?>
            <div class="alert alert-success"><?= $succes ?></div>
            <a href="<?= BASE_PATH ?>/index.php?route=auth/login" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">
                Se connecter
            </a>
        <?php else: ?>
            <form method="POST" action="<?= BASE_PATH ?>/index.php?route=auth/forgot">
                <div class="form-group">
                    <label class="form-label">Adresse email</label>
                    <input type="email" name="email" class="form-control" required
                           placeholder="votre.email@exemple.fr"
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    Réinitialiser le mot de passe
                </button>
            </form>

            <p style="text-align: center; margin-top: 1.5rem; color: #666;">
                Vous vous souvenez de votre mot de passe ?
                <a href="<?= BASE_PATH ?>/index.php?route=auth/login" style="color: #1488ff; font-weight: 700; text-decoration: none;">
                    Se connecter
                </a>
            </p>
        <?php endif; ?>
    </div>
</body>
</html>

