<?php
// shows all cars the user has saved/favourited
session_start();
$root      = "";
$pageTitle = "Saved Cars — sgCar";

include "inc/auth.inc.php";

$user_id = (int)$_SESSION['user_id'];

include "inc/db.inc.php";

// fetch saved cars with the car details and primary image
$stmt = $conn->prepare("
    SELECT
        c.car_id,
        c.brand,
        c.model,
        c.year,
        c.price,
        c.mileage,
        c.transmission,
        c.fuel_type,
        c.type,
        c.status,
        ci.image_path AS primary_image,
        sc.saved_at
    FROM saved_cars sc
    JOIN cars c ON sc.car_id = c.car_id
    LEFT JOIN car_images ci ON c.car_id = ci.car_id AND ci.is_primary = 1
    WHERE sc.user_id = ?
    ORDER BY sc.saved_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$savedCars = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$conn->close();
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
                <h1 class="h4 fw-bold mb-0">Saved Cars</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 small">
                        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Saved Cars</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="container py-4">

            <p class="text-muted small mb-3">
                <strong><?= count($savedCars) ?></strong>
                saved car<?= count($savedCars) !== 1 ? 's' : '' ?>
            </p>

            <?php if (empty($savedCars)): ?>
                <div class="text-center py-5 text-muted border rounded">
                    <span class="material-icons" style="font-size:3rem;">favorite_border</span>
                    <p class="mt-2">You haven't saved any cars yet.</p>
                    <a href="listings.php" class="btn btn-sgcar mt-1">Browse Cars</a>
                </div>
            <?php else: ?>
                <div class="row row-cols-1 row-cols-sm-2 row-cols-xl-3 g-4">
                    <?php foreach ($savedCars as $car): ?>
                        <?php
                        $imgSrc  = !empty($car['primary_image'])
                            ? htmlspecialchars($car['primary_image'])
                            : 'https://placehold.co/800x500/1e293b/94a3b8?text=No+Photo';
                        $altText = htmlspecialchars($car['year'] . ' ' . $car['brand'] . ' ' . $car['model']);
                        $isSold  = ($car['status'] !== 'available');
                        ?>
                        <article class="col">
                            <div class="car-card card h-100 <?= $isSold ? 'opacity-75' : '' ?>">
                                <div class="car-card-img-wrap">
                                    <img src="<?= $imgSrc ?>"
                                         alt="<?= $altText ?>"
                                         class="car-card-img"
                                         loading="lazy">
                                    <span class="car-type-badge"><?= htmlspecialchars($car['type']) ?></span>
                                    <?php if ($isSold): ?>
                                        <span class="position-absolute top-0 start-0 m-2 badge bg-secondary">Sold / Removed</span>
                                    <?php endif; ?>
                                </div>
                                <div class="card-body">
                                    <h2 class="car-card-title">
                                        <?= htmlspecialchars($car['year'] . ' ' . $car['brand'] . ' ' . $car['model']) ?>
                                    </h2>
                                    <p class="car-card-price">S$ <?= number_format($car['price']) ?></p>
                                    <div class="car-card-specs">
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
                                    <p class="text-muted small mt-2 mb-0">
                                        Saved <?= date('d M Y', strtotime($car['saved_at'])) ?>
                                    </p>
                                </div>
                                <div class="card-footer bg-transparent border-0 pb-3 d-flex gap-2">
                                    <?php if (!$isSold): ?>
                                        <a href="car-detail.php?id=<?= (int)$car['car_id'] ?>"
                                           class="btn btn-sgcar flex-fill">View</a>
                                    <?php endif; ?>
                                    <!-- unsave button -->
                                    <form method="post" action="process-save-car.php" class="flex-fill">
                                        <input type="hidden" name="car_id" value="<?= (int)$car['car_id'] ?>">
                                        <input type="hidden" name="redirect" value="saved-cars.php">
                                        <button type="submit" class="btn btn-outline-secondary w-100">
                                            <span class="material-icons btn-icon" style="font-size:1rem;" aria-hidden="true">heart_broken</span>
                                            Remove
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        </div>

    </main>

    <?php include "inc/footer.inc.php"; ?>

</body>
</html>
