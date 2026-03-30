<?php
require_once __DIR__ . '/../configuration/database.php';
require_once __DIR__ . '/../configuration/session.php';

requireLogin();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

$conn = getConnection();
$user_id = (int)$_SESSION['user_id'];
$last_id = isset($_GET['last_id']) ? (int)$_GET['last_id'] : 0;

$stmt = $conn->prepare("SELECT COUNT(*) FROM messages WHERE receiver_id = ? AND lu = 0");
$stmt->execute([$user_id]);
$unread_count = (int)$stmt->fetchColumn();

$stmt = $conn->prepare("
    SELECT m.id, m.sender_id, m.contenu, m.date_envoi, u.prenom, u.nom
    FROM messages m
    JOIN utilisateurs u ON u.id = m.sender_id
    WHERE m.receiver_id = ? AND m.id > ?
    ORDER BY m.id ASC
    LIMIT 50
");
$stmt->execute([$user_id, $last_id]);
$new_messages = $stmt->fetchAll();

$max_id = $last_id;
foreach ($new_messages as $m) {
    $mid = (int)$m['id'];
    if ($mid > $max_id) {
        $max_id = $mid;
    }
}

echo json_encode([
    'unread_count' => $unread_count,
    'max_id' => $max_id,
    'new_messages' => $new_messages,
]);
