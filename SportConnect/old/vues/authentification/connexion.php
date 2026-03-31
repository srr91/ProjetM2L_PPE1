<?php require_once __DIR__ . '/../mise-en-page/entete.php'; ?>

<div class="container" style="max-width: 500px; margin-top: 5rem;">
    <div class="card">
        <div class="card-header">
            <h1 class="card-title">Connexion</h1>
        </div>

        <?php if (!empty($erreur)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($erreur) ?></div>
        <?php endif; ?>

        <form method="POST" action="/SportConnect/authentification/connexion.php">
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
                            style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; font-size: 1.2rem; color: var(--gray);">
                        <span id="eye-icon">👁️</span>
                    </button>
                </div>
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

            <div style="text-align: right; margin-bottom: 1rem;">
                <a href="/SportConnect/authentification/mot-de-passe-oublie.php" style="color: var(--primary); font-size: 0.9rem; text-decoration: none;">
                    Mot de passe oublié ?
                </a>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%;">
                Se connecter
            </button>
        </form>

        <p style="text-align: center; margin-top: 1.5rem; color: var(--gray);">
            Pas encore de compte ? 
            <a href="/SportConnect/inscription.php" style="color: var(--primary); font-weight: 600;">
                S'inscrire
            </a>
        </p>

        <div style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid var(--border);">
            <p style="color: var(--gray); font-size: 0.875rem; text-align: center; margin-bottom: 1rem;">
                Comptes de démonstration :
            </p>
            <div style="background: var(--light); padding: 1rem; border-radius: 0.5rem; font-size: 0.875rem;">
                <p><strong>Admin :</strong> admin@sportconnect.fr / admin123</p>
                <p><strong>Coach :</strong> jean.dupont@coach.fr / admin123</p>
                <p><strong>Sportif :</strong> marie.durand@email.fr / admin123</p>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../mise-en-page/pied-de-page.php'; ?>
