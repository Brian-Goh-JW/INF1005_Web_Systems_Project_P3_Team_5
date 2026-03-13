<?php
// handles the contact form. validates the fields and stores the message to the database
session_start();
$root = "";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: contact.php");
    exit();
}

$senderName  = trim($_POST['sender_name']  ?? '');
$senderEmail = trim($_POST['sender_email'] ?? '');
$subject     = trim($_POST['subject']      ?? '');
$message     = trim($_POST['message']      ?? '');

$errors = [];

if (empty($senderName))  $errors[] = "Your name is required.";
if (empty($senderEmail)) {
    $errors[] = "Your email address is required.";
} elseif (!filter_var($senderEmail, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Please enter a valid email address.";
}
if (empty($message))     $errors[] = "A message is required.";

if (!empty($errors)) {
    $_SESSION['contact_errors'] = $errors;
    $_SESSION['contact_data']   = [
        'sender_name'  => $senderName,
        'sender_email' => $senderEmail,
        'subject'      => $subject,
        'message'      => $message,
    ];
    header("Location: contact.php");
    exit();
}

include "inc/db.inc.php";

$stmt = $conn->prepare(
    "INSERT INTO contact_messages (sender_name, sender_email, subject, message) VALUES (?, ?, ?, ?)"
);
$stmt->bind_param("ssss", $senderName, $senderEmail, $subject, $message);

if (!$stmt->execute()) {
    $stmt->close();
    $conn->close();
    $_SESSION['contact_errors'] = ["Failed to send your message. Please try again."];
    header("Location: contact.php");
    exit();
}

$stmt->close();
$conn->close();

$_SESSION['contact_success'] = "Thank you! Your message has been received. We'll get back to you within 1-2 business days.";
header("Location: contact.php");
exit();
