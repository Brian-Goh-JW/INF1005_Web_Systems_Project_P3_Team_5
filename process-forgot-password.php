<?php
// handles the forgot password form. generates a secure token and emails a reset link
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: forgot-password.php");
    exit();
}

include "inc/db.inc.php";

function cleanInput($val) {
    return htmlspecialchars(stripslashes(trim($val)));
}

$email = cleanInput($_POST['email'] ?? '');

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['fp_error'] = "Please enter a valid email address.";
    header("Location: forgot-password.php");
    exit();
}

// check if the email belongs to a registered user
$stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();
$exists = $stmt->num_rows > 0;
$stmt->close();

if ($exists) {
    // remove any previous token for this email so only one active link exists at a time
    $del = $conn->prepare("DELETE FROM password_resets WHERE email = ?");
    $del->bind_param("s", $email);
    $del->execute();
    $del->close();

    // generate a cryptographically secure token valid for 1 hour
    $token   = bin2hex(random_bytes(32));
    $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

    $ins = $conn->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
    $ins->bind_param("sss", $email, $token, $expires);
    $ins->execute();
    $ins->close();

    // build the reset url and email it to the user
    $protocol  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $base      = $protocol . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']);
    $resetLink = $base . '/reset-password.php?token=' . urlencode($token);

    $subject = "sgCar â Password Reset Request";
    $body    = "Hi,

Click the link below to reset your sgCar password. This link expires in 1 hour.

"
             . $resetLink
             . "

If you didn't request this, you can safely ignore this email.

â The sgCar Team";
    $headers = "From: noreply@sgcar.com

Content-Type: text/plain; charset=UTF-8";

    mail($email, $subject, $body, $headers);
}

$conn->close();

// always show the same message to prevent email enumeration attacks
$_SESSION['fp_msg'] = "If an account with that email exists, a reset link has been sent. Check your inbox.";
header("Location: forgot-password.php");
exit();
