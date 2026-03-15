<?php
// shows the post-a-car form. only accessible to logged-in users
// form uses multipart/form-data so php can receive the uploaded images
session_start();
$root      = "";
$pageTitle = "Sell My Car — sgCar";

include "inc/auth.inc.php";   // redirect to login if not logged in.

$errors   = $_SESSION['errors']    ?? [];
$formData = $_SESSION['form_data'] ?? [];
unset($_SESSION['errors'], $_SESSION['form_data']);

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
                <h1 class="h4 fw-bold mb-0">Post a Listing</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 small">
                        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Sell My Car</li>
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

                    <form action="process_listing.php"
                          method="post"
                          enctype="multipart/form-data"
                          novalidate>


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
                                                $sel = (($formData['brand'] ?? '') === $b) ? 'selected' : '';
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
                                               value="<?= htmlspecialchars($formData['model'] ?? '') ?>">
                                    </div>

                                    <div class="col-12 col-md-4">
                                        <label for="year" class="form-label">Year <span class="text-danger">*</span></label>
                                        <select id="year" name="year" class="form-select" required>
                                            <option value="">Select year</option>
                                            <?php for ($y = $currentYear; $y >= 1970; $y--):
                                                $sel = (($formData['year'] ?? '') == $y) ? 'selected' : '';
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
                                                $sel = (($formData['type'] ?? '') === $t) ? 'selected' : '';
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
                                               value="<?= htmlspecialchars($formData['color'] ?? '') ?>">
                                    </div>

                                </div>
                            </div>
                        </div>
                        <!-- end section 1 -->


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
                                                   placeholder="e.g. 145000"
                                                   required
                                                   min="1"
                                                   max="9999999"
                                                   value="<?= htmlspecialchars($formData['price'] ?? '') ?>">
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <label for="mileage" class="form-label">Mileage (km) <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <input type="number"
                                                   id="mileage"
                                                   name="mileage"
                                                   class="form-control"
                                                   placeholder="e.g. 25000"
                                                   required
                                                   min="0"
                                                   max="999999"
                                                   value="<?= htmlspecialchars($formData['mileage'] ?? '') ?>">
                                            <span class="input-group-text">km</span>
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <label for="transmission" class="form-label">Transmission <span class="text-danger">*</span></label>
                                        <select id="transmission" name="transmission" class="form-select" required>
                                            <option value="">Select transmission</option>
                                            <?php foreach (['Auto','Manual'] as $t):
                                                $sel = (($formData['transmission'] ?? '') === $t) ? 'selected' : '';
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
                                                $sel = (($formData['fuel_type'] ?? '') === $f) ? 'selected' : '';
                                            ?>
                                                <option <?= $sel ?>><?= $f ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <!-- sg-specific fields -->
                                    <div class="col-12 col-md-6">
                                        <label for="reg_date" class="form-label">Registration Date</label>
                                        <input type="date"
                                               id="reg_date"
                                               name="reg_date"
                                               class="form-control"
                                               value="<?= htmlspecialchars($formData['reg_date'] ?? '') ?>">
                                        <div class="form-text">When the car was first registered in Singapore.</div>
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <label for="coe_expiry" class="form-label">COE Expiry Date</label>
                                        <input type="date"
                                               id="coe_expiry"
                                               name="coe_expiry"
                                               class="form-control"
                                               value="<?= htmlspecialchars($formData['coe_expiry'] ?? '') ?>">
                                        <div class="form-text">Leave blank if not applicable (e.g. electric cars).</div>
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <label for="engine_cap" class="form-label">Engine Capacity (cc)</label>
                                        <div class="input-group">
                                            <input type="number"
                                                   id="engine_cap"
                                                   name="engine_cap"
                                                   class="form-control"
                                                   placeholder="e.g. 1598"
                                                   min="0"
                                                   max="9999"
                                                   value="<?= htmlspecialchars($formData['engine_cap'] ?? '') ?>">
                                            <span class="input-group-text">cc</span>
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <label for="no_of_owners" class="form-label">No. of Owners</label>
                                        <select id="no_of_owners" name="no_of_owners" class="form-select">
                                            <option value="">Select</option>
                                            <?php foreach ([1,2,3,4,5] as $n):
                                                $sel = (($formData['no_of_owners'] ?? '') == $n) ? 'selected' : '';
                                            ?>
                                                <option value="<?= $n ?>" <?= $sel ?>><?= $n ?></option>
                                            <?php endforeach; ?>
                                            <option value="6" <?= (($formData['no_of_owners'] ?? '') == 6) ? 'selected' : '' ?>>6 or more</option>
                                        </select>
                                    </div>

                                </div>
                            </div>
                        </div>
                        <!-- end section 2 -->


                        <!-- section 3: description and photos -->
                        <div class="card mb-4">
                            <div class="card-header fw-bold">
                                <span class="material-icons btn-icon" aria-hidden="true">description</span>
                                Description &amp; Photos
                            </div>
                            <div class="card-body">

                                <div class="mb-3">
                                    <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                                    <textarea id="description"
                                              name="description"
                                              class="form-control"
                                              rows="5"
                                              required
                                              maxlength="2000"
                                              placeholder="Describe the condition, history, features and anything a buyer should know..."><?= htmlspecialchars($formData['description'] ?? '') ?></textarea>
                                    <div class="form-text">Max 2000 characters.</div>
                                </div>

                                <div class="mb-3">
                                    <label for="carImages" class="form-label">
                                        Photos <span class="text-danger">*</span>
                                        <span class="text-muted small">(up to 5 images, JPG/PNG/WebP, max 10MB each)</span>
                                    </label>
                                    <input type="file"
                                           id="carImages"
                                           name="images[]"
                                           class="form-control"
                                           multiple
                                           accept="image/jpeg,image/png,image/webp"
                                           required>
                                    <div class="form-text">The first image will be used as the main photo.</div>
                                </div>

                                <!-- image preview thumbnails — populated by JS when files are selected -->
                                <div id="imagePreview" class="image-preview-grid" role="region" aria-label="Selected photo previews"></div>

                            </div>
                        </div>
                        <!-- end section 3 -->

                        <div class="d-grid">
                            <button type="submit" class="btn btn-sgcar btn-lg">
                                <span class="material-icons btn-icon" aria-hidden="true">publish</span>
                                Post Listing
                            </button>
                        </div>

                    </form>

                </div>
            </div>
        </div>

    </main>

    <?php include "inc/footer.inc.php"; ?>

</body>
</html>
