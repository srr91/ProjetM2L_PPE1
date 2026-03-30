<?php
require_once __DIR__ . '/../configuration/database.php';
require_once __DIR__ . '/../configuration/session.php';

requireLogin();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

$conn = getConnection();
$user_id = (int)$_SESSION['user_id'];
$with = isset($_POST['with']) ? (int)$_POST['with'] : 0;

if ($with <= 0) {
    echo json_encode(['success' => false, 'error' => 'Paramètre manquant']);
    exit();
}

$stmt = $conn->prepare("UPDATE messages SET lu = 1 WHERE sender_id = ? AND receiver_id = ? AND lu = 0");
$stmt->execute([$with, $user_id]);

$stmt = $conn->prepare("SELECT COUNT(*) FROM messages WHERE receiver_id = ? AND lu = 0");
$stmt->execute([$user_id]);
$unread_count = (int)$stmt->fetchColumn();

echo json_encode(['success' => true, 'unread_count' => $unread_count]);
