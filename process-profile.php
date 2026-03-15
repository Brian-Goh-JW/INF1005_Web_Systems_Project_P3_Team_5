<?php
// handles the profile update form. updates name/email and optionally changes the password
session_start();
$root = "";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: profile.php");
    exit();
}

$user_id    = (int)$_SESSION['user_id'];
$fname      = trim($_POST['fname']      ?? '');
$lname      = trim($_POST['lname']      ?? '');
$email      = trim($_POST['email']      ?? '');
$currentPwd = $_POST['current_pwd']     ?? '';
$newPwd     = $_POST['new_pwd']         ?? '';
$confirmPwd = $_POST['confirm_pwd']     ?? '';

$errors = [];

// validate personal details
if (empty($lname))                                $errors[] = "Last name is required.";
if (empty($email))                                $errors[] = "Email is required.";
if (!filter_var($email, FILTER_VALIDATE_EMAIL))   $errors[] = "Please enter a valid email address.";

// only validate password fields if the user filled any of them in
$changingPassword = !empty($currentPwd) || !empty($newPwd) || !empty($confirmPwd);

if ($changingPassword) {
    if (empty($currentPwd))         $errors[] = "Please enter your current password.";
    if (strlen($newPwd) < 8)        $errors[] = "New password must be at least 8 characters.";
    if ($newPwd !== $confirmPwd)    $errors[] = "New passwords do not match.";
}

if (!empty($errors)) {
    $_SESSION['profile_errors'] = $errors;
    $_SESSION['profile_data']   = ['fname' => $fname, 'lname' => $lname, 'email' => $email];
    header("Location: profile.php");
    exit();
}

include "inc/db.inc.php";

// check the email is not already taken by a different account
$emailCheck = $conn->prepare(
    "SELECT user_id FROM users WHERE email = ? AND user_id != ? LIMIT 1"
);
$emailCheck->bind_param("si", $email, $user_id);
$emailCheck->execute();
if ($emailCheck->get_result()->num_rows > 0) {
    $emailCheck->close();
    $conn->close();
    $_SESSION['profile_errors'] = ["That email address is already in use by another account."];
    $_SESSION['profile_data']   = ['fname' => $fname, 'lname' => $lname, 'email' => $email];
    header("Location: profile.php");
    exit();
}
$emailCheck->close();

if ($changingPassword) {
    // verify the current password before allowing a change
    $pwdCheck = $conn->prepare("SELECT password FROM users WHERE user_id = ? LIMIT 1");
    $pwdCheck->bind_param("i", $user_id);
    $pwdCheck->execute();
    $row = $pwdCheck->get_result()->fetch_assoc();
    $pwdCheck->close();

    if (!$row || !password_verify($currentPwd, $row['password'])) {
        $conn->close();
        $_SESSION['profile_errors'] = ["Current password is incorrect."];
        $_SESSION['profile_data']   = ['fname' => $fname, 'lname' => $lname, 'email' => $email];
        header("Location: profile.php");
        exit();
    }

    // update name, email, and password together
    $newHash = password_hash($newPwd, PASSWORD_DEFAULT);
    $stmt = $conn->prepare(
        "UPDATE users SET fname = ?, lname = ?, email = ?, password = ? WHERE user_id = ?"
    );
    $stmt->bind_param("ssssi", $fname, $lname, $email, $newHash, $user_id);
} else {
    // update name and email only
    $stmt = $conn->prepare(
        "UPDATE users SET fname = ?, lname = ?, email = ? WHERE user_id = ?"
    );
    $stmt->bind_param("sssi", $fname, $lname, $email, $user_id);
}

if (!$stmt->execute()) {
    $stmt->close();
    $conn->close();
    $_SESSION['profile_errors'] = ["Could not update profile. Please try again."];
    header("Location: profile.php");
    exit();
}
$stmt->close();
$conn->close();

// update the session so the nav greeting reflects the name change immediately
$_SESSION['fname'] = $fname;

$_SESSION['profile_success'] = "Your profile has been updated.";
header("Location: profile.php");
exit();
