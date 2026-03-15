<?php
// handles the login form. checks the email and password against the database, then starts a session
// set session lifetime before session_start so remember me takes effect
// gc_maxlifetime controls how long the server keeps the session file
// session_set_cookie_params controls how long the browser keeps the cookie
// both must match or the server will delete the session before the cookie expires
if (!empty($_POST['remember_me'])) {
    $lifetime = 30 * 24 * 60 * 60; // 30 days
} else {
    $lifetime = 2 * 60 * 60; // 2 hours for normal login
}
ini_set('session.gc_maxlifetime', $lifetime);
session_set_cookie_params($lifetime);
session_start();
$root = "";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login.php");
    exit();
}


$email = trim($_POST['email'] ?? '');
$pwd   = $_POST['pwd'] ?? '';
$errors = [];

// quick checks before hitting the database
if (empty($email)) {
    $errors[] = "Email address is required.";
}
if (empty($pwd)) {
    $errors[] = "Password is required.";
}

if (!empty($errors)) {
    $_SESSION['errors'] = $errors;
    header("Location: login.php");
    exit();
}

// look up the user in the database
include "inc/db.inc.php";

// fetch the stored hash by email. never compare passwords directly in sql
$stmt = $conn->prepare(
    "SELECT user_id, fname, lname, email, password, role FROM users WHERE email = ?"
);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // no account found — keep the error vague so attackers cannot tell if the email exists
    $stmt->close();
    $conn->close();
    $_SESSION['errors'] = ["Email address or password is incorrect."];
    header("Location: login.php");
    exit();
}

$user = $result->fetch_assoc();
$stmt->close();
$conn->close();

// verify the password

// password_verify checks the typed password against the stored hash
if (!password_verify($pwd, $user['password'])) {
    $_SESSION['errors'] = ["Email address or password is incorrect."];
    header("Location: login.php");
    exit();
}

// login ok — set up the session

session_regenerate_id(true);

$_SESSION['user_id'] = $user['user_id'];
$_SESSION['fname']   = $user['fname'];
$_SESSION['lname']   = $user['lname'];
$_SESSION['email']   = $user['email'];
$_SESSION['role']    = $user['role'];

// admins go to the dashboard, everyone else goes home
if ($user['role'] === 'admin') {
    header("Location: admin/index.php");
} else {
    header("Location: index.php");
}
exit();
