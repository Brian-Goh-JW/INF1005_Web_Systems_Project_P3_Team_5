<?php
// returns new messages for a conversation after a given message_id — used by inbox AJAX polling
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['messages' => []]);
    exit;
}

$enquiryId = (int)($_GET['enquiry_id'] ?? 0);
$afterId   = (int)($_GET['after_id']   ?? 0);

if (!$enquiryId) {
    echo json_encode(['messages' => []]);
    exit;
}

$config = parse_ini_file('/var/www/private/db-config.ini');
if (!$config) {
    echo json_encode(['messages' => []]);
    exit;
}

$conn = new mysqli($config['servername'], $config['username'], $config['password'], $config['dbname']);
if ($conn->connect_error) {
    echo json_encode(['messages' => []]);
    exit;
}

$userId = (int)$_SESSION['user_id'];

// verify user is buyer or seller of this conversation
$check = $conn->prepare("
    SELECT e.sender_user_id AS buyer_id, c.user_id AS seller_id
    FROM enquiries e
    JOIN cars c ON e.car_id = c.car_id
    WHERE e.enquiry_id = ?
");
$check->bind_param("i", $enquiryId);
$check->execute();
$conv = $check->get_result()->fetch_assoc();
$check->close();

if (!$conv || ($userId !== (int)$conv['buyer_id'] && $userId !== (int)$conv['seller_id'])) {
    $conn->close();
    echo json_encode(['messages' => []]);
    exit;
}

// fetch only messages newer than the last known id
$stmt = $conn->prepare("
    SELECT message_id, sender_user_id, body, created_at
    FROM messages
    WHERE enquiry_id = ? AND message_id > ?
    ORDER BY created_at ASC
");
$stmt->bind_param("ii", $enquiryId, $afterId);
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();

echo json_encode(['messages' => $rows]);
