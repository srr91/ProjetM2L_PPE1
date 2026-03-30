<?php
require_once __DIR__ . '/../configuration/database.php';
require_once __DIR__ . '/../configuration/session.php';

requireLogin();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

$conn = getConnection();
$user_id = (int)$_SESSION['user_id'];

if (ini_get('file_uploads') === '0') {
    echo json_encode(['success' => false, 'error' => "Les uploads sont désactivés côté serveur (file_uploads=Off)."]);
    exit();
}

if (!extension_loaded('gd') || !function_exists('imagecreatetruecolor') || !function_exists('imagejpeg')) {
    echo json_encode(['success' => false, 'error' => "Le module GD n'est pas activé sur le serveur PHP (extension=gd)."]);
    exit();
}

function scUploadErrorMessage(int $code): string {
    $uploadMax = ini_get('upload_max_filesize');
    $postMax = ini_get('post_max_size');
    switch ($code) {
        case UPLOAD_ERR_INI_SIZE:
            return "Fichier trop volumineux (limite serveur upload_max_filesize={$uploadMax}, post_max_size={$postMax}).";
        case UPLOAD_ERR_FORM_SIZE:
            return "Fichier trop volumineux (limite du formulaire).";
        case UPLOAD_ERR_PARTIAL:
            return "Upload incomplet. Réessayez.";
        case UPLOAD_ERR_NO_FILE:
            return "Aucun fichier reçu.";
        case UPLOAD_ERR_NO_TMP_DIR:
            return "Dossier temporaire manquant sur le serveur (UPLOAD_ERR_NO_TMP_DIR).";
        case UPLOAD_ERR_CANT_WRITE:
            return "Impossible d'écrire le fichier sur le disque (UPLOAD_ERR_CANT_WRITE).";
        case UPLOAD_ERR_EXTENSION:
            return "Upload bloqué par une extension PHP (UPLOAD_ERR_EXTENSION).";
        default:
            return "Erreur lors de l'upload (code {$code}).";
    }
}

$hasFile = isset($_FILES['banniere']) && is_array($_FILES['banniere']) && ($_FILES['banniere']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;

$uploadDir = __DIR__ . '/../telechargements/banners';
$origDir = __DIR__ . '/../telechargements/banners/originals';
if (!file_exists($uploadDir)) {
    @mkdir($uploadDir, 0777, true);
}
if (!file_exists($origDir)) {
    @mkdir($origDir, 0777, true);
}

if (!is_dir($uploadDir) || !is_writable($uploadDir)) {
    error_log('[banner_update] uploadDir not writable: ' . $uploadDir);
    echo json_encode(['success' => false, 'error' => "Le serveur ne peut pas écrire dans le dossier des bannières : {$uploadDir}."]);
    exit();
}

if (!is_dir($origDir) || !is_writable($origDir)) {
    error_log('[banner_update] origDir not writable: ' . $origDir);
    echo json_encode(['success' => false, 'error' => "Le serveur ne peut pas écrire dans le dossier originals : {$origDir}."]);
    exit();
}

try {
    $stmt = $conn->prepare("SELECT banniere_profil FROM utilisateurs WHERE id = ?");
    $stmt->execute([$user_id]);
    $current = $stmt->fetch();
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => "Veuillez mettre à jour la base de données (colonnes bannière manquantes)."]);
    exit();
}

$currentBanner = $current['banniere_profil'] ?? null;

$newBanner = $currentBanner;

if ($hasFile) {
    $err = (int)($_FILES['banniere']['error'] ?? UPLOAD_ERR_OK);
    if ($err !== UPLOAD_ERR_OK) {
        $msg = scUploadErrorMessage($err);
        error_log('[banner_update] upload error: ' . $msg);
        echo json_encode(['success' => false, 'error' => $msg]);
        exit();
    }

    $maxBytes = 10 * 1024 * 1024;
    if ((int)$_FILES['banniere']['size'] > $maxBytes) {
        echo json_encode(['success' => false, 'error' => 'Fichier trop volumineux (max 10MB)']);
        exit();
    }

    $filename = (string)($_FILES['banniere']['name'] ?? '');
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    $allowedExt = ['jpg', 'jpeg', 'png', 'webp'];
    if (!in_array($ext, $allowedExt, true)) {
        echo json_encode(['success' => false, 'error' => 'Format non accepté (JPG, PNG, WebP)']);
        exit();
    }

    $tmp = (string)($_FILES['banniere']['tmp_name'] ?? '');
    if (empty($tmp) || !file_exists($tmp)) {
        error_log('[banner_update] tmp file missing: ' . $tmp);
        echo json_encode(['success' => false, 'error' => "Fichier temporaire introuvable. Réessayez."]);
        exit();
    }

    $imgInfo = @getimagesize($tmp);
    if ($imgInfo === false) {
        echo json_encode(['success' => false, 'error' => 'Fichier image invalide']);
        exit();
    }

    $srcW = (int)$imgInfo[0];
    $srcH = (int)$imgInfo[1];

    $srcImg = null;
    if ($ext === 'jpg' || $ext === 'jpeg') {
        $srcImg = @imagecreatefromjpeg($tmp);
    } elseif ($ext === 'png') {
        $srcImg = @imagecreatefrompng($tmp);
    } elseif ($ext === 'webp') {
        if (!function_exists('imagecreatefromwebp')) {
            echo json_encode(['success' => false, 'error' => 'WebP non supporté sur ce serveur']);
            exit();
        }
        $srcImg = @imagecreatefromwebp($tmp);
    }

    if (!$srcImg) {
        error_log('[banner_update] imagecreatefrom* failed for ext=' . $ext);
        echo json_encode(['success' => false, 'error' => 'Impossible de lire l\'image']);
        exit();
    }

    $targetW = 1200;
    $targetH = 400;
    $targetRatio = $targetW / $targetH;

    $cropW = $srcW;
    $cropH = (int)round($cropW / $targetRatio);
    if ($cropH > $srcH) {
        $cropH = $srcH;
        $cropW = (int)round($cropH * $targetRatio);
    }

    $cropX = (int)round(($srcW - $cropW) / 2);
    $cropY = (int)round(($srcH - $cropH) / 2);

    $dst = imagecreatetruecolor($targetW, $targetH);
    imagefill($dst, 0, 0, imagecolorallocate($dst, 0, 0, 0));
    imagecopyresampled($dst, $srcImg, 0, 0, $cropX, $cropY, $targetW, $targetH, $cropW, $cropH);

    $thumbW = 480;
    $thumbH = 160;
    $thumb = imagecreatetruecolor($thumbW, $thumbH);
    imagefill($thumb, 0, 0, imagecolorallocate($thumb, 0, 0, 0));
    imagecopyresampled($thumb, $dst, 0, 0, 0, 0, $thumbW, $thumbH, $targetW, $targetH);

    $base = 'banner_' . $user_id . '_' . time();
    $mainName = $base . '.jpg';
    $thumbName = $base . '_thumb.jpg';
    $origName = $base . '_orig.' . $ext;

    $mainPath = $uploadDir . '/' . $mainName;
    $thumbPath = $uploadDir . '/' . $thumbName;
    $origPath = $origDir . '/' . $origName;

    if (!@move_uploaded_file($tmp, $origPath)) {
        if (!@copy($tmp, $origPath)) {
            error_log('[banner_update] cannot persist original to: ' . $origPath);
        }
    }

    $okMain = imagejpeg($dst, $mainPath, 82);
    $okThumb = imagejpeg($thumb, $thumbPath, 80);
    if (!$okMain || !file_exists($mainPath)) {
        error_log('[banner_update] imagejpeg main failed: ' . $mainPath);
        echo json_encode(['success' => false, 'error' => "Impossible d'écrire l'image sur le serveur."]);
        exit();
    }
    if (!$okThumb || !file_exists($thumbPath)) {
        error_log('[banner_update] imagejpeg thumb failed: ' . $thumbPath);
    }

    imagedestroy($thumb);
    imagedestroy($dst);
    imagedestroy($srcImg);

    if (!empty($currentBanner)) {
        $oldMain = $uploadDir . '/' . $currentBanner;
        $oldThumb = $uploadDir . '/' . preg_replace('/\.jpg$/', '_thumb.jpg', $currentBanner);
        if (file_exists($oldMain)) @unlink($oldMain);
        if (file_exists($oldThumb)) @unlink($oldThumb);

        $baseOld = preg_replace('/\.jpg$/', '', $currentBanner);
        if (!empty($baseOld)) {
            $candidates = glob($origDir . '/' . $baseOld . '_orig.*');
            if (is_array($candidates)) {
                foreach ($candidates as $p) {
                    if (is_string($p) && file_exists($p)) {
                        @unlink($p);
                    }
                }
            }
        }
    }

    $newBanner = $mainName;
}

try {
    $stmt = $conn->prepare("UPDATE utilisateurs SET banniere_profil = ? WHERE id = ?");
    $stmt->execute([$newBanner, $user_id]);

    $stmt = $conn->prepare("SELECT banniere_profil FROM utilisateurs WHERE id = ?");
    $stmt->execute([$user_id]);
    $row = $stmt->fetch();

    echo json_encode([
        'success' => true,
        'banniere_profil' => $row['banniere_profil'] ?? null,
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => "Erreur serveur : réessayez."]);
}
