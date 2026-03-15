<?php
// shows the car detail page. fetches the listing and its photos, then displays the gallery, specs, and enquiry form
session_start();
$root = "";

// validate the id from the url
$carId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($carId <= 0) {
    header("Location: listings.php");
    exit();
}

// connect and fetch the car
include "inc/db.inc.php";

// join with users to get seller details in the same query
$stmt = $conn->prepare("
    SELECT
        c.car_id,
        c.user_id,
        c.brand,
        c.model,
        c.year,
        c.price,
        c.mileage,
        c.transmission,
        c.fuel_type,
        c.type,
        c.color,
        c.description,
        c.status,
        c.created_at,
        c.reg_date,
        c.coe_expiry,
        c.engine_cap,
        c.no_of_owners,
        u.fname,
        u.lname,
        u.created_at AS member_since
    FROM cars c
    JOIN users u ON c.user_id = u.user_id
    WHERE c.car_id = ?
    LIMIT 1
");
$stmt->bind_param("i", $carId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // car doesn't exist. send them back rather than showing a blank page
    $stmt->close();
    $conn->close();
    header("Location: listings.php");
    exit();
}

$car = $result->fetch_assoc();
$stmt->close();

// fetch all photos. is_primary desc puts the cover photo first
$imgStmt = $conn->prepare("
    SELECT image_path
    FROM car_images
    WHERE car_id = ?
    ORDER BY is_primary DESC, image_id ASC
");
$imgStmt->bind_param("i", $carId);
$imgStmt->execute();
$imgResult = $imgStmt->get_result();
$gallery   = $imgResult->fetch_all(MYSQLI_ASSOC);
$imgStmt->close();

// check if the logged-in user has saved this car
$isSaved  = false;
$isOwner  = false;
$loggedIn = isset($_SESSION['user_id']);

if ($loggedIn) {
    $currentUserId = (int)$_SESSION['user_id'];
    $isOwner       = ($currentUserId === (int)$car['user_id']);

    $saveCheck = $conn->prepare(
        "SELECT save_id FROM saved_cars WHERE user_id = ? AND car_id = ? LIMIT 1"
    );
    $saveCheck->bind_param("ii", $currentUserId, $carId);
    $saveCheck->execute();
    $isSaved = $saveCheck->get_result()->num_rows > 0;
    $saveCheck->close();

    // check if buyer already sent an enquiry for this car
    $hasEnquired = false;
    if (!$isOwner) {
        $eCheck = $conn->prepare(
            "SELECT enquiry_id FROM enquiries WHERE car_id = ? AND sender_user_id = ? LIMIT 1"
        );
        $eCheck->bind_param("ii", $carId, $currentUserId);
        $eCheck->execute();
        $hasEnquired = $eCheck->get_result()->num_rows > 0;
        $eCheck->close();
    }
}

$conn->close();


// build display values
$carTitle    = $car['year'] . ' ' . $car['brand'] . ' ' . $car['model'];
$brandModel  = htmlspecialchars($car['brand'] . ' ' . $car['model']);
$pageTitle   = htmlspecialchars($carTitle) . ' — sgCar';
$sellerName  = htmlspecialchars(trim($car['fname'] . ' ' . $car['lname']));
$memberSince = date('Y', strtotime($car['member_since']));
$listedOn    = date('d M Y', strtotime($car['created_at']));

// pre-compute save button state so the html stays clean
$saveClass = $isSaved ? 'btn-danger' : 'btn-outline-danger';
$saveIcon  = $isSaved ? 'favorite' : 'favorite_border';
$saveLabel = $isSaved ? 'Saved' : 'Save Car';

// calculate how much COE is left
$coeLeft = '';
if (!empty($car['coe_expiry'])) {
    $expiry = new DateTime($car['coe_expiry']);
    $now    = new DateTime();
    $diff   = $now->diff($expiry);
    if ($diff->invert) {
        $coeLeft = 'COE expired';
    } else {
        $coeLeft = $diff->y . 'yr ' . $diff->m . 'mth left';
    }
}

// main image: first from gallery, or placeholder if no photos uploaded
$mainImage = !empty($gallery)
    ? htmlspecialchars($gallery[0]['image_path'])
    : 'https://placehold.co/1200x700/1e293b/94a3b8?text=No+Photo';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title><?= $pageTitle ?></title>
    <?php include "inc/head.inc.php"; ?>
</head>

<body class="d-flex flex-column min-vh-100">

    <?php include "inc/nav.inc.php"; ?>

    <main id="main-content">

        <!-- breadcrumb -->
        <div class="bg-light border-bottom py-3">
            <div class="container">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 small">
                        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                        <li class="breadcrumb-item"><a href="listings.php">Browse Cars</a></li>
                        <li class="breadcrumb-item active" aria-current="page">
                            <?= htmlspecialchars($carTitle) ?>
                        </li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="container py-4">
            <div class="row g-4">


                <!-- left column: gallery, description, specs -->
                <div class="col-12 col-lg-8">

                    <!-- page title -->
                    <h1 class="h3 fw-bold mb-1"><?= htmlspecialchars($carTitle) ?></h1>
                    <p class="text-muted small mb-3">Listed on <?= $listedOn ?></p>

                    <!-- image gallery -->
                    <figure class="mb-0">
                        <img id="mainCarImage"
                             src="<?= $mainImage ?>"
                             alt="<?= htmlspecialchars($carTitle) ?> — main photo"
                             class="gallery-main-img">

                        <?php if (count($gallery) > 1): ?>
                            <!-- thumbnail strip — clicking any thumb swaps the main image (main.js) -->
                            <div class="gallery-thumbs" role="list" aria-label="Car photo thumbnails">
                                <?php foreach ($gallery as $i => $img): ?>
                                    <img src="<?= htmlspecialchars($img['image_path']) ?>"
                                         alt="<?= $brandModel ?> photo <?= $i + 1 ?>"
                                         class="gallery-thumb<?= $i === 0 ? ' active' : '' ?>"
                                         role="listitem"
                                         tabindex="0">
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <figcaption class="visually-hidden">
                            Photo gallery for <?= htmlspecialchars($carTitle) ?>
                        </figcaption>
                    </figure>
                    <!-- end gallery -->


                    <!-- description -->
                    <section class="mt-4" aria-label="Seller description">
                        <h2 class="h5 fw-bold mb-2">About This Car</h2>
                        <p class="text-muted" style="line-height:1.8;">
                            <?= nl2br(htmlspecialchars($car['description'])) ?>
                        </p>
                    </section>


                    <!-- specs table -->
                    <section class="mt-4" aria-label="Car specifications">
                        <h2 class="h5 fw-bold mb-3">Specifications</h2>
                        <div class="table-responsive">
                            <table class="table specs-table table-borderless">
                                <tbody>
                                    <tr>
                                        <td>Brand</td>
                                        <td><?= htmlspecialchars($car['brand']) ?></td>
                                    </tr>
                                    <tr>
                                        <td>Model</td>
                                        <td><?= htmlspecialchars($car['model']) ?></td>
                                    </tr>
                                    <tr>
                                        <td>Year</td>
                                        <td><?= htmlspecialchars($car['year']) ?></td>
                                    </tr>
                                    <tr>
                                        <td>Mileage</td>
                                        <td><?= number_format($car['mileage']) ?> km</td>
                                    </tr>
                                    <tr>
                                        <td>Transmission</td>
                                        <td><?= htmlspecialchars($car['transmission']) ?></td>
                                    </tr>
                                    <tr>
                                        <td>Fuel Type</td>
                                        <td><?= htmlspecialchars($car['fuel_type']) ?></td>
                                    </tr>
                                    <tr>
                                        <td>Body Type</td>
                                        <td><?= htmlspecialchars($car['type']) ?></td>
                                    </tr>
                                    <?php if (!empty($car['color'])): ?>
                                    <tr>
                                        <td>Colour</td>
                                        <td><?= htmlspecialchars($car['color']) ?></td>
                                    </tr>
                                    <?php endif; ?>
                                    <?php if (!empty($car['engine_cap'])): ?>
                                    <tr>
                                        <td>Engine Cap</td>
                                        <td><?= number_format($car['engine_cap']) ?> cc</td>
                                    </tr>
                                    <?php endif; ?>
                                    <?php if (!empty($car['no_of_owners'])): ?>
                                    <tr>
                                        <td>No. of Owners</td>
                                        <td><?= $car['no_of_owners'] == 6 ? '6 or more' : $car['no_of_owners'] ?></td>
                                    </tr>
                                    <?php endif; ?>
                                    <?php if (!empty($car['reg_date'])): ?>
                                    <tr>
                                        <td>Reg. Date</td>
                                        <td><?= date('d M Y', strtotime($car['reg_date'])) ?></td>
                                    </tr>
                                    <?php endif; ?>
                                    <?php if (!empty($car['coe_expiry'])): ?>
                                    <tr>
                                        <td>COE Expiry</td>
                                        <td>
                                            <?= date('M Y', strtotime($car['coe_expiry'])) ?>
                                            <?php if ($coeLeft): ?>
                                                <span class="text-muted small">(<?= htmlspecialchars($coeLeft) ?>)</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </section>

                </div>
                <!-- end left column -->


                <!-- right column: price card and enquiry form -->
                <aside class="col-12 col-lg-4" aria-label="Price and enquiry">

                    <div class="price-card">

                        <!-- price -->
                        <p class="detail-price mb-1">S$ <?= number_format($car['price']) ?></p>
                        <p class="text-muted small mb-3">Negotiable · COE included</p>

                        <!-- owner: edit and delete controls -->
                        <?php if ($isOwner): ?>
                            <div class="d-flex gap-2 mb-3">
                                <a href="edit-listing.php?id=<?= $carId ?>"
                                   class="btn btn-outline-primary btn-sm flex-fill">
                                    <span class="material-icons btn-icon" style="font-size:1rem;" aria-hidden="true">edit</span>
                                    Edit
                                </a>
                                <form method="post" action="process-delete-listing.php"
                                      onsubmit="return confirm('Delete this listing? This cannot be undone.');"
                                      class="flex-fill">
                                    <input type="hidden" name="car_id" value="<?= $carId ?>">
                                    <button type="submit" class="btn btn-outline-danger btn-sm w-100">
                                        <span class="material-icons btn-icon" style="font-size:1rem;" aria-hidden="true">delete</span>
                                        Delete
                                    </button>
                                </form>
                            </div>
                        <?php endif; ?>

                        <!-- save/unsave button for logged-in non-owners -->
                        <?php if ($loggedIn && !$isOwner): ?>
                            <form method="post" action="process-save-car.php" class="mb-3">
                                <input type="hidden" name="car_id" value="<?= $carId ?>">
                                <input type="hidden" name="redirect" value="car-detail.php">
                                <button type="submit" class="btn <?= $saveClass ?> w-100">
                                    <span class="material-icons btn-icon" style="font-size:1rem;" aria-hidden="true"><?= $saveIcon ?></span>
                                    <?= $saveLabel ?>
                                </button>
                            </form>
                        <?php elseif (!$loggedIn): ?>
                            <a href="login.php" class="btn btn-outline-secondary w-100 mb-3">
                                <span class="material-icons btn-icon" style="font-size:1rem;" aria-hidden="true">favorite_border</span>
                                Log in to Save
                            </a>
                        <?php endif; ?>

                        <!-- quick-spec pills for at-a-glance reference -->
                        <div class="car-card-specs mb-4">
                            <span>
                                <span class="material-icons spec-icon" aria-hidden="true">speed</span>
                                <?= number_format($car['mileage']) ?> km
                            </span>
                            <span>
                                <span class="material-icons spec-icon" aria-hidden="true">settings</span>
                                <?= htmlspecialchars($car['transmission']) ?>
                            </span>
                            <span>
                                <span class="material-icons spec-icon" aria-hidden="true">local_gas_station</span>
                                <?= htmlspecialchars($car['fuel_type']) ?>
                            </span>
                        </div>

                        <hr>

                        <!-- seller info -->
                        <div class="d-flex align-items-center gap-2 mb-4">
                            <span class="material-icons fs-2 text-muted" aria-hidden="true">account_circle</span>
                            <div>
                                <p class="mb-0 fw-semibold"><?= $sellerName ?></p>
                                <p class="mb-0 text-muted small">Member since <?= $memberSince ?></p>
                            </div>
                        </div>

                        <!-- enquiry form -->
                        <h2 class="h6 fw-bold mb-3">Send an Enquiry</h2>

                        <?php if (!isset($_SESSION['user_id'])): ?>
                            <p class="text-muted small">
                                <a href="login.php">Log in</a> to send an enquiry to the seller.
                            </p>
                        <?php elseif ((int)$_SESSION['user_id'] === (int)$car['user_id']): ?>
                            <p class="text-muted small">This is your own listing.</p>
                        <?php elseif ($hasEnquired): ?>
                            <div class="text-center py-3">
                                <span class="material-icons text-success" style="font-size:2.2rem;">chat</span>
                                <p class="text-muted small mt-2 mb-3">You've already sent an enquiry for this car.</p>
                                <a href="inbox.php" class="btn btn-sgcar w-100">
                                    <span class="material-icons btn-icon" aria-hidden="true">forum</span>
                                    View Conversation
                                </a>
                            </div>
                        <?php else: ?>
                            <form action="process_enquiry.php" method="post" aria-label="Send enquiry to seller">

                                <input type="hidden" name="car_id" value="<?= $carId ?>">

                                <?php if (!empty($_SESSION['enquiry_errors'])): ?>
                                    <div class="alert alert-danger alert-sm py-2">
                                        <ul class="mb-0 ps-3 small">
                                            <?php foreach ($_SESSION['enquiry_errors'] as $e): ?>
                                                <li><?= htmlspecialchars($e) ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                    <?php unset($_SESSION['enquiry_errors']); ?>
                                <?php endif; ?>

                                <?php if (!empty($_SESSION['enquiry_success'])): ?>
                                    <div class="alert alert-success alert-sm py-2 small">
                                        <?= htmlspecialchars($_SESSION['enquiry_success']) ?>
                                    </div>
                                    <?php unset($_SESSION['enquiry_success']); ?>
                                <?php endif; ?>

                                <div class="mb-3">
                                    <textarea id="enquiry-msg"
                                              name="message"
                                              class="form-control"
                                              rows="4"
                                              placeholder="Hi, I'm interested in this car. Is it still available?"
                                              required
                                              maxlength="1000"><?= htmlspecialchars($_SESSION['enquiry_data']['message'] ?? '') ?></textarea>
                                </div>

                                <?php unset($_SESSION['enquiry_data']); ?>

                                <div class="d-grid">
                                    <button type="submit" class="btn btn-sgcar">
                                        <span class="material-icons btn-icon" aria-hidden="true">send</span>
                                        Send Enquiry
                                    </button>
                                </div>

                            </form>
                        <?php endif; ?>
                        <!-- end enquiry form -->

                    </div>
                </aside>
                <!-- end right column -->

            </div>
        </div>

    </main>

    <?php include "inc/footer.inc.php"; ?>

</body>
</html>
