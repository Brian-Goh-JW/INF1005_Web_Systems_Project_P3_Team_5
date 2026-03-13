<?php
// shows the edit-listing form pre-filled with the existing car data. only the car's owner can access this
session_start();
$root      = "";
$pageTitle = "Edit Listing — sgCar";

include "inc/auth.inc.php";

$carId   = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$user_id = (int)$_SESSION['user_id'];

if ($carId <= 0) {
    header("Location: dashboard.php");
    exit();
}

include "inc/db.inc.php";

// fetch the car and verify ownership in one query
$stmt = $conn->prepare("
    SELECT car_id, brand, model, year, type, color, price, mileage, transmission, fuel_type, description
    FROM cars
    WHERE car_id = ? AND user_id = ? AND status != 'removed'
    LIMIT 1
");
$stmt->bind_param("ii", $carId, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // car doesn't exist or doesn't belong to this user
    $stmt->close();
    $conn->close();
    header("Location: dashboard.php");
    exit();
}

$car = $result->fetch_assoc();
$stmt->close();

// fetch existing photos so we can show them on the form
$imgStmt = $conn->prepare("
    SELECT image_id, image_path, is_primary
    FROM car_images
    WHERE car_id = ?
    ORDER BY is_primary DESC, image_id ASC
");
$imgStmt->bind_param("i", $carId);
$imgStmt->execute();
$existingImages = $imgStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$imgStmt->close();

$conn->close();

// pull flash errors and form data back if the submission failed
$errors   = $_SESSION['errors']    ?? [];
$formData = $_SESSION['form_data'] ?? [];
unset($_SESSION['errors'], $_SESSION['form_data']);

// formData overrides db values if set (re-populating after a failed submission)
$val = function($key) use ($car, $formData) {
    return $formData[$key] ?? $car[$key] ?? '';
};

$currentYear = (int)date('Y');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <?php include "inc/head.inc.php"; ?>
</head>

<body class="d-flex flex-column min-vh-100">

    <?php include "inc/nav.inc.php"; ?>

    <main id="main-content">

        <!-- page header -->
        <div class="bg-light border-bottom py-3">
            <div class="container">
                <h1 class="h4 fw-bold mb-0">Edit Listing</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 small">
                        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Edit Listing</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="container py-4">
            <div class="row justify-content-center">
                <div class="col-12 col-lg-8">

                    <!-- error summary -->
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger" role="alert">
                            <strong>Please fix the following:</strong>
                            <ul class="mb-0 mt-1">
                                <?php foreach ($errors as $e): ?>
                                    <li><?= htmlspecialchars($e) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form action="process-edit-listing.php"
                          method="post"
                          enctype="multipart/form-data"
                          novalidate>

                        <!-- hidden car id so the processor knows which row to update -->
                        <input type="hidden" name="car_id" value="<?= $carId ?>">


                        <!-- section 1: car details -->
                        <div class="card mb-4">
                            <div class="card-header fw-bold">
                                <span class="material-icons btn-icon" aria-hidden="true">directions_car</span>
                                Car Details
                            </div>
                            <div class="card-body">
                                <div class="row g-3">

                                    <div class="col-12 col-md-6">
                                        <label for="brand" class="form-label">Brand <span class="text-danger">*</span></label>
                                        <select id="brand" name="brand" class="form-select" required>
                                            <option value="">Select brand</option>
                                            <?php
                                            $brands = ['Toyota','Honda','BMW','Mercedes-Benz','Mazda',
                                                       'Hyundai','Volkswagen','Subaru','Mitsubishi','Kia',
                                                       'Nissan','Audi','Porsche','Lexus','Ford','Other'];
                                            foreach ($brands as $b):
                                                $sel = ($val('brand') === $b) ? 'selected' : '';
                                            ?>
                                                <option <?= $sel ?>><?= htmlspecialchars($b) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <label for="model" class="form-label">Model <span class="text-danger">*</span></label>
                                        <input type="text"
                                               id="model"
                                               name="model"
                                               class="form-control"
                                               placeholder="e.g. Camry, Civic, X3"
                                               required
                                               maxlength="45"
                                               value="<?= htmlspecialchars($val('model')) ?>">
                                    </div>

                                    <div class="col-12 col-md-4">
                                        <label for="year" class="form-label">Year <span class="text-danger">*</span></label>
                                        <select id="year" name="year" class="form-select" required>
                                            <option value="">Select year</option>
                                            <?php for ($y = $currentYear; $y >= 1990; $y--):
                                                $sel = ((int)$val('year') === $y) ? 'selected' : '';
                                            ?>
                                                <option value="<?= $y ?>" <?= $sel ?>><?= $y ?></option>
                                            <?php endfor; ?>
                                        </select>
                                    </div>

                                    <div class="col-12 col-md-4">
                                        <label for="type" class="form-label">Body Type <span class="text-danger">*</span></label>
                                        <select id="type" name="type" class="form-select" required>
                                            <option value="">Select type</option>
                                            <?php foreach (['Sedan','SUV','Hatchback','MPV','Coupe','Electric','Others'] as $t):
                                                $sel = ($val('type') === $t) ? 'selected' : '';
                                            ?>
                                                <option <?= $sel ?>><?= htmlspecialchars($t) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="col-12 col-md-4">
                                        <label for="color" class="form-label">Colour</label>
                                        <input type="text"
                                               id="color"
                                               name="color"
                                               class="form-control"
                                               placeholder="e.g. Pearl White"
                                               maxlength="45"
                                               value="<?= htmlspecialchars($val('color')) ?>">
                                    </div>

                                </div>
                            </div>
                        </div>


                        <!-- section 2: specs and price -->
                        <div class="card mb-4">
                            <div class="card-header fw-bold">
                                <span class="material-icons btn-icon" aria-hidden="true">tune</span>
                                Specifications &amp; Price
                            </div>
                            <div class="card-body">
                                <div class="row g-3">

                                    <div class="col-12 col-md-6">
                                        <label for="price" class="form-label">Asking Price (S$) <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text">S$</span>
                                            <input type="number"
                                                   id="price"
                                                   name="price"
                                                   class="form-control"
                                                   required
                                                   min="1"
                                                   max="9999999"
                                                   value="<?= htmlspecialchars($val('price')) ?>">
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <label for="mileage" class="form-label">Mileage (km) <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <input type="number"
                                                   id="mileage"
                                                   name="mileage"
                                                   class="form-control"
                                                   required
                                                   min="0"
                                                   max="999999"
                                                   value="<?= htmlspecialchars($val('mileage')) ?>">
                                            <span class="input-group-text">km</span>
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <label for="transmission" class="form-label">Transmission <span class="text-danger">*</span></label>
                                        <select id="transmission" name="transmission" class="form-select" required>
                                            <option value="">Select transmission</option>
                                            <?php foreach (['Auto','Manual'] as $t):
                                                $sel = ($val('transmission') === $t) ? 'selected' : '';
                                            ?>
                                                <option <?= $sel ?>><?= $t ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <label for="fuel_type" class="form-label">Fuel Type <span class="text-danger">*</span></label>
                                        <select id="fuel_type" name="fuel_type" class="form-select" required>
                                            <option value="">Select fuel type</option>
                                            <?php foreach (['Petrol','Diesel','Electric','Hybrid'] as $f):
                                                $sel = ($val('fuel_type') === $f) ? 'selected' : '';
                                            ?>
                                                <option <?= $sel ?>><?= $f ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                </div>
                            </div>
                        </div>


                        <!-- section 3: description and photos -->
                        <div class="card mb-4">
                            <div class="card-header fw-bold">
                                <span class="material-icons btn-icon" aria-hidden="true">description</span>
                                Description &amp; Photos
                            </div>
                            <div class="card-body">

                                <div class="mb-4">
                                    <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                                    <textarea id="description"
                                              name="description"
                                              class="form-control"
                                              rows="5"
                                              required
                                              maxlength="2000"><?= htmlspecialchars($val('description')) ?></textarea>
                                    <div class="form-text">Max 2000 characters.</div>
                                </div>

                                <!-- current photos -->
                                <?php if (!empty($existingImages)): ?>
                                    <p class="form-label">Current Photos</p>
                                    <div class="d-flex flex-wrap gap-2 mb-3">
                                        <?php foreach ($existingImages as $img): ?>
                                            <img src="<?= htmlspecialchars($img['image_path']) ?>"
                                                 alt="Current car photo"
                                                 style="height:80px; width:120px; object-fit:cover; border-radius:6px; border:2px solid <?= $img['is_primary'] ? 'var(--sgcar-red)' : '#dee2e6' ?>;"
                                                 title="<?= $img['is_primary'] ? 'Cover photo' : 'Photo' ?>">
                                        <?php endforeach; ?>
                                    </div>
                                    <p class="text-muted small mb-3">
                                        <span class="material-icons" style="font-size:0.9rem;vertical-align:middle;">info</span>
                                        Uploading new photos below will replace all current photos. Leave empty to keep existing photos.
                                    </p>
                                <?php endif; ?>

                                <div class="mb-3">
                                    <label for="carImages" class="form-label">
                                        Replace Photos
                                        <span class="text-muted small">(optional — up to 5 images, JPG/PNG/WebP, max 5MB each)</span>
                                    </label>
                                    <input type="file"
                                           id="carImages"
                                           name="images[]"
                                           class="form-control"
                                           multiple
                                           accept="image/jpeg,image/png,image/webp">
                                    <div class="form-text">The first image will be the cover photo.</div>
                                </div>

                                <div id="imagePreview" class="image-preview-grid" aria-label="Selected photo previews"></div>

                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-sgcar btn-lg">
                                <span class="material-icons btn-icon" aria-hidden="true">save</span>
                                Save Changes
                            </button>
                            <a href="dashboard.php" class="btn btn-outline-secondary btn-lg">Cancel</a>
                        </div>

                    </form>

                </div>
            </div>
        </div>

    </main>

    <?php include "inc/footer.inc.php"; ?>

</body>
</html>
