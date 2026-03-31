<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - SportConnect</title>
    <link rel="stylesheet" href="<?= BASE_PATH ?>/ressources/styles/style.css?v=<?= time() ?>">
</head>
<body class="auth-page">
    <a href="<?= BASE_PATH ?>/index.php" class="btn btn-outline" style="position: fixed; top: 18px; left: 18px; z-index: 3000; padding: 10px 14px; font-size: 0.85rem;">
        ← Accueil
    </a>
    <div class="auth-card" style="max-width: 980px;">
        <div class="card-header" style="margin-bottom: 1rem;">
            <h1 class="card-title">🎯 Créer un compte SportConnect</h1>
            <p style="color: #666; margin-top: 0.2rem;">Rejoignez notre communauté sportive</p>
        </div>

        <?php if (!empty($erreur)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($erreur) ?></div>
        <?php endif; ?>

        <?php if (!empty($succes)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($succes) ?></div>
        <?php endif; ?>

        <form method="POST" action="<?= BASE_PATH ?>/index.php?route=auth/register">
            <!-- Choix du rôle -->
            <div class="form-group">
                <label class="form-label">Je suis</label>
                <?php $role = $_POST['role'] ?? 'sportif'; ?>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.75rem;">
                    <label style="cursor:pointer; border:1px solid #ddd; border-radius:10px; padding:0.75rem; display:block; background:#f9f9f9; font-weight:700;">
                        <span style="display:inline-flex; align-items:center; gap:0.45rem; margin:0 auto;">
                            <input type="radio" name="role" value="sportif" <?= $role === 'sportif' ? 'checked' : '' ?> required style="margin:0;" onchange="toggleRoleFields()">
                            <span>Sportif</span>
                        </span>
                    </label>
                    <label style="cursor:pointer; border:1px solid #ddd; border-radius:10px; padding:0.75rem; display:block; background:#f9f9f9; font-weight:700;">
                        <span style="display:inline-flex; align-items:center; gap:0.45rem; margin:0 auto;">
                            <input type="radio" name="role" value="coach" <?= $role === 'coach' ? 'checked' : '' ?> required style="margin:0;" onchange="toggleRoleFields()">
                            <span>Coach</span>
                        </span>
                    </label>
                </div>
            </div>

            <hr style="margin: 1.2rem 0; border: none; border-top: 1px solid rgba(0,0,0,0.08);">

            <!-- Informations personnelles -->
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.75rem;">
                <div class="form-group">
                    <label class="form-label">Prénom *</label>
                    <input type="text" name="prenom" class="form-control" required value="<?= htmlspecialchars($_POST['prenom'] ?? '') ?>" placeholder="Votre prénom">
                </div>
                <div class="form-group">
                    <label class="form-label">Nom *</label>
                    <input type="text" name="nom" class="form-control" required value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>" placeholder="Votre nom">
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.75rem;">
                <div class="form-group">
                    <label class="form-label">Email *</label>
                    <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" placeholder="votre.email@exemple.fr">
                </div>
                <div class="form-group">
                    <label class="form-label">Téléphone *</label>
                    <input type="tel" name="telephone" class="form-control" required value="<?= htmlspecialchars($_POST['telephone'] ?? '') ?>" placeholder="06 12 34 56 78">
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.75rem;">
                <div class="form-group">
                    <label class="form-label">Date de naissance</label>
                    <input type="date" name="date_naissance" class="form-control" value="<?= htmlspecialchars($_POST['date_naissance'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Sexe</label>
                    <?php $sexe = $_POST['sexe'] ?? ''; ?>
                    <select name="sexe" class="form-control">
                        <option value="">-- Sélectionner --</option>
                        <option value="M" <?= $sexe === 'M' ? 'selected' : '' ?>>Homme</option>
                        <option value="F" <?= $sexe === 'F' ? 'selected' : '' ?>>Femme</option>
                        <option value="A" <?= $sexe === 'A' ? 'selected' : '' ?>>Autre</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Adresse</label>
                <input type="text" name="adresse" class="form-control" value="<?= htmlspecialchars($_POST['adresse'] ?? '') ?>" placeholder="Numéro et nom de rue">
            </div>

            <div style="display:grid;grid-template-columns:2fr 1fr;gap:0.75rem;">
                <div class="form-group">
                    <label class="form-label">Ville</label>
                    <input type="text" name="ville" class="form-control" value="<?= htmlspecialchars($_POST['ville'] ?? '') ?>" placeholder="Votre ville">
                </div>
                <div class="form-group">
                    <label class="form-label">Code postal</label>
                    <input type="text" name="code_postal" class="form-control" maxlength="5" value="<?= htmlspecialchars($_POST['code_postal'] ?? '') ?>" placeholder="75000">
                </div>
            </div>

            <!-- Champs spécifiques sportif -->
            <div id="sportif-fields" style="margin-top: 1rem;">
                <hr style="margin: 1.2rem 0; border: none; border-top: 1px solid rgba(0,0,0,0.08);">
                <div style="font-weight: 800; margin-bottom: 0.6rem;">Informations sportives</div>

                <div class="form-group">
                    <label class="form-label">Sport(s) pratiqué(s)</label>
                    <input type="text" name="sport_pratique" class="form-control"
                           value="<?= htmlspecialchars($_POST['sport_pratique'] ?? '') ?>"
                           placeholder="Ex: Musculation, Course à pied, Yoga...">
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.75rem;">
                    <div class="form-group">
                        <label class="form-label">Niveau</label>
                        <?php $niveau = $_POST['niveau'] ?? ''; ?>
                        <select name="niveau" class="form-control">
                            <option value="">-- Sélectionner --</option>
                            <option value="debutant" <?= $niveau === 'debutant' ? 'selected' : '' ?>>Débutant</option>
                            <option value="intermediaire" <?= $niveau === 'intermediaire' ? 'selected' : '' ?>>Intermédiaire</option>
                            <option value="avance" <?= $niveau === 'avance' ? 'selected' : '' ?>>Avancé</option>
                            <option value="expert" <?= $niveau === 'expert' ? 'selected' : '' ?>>Expert</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Fréquence d'entraînement</label>
                        <?php $freq = $_POST['frequence_entrainement'] ?? ''; ?>
                        <select name="frequence_entrainement" class="form-control">
                            <option value="">-- Sélectionner --</option>
                            <option value="1-2" <?= $freq === '1-2' ? 'selected' : '' ?>>1-2 fois/semaine</option>
                            <option value="3-4" <?= $freq === '3-4' ? 'selected' : '' ?>>3-4 fois/semaine</option>
                            <option value="5+" <?= $freq === '5+' ? 'selected' : '' ?>>5+ fois/semaine</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Objectifs</label>
                    <textarea name="objectifs" class="form-control" rows="2"
                              placeholder="Ex: Perdre du poids, Gagner en muscle, Améliorer mon endurance..."><?= htmlspecialchars($_POST['objectifs'] ?? '') ?></textarea>
                </div>
            </div>

            <!-- Champs spécifiques coach -->
            <div id="coach-fields" style="display:none; margin-top: 1rem;">
                <hr style="margin: 1.2rem 0; border: none; border-top: 1px solid rgba(0,0,0,0.08);">
                <div style="font-weight: 800; margin-bottom: 0.6rem;">Informations professionnelles</div>

                <div style="display:grid;grid-template-columns:2fr 1fr;gap:0.75rem;">
                    <div class="form-group">
                        <label class="form-label">Spécialité *</label>
                        <?php $specialite = $_POST['specialite'] ?? ''; ?>
                        <select name="specialite" class="form-control">
                            <option value="">-- Sélectionnez --</option>
                            <option value="Musculation" <?= $specialite === 'Musculation' ? 'selected' : '' ?>>Musculation</option>
                            <option value="Yoga" <?= $specialite === 'Yoga' ? 'selected' : '' ?>>Yoga</option>
                            <option value="Running" <?= $specialite === 'Running' ? 'selected' : '' ?>>Running / Course à pied</option>
                            <option value="Fitness" <?= $specialite === 'Fitness' ? 'selected' : '' ?>>Fitness</option>
                            <option value="Boxe" <?= $specialite === 'Boxe' ? 'selected' : '' ?>>Boxe</option>
                            <option value="Natation" <?= $specialite === 'Natation' ? 'selected' : '' ?>>Natation</option>
                            <option value="Crossfit" <?= $specialite === 'Crossfit' ? 'selected' : '' ?>>Crossfit</option>
                            <option value="Pilates" <?= $specialite === 'Pilates' ? 'selected' : '' ?>>Pilates</option>
                            <option value="Nutrition" <?= $specialite === 'Nutrition' ? 'selected' : '' ?>>Nutrition sportive</option>
                            <option value="Preparation physique" <?= $specialite === 'Preparation physique' ? 'selected' : '' ?>>Préparation physique</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Tarif horaire (€) *</label>
                        <input type="number" name="tarif" class="form-control" min="0" step="5"
                               value="<?= htmlspecialchars($_POST['tarif'] ?? '') ?>"
                               placeholder="Ex: 45">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Diplômes et certifications</label>
                    <textarea name="diplomes" class="form-control" rows="2"
                              placeholder="Ex: BPJEPS, CQP, Licence STAPS..."><?= htmlspecialchars($_POST['diplomes'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Expérience (années)</label>
                    <input type="number" name="experience" class="form-control" min="0"
                           value="<?= htmlspecialchars($_POST['experience'] ?? '') ?>"
                           placeholder="Nombre d'années d'expérience">
                </div>

                <div class="form-group">
                    <label class="form-label">Description de vos services</label>
                    <textarea name="description" class="form-control" rows="3"
                              placeholder="Présentez votre approche, votre méthode d'entraînement..."><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Localisation</label>
                    <input type="text" name="localisation" class="form-control"
                           value="<?= htmlspecialchars($_POST['localisation'] ?? '') ?>"
                           placeholder="Ex: Paris 15ème, Lyon, Marseille...">
                </div>
            </div>

            <hr style="margin: 1.2rem 0; border: none; border-top: 1px solid rgba(0,0,0,0.08);">

            <!-- Sécurité -->
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.75rem;">
                <div class="form-group">
                    <label class="form-label">Mot de passe *</label>
                    <input type="password" name="password" class="form-control" required minlength="6" placeholder="Au moins 6 caractères">
                </div>
                <div class="form-group">
                    <label class="form-label">Confirmer *</label>
                    <input type="password" name="confirm_password" class="form-control" required placeholder="Retapez le mot de passe">
                </div>
            </div>

            <div class="form-group">
                <label style="display:flex; align-items:center; gap:0.5rem; cursor:pointer;">
                    <input type="checkbox" required>
                    <span>J'accepte les conditions d'utilisation</span>
                </label>
            </div>

            <div class="form-group" style="margin-top: 0.6rem;">
                <button type="submit" class="btn btn-primary" style="width:100%;">Créer mon compte</button>
            </div>
        </form>

        <p style="text-align:center; margin-top:0.7rem;color:#666;">Déjà un compte ? <a href="<?= BASE_PATH ?>/index.php?route=auth/login" style="color:#1488ff;font-weight:700;">Se connecter</a></p>
    </div>

    <script>
    function toggleRoleFields() {
        const roleInput = document.querySelector('input[name="role"]:checked');
        const role = roleInput ? roleInput.value : 'sportif';

        const coachFields = document.getElementById('coach-fields');
        const sportifFields = document.getElementById('sportif-fields');

        const specialite = document.querySelector('[name="specialite"]');
        const tarif = document.querySelector('[name="tarif"]');

        if (role === 'coach') {
            coachFields.style.display = 'block';
            sportifFields.style.display = 'none';
            if (specialite) specialite.required = true;
            if (tarif) tarif.required = true;
        } else {
            coachFields.style.display = 'none';
            sportifFields.style.display = 'block';
            if (specialite) specialite.required = false;
            if (tarif) tarif.required = false;
        }
    }

    document.addEventListener('DOMContentLoaded', toggleRoleFields);
    </script>
</body>
</html>
