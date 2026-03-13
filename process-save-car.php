<?php
// toggles a car as saved or unsaved for the logged-in user
session_start();
$root = "";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: listings.php");
    exit();
}

$user_id  = (int)$_SESSION['user_id'];
$carId    = isset($_POST['car_id']) ? (int)$_POST['car_id'] : 0;
$redirect = $_POST['redirect'] ?? 'listings.php';

// only allow safe redirect targets to prevent open-redirect abuse
$allowed = ['listings.php', 'saved-cars.php', 'car-detail.php'];
$base    = strtok($redirect, '?');
if (!in_array($base, $allowed)) {
    $redirect = 'listings.php';
}

if ($carId <= 0) {
    header("Location: " . $redirect);
    exit();
}

include "inc/db.inc.php";

// check if already saved
$check = $conn->prepare(
    "SELECT save_id FROM saved_cars WHERE user_id = ? AND car_id = ? LIMIT 1"
);
$check->bind_param("ii", $user_id, $carId);
$check->execute();
$exists = $check->get_result()->num_rows > 0;
$check->close();

if ($exists) {
    // already saved — remove it
    $del = $conn->prepare("DELETE FROM saved_cars WHERE user_id = ? AND car_id = ?");
    $del->bind_param("ii", $user_id, $carId);
    $del->execute();
    $del->close();
} else {
    // not saved yet — add it
    $ins = $conn->prepare("INSERT INTO saved_cars (user_id, car_id) VALUES (?, ?)");
    $ins->bind_param("ii", $user_id, $carId);
    $ins->execute();
    $ins->close();
}

$conn->close();

// redirect back to wherever the user came from (car detail or saved list)
if ($base === 'car-detail.php') {
    header("Location: car-detail.php?id=" . $carId);
} else {
    header("Location: " . $redirect);
}
exit();
