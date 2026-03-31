<?php
require_once __DIR__ . '/../../configuration/database.php';
require_once __DIR__ . '/../../configuration/session.php';
requireRole('coach');

$conn = getConnection();
$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Récupérer les infos
$stmt = $conn->prepare("
    SELECT u.*, pc.*
    FROM utilisateurs u
    LEFT JOIN profils_coachs pc ON u.id = pc.user_id
    WHERE u.id = ?
");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $photo_profil = $user['photo_profil'] ?? 'default.jpg';

    if (isset($_POST['supprimer_photo'])) {
        if (!empty($photo_profil) && $photo_profil !== 'default.jpg' && file_exists('../telechargements/profils/' . $photo_profil)) {
            unlink('../telechargements/profils/' . $photo_profil);
        }
        $photo_profil = 'default.jpg';
    }

    if (isset($_FILES['photo_profil']) && $_FILES['photo_profil']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png'];
        $filename = $_FILES['photo_profil']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (in_array($ext, $allowed) && $_FILES['photo_profil']['size'] < 5000000) {
            $imgInfo = @getimagesize($_FILES['photo_profil']['tmp_name']);
            if ($imgInfo !== false) {
                if (!file_exists('../telechargements/profils')) {
                    mkdir('../telechargements/profils', 0777, true);
                }

                $new_filename = 'profil_' . $user_id . '_' . time() . '.' . $ext;
                $upload_path = '../telechargements/profils/' . $new_filename;

                if (move_uploaded_file($_FILES['photo_profil']['tmp_name'], $upload_path)) {
                    if (!empty($photo_profil) && $photo_profil !== 'default.jpg' && file_exists('../telechargements/profils/' . $photo_profil)) {
                        unlink('../telechargements/profils/' . $photo_profil);
                    }
                    $photo_profil = $new_filename;
                }
            }
        }
    }

    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $telephone = trim($_POST['telephone']);
    $specialite = trim($_POST['specialite']);
    $description = trim($_POST['description']);
    $experience = $_POST['experience'] ?? 0;
    $diplomes = trim($_POST['diplomes']);
    $tarif_horaire = $_POST['tarif_horaire'];
    $localisation = trim($_POST['localisation']);
    
    // Mise à jour utilisateur
    $stmt = $conn->prepare("UPDATE utilisateurs SET nom = ?, prenom = ?, telephone = ?, photo_profil = ? WHERE id = ?");
    $stmt->execute([$nom, $prenom, $telephone, $photo_profil, $user_id]);
    
    // Mise à jour profil coach
    $stmt = $conn->prepare("
        UPDATE profils_coachs 
        SET specialite = ?, description = ?, experience = ?, diplomes = ?, 
            tarif_horaire = ?, localisation = ?
        WHERE user_id = ?
    ");
    $stmt->execute([$specialite, $description, $experience, $diplomes, $tarif_horaire, $localisation, $user_id]);
    
    $success = "Profil mis à jour avec succès !";
    
    // Recharger les données
    $stmt = $conn->prepare("
        SELECT u.*, pc.*
        FROM utilisateurs u
        LEFT JOIN profils_coachs pc ON u.id = pc.user_id
        WHERE u.id = ?
    ");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
}

$titre = 'Mon profil coach - SportConnect';
require_once '../vues/mise-en-page/entete-sous-dossier.php';
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
                    <?= strtoupper(substr($user['prenom'] ?? 'C', 0, 1)) ?>
                <?php endif; ?>
            </div>

            <div class="sc-profile-banner__content">
                <div class="sc-profile-banner__name"><?= htmlspecialchars(($user['prenom'] ?? '') . ' ' . ($user['nom'] ?? '')) ?></div>
                <div class="sc-profile-banner__subtitle"><?= htmlspecialchars($user['specialite'] ?? '') ?></div>
            </div>
        </div>

        <div id="scBannerMsg" style="margin-top: 1rem;"></div>

        <h1 style="margin-bottom: 2rem;">Mon profil professionnel</h1>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <?php if (!$user['valide']): ?>
            <div class="alert alert-info">
                ℹ️ Votre profil n'est pas visible pour le moment. Complétez vos informations et réessayez. Si le problème persiste, contactez le support.
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 2rem;">
                <div>
                    <div class="card" style="text-align: center;">
                        <h3 style="margin-bottom: 1.5rem;">Photo de profil</h3>

                        <div style="margin-bottom: 1.5rem;">
                            <?php if (!empty($user['photo_profil']) && $user['photo_profil'] !== 'default.jpg' && file_exists('../telechargements/profils/' . $user['photo_profil'])): ?>
                                <img src="../telechargements/profils/<?= htmlspecialchars($user['photo_profil']) ?>" alt="Photo de profil" style="width: 200px; height: 200px; border-radius: 50%; object-fit: cover; border: 1px solid rgba(255,255,255,0.18); box-shadow: 0 16px 45px rgba(0,0,0,0.45);">
                            <?php else: ?>
                                <div style="width: 200px; height: 200px; border-radius: 50%; background: rgba(255,255,255,0.06); display: flex; align-items: center; justify-content: center; margin: 0 auto; border: 1px solid rgba(255,255,255,0.18); box-shadow: 0 16px 45px rgba(0,0,0,0.45);">
                                    <span style="font-size: 5rem; color: rgba(255,255,255,0.92);">
                                        <?= strtoupper(substr($user['prenom'], 0, 1)) ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label class="btn btn-outline" style="cursor: pointer; display: inline-block; width: 100%;">
                                📷 Changer la photo
                                <input type="file" name="photo_profil" accept=".jpg,.jpeg,.png" style="display: none;" onchange="previewImage(this)">
                            </label>

                            <?php if (!empty($user['photo_profil']) && $user['photo_profil'] !== 'default.jpg' && file_exists('../telechargements/profils/' . $user['photo_profil'])): ?>
                                <button type="submit" name="supprimer_photo" class="btn btn-secondary" style="width: 100%; margin-top: 0.5rem;" onclick="return confirm('Êtes-vous sûr de vouloir supprimer votre photo de profil ?');">
                                    🗑️ Supprimer la photo
                                </button>
                            <?php endif; ?>

                            <p style="font-size: 0.85rem; color: var(--gray); margin-top: 0.5rem; text-align: center;">
                                JPG ou PNG (max 5MB)
                            </p>
                        </div>
                    </div>
                </div>

                <div>
                    <div class="card">
                        <h2 style="margin-bottom: 1.5rem;">Informations personnelles</h2>
                
                <div class="grid grid-2">
                    <div class="form-group">
                        <label class="form-label">Nom</label>
                        <input type="text" name="nom" class="form-control" required value="<?= htmlspecialchars($user['nom']) ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Prénom</label>
                        <input type="text" name="prenom" class="form-control" required value="<?= htmlspecialchars($user['prenom']) ?>">
                    </div>
                </div>

                <div class="grid grid-2">
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" disabled>
                        <small style="color: var(--gray);">L'email ne peut pas être modifié</small>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Téléphone</label>
                        <input type="tel" name="telephone" class="form-control" required value="<?= htmlspecialchars($user['telephone'] ?? '') ?>">
                    </div>
                </div>

                <hr style="margin: 2rem 0; border: none; border-top: 1px solid var(--border);">

                <h2 style="margin-bottom: 1.5rem;">Profil professionnel</h2>

                <div class="grid grid-2">
                    <div class="form-group">
                        <label class="form-label">Spécialité</label>
                        <select name="specialite" class="form-control" required>
                            <option value="">Sélectionnez une spécialité</option>
                            <option value="Musculation" <?= $user['specialite'] === 'Musculation' ? 'selected' : '' ?>>Musculation</option>
                            <option value="Yoga" <?= $user['specialite'] === 'Yoga' ? 'selected' : '' ?>>Yoga</option>
                            <option value="Running" <?= $user['specialite'] === 'Running' ? 'selected' : '' ?>>Running</option>
                            <option value="Fitness" <?= $user['specialite'] === 'Fitness' ? 'selected' : '' ?>>Fitness</option>
                            <option value="Boxe" <?= $user['specialite'] === 'Boxe' ? 'selected' : '' ?>>Boxe</option>
                            <option value="Natation" <?= $user['specialite'] === 'Natation' ? 'selected' : '' ?>>Natation</option>
                            <option value="Crossfit" <?= $user['specialite'] === 'Crossfit' ? 'selected' : '' ?>>Crossfit</option>
                            <option value="Pilates" <?= $user['specialite'] === 'Pilates' ? 'selected' : '' ?>>Pilates</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Expérience (années)</label>
                        <input type="number" name="experience" class="form-control" min="0" required value="<?= htmlspecialchars($user['experience'] ?? 0) ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="4" required placeholder="Présentez-vous et décrivez votre approche..."><?= htmlspecialchars($user['description'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Diplômes et certifications</label>
                    <textarea name="diplomes" class="form-control" rows="3" placeholder="Listez vos diplômes et certifications..."><?= htmlspecialchars($user['diplomes'] ?? '') ?></textarea>
                </div>

                <div class="grid grid-2">
                    <div class="form-group">
                        <label class="form-label">Tarif horaire (€)</label>
                        <input type="number" name="tarif_horaire" class="form-control" min="0" step="5" required value="<?= htmlspecialchars($user['tarif_horaire'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Localisation</label>
                        <input type="text" name="localisation" class="form-control" required placeholder="Ex: Paris 15e" value="<?= htmlspecialchars($user['localisation'] ?? '') ?>">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%;">Enregistrer les modifications</button>
                    </div>
                </div>
            </div>
        </form>

        <div class="card" style="margin-top: 2rem;">
            <h3 style="margin-bottom: 1rem;">Aperçu de votre profil public</h3>
            <a href="../pages/profil-coach.php?id=<?= $user_id ?>" class="btn btn-outline" target="_blank">Voir mon profil tel que les sportifs le voient</a>
        </div>
    </div>

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

<script>
function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const img = input.closest('.card').querySelector('img, div[style*="border-radius: 50%"]');
            if (img.tagName === 'IMG') {
                img.src = e.target.result;
            } else {
                img.outerHTML = '<img src="' + e.target.result + '" alt="Photo de profil" style="width: 200px; height: 200px; border-radius: 50%; object-fit: cover; border: 1px solid rgba(255,255,255,0.18); box-shadow: 0 16px 45px rgba(0,0,0,0.45);">';
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

<?php require_once '../vues/mise-en-page/pied-de-page-sous-dossier.php'; ?>
