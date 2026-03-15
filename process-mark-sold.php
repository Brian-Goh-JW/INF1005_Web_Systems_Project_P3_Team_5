<?php
// toggles a listing between 'available' and 'sold'. only the owner can do this
session_start();
$root = "";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: dashboard.php");
    exit();
}

$user_id = (int)$_SESSION['user_id'];
$carId   = isset($_POST['car_id'])     ? (int)$_POST['car_id']     : 0;
$action  = isset($_POST['action'])     ? $_POST['action']          : '';

if ($carId <= 0 || !in_array($action, ['sold', 'available'])) {
    header("Location: dashboard.php");
    exit();
}

include "inc/db.inc.php";

$stmt = $conn->prepare(
    "UPDATE cars SET status = ? WHERE car_id = ? AND user_id = ? AND status != 'removed'"
);
$stmt->bind_param("sii", $action, $carId, $user_id);
$stmt->execute();
$stmt->close();
$conn->close();

$msg = $action === 'sold' ? "Listing marked as sold." : "Listing marked as available again.";
$_SESSION['dash_success'] = $msg;
header("Location: dashboard.php");
exit();
