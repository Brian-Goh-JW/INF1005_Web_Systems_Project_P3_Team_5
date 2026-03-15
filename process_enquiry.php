<?php
// handles the car enquiry form. validates the message and saves it to the database
session_start();
$root = "";

// only accepts post requests. rejects direct visits
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: listings.php");
    exit();
}

// must be logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// validate the car id
$carId = isset($_POST['car_id']) ? (int)$_POST['car_id'] : 0;

if ($carId <= 0) {
    header("Location: listings.php");
    exit();
}

// sender details come from the session
$senderId    = (int)$_SESSION['user_id'];
$senderName  = trim(($_SESSION['fname'] ?? '') . ' ' . ($_SESSION['lname'] ?? ''));
$senderEmail = $_SESSION['email'] ?? '';
$message     = trim($_POST['message'] ?? '');

$errors = [];

if (empty($message)) {
    $errors[] = "A message is required.";
}

// validation failed — send back with error
if (!empty($errors)) {
    $_SESSION['enquiry_errors'] = $errors;
    $_SESSION['enquiry_data']   = ['message' => $message];
    header("Location: car-detail.php?id=" . $carId);
    exit();
}

include "inc/db.inc.php";

// double-check the car is still available. stops enquiries on sold or deleted listings
$check = $conn->prepare(
    "SELECT car_id FROM cars WHERE car_id = ? AND status = 'available' LIMIT 1"
);
$check->bind_param("i", $carId);
$check->execute();

if ($check->get_result()->num_rows === 0) {
    $check->close();
    $conn->close();
    header("Location: listings.php");
    exit();
}
$check->close();

// insert the enquiry
$stmt = $conn->prepare(
    "INSERT INTO enquiries (car_id, sender_user_id, sender_name, sender_email, message)
     VALUES (?, ?, ?, ?, ?)"
);
$stmt->bind_param("iisss", $carId, $senderId, $senderName, $senderEmail, $message);

if (!$stmt->execute()) {
    $stmt->close();
    $conn->close();
    $_SESSION['enquiry_errors'] = ["Failed to send enquiry. Please try again."];
    header("Location: car-detail.php?id=" . $carId);
    exit();
}

$enquiryId = $conn->insert_id;

// also store in the messages table for multi-turn chat
$mStmt = $conn->prepare("INSERT INTO messages (enquiry_id, sender_user_id, body, created_at) VALUES (?, ?, ?, NOW())");
$mStmt->bind_param("iis", $enquiryId, $senderId, $message);
$mStmt->execute();
$mStmt->close();

$stmt->close();
$conn->close();

// success — redirect back with a flash message
$_SESSION['enquiry_success'] = "Enquiry sent! You can continue the conversation in your inbox.";
header("Location: car-detail.php?id=" . $carId);
exit();
