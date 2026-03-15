<?php
// returns unread inbox count as JSON — used by the nav badge AJAX polling
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['count' => 0]);
    exit;
}

$config = parse_ini_file('/var/www/private/db-config.ini');
if (!$config) {
    echo json_encode(['count' => 0]);
    exit;
}

$conn = new mysqli($config['servername'], $config['username'], $config['password'], $config['dbname']);
if ($conn->connect_error) {
    echo json_encode(['count' => 0]);
    exit;
}

$userId = (int)$_SESSION['user_id'];
$stmt = $conn->prepare("
    SELECT
        (SELECT COUNT(*) FROM messages m JOIN enquiries e ON m.enquiry_id = e.enquiry_id JOIN cars c ON e.car_id = c.car_id WHERE c.user_id = ? AND e.is_read = 0 AND m.sender_user_id != ?)
      + (SELECT COUNT(*) FROM messages m JOIN enquiries e ON m.enquiry_id = e.enquiry_id WHERE e.sender_user_id = ? AND e.buyer_unread = 1 AND m.sender_user_id != ?)
");
$stmt->bind_param("iiii", $userId, $userId, $userId, $userId);
$stmt->execute();
$stmt->bind_result($count);
$stmt->fetch();
$stmt->close();
$conn->close();

echo json_encode(['count' => (int)$count]);
