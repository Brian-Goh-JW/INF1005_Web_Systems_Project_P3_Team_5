<?php
// validates the reset token and updates the user's password. deletes the token after use
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login.php");
    exit();
}

include "inc/db.inc.php";

$token    = trim($_POST['token']    ?? '');
$password =      $_POST['password'] ?? '';
$confirm  =      $_POST['confirm']  ?? '';

// re-validate the token in case it expired between page load and submit
$stmt = $conn->prepare("SELECT email FROM password_resets WHERE token = ? AND expires_at > NOW()");
$stmt->bind_param("s", $token);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();
$stmt->close();

if (!$row) {
    $_SESSION['rp_error'] = "This reset link has expired or is invalid. Please request a new one.";
    header("Location: reset-password.php?token=" . urlencode($token));
    exit();
}

$email = $row['email'];

// validate the new password
if (strlen($password) < 8) {
    $_SESSION['rp_error'] = "Password must be at least 8 characters.";
    header("Location: reset-password.php?token=" . urlencode($token));
    exit();
}

if ($password !== $confirm) {
    $_SESSION['rp_error'] = "Passwords do not match.";
    header("Location: reset-password.php?token=" . urlencode($token));
    exit();
}

// hash and save the new password
$hashed = password_hash($password, PASSWORD_DEFAULT);
$upd    = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
$upd->bind_param("ss", $hashed, $email);
$upd->execute();
$upd->close();

// delete the used token so it cannot be reused
$del = $conn->prepare("DELETE FROM password_resets WHERE email = ?");
$del->bind_param("s", $email);
$del->execute();
$del->close();

$conn->close();

$_SESSION['success'] = "Password reset successfully. Please log in with your new password.";
header("Location: login.php");
exit();
