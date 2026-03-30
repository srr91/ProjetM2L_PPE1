<?php
require_once __DIR__ . '/../../configuration/database.php';
require_once __DIR__ . '/../../configuration/session.php';
requireRole('sportif');

$conn = getConnection();
$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Récupérer les infos
$stmt = $conn->prepare("
    SELECT u.*, ps.*
    FROM utilisateurs u
    LEFT JOIN profils_sportifs ps ON u.id = ps.user_id
    WHERE u.id = ?
");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Supprimer la photo si demandé
    if (isset($_POST['supprimer_photo'])) {
        try {
            if (!empty($user['photo_profil']) && $user['photo_profil'] !== 'default.jpg' && file_exists('../telechargements/profils/' . $user['photo_profil'])) {
                unlink('../telechargements/profils/' . $user['photo_profil']);
            }

            $stmt = $conn->prepare("UPDATE utilisateurs SET photo_profil = 'default.jpg' WHERE id = ?");
            $stmt->execute([$user_id]);

            $stmt = $conn->prepare("UPDATE profils_sportifs SET photo_profil = NULL WHERE user_id = ?");
            $stmt->execute([$user_id]);
            
            $success = "Photo de profil supprimée avec succès !";
            
            // Recharger les données
            $stmt = $conn->prepare("SELECT u.*, ps.* FROM utilisateurs u LEFT JOIN profils_sportifs ps ON u.id = ps.user_id WHERE u.id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();
        } catch (PDOException $e) {
            $error = "Erreur : Veuillez d'abord exécuter la mise à jour de la base de données. <a href='/SportConnect/utils/executer-update-profils.php' style='color: #FFD700; font-weight: bold;'>Cliquez ici</a>";
        }
    }
    
    // Gestion de la photo de profil
    $photo_profil = $user['photo_profil'] ?? 'default.jpg';
    
    if (isset($_FILES['photo_profil']) && $_FILES['photo_profil']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png'];
        $filename = $_FILES['photo_profil']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed) && $_FILES['photo_profil']['size'] < 5000000) {
            $imgInfo = @getimagesize($_FILES['photo_profil']['tmp_name']);
            if ($imgInfo === false) {
                $ext = '';
            }

            $new_filename = 'profil_' . $user_id . '_' . time() . '.' . $ext;
            $upload_path = '../telechargements/profils/' . $new_filename;
            
            // Créer le dossier si nécessaire
            if (!file_exists('../telechargements/profils')) {
                mkdir('../telechargements/profils', 0777, true);
            }
            
            if (!empty($ext) && move_uploaded_file($_FILES['photo_profil']['tmp_name'], $upload_path)) {
                if (!empty($photo_profil) && $photo_profil !== 'default.jpg' && file_exists('../telechargements/profils/' . $photo_profil)) {
                    unlink('../telechargements/profils/' . $photo_profil);
                }
                $photo_profil = $new_filename;
            }
        }
    }
    
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $email = trim($_POST['email']);
    $telephone = trim($_POST['telephone']);
    $date_naissance = $_POST['date_naissance'] ?? null;
    $sexe = $_POST['sexe'] ?? null;
    $adresse = trim($_POST['adresse']);
    $ville = trim($_POST['ville']);
    $code_postal = trim($_POST['code_postal']);
    
    // Infos sportives
    $sport_pratique = trim($_POST['sport_pratique']);
    $objectifs = trim($_POST['objectifs']);
    $niveau = $_POST['niveau'];
    $frequence_entrainement = $_POST['frequence_entrainement'] ?? null;
    $poids = $_POST['poids'] ?? null;
    $taille = $_POST['taille'] ?? null;
    $blessures = trim($_POST['blessures']);
    $disponibilites = trim($_POST['disponibilites']);
    
    try {
        // Mise à jour utilisateur
        $stmt = $conn->prepare("UPDATE utilisateurs SET nom = ?, prenom = ?, email = ?, telephone = ?, photo_profil = ? WHERE id = ?");
        $stmt->execute([$nom, $prenom, $email, $telephone, $photo_profil, $user_id]);
        
        // Mise à jour profil sportif
        $stmt = $conn->prepare("
            UPDATE profils_sportifs 
            SET photo_profil = ?, date_naissance = ?, sexe = ?, adresse = ?, ville = ?, code_postal = ?,
                age = YEAR(CURDATE()) - YEAR(?), sport_pratique = ?, objectifs = ?, niveau = ?, 
                frequence_entrainement = ?, poids = ?, taille = ?, blessures = ?, disponibilites = ?
            WHERE user_id = ?
        ");
        $stmt->execute([
            $photo_profil, $date_naissance, $sexe, $adresse, $ville, $code_postal,
            $date_naissance, $sport_pratique, $objectifs, $niveau,
            $frequence_entrainement, $poids, $taille, $blessures, $disponibilites,
            $user_id
        ]);
        
        $success = "Profil mis à jour avec succès !";
        
        // Recharger les données
        $stmt = $conn->prepare("
            SELECT u.*, ps.*
            FROM utilisateurs u
            LEFT JOIN profils_sportifs ps ON u.id = ps.user_id
            WHERE u.id = ?
        ");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
    } catch (PDOException $e) {
        $error = "Erreur : Veuillez d'abord exécuter la mise à jour de la base de données. <a href='/SportConnect/utils/executer-update-profils.php' style='color: #FFD700; font-weight: bold;'>Cliquez ici pour mettre à jour</a>";
    }
}

$titre = 'Mon profil - SportConnect';
require_once __DIR__ . '/../vues/mise-en-page/entete-sous-dossier.php';
?>

<div class="container" style="max-width: 1000px; margin-top: 2rem;">
    <?php
    $bannerFile = $user['banniere_profil'] ?? null;
    $bannerFileSafe = is_string($bannerFile) ? basename($bannerFile) : '';
    $bannerFileOk = (!empty($bannerFileSafe) && preg_match('/^[a-zA-Z0-9._-]+$/', $bannerFileSafe));
    $hasBanner = $bannerFileOk && file_exists(__DIR__ . '/../telechargements/banners/' . $bannerFileSafe);
    $bannerClass = $hasBanner ? 'sc-profile-banner--image' : 'sc-profile-banner--g-default';
    $bannerStyle = '';
    if ($hasBanner) {
        $bannerStyle = "--sc-banner-url: url(/SportConnect/telechargements/banners/" . rawurlencode($bannerFileSafe) . ");";
    }
    ?>

    <div class="sc-profile-banner <?= $bannerClass ?>" id="scBanner" style="<?= $bannerStyle ?>">
        <div class="sc-banner-actions">
            <button type="button" class="sc-banner-btn" id="scBannerEditBtn">📷 Modifier</button>
            <button type="button" class="sc-banner-btn" id="scBannerDeleteBtn">🗑️</button>
        </div>

        <div class="sc-profile-banner__avatar">
            <?php if (!empty($user['photo_profil']) && $user['photo_profil'] !== 'default.jpg' && file_exists('../telechargements/profils/' . $user['photo_profil'])): ?>
                <img src="../telechargements/profils/<?= htmlspecialchars($user['photo_profil']) ?>" alt="">
            <?php else: ?>
                <?= strtoupper(substr($user['prenom'] ?? 'S', 0, 1)) ?>
            <?php endif; ?>
        </div>

        <div class="sc-profile-banner__content">
            <div class="sc-profile-banner__name"><?= htmlspecialchars(($user['prenom'] ?? '') . ' ' . ($user['nom'] ?? '')) ?></div>
            <div class="sc-profile-banner__subtitle">Sportif</div>
        </div>
    </div>

    <div id="scBannerMsg" style="margin-top: 1rem;"></div>

    <h1 style="margin-bottom: 2rem;">👤 Mon profil</h1>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

<div class="sc-modal-backdrop" id="scBannerModal" aria-hidden="true">
    <div class="sc-modal" role="dialog" aria-modal="true">
        <div class="sc-modal__header">
            <div class="sc-modal__title">Modifier la bannière</div>
            <button type="button" class="sc-banner-btn" id="scBannerCloseBtn">✕</button>
        </div>
        <div class="sc-modal__body">
            <div class="sc-banner-preview" id="scBannerPreview">
                <div class="sc-banner-preview__img" id="scBannerPreviewImg"></div>
                <div class="sc-banner-preview__overlay"></div>
            </div>

            <div class="sc-inline-row">
                <label class="btn btn-outline" style="cursor: pointer; display: inline-block;">
                    Parcourir
                    <input type="file" id="scBannerFile" accept=".jpg,.jpeg,.png,.webp" style="display:none;">
                </label>

                <div class="sc-banner-loader" id="scBannerLoader">
                    <span class="sc-spinner"></span>
                    <span>Upload en cours...</span>
                </div>
            </div>

            <div class="sc-banner-help">
                Formats acceptés : JPG, PNG, WebP. Taille max : 10MB. Recommandé : 1200×400.
            </div>
        </div>
        <div class="sc-modal__footer">
            <button type="button" class="btn btn-outline" id="scBannerCancelBtn">Annuler</button>
            <button type="button" class="btn btn-primary" id="scBannerSaveBtn">Enregistrer</button>
        </div>
    </div>
</div>

    <form method="POST" enctype="multipart/form-data">
        <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 2rem;">
            <!-- Colonne gauche : Photo de profil -->
            <div>
                <div class="card" style="text-align: center;">
                    <h3 style="margin-bottom: 1.5rem;">Photo de profil</h3>
                    
                    <div style="margin-bottom: 1.5rem;">
                        <?php if (!empty($user['photo_profil']) && $user['photo_profil'] !== 'default.jpg' && file_exists('../telechargements/profils/' . $user['photo_profil'])): ?>
                            <img src="../telechargements/profils/<?= htmlspecialchars($user['photo_profil']) ?>" 
                                 alt="Photo de profil" 
                                 style="width: 200px; height: 200px; border-radius: 50%; object-fit: cover; border: 4px solid #FFD700; box-shadow: 0 4px 15px rgba(0,0,0,0.2);">
                        <?php else: ?>
                            <div style="width: 200px; height: 200px; border-radius: 50%; background: linear-gradient(135deg, #1E1E1E 0%, #2C2C2C 100%); display: flex; align-items: center; justify-content: center; margin: 0 auto; border: 4px solid #FFD700; box-shadow: 0 4px 15px rgba(0,0,0,0.2);">
                                <span style="font-size: 5rem; color: #FFD700;">
                                    <?= strtoupper(substr($user['prenom'], 0, 1)) ?>
                                </span>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label class="btn btn-outline" style="cursor: pointer; display: inline-block; width: 100%;">
                            📷 Changer la photo
                            <input type="file" name="photo_profil" accept="image/*" style="display: none;" onchange="previewImage(this)">
                        </label>
                        
                        <?php if (!empty($user['photo_profil']) && $user['photo_profil'] !== 'default.jpg' && file_exists('../telechargements/profils/' . $user['photo_profil'])): ?>
                            <button type="submit" name="supprimer_photo" class="btn" style="width: 100%; background: #DC3545; color: white; padding: 0.75rem; margin-top: 0.5rem;" onclick="return confirm('Êtes-vous sûr de vouloir supprimer votre photo de profil ?');">
                                🗑️ Supprimer la photo
                            </button>
                        <?php endif; ?>
                        
                        <p style="font-size: 0.85rem; color: var(--gray); margin-top: 0.5rem; text-align: center;">
                            JPG ou PNG (max 5MB)
                        </p>
                    </div>
                </div>
            </div>

            <!-- Colonne droite : Formulaire -->
            <div>
                <!-- Informations personnelles -->
                <div class="card" style="margin-bottom: 2rem;">
                    <h2 style="margin-bottom: 1.5rem; border-bottom: 2px solid #FFD700; padding-bottom: 0.5rem;">
                        📋 Informations personnelles
                    </h2>
                    
                    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem;">
                        <div class="form-group">
                            <label class="form-label">Prénom *</label>
                            <input type="text" name="prenom" class="form-control" required value="<?= htmlspecialchars($user['prenom']) ?>">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Nom *</label>
                            <input type="text" name="nom" class="form-control" required value="<?= htmlspecialchars($user['nom']) ?>">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Date de naissance</label>
                            <input type="date" name="date_naissance" class="form-control" value="<?= htmlspecialchars($user['date_naissance'] ?? '') ?>">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Sexe</label>
                            <select name="sexe" class="form-control">
                                <option value="">-- Sélectionner --</option>
                                <option value="M" <?= ($user['sexe'] ?? '') === 'M' ? 'selected' : '' ?>>Homme</option>
                                <option value="F" <?= ($user['sexe'] ?? '') === 'F' ? 'selected' : '' ?>>Femme</option>
                                <option value="A" <?= ($user['sexe'] ?? '') === 'A' ? 'selected' : '' ?>>Autre</option>
                            </select>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem;">
                        <div class="form-group">
                            <label class="form-label">Email *</label>
                            <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($user['email']) ?>">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Téléphone</label>
                            <input type="tel" name="telephone" class="form-control" value="<?= htmlspecialchars($user['telephone'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Adresse</label>
                        <input type="text" name="adresse" class="form-control" placeholder="Numéro et nom de rue" value="<?= htmlspecialchars($user['adresse'] ?? '') ?>">
                    </div>

                    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label class="form-label">Ville</label>
                            <input type="text" name="ville" class="form-control" value="<?= htmlspecialchars($user['ville'] ?? '') ?>">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Code postal</label>
                            <input type="text" name="code_postal" class="form-control" maxlength="5" value="<?= htmlspecialchars($user['code_postal'] ?? '') ?>">
                        </div>
                    </div>
                </div>

                <!-- Informations sportives -->
                <div class="card" style="margin-bottom: 2rem;">
                    <h2 style="margin-bottom: 1.5rem; border-bottom: 2px solid #FFD700; padding-bottom: 0.5rem;">
                        💪 Informations sportives
                    </h2>
                    
                    <div class="form-group">
                        <label class="form-label">Sport(s) pratiqué(s)</label>
                        <input type="text" name="sport_pratique" class="form-control" placeholder="Ex: Musculation, Course à pied, Yoga..." value="<?= htmlspecialchars($user['sport_pratique'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Niveau</label>
                        <select name="niveau" class="form-control">
                            <option value="">-- Sélectionner --</option>
                            <option value="debutant" <?= ($user['niveau'] ?? '') === 'debutant' ? 'selected' : '' ?>>Débutant</option>
                            <option value="intermediaire" <?= ($user['niveau'] ?? '') === 'intermediaire' ? 'selected' : '' ?>>Intermédiaire</option>
                            <option value="avance" <?= ($user['niveau'] ?? '') === 'avance' ? 'selected' : '' ?>>Avancé</option>
                            <option value="expert" <?= ($user['niveau'] ?? '') === 'expert' ? 'selected' : '' ?>>Expert</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Fréquence d'entraînement</label>
                        <select name="frequence_entrainement" class="form-control">
                            <option value="">-- Sélectionner --</option>
                            <option value="1-2" <?= ($user['frequence_entrainement'] ?? '') === '1-2' ? 'selected' : '' ?>>1-2 fois/semaine</option>
                            <option value="3-4" <?= ($user['frequence_entrainement'] ?? '') === '3-4' ? 'selected' : '' ?>>3-4 fois/semaine</option>
                            <option value="5+" <?= ($user['frequence_entrainement'] ?? '') === '5+' ? 'selected' : '' ?>>5+ fois/semaine</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Objectifs</label>
                        <textarea name="objectifs" class="form-control" rows="3" placeholder="Ex: Perdre du poids, Gagner en muscle, Améliorer mon endurance..."><?= htmlspecialchars($user['objectifs'] ?? '') ?></textarea>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div class="form-group">
                            <label class="form-label">Poids (kg)</label>
                            <input type="number" name="poids" class="form-control" step="0.1" min="0" value="<?= htmlspecialchars($user['poids'] ?? '') ?>">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Taille (cm)</label>
                            <input type="number" name="taille" class="form-control" min="0" value="<?= htmlspecialchars($user['taille'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Blessures ou limitations</label>
                        <textarea name="blessures" class="form-control" rows="2" placeholder="Mentionnez toute blessure ou limitation physique..."><?= htmlspecialchars($user['blessures'] ?? '') ?></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Disponibilités</label>
                        <textarea name="disponibilites" class="form-control" rows="2" placeholder="Ex: Lundi-Mercredi soir, Weekend matin..."><?= htmlspecialchars($user['disponibilites'] ?? '') ?></textarea>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1rem; font-size: 1.1rem;">
                    Enregistrer les modifications
                </button>
            </div>
        </div>
    </form>
</div>

<script>
function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const img = input.closest('.card').querySelector('img, div[style*="border-radius: 50%"]');
            if (img.tagName === 'IMG') {
                img.src = e.target.result;
            } else {
                img.outerHTML = '<img src="' + e.target.result + '" alt="Photo de profil" style="width: 200px; height: 200px; border-radius: 50%; object-fit: cover; border: 4px solid #FFD700; box-shadow: 0 4px 15px rgba(0,0,0,0.2);">';
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<script>
(function() {
    const banner = document.getElementById('scBanner');
    const msg = document.getElementById('scBannerMsg');
    const modal = document.getElementById('scBannerModal');
    const editBtn = document.getElementById('scBannerEditBtn');
    const delBtn = document.getElementById('scBannerDeleteBtn');
    const closeBtn = document.getElementById('scBannerCloseBtn');
    const cancelBtn = document.getElementById('scBannerCancelBtn');
    const saveBtn = document.getElementById('scBannerSaveBtn');
    const fileInput = document.getElementById('scBannerFile');
    const loader = document.getElementById('scBannerLoader');
    const prev = document.getElementById('scBannerPreview');
    const prevImg = document.getElementById('scBannerPreviewImg');

    if (!banner || !modal) return;

    let selectedFile = null;
    let previewUrl = '';

    function setMsg(type, text) {
        msg.innerHTML = text ? ('<div class="alert alert-' + type + '">' + text + '</div>') : '';
    }

    function openModal() {
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        const bg = window.getComputedStyle(banner).backgroundImage;
        prevImg.style.backgroundImage = bg;
    }

    function closeModal() {
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        if (previewUrl) {
            URL.revokeObjectURL(previewUrl);
            previewUrl = '';
        }
        selectedFile = null;
        fileInput.value = '';
        loader.classList.remove('is-on');
        saveBtn.disabled = false;
    }

    editBtn.addEventListener('click', openModal);
    closeBtn.addEventListener('click', closeModal);
    cancelBtn.addEventListener('click', closeModal);
    modal.addEventListener('click', function(e) {
        if (e.target === modal) closeModal();
    });

    fileInput.addEventListener('change', function() {
        if (!fileInput.files || !fileInput.files[0]) return;
        selectedFile = fileInput.files[0];
        if (previewUrl) URL.revokeObjectURL(previewUrl);
        previewUrl = URL.createObjectURL(selectedFile);
        prevImg.style.backgroundImage = 'url(' + JSON.stringify(previewUrl).slice(1, -1) + ')';
    });

    async function postForm(url, formData) {
        const res = await fetch(url, { method: 'POST', body: formData, credentials: 'same-origin' });
        let data = null;
        try { data = await res.json(); } catch (e) {}
        if (!res.ok || !data) throw new Error('request');
        return data;
    }

    function applyBannerState(data) {
        const file = data.banniere_profil || null;

        const g = ['default','violet','cyan','sunset','emerald','midnight'];

        banner.classList.remove('sc-profile-banner--image');
        for (let i = 0; i < g.length; i++) {
            banner.classList.remove('sc-profile-banner--g-' + g[i]);
        }

        if (file) {
            banner.classList.add('sc-profile-banner--image');
            banner.style.setProperty('--sc-banner-url', "url('/SportConnect/telechargements/banners/" + file + "')");
        } else {
            banner.style.removeProperty('--sc-banner-url');
            banner.classList.add('sc-profile-banner--g-default');
        }
    }

    saveBtn.addEventListener('click', async function() {
        setMsg('', '');

        if (!selectedFile) {
            setMsg('danger', 'Choisissez une image.');
            return;
        }

        loader.classList.add('is-on');
        saveBtn.disabled = true;
        try {
            const fd = new FormData();
            fd.append('banniere', selectedFile);
            const data = await postForm('/SportConnect/api/mise-a-jour-banniere.php', fd);
            if (!data.success) {
                throw new Error(data.error || 'Erreur');
            }
            applyBannerState(data);
            setMsg('success', 'Bannière mise à jour avec succès !');
            closeModal();
        } catch (e) {
            setMsg('danger', (e && e.message) ? e.message : "Erreur lors de l'upload, réessayez");
            loader.classList.remove('is-on');
            saveBtn.disabled = false;
        }
    });

    delBtn.addEventListener('click', async function() {
        if (!confirm('Supprimer la bannière ?')) return;
        setMsg('', '');
        try {
            const fd = new FormData();
            const data = await postForm('/SportConnect/api/suppression-banniere.php', fd);
            if (!data.success) {
                throw new Error(data.error || 'Erreur');
            }
            applyBannerState({ banniere_profil: null });
            setMsg('success', 'Bannière supprimée.');
        } catch (e) {
            setMsg('danger', (e && e.message) ? e.message : "Erreur serveur, réessayez");
        }
    });
})();
</script>

<?php require_once __DIR__ . '/../vues/mise-en-page/pied-de-page-sous-dossier.php'; ?>
