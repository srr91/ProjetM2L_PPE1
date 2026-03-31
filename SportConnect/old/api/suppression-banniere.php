<?php
require_once __DIR__ . '/../configuration/database.php';
require_once __DIR__ . '/../configuration/session.php';

requireLogin();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

$conn = getConnection();
$user_id = (int)$_SESSION['user_id'];

$uploadDir = __DIR__ . '/../telechargements/banners';
$origDir = __DIR__ . '/../telechargements/banners/originals';

try {
    $stmt = $conn->prepare("SELECT banniere_profil FROM utilisateurs WHERE id = ?");
    $stmt->execute([$user_id]);
    $row = $stmt->fetch();
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => "Veuillez mettre à jour la base de données (colonnes bannière manquantes)."]);
    exit();
}

$current = $row['banniere_profil'] ?? null;

if (!empty($current)) {
    $mainPath = $uploadDir . '/' . $current;
    $thumbPath = $uploadDir . '/' . preg_replace('/\\.jpg$/', '_thumb.jpg', $current);
    if (file_exists($mainPath)) {
        @unlink($mainPath);
    }
    if (file_exists($thumbPath)) {
        @unlink($thumbPath);
    }

    $base = preg_replace('/\\.jpg$/', '', $current);
    if ($base) {
        $candidates = glob($origDir . '/' . $base . '_orig.*');
        if (is_array($candidates)) {
            foreach ($candidates as $p) {
                if (is_string($p) && file_exists($p)) {
                    @unlink($p);
                }
            }
        }
    }
}

try {
    $stmt = $conn->prepare("UPDATE utilisateurs SET banniere_profil = NULL WHERE id = ?");
    $stmt->execute([$user_id]);

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Erreur serveur : réessayez.']);
}
