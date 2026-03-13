<?php
// handles deleting a listing. soft-deletes by setting status to 'removed' so enquiry records are preserved
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
$carId   = isset($_POST['car_id']) ? (int)$_POST['car_id'] : 0;

if ($carId <= 0) {
    header("Location: dashboard.php");
    exit();
}

include "inc/db.inc.php";

// verify ownership before deleting. only the owner can remove their listing
$stmt = $conn->prepare(
    "UPDATE cars SET status = 'removed', deleted_at = NOW() WHERE car_id = ? AND user_id = ?"
);
$stmt->bind_param("ii", $carId, $user_id);
$stmt->execute();
$affected = $stmt->affected_rows;
$stmt->close();

$conn->close();

if ($affected > 0) {
    $_SESSION['dash_success'] = "Your listing has been removed.";
} else {
    // either the car doesn't exist or it belongs to a different user — fail silently
    $_SESSION['dash_success'] = "";
}

header("Location: dashboard.php");
exit();
