<?php
// handles sending a message in a conversation — works for both buyer and seller
session_start();
$root = "";

include "inc/auth.inc.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: inbox.php");
    exit();
}

$enquiryId = isset($_POST['enquiry_id']) ? (int)$_POST['enquiry_id'] : 0;
$body      = trim($_POST['body'] ?? '');

if ($enquiryId <= 0 || empty($body)) {
    header("Location: inbox.php");
    exit();
}

$userId = (int)$_SESSION['user_id'];

include "inc/db.inc.php";

// verify sender is either the buyer or the seller for this enquiry
$check = $conn->prepare("
    SELECT e.sender_user_id AS buyer_id, c.user_id AS seller_id
    FROM enquiries e
    JOIN cars c ON e.car_id = c.car_id
    WHERE e.enquiry_id = ?
    LIMIT 1
");
$check->bind_param("i", $enquiryId);
$check->execute();
$conv = $check->get_result()->fetch_assoc();
$check->close();

if (!$conv || ($userId !== (int)$conv['buyer_id'] && $userId !== (int)$conv['seller_id'])) {
    $conn->close();
    header("Location: inbox.php");
    exit();
}

// insert the message
$stmt = $conn->prepare("INSERT INTO messages (enquiry_id, sender_user_id, body) VALUES (?, ?, ?)");
$stmt->bind_param("iis", $enquiryId, $userId, $body);
$stmt->execute();
$stmt->close();

// update notification flags
if ($userId === (int)$conv['seller_id']) {
    // seller sent → notify buyer, mark seller's view as read
    $conn->query("UPDATE enquiries SET buyer_unread = 1, is_read = 1 WHERE enquiry_id = $enquiryId");
} else {
    // buyer sent → notify seller
    $conn->query("UPDATE enquiries SET is_read = 0 WHERE enquiry_id = $enquiryId");
}

$conn->close();

// if called via AJAX, return JSON instead of redirecting
if (!empty($_POST['_ajax'])) {
    header('Content-Type: application/json');
    echo json_encode(['ok' => true]);
    exit;
}

header("Location: inbox.php");
exit();
