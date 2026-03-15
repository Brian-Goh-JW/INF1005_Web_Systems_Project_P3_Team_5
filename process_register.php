<?php
// handles the registration form. validates the inputs and creates a new user account
session_start();
$root = "";

include "inc/helpers.inc.php";

// only accepts post requests. rejects anyone visiting this url directly
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: register.php");
    exit();
}


// collect the raw post values
$fname       = $_POST['fname']        ?? '';
$lname       = $_POST['lname']        ?? '';
$email       = $_POST['email']        ?? '';
$pwd         = $_POST['pwd']          ?? '';
$pwd_confirm = $_POST['pwd_confirm']  ?? '';
$agree       = isset($_POST['agree']);

$errors = [];

// clean the text fields before validation
$fname = cleanInput($fname);
$lname = cleanInput($lname);
$email = cleanInput($email);
// passwords are not cleaned here — hashing handles that

// validate each field

// last name is required
if (empty($lname)) {
    $errors[] = "Last name is required.";
}

// email must be present and in a valid format
if (empty($email)) {
    $errors[] = "Email address is required.";
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Please enter a valid email address.";
}

// password must be at least 8 characters
if (empty($pwd)) {
    $errors[] = "Password is required.";
} elseif (strlen($pwd) < 8) {
    $errors[] = "Password must be at least 8 characters.";
}

// both password fields must match
if ($pwd !== $pwd_confirm) {
    $errors[] = "Passwords do not match.";
}

// terms checkbox must be checked
if (!$agree) {
    $errors[] = "You must agree to the Terms & Conditions.";
}

// if there were errors, go back to the form
if (!empty($errors)) {
    $_SESSION['errors']    = $errors;
    // pass values back so the user does not have to retype everything
    $_SESSION['form_data'] = ['fname' => $fname, 'lname' => $lname, 'email' => $email];
    header("Location: register.php");
    exit();
}

// database operations
include "inc/db.inc.php";

// check if this email is already registered
$stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->close();
    $conn->close();
    $_SESSION['errors']    = ["An account with that email address already exists."];
    $_SESSION['form_data'] = ['fname' => $fname, 'lname' => $lname, 'email' => $email];
    header("Location: register.php");
    exit();
}
$stmt->close();

// hash the password. never store it as plain text
$pwd_hashed = password_hash($pwd, PASSWORD_DEFAULT);

// insert the new user
$stmt = $conn->prepare(
    "INSERT INTO users (fname, lname, email, password) VALUES (?, ?, ?, ?)"
);
$stmt->bind_param("ssss", $fname, $lname, $email, $pwd_hashed);

if (!$stmt->execute()) {
    $stmt->close();
    $conn->close();
    $_SESSION['errors'] = ["Something went wrong. Please try again."];
    header("Location: register.php");
    exit();
}

$stmt->close();
$conn->close();

// all good — redirect to login with a confirmation message
$_SESSION['success'] = "Account created successfully! Please log in.";
header("Location: login.php");
exit();


