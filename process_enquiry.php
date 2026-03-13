<?php
// handles the car enquiry form. validates the message and saves it to the database
session_start();
$root = "";

// only accepts post requests. rejects direct visits
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: listings.php");
    exit();
}


// validate the car id
$carId = isset($_POST['car_id']) ? (int)$_POST['car_id'] : 0;

if ($carId <= 0) {
    header("Location: listings.php");
    exit();
}

// validate the form fields
$senderName  = trim($_POST['sender_name']  ?? '');
$senderEmail = trim($_POST['sender_email'] ?? '');
$message     = trim($_POST['message']      ?? '');

$errors = [];

if (empty($senderName)) {
    $errors[] = "Your name is required.";
}
if (empty($senderEmail)) {
    $errors[] = "Your email address is required.";
} elseif (!filter_var($senderEmail, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Please enter a valid email address.";
}
if (empty($message)) {
    $errors[] = "A message is required.";
}

// validation failed — send back with errors
if (!empty($errors)) {
    $_SESSION['enquiry_errors'] = $errors;
    $_SESSION['enquiry_data']   = [
        'sender_name'  => $senderName,
        'sender_email' => $senderEmail,
        'message'      => $message,
    ];
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
    "INSERT INTO enquiries (car_id, sender_name, sender_email, message)
     VALUES (?, ?, ?, ?)"
);
$stmt->bind_param("isss", $carId, $senderName, $senderEmail, $message);

if (!$stmt->execute()) {
    $stmt->close();
    $conn->close();
    $_SESSION['enquiry_errors'] = ["Failed to send enquiry. Please try again."];
    header("Location: car-detail.php?id=" . $carId);
    exit();
}

$stmt->close();
$conn->close();

// success — redirect back with a flash message
$_SESSION['enquiry_success'] = "Your enquiry has been sent! The seller will be in touch soon.";
header("Location: car-detail.php?id=" . $carId);
exit();
