<?php
// handles the post-a-listing form. validates the text fields, checks uploaded images, and saves everything to the database
session_start();
$root = "";

// make sure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: post-listing.php");
    exit();
}


$user_id = (int)$_SESSION['user_id'];

// collect and clean the form fields
$brand        = cleanInput($_POST['brand']        ?? '');
$model        = cleanInput($_POST['model']        ?? '');
$year         = (int)($_POST['year']                  ?? 0);
$type         = cleanInput($_POST['type']         ?? '');
$color        = cleanInput($_POST['color']        ?? '');
$price        = (float)($_POST['price']               ?? 0);
$mileage      = (int)($_POST['mileage']               ?? 0);
$transmission = cleanInput($_POST['transmission'] ?? '');
$fuel_type    = cleanInput($_POST['fuel_type']    ?? '');
$description  = cleanInput($_POST['description']  ?? '');

$currentYear  = (int)date('Y');
$errors       = [];

// validate the text fields
if (empty($brand))                              $errors[] = "Brand is required.";
if (empty($model))                              $errors[] = "Model is required.";
if ($year < 1990 || $year > $currentYear)       $errors[] = "Please select a valid year.";
if (empty($type))                               $errors[] = "Body type is required.";
if ($price <= 0)                                $errors[] = "Please enter a valid asking price.";
if ($mileage < 0)                               $errors[] = "Please enter a valid mileage.";
if (empty($transmission))                       $errors[] = "Transmission is required.";
if (empty($fuel_type))                          $errors[] = "Fuel type is required.";
if (empty($description))                        $errors[] = "Description is required.";

// validate the uploaded images
$allowedMimes  = ['image/jpeg', 'image/png', 'image/webp'];
$maxFileSize   = 5 * 1024 * 1024;   // 5 mb in bytes.
$validFiles    = [];                 // files that passed all checks.

$hasFiles = isset($_FILES['images']) && !empty($_FILES['images']['name'][0]);

if (!$hasFiles) {
    $errors[] = "Please upload at least one photo.";
} else {
    $fileCount = count($_FILES['images']['name']);

    if ($fileCount > 5) {
        $errors[] = "You can upload a maximum of 5 photos.";
    } else {
        for ($i = 0; $i < $fileCount; $i++) {

            // skip empty file slots
            if ($_FILES['images']['error'][$i] === UPLOAD_ERR_NO_FILE) continue;

            // check for upload errors
            if ($_FILES['images']['error'][$i] !== UPLOAD_ERR_OK) {
                $errors[] = "Upload failed for file " . ($i + 1) . ". Please try again.";
                continue;
            }

            $tmpName  = $_FILES['images']['tmp_name'][$i];
            $origName = $_FILES['images']['name'][$i];
            $fileSize = $_FILES['images']['size'][$i];

            // size check
            if ($fileSize > $maxFileSize) {
                $errors[] = htmlspecialchars($origName) . " is too large (max 5MB per image).";
                continue;
            }

            // reads actual file bytes to check the type, not just the extension. a renamed .exe will not pass
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime  = finfo_file($finfo, $tmpName);
            finfo_close($finfo);

            if (!in_array($mime, $allowedMimes)) {
                $errors[] = htmlspecialchars($origName) . " is not a valid image. Use JPG, PNG or WebP.";
                continue;
            }

            // random unique filename so nothing gets overwritten and names cannot be guessed
            $ext = match($mime) {
                'image/jpeg' => 'jpg',
                'image/png'  => 'png',
                'image/webp' => 'webp',
                default      => 'jpg',
            };
            $newName = uniqid('car_', true) . '.' . $ext;

            $validFiles[] = ['tmp' => $tmpName, 'name' => $newName];
        }
    }
}

if (empty($validFiles) && empty($errors)) {
    $errors[] = "Please upload at least one valid photo.";
}

// errors found — go back to the form
if (!empty($errors)) {
    $_SESSION['errors']    = $errors;
    $_SESSION['form_data'] = $_POST;
    header("Location: post-listing.php");
    exit();
}

// insert the car into the database
include "inc/db.inc.php";

$stmt = $conn->prepare(
    "INSERT INTO cars
        (user_id, brand, model, year, price, mileage, transmission, fuel_type, type, color, description)
     VALUES
        (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
);
// type string: i=int, s=string, d=float
$stmt->bind_param(
    "issidisssss",
    $user_id, $brand, $model, $year,
    $price, $mileage,
    $transmission, $fuel_type, $type, $color, $description
);

if (!$stmt->execute()) {
    $stmt->close();
    $conn->close();
    $_SESSION['errors'] = ["Failed to save listing. Please try again."];
    header("Location: post-listing.php");
    exit();
}

$carId = $conn->insert_id;   // grab the new car id for images and the redirect.
$stmt->close();

// move image files to disk and record them in car_images
$uploadDir = __DIR__ . '/images/cars/';

// create the upload folder if it does not exist yet
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0775, true);
}

$imgStmt = $conn->prepare(
    "INSERT INTO car_images (car_id, image_path, is_primary) VALUES (?, ?, ?)"
);

foreach ($validFiles as $index => $file) {
    $destPath  = $uploadDir . $file['name'];
    $isPrimary = ($index === 0) ? 1 : 0;   // first image becomes the cover photo.

    if (move_uploaded_file($file['tmp'], $destPath)) {
        $relativePath = 'images/cars/' . $file['name'];
        $imgStmt->bind_param("isi", $carId, $relativePath, $isPrimary);
        $imgStmt->execute();
    }
}

$imgStmt->close();
$conn->close();

// all done — redirect to the new listing
header("Location: car-detail.php?id=" . $carId);
exit();


// helper
function cleanInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
