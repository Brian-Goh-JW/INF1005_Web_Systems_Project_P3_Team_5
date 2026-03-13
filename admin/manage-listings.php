<?php
// shows all listings. admins can toggle the status between available and sold, or delete a listing
session_start();
$root      = "../";
$pageTitle = "Manage Listings — sgCar Admin";

include "admin-auth.inc.php";
include "../inc/db.inc.php";

// handle post actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {


    $action = $_POST['action'] ?? '';
    $carId  = isset($_POST['car_id']) ? (int)$_POST['car_id'] : 0;

    if ($carId > 0) {

        if ($action === 'toggle_status') {
            // read the current status first, then flip it. avoids race conditions if two admins act at the same time
            $r = $conn->prepare("SELECT status FROM cars WHERE car_id = ?");
            $r->bind_param("i", $carId);
            $r->execute();
            $row = $r->get_result()->fetch_assoc();
            $r->close();

            if ($row) {
                $newStatus = ($row['status'] === 'available') ? 'sold' : 'available';
                $u = $conn->prepare("UPDATE cars SET status = ? WHERE car_id = ?");
                $u->bind_param("si", $newStatus, $carId);
                $u->execute();
                $u->close();
            }
        }

        if ($action === 'delete') {
            // grab image paths before deleting the row. the cascade removes car_images rows but not the files on disk
            $imgs = $conn->prepare("SELECT image_path FROM car_images WHERE car_id = ?");
            $imgs->bind_param("i", $carId);
            $imgs->execute();
            $imagePaths = $imgs->get_result()->fetch_all(MYSQLI_ASSOC);
            $imgs->close();

            // delete the car row. the cascade constraint removes car_images rows automatically
            $del = $conn->prepare("DELETE FROM cars WHERE car_id = ?");
            $del->bind_param("i", $carId);
            $del->execute();
            $del->close();

            // remove the image files from disk
            foreach ($imagePaths as $img) {
                $fullPath = dirname(__DIR__) . '/' . $img['image_path'];
                if (file_exists($fullPath)) {
                    unlink($fullPath);
                }
            }
        }
    }

    $conn->close();
    header("Location: manage-listings.php");
    exit();
}

// fetch all listings
$listings = $conn->query("
    SELECT
        c.car_id,
        c.brand,
        c.model,
        c.year,
        c.price,
        c.status,
        c.created_at,
        u.fname,
        u.lname,
        u.email AS seller_email,
        (SELECT COUNT(*) FROM car_images ci WHERE ci.car_id = c.car_id) AS photo_count
    FROM cars c
    JOIN users u ON c.user_id = u.user_id
    ORDER BY c.created_at DESC
")->fetch_all(MYSQLI_ASSOC);

$conn->close();


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <?php include "../inc/head.inc.php"; ?>
</head>

<body class="d-flex flex-column min-vh-100">

    <?php include "../inc/nav.inc.php"; ?>

    <main id="main-content">

        <!-- page header -->
        <div class="bg-light border-bottom py-3">
            <div class="container-fluid">
                <h1 class="h4 fw-bold mb-0">Manage Listings</h1>
                <p class="text-muted small mb-0"><?= count($listings) ?> listing<?= count($listings) !== 1 ? 's' : '' ?> total</p>
            </div>
        </div>

        <div class="container-fluid py-4">
            <div class="row g-4">

                <?php include "admin-sidebar.inc.php"; ?>

                <div class="col-12 col-md-9 col-xl-10">
                    <div class="card border-0 shadow-sm">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 admin-table">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Car</th>
                                        <th>Seller</th>
                                        <th>Price</th>
                                        <th>Photos</th>
                                        <th>Status</th>
                                        <th>Listed</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($listings)): ?>
                                        <tr>
                                            <td colspan="8" class="text-center text-muted py-4">No listings yet.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($listings as $car): ?>
                                            <tr>
                                                <td class="text-muted small align-middle"><?= $car['car_id'] ?></td>
                                                <td class="align-middle">
                                                    <a href="../car-detail.php?id=<?= $car['car_id'] ?>" target="_blank" class="fw-semibold text-decoration-none">
                                                        <?= htmlspecialchars($car['year'] . ' ' . $car['brand'] . ' ' . $car['model']) ?>
                                                    </a>
                                                </td>
                                                <td class="align-middle">
                                                    <span><?= htmlspecialchars($car['fname'] . ' ' . $car['lname']) ?></span><br>
                                                    <span class="text-muted small"><?= htmlspecialchars($car['seller_email']) ?></span>
                                                </td>
                                                <td class="align-middle text-nowrap">S$ <?= number_format($car['price']) ?></td>
                                                <td class="align-middle text-center"><?= $car['photo_count'] ?></td>
                                                <td class="align-middle">
                                                    <?php if ($car['status'] === 'available'): ?>
                                                        <span class="badge bg-success">Available</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary">Sold</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="align-middle text-muted small text-nowrap">
                                                    <?= date('d M Y', strtotime($car['created_at'])) ?>
                                                </td>
                                                <td class="align-middle text-nowrap">

                                                    <!-- toggle status between available and sold -->
                                                    <form method="post" class="d-inline">
                                                        <input type="hidden" name="action" value="toggle_status">
                                                        <input type="hidden" name="car_id" value="<?= $car['car_id'] ?>">
                                                        <button type="submit"
                                                                class="btn btn-sm <?= $car['status'] === 'available' ? 'btn-outline-secondary' : 'btn-outline-success' ?>"
                                                                title="<?= $car['status'] === 'available' ? 'Mark as Sold' : 'Mark as Available' ?>">
                                                            <?= $car['status'] === 'available' ? 'Mark Sold' : 'Relist' ?>
                                                        </button>
                                                    </form>

                                                    <!-- delete listing -->
                                                    <form method="post" class="d-inline"
                                                          onsubmit="return confirm('Permanently delete this listing and all its photos? This cannot be undone.');">
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="car_id" value="<?= $car['car_id'] ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete listing">
                                                            <span class="material-icons" style="font-size:1rem;vertical-align:middle;">delete</span>
                                                        </button>
                                                    </form>

                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </main>

    <?php include "../inc/footer.inc.php"; ?>

</body>
</html>
