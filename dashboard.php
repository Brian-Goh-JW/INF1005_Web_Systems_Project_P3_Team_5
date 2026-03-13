<?php
// shows the seller dashboard — the user's own listings with edit/delete and enquiry counts
session_start();
$root      = "";
$pageTitle = "My Dashboard — sgCar";

include "inc/auth.inc.php";

$user_id = (int)$_SESSION['user_id'];

$success = $_SESSION['dash_success'] ?? '';
unset($_SESSION['dash_success']);

include "inc/db.inc.php";

// fetch the user's listings with a count of enquiries per car
$stmt = $conn->prepare("
    SELECT
        c.car_id,
        c.brand,
        c.model,
        c.year,
        c.price,
        c.status,
        c.created_at,
        ci.image_path AS primary_image,
        COUNT(e.enquiry_id) AS enquiry_count
    FROM cars c
    LEFT JOIN car_images ci ON c.car_id = ci.car_id AND ci.is_primary = 1
    LEFT JOIN enquiries e   ON c.car_id = e.car_id
    WHERE c.user_id = ? AND c.status != 'removed'
    GROUP BY c.car_id
    ORDER BY c.created_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$listings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// count the user's saved cars
$savedStmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM saved_cars WHERE user_id = ?");
$savedStmt->bind_param("i", $user_id);
$savedStmt->execute();
$savedCount = (int)$savedStmt->get_result()->fetch_assoc()['cnt'];
$savedStmt->close();

$conn->close();

$listingCount  = count($listings);
$totalEnquiries = array_sum(array_column($listings, 'enquiry_count'));
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
                <h1 class="h4 fw-bold mb-0">My Dashboard</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 small">
                        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="container py-4">

            <!-- success flash message -->
            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($success) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- stats row -->
            <div class="row g-3 mb-4">
                <div class="col-6 col-md-4">
                    <div class="card text-center border-0 shadow-sm h-100">
                        <div class="card-body py-3">
                            <p class="display-6 fw-bold text-danger mb-0"><?= $listingCount ?></p>
                            <p class="text-muted small mb-0">Active Listing<?= $listingCount !== 1 ? 's' : '' ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-4">
                    <div class="card text-center border-0 shadow-sm h-100">
                        <div class="card-body py-3">
                            <p class="display-6 fw-bold text-danger mb-0"><?= $totalEnquiries ?></p>
                            <p class="text-muted small mb-0">Total Enquir<?= $totalEnquiries !== 1 ? 'ies' : 'y' ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-4">
                    <div class="card text-center border-0 shadow-sm h-100">
                        <div class="card-body py-3">
                            <p class="display-6 fw-bold text-danger mb-0"><?= $savedCount ?></p>
                            <p class="text-muted small mb-0">Saved Car<?= $savedCount !== 1 ? 's' : '' ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- quick action links -->
            <div class="d-flex flex-wrap gap-2 mb-4">
                <a href="post-listing.php" class="btn btn-sgcar">
                    <span class="material-icons btn-icon" aria-hidden="true">add_circle</span>
                    Post New Listing
                </a>
                <a href="inbox.php" class="btn btn-outline-secondary">
                    <span class="material-icons btn-icon" aria-hidden="true">inbox</span>
                    My Inbox
                </a>
                <a href="saved-cars.php" class="btn btn-outline-secondary">
                    <span class="material-icons btn-icon" aria-hidden="true">favorite</span>
                    Saved Cars
                </a>
            </div>

            <!-- my listings section -->
            <h2 class="h5 fw-bold mb-3">My Listings</h2>

            <?php if (empty($listings)): ?>
                <div class="text-center py-5 text-muted border rounded">
                    <span class="material-icons" style="font-size:3rem;">directions_car</span>
                    <p class="mt-2">You haven't posted any listings yet.</p>
                    <a href="post-listing.php" class="btn btn-sgcar mt-1">Post Your First Listing</a>
                </div>
            <?php else: ?>
                <div class="row g-3">
                    <?php foreach ($listings as $car): ?>
                        <?php
                        $imgSrc  = !empty($car['primary_image'])
                            ? htmlspecialchars($car['primary_image'])
                            : 'https://placehold.co/400x250/1e293b/94a3b8?text=No+Photo';
                        $carTitle = htmlspecialchars($car['year'] . ' ' . $car['brand'] . ' ' . $car['model']);
                        ?>
                        <div class="col-12 col-md-6 col-xl-4">
                            <div class="card h-100 shadow-sm">
                                <img src="<?= $imgSrc ?>"
                                     alt="<?= $carTitle ?>"
                                     class="card-img-top"
                                     style="height:180px; object-fit:cover;">
                                <div class="card-body">
                                    <h3 class="h6 fw-bold mb-1"><?= $carTitle ?></h3>
                                    <p class="text-danger fw-semibold mb-1">S$ <?= number_format($car['price']) ?></p>
                                    <p class="text-muted small mb-2">
                                        Listed <?= date('d M Y', strtotime($car['created_at'])) ?>
                                    </p>
                                    <!-- enquiry badge -->
                                    <a href="inbox.php?car=<?= (int)$car['car_id'] ?>" class="badge bg-secondary text-decoration-none mb-2">
                                        <span class="material-icons" style="font-size:0.85rem;vertical-align:middle;">mail</span>
                                        <?= (int)$car['enquiry_count'] ?> enquir<?= (int)$car['enquiry_count'] !== 1 ? 'ies' : 'y' ?>
                                    </a>
                                </div>
                                <div class="card-footer bg-transparent border-0 d-flex gap-2 pb-3">
                                    <a href="car-detail.php?id=<?= (int)$car['car_id'] ?>"
                                       class="btn btn-outline-secondary btn-sm flex-fill">View</a>
                                    <a href="edit-listing.php?id=<?= (int)$car['car_id'] ?>"
                                       class="btn btn-outline-primary btn-sm flex-fill">Edit</a>
                                    <!-- delete — requires a POST so we use a tiny form with a confirm dialog -->
                                    <form method="post" action="process-delete-listing.php"
                                          onsubmit="return confirm('Delete this listing? This cannot be undone.');"
                                          class="flex-fill">
                                        <input type="hidden" name="car_id" value="<?= (int)$car['car_id'] ?>">
                                        <button type="submit" class="btn btn-outline-danger btn-sm w-100">Delete</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        </div>

    </main>

    <?php include "inc/footer.inc.php"; ?>

</body>
</html>
