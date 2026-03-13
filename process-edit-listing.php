<?php
// handles the edit-listing form. validates fields, optionally replaces photos, then updates the database
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

// collect and clean the form fields
$brand        = cleanInput($_POST['brand']        ?? '');
$model        = cleanInput($_POST['model']        ?? '');
$year         = (int)($_POST['year']              ?? 0);
$type         = cleanInput($_POST['type']         ?? '');
$color        = cleanInput($_POST['color']        ?? '');
$price        = (float)($_POST['price']           ?? 0);
$mileage      = (int)($_POST['mileage']           ?? 0);
$transmission = cleanInput($_POST['transmission'] ?? '');
$fuel_type    = cleanInput($_POST['fuel_type']    ?? '');
$description  = cleanInput($_POST['description']  ?? '');

$currentYear = (int)date('Y');
$errors      = [];

// validate text fields
if (empty($brand))                              $errors[] = "Brand is required.";
if (empty($model))                              $errors[] = "Model is required.";
if ($year < 1990 || $year > $currentYear)       $errors[] = "Please select a valid year.";
if (empty($type))                               $errors[] = "Body type is required.";
if ($price <= 0)                                $errors[] = "Please enter a valid asking price.";
if ($mileage < 0)                               $errors[] = "Please enter a valid mileage.";
if (empty($transmission))                       $errors[] = "Transmission is required.";
if (empty($fuel_type))                          $errors[] = "Fuel type is required.";
if (empty($description))                        $errors[] = "Description is required.";

// check if the user uploaded new photos
$allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
$maxFileSize  = 5 * 1024 * 1024;
$validFiles   = [];
$hasNewFiles  = isset($_FILES['images']) && !empty($_FILES['images']['name'][0]);

if ($hasNewFiles) {
    $fileCount = count($_FILES['images']['name']);

    if ($fileCount > 5) {
        $errors[] = "You can upload a maximum of 5 photos.";
    } else {
        for ($i = 0; $i < $fileCount; $i++) {
            if ($_FILES['images']['error'][$i] === UPLOAD_ERR_NO_FILE) continue;

            if ($_FILES['images']['error'][$i] !== UPLOAD_ERR_OK) {
                $errors[] = "Upload failed for file " . ($i + 1) . ". Please try again.";
                continue;
            }

            $tmpName  = $_FILES['images']['tmp_name'][$i];
            $origName = $_FILES['images']['name'][$i];
            $fileSize = $_FILES['images']['size'][$i];

            if ($fileSize > $maxFileSize) {
                $errors[] = htmlspecialchars($origName) . " is too large (max 5MB per image).";
                continue;
            }

            // check actual file bytes, not just the extension
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime  = finfo_file($finfo, $tmpName);
            finfo_close($finfo);

            if (!in_array($mime, $allowedMimes)) {
                $errors[] = htmlspecialchars($origName) . " is not a valid image. Use JPG, PNG or WebP.";
                continue;
            }

            $ext = match($mime) {
                'image/jpeg' => 'jpg',
                'image/png'  => 'png',
                'image/webp' => 'webp',
                default      => 'jpg',
            };
            $newName      = uniqid('car_', true) . '.' . $ext;
            $validFiles[] = ['tmp' => $tmpName, 'name' => $newName];
        }
    }
}

if (!empty($errors)) {
    $_SESSION['errors']    = $errors;
    $_SESSION['form_data'] = $_POST;
    header("Location: edit-listing.php?id=" . $carId);
    exit();
}

include "inc/db.inc.php";

// verify the user owns this car before updating anything
$check = $conn->prepare(
    "SELECT car_id FROM cars WHERE car_id = ? AND user_id = ? AND status != 'removed' LIMIT 1"
);
$check->bind_param("ii", $carId, $user_id);
$check->execute();

if ($check->get_result()->num_rows === 0) {
    $check->close();
    $conn->close();
    header("Location: dashboard.php");
    exit();
}
$check->close();

// update the car record
$stmt = $conn->prepare("
    UPDATE cars
    SET brand=?, model=?, year=?, price=?, mileage=?, transmission=?, fuel_type=?, type=?, color=?, description=?
    WHERE car_id = ? AND user_id = ?
");
$stmt->bind_param(
    "ssidisssssii",
    $brand, $model, $year, $price, $mileage,
    $transmission, $fuel_type, $type, $color, $description,
    $carId, $user_id
);

if (!$stmt->execute()) {
    $stmt->close();
    $conn->close();
    $_SESSION['errors'] = ["Failed to update listing. Please try again."];
    header("Location: edit-listing.php?id=" . $carId);
    exit();
}
$stmt->close();

// if new photos were uploaded, delete the old ones and insert the new ones
if (!empty($validFiles)) {
    $uploadDir = __DIR__ . '/images/cars/';

    // get old image paths so we can delete the files from disk too
    $oldImgs = $conn->prepare("SELECT image_path FROM car_images WHERE car_id = ?");
    $oldImgs->bind_param("i", $carId);
    $oldImgs->execute();
    $oldPaths = $oldImgs->get_result()->fetch_all(MYSQLI_ASSOC);
    $oldImgs->close();

    // delete old image records
    $del = $conn->prepare("DELETE FROM car_images WHERE car_id = ?");
    $del->bind_param("i", $carId);
    $del->execute();
    $del->close();

    // remove old files from disk (ignore errors if already gone)
    foreach ($oldPaths as $old) {
        $diskPath = __DIR__ . '/' . $old['image_path'];
        if (file_exists($diskPath)) {
            @unlink($diskPath);
        }
    }

    // create upload folder if needed
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0775, true);
    }

    // insert new images
    $imgStmt = $conn->prepare(
        "INSERT INTO car_images (car_id, image_path, is_primary) VALUES (?, ?, ?)"
    );
    foreach ($validFiles as $index => $file) {
        $destPath     = $uploadDir . $file['name'];
        $isPrimary    = ($index === 0) ? 1 : 0;
        if (move_uploaded_file($file['tmp'], $destPath)) {
            $relativePath = 'images/cars/' . $file['name'];
            $imgStmt->bind_param("isi", $carId, $relativePath, $isPrimary);
            $imgStmt->execute();
        }
    }
    $imgStmt->close();
}

$conn->close();

$_SESSION['dash_success'] = "Your listing has been updated successfully.";
header("Location: dashboard.php");
exit();


// helper
function cleanInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
