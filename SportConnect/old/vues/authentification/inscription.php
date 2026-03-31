<?php require_once __DIR__ . '/../mise-en-page/entete.php'; ?>

<div class="container" style="max-width: 900px; margin-top: 3rem; margin-bottom: 3rem;">
    <div class="card">
        <div class="card-header" style="text-align: center;">
            <h1 class="card-title" style="font-size: 2rem;">🎯 Créer un compte SportConnect</h1>
            <p style="color: var(--gray); margin-top: 0.5rem;">Rejoignez notre communauté sportive</p>
        </div>

        <?php if (!empty($erreur)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($erreur) ?></div>
        <?php endif; ?>

        <?php if (!empty($succes)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($succes) ?></div>
        <?php endif; ?>

        <form method="POST" action="/SportConnect/authentification/inscription.php">
            <!-- Choix du rôle -->
            <div class="form-group">
                <label class="form-label" style="font-size: 1.1rem; font-weight: 700;">Je suis :</label>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <label style="cursor: pointer;">
                        <input type="radio" name="role" value="sportif" checked required onchange="toggleCoachFields()" style="display: none;">
                        <div class="role-card" id="sportif-card" style="border: 3px solid #FFD700; background: rgba(255, 215, 0, 0.1); text-align: center; padding: 1.5rem; transition: all 0.3s; border-radius: 8px;">
                            <div style="font-size: 3rem; margin-bottom: 0.5rem;">🏃</div>
                            <strong style="font-size: 1.2rem;">Sportif</strong>
                            <p style="color: var(--gray); font-size: 0.9rem; margin-top: 0.5rem;">Je cherche un coach</p>
                        </div>
                    </label>
                    <label style="cursor: pointer;">
                        <input type="radio" name="role" value="coach" required onchange="toggleCoachFields()" style="display: none;">
                        <div class="role-card" id="coach-card" style="border: 3px solid #E8E8E8; background: white; text-align: center; padding: 1.5rem; transition: all 0.3s; border-radius: 8px;">
                            <div style="font-size: 3rem; margin-bottom: 0.5rem;">🧑‍🏫</div>
                            <strong style="font-size: 1.2rem;">Coach</strong>
                            <p style="color: var(--gray); font-size: 0.9rem; margin-top: 0.5rem;">Je propose mes services</p>
                        </div>
                    </label>
                </div>
            </div>

            <hr style="margin: 2rem 0; border: none; border-top: 2px solid #FFD700;">

            <!-- Informations personnelles -->
            <h3 style="margin-bottom: 1.5rem; color: var(--dark);">Informations personnelles</h3>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label class="form-label">Prénom *</label>
                    <input type="text" name="prenom" class="form-control" required 
                           value="<?= htmlspecialchars($_POST['prenom'] ?? '') ?>"
                           placeholder="Votre prénom">
                </div>

                <div class="form-group">
                    <label class="form-label">Nom *</label>
                    <input type="text" name="nom" class="form-control" required 
                           value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>"
                           placeholder="Votre nom">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label class="form-label">Email *</label>
                    <input type="email" name="email" class="form-control" required 
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                           placeholder="votre.email@exemple.fr">
                </div>

                <div class="form-group">
                    <label class="form-label">Téléphone *</label>
                    <input type="tel" name="telephone" class="form-control" required
                           value="<?= htmlspecialchars($_POST['telephone'] ?? '') ?>"
                           placeholder="06 12 34 56 78">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label class="form-label">Date de naissance</label>
                    <input type="date" name="date_naissance" class="form-control"
                           value="<?= htmlspecialchars($_POST['date_naissance'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label class="form-label">Sexe</label>
                    <select name="sexe" class="form-control">
                        <option value="">-- Sélectionner --</option>
                        <option value="M">Homme</option>
                        <option value="F">Femme</option>
                        <option value="A">Autre</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Adresse</label>
                <input type="text" name="adresse" class="form-control" 
                       value="<?= htmlspecialchars($_POST['adresse'] ?? '') ?>"
                       placeholder="Numéro et nom de rue">
            </div>

            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label class="form-label">Ville</label>
                    <input type="text" name="ville" class="form-control" 
                           value="<?= htmlspecialchars($_POST['ville'] ?? '') ?>"
                           placeholder="Votre ville">
                </div>

                <div class="form-group">
                    <label class="form-label">Code postal</label>
                    <input type="text" name="code_postal" class="form-control" maxlength="5"
                           value="<?= htmlspecialchars($_POST['code_postal'] ?? '') ?>"
                           placeholder="75000">
                </div>
            </div>

            <!-- Champs spécifiques sportif -->
            <div id="sportif-fields">
                <hr style="margin: 2rem 0; border: none; border-top: 2px solid #FFD700;">
                <h3 style="margin-bottom: 1.5rem; color: var(--dark);">Informations sportives</h3>
                
                <div class="form-group">
                    <label class="form-label">Sport(s) pratiqué(s)</label>
                    <input type="text" name="sport_pratique" class="form-control" 
                           placeholder="Ex: Musculation, Course à pied, Yoga...">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label">Niveau</label>
                        <select name="niveau" class="form-control">
                            <option value="">-- Sélectionner --</option>
                            <option value="debutant">Débutant</option>
                            <option value="intermediaire">Intermédiaire</option>
                            <option value="avance">Avancé</option>
                            <option value="expert">Expert</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Fréquence d'entraînement</label>
                        <select name="frequence_entrainement" class="form-control">
                            <option value="">-- Sélectionner --</option>
                            <option value="1-2">1-2 fois/semaine</option>
                            <option value="3-4">3-4 fois/semaine</option>
                            <option value="5+">5+ fois/semaine</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Objectifs</label>
                    <textarea name="objectifs" class="form-control" rows="2" 
                              placeholder="Ex: Perdre du poids, Gagner en muscle, Améliorer mon endurance..."></textarea>
                </div>
            </div>

            <!-- Champs spécifiques coach -->
            <div id="coach-fields" style="display: none;">
                <hr style="margin: 2rem 0; border: none; border-top: 2px solid #FFD700;">
                <h3 style="margin-bottom: 1.5rem; color: var(--dark);">🏋️ Informations professionnelles</h3>
                
                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label">Spécialité *</label>
                        <select name="specialite" class="form-control">
                            <option value="">-- Sélectionnez --</option>
                            <option value="Musculation">Musculation</option>
                            <option value="Yoga">Yoga</option>
                            <option value="Running">Running / Course à pied</option>
                            <option value="Fitness">Fitness</option>
                            <option value="Boxe">Boxe</option>
                            <option value="Natation">Natation</option>
                            <option value="Crossfit">Crossfit</option>
                            <option value="Pilates">Pilates</option>
                            <option value="Nutrition">Nutrition sportive</option>
                            <option value="Preparation physique">Préparation physique</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Tarif horaire (€) *</label>
                        <input type="number" name="tarif" class="form-control" min="0" step="5" 
                               placeholder="Ex: 45">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Diplômes et certifications</label>
                    <textarea name="diplomes" class="form-control" rows="2" 
                              placeholder="Ex: BPJEPS, CQP, Licence STAPS..."></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Expérience (années)</label>
                    <input type="number" name="experience" class="form-control" min="0" 
                           placeholder="Nombre d'années d'expérience">
                </div>

                <div class="form-group">
                    <label class="form-label">Description de vos services</label>
                    <textarea name="description" class="form-control" rows="3" 
                              placeholder="Présentez votre approche, votre méthode d'entraînement..."></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Localisation</label>
                    <input type="text" name="localisation" class="form-control" 
                           placeholder="Ex: Paris 15ème, Lyon, Marseille...">
                </div>
            </div>

            <hr style="margin: 2rem 0; border: none; border-top: 2px solid #FFD700;">

            <!-- Mot de passe -->
            <h3 style="margin-bottom: 1.5rem; color: var(--dark);">Sécurité</h3>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label class="form-label">Mot de passe *</label>
                    <input type="password" name="password" class="form-control" required minlength="6"
                           placeholder="Au moins 6 caractères">
                    <small style="color: var(--gray);">Minimum 6 caractères</small>
                </div>

                <div class="form-group">
                    <label class="form-label">Confirmer le mot de passe *</label>
                    <input type="password" name="confirm_password" class="form-control" required
                           placeholder="Confirmez votre mot de passe">
                </div>
            </div>

            <div class="form-group" style="margin-top: 1.5rem;">
                <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                    <input type="checkbox" required>
                    <span>J'accepte les <a href="/SportConnect/conditions-utilisation.php" target="_blank" style="color: var(--primary);">conditions d'utilisation</a> et la politique de confidentialité</span>
                </label>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1.2rem; font-size: 1.2rem; margin-top: 1rem;">
                Créer mon compte
            </button>
        </form>

        <p style="text-align: center; margin-top: 2rem; padding-top: 2rem; border-top: 1px solid var(--border); color: var(--gray);">
            Déjà un compte ? 
            <a href="/SportConnect/authentification/connexion.php" style="color: var(--primary); font-weight: 700; text-decoration: none;">
                Se connecter →
            </a>
        </p>
    </div>
</div>

<script>
function toggleCoachFields() {
    const role = document.querySelector('input[name="role"]:checked').value;
    const coachFields = document.getElementById('coach-fields');
    const sportifFields = document.getElementById('sportif-fields');
    const sportifCard = document.getElementById('sportif-card');
    const coachCard = document.getElementById('coach-card');
    
    if (role === 'coach') {
        // Afficher champs coach, masquer sportif
        coachFields.style.display = 'block';
        sportifFields.style.display = 'none';
        
        // Style des cartes
        coachCard.style.border = '3px solid #FFD700';
        coachCard.style.background = 'rgba(255, 215, 0, 0.1)';
        sportifCard.style.border = '3px solid #E8E8E8';
        sportifCard.style.background = 'white';
        
        // Rendre les champs coach obligatoires
        document.querySelector('[name="specialite"]').required = true;
        document.querySelector('[name="tarif"]').required = true;
    } else {
        // Afficher champs sportif, masquer coach
        coachFields.style.display = 'none';
        sportifFields.style.display = 'block';
        
        // Style des cartes
        sportifCard.style.border = '3px solid #FFD700';
        sportifCard.style.background = 'rgba(255, 215, 0, 0.1)';
        coachCard.style.border = '3px solid #E8E8E8';
        coachCard.style.background = 'white';
        
        // Retirer l'obligation des champs coach
        document.querySelector('[name="specialite"]').required = false;
        document.querySelector('[name="tarif"]').required = false;
    }
}

// Initialiser au chargement
document.addEventListener('DOMContentLoaded', function() {
    toggleCoachFields();
    
    // Ajouter les événements sur les cartes
    document.querySelectorAll('.role-card').forEach(card => {
        card.addEventListener('click', function() {
            const input = this.previousElementSibling || this.querySelector('input');
            if (input) {
                input.checked = true;
                toggleCoachFields();
            }
        });
    });
});
</script>

<style>
.role-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
}
</style>

<?php require_once __DIR__ . '/../mise-en-page/pied-de-page.php'; ?>
