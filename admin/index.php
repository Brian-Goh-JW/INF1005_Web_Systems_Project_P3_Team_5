<?php
// shows the admin dashboard. pulls platform stats and the 10 most recent enquiries
session_start();
$root      = "../";
$pageTitle = "Admin Dashboard — sgCar";

include "admin-auth.inc.php";
include "../inc/db.inc.php";

// pull the platform stats

// count of active listings
$r = $conn->query("SELECT COUNT(*) AS n FROM cars WHERE status = 'available'");
$totalActive = (int)$r->fetch_assoc()['n'];

// count of sold listings
$r = $conn->query("SELECT COUNT(*) AS n FROM cars WHERE status = 'sold'");
$totalSold = (int)$r->fetch_assoc()['n'];

// count of registered users, not counting admins
$r = $conn->query("SELECT COUNT(*) AS n FROM users WHERE role = 'user'");
$totalUsers = (int)$r->fetch_assoc()['n'];

// total enquiries
$r = $conn->query("SELECT COUNT(*) AS n FROM enquiries");
$totalEnquiries = (int)$r->fetch_assoc()['n'];

// fetch the 10 most recent enquiries
$enquiries = $conn->query("
    SELECT
        e.enquiry_id,
        e.sender_name,
        e.sender_email,
        LEFT(e.message, 80) AS preview,
        e.created_at,
        c.brand,
        c.model,
        c.year,
        c.car_id
    FROM enquiries e
    JOIN cars c ON e.car_id = c.car_id
    ORDER BY e.created_at DESC
    LIMIT 10
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
                <h1 class="h4 fw-bold mb-0">Admin Dashboard</h1>
                <p class="text-muted small mb-0">Welcome back, <?= htmlspecialchars($_SESSION['fname']) ?></p>
            </div>
        </div>

        <div class="container-fluid py-4">
            <div class="row g-4">

                <!-- admin sidebar -->
                <?php include "admin-sidebar.inc.php"; ?>

                <!-- main content -->
                <div class="col-12 col-md-9 col-xl-10">

                    <!-- stats cards -->
                    <div class="row g-3 mb-4">

                        <div class="col-6 col-xl-3">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-body">
                                    <p class="text-muted small mb-1">Active Listings</p>
                                    <p class="h2 fw-bold mb-0 text-success"><?= number_format($totalActive) ?></p>
                                </div>
                            </div>
                        </div>

                        <div class="col-6 col-xl-3">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-body">
                                    <p class="text-muted small mb-1">Sold Cars</p>
                                    <p class="h2 fw-bold mb-0 text-secondary"><?= number_format($totalSold) ?></p>
                                </div>
                            </div>
                        </div>

                        <div class="col-6 col-xl-3">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-body">
                                    <p class="text-muted small mb-1">Registered Users</p>
                                    <p class="h2 fw-bold mb-0" style="color:var(--sgcar-red)"><?= number_format($totalUsers) ?></p>
                                </div>
                            </div>
                        </div>

                        <div class="col-6 col-xl-3">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-body">
                                    <p class="text-muted small mb-1">Total Enquiries</p>
                                    <p class="h2 fw-bold mb-0 text-primary"><?= number_format($totalEnquiries) ?></p>
                                </div>
                            </div>
                        </div>

                    </div>
                    <!-- end stats cards -->

                    <!-- recent enquiries -->
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center">
                            <span>
                                <span class="material-icons btn-icon" aria-hidden="true">mail</span>
                                Recent Enquiries
                            </span>
                            <a href="manage-enquiries.php" class="btn btn-outline-secondary btn-sm">View All</a>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 admin-table">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Car</th>
                                        <th>From</th>
                                        <th>Preview</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($enquiries)): ?>
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-3">No enquiries yet.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($enquiries as $e): ?>
                                            <tr>
                                                <td class="text-muted small"><?= $e['enquiry_id'] ?></td>
                                                <td>
                                                    <a href="../car-detail.php?id=<?= $e['car_id'] ?>">
                                                        <?= htmlspecialchars($e['year'] . ' ' . $e['brand'] . ' ' . $e['model']) ?>
                                                    </a>
                                                </td>
                                                <td>
                                                    <span class="fw-semibold"><?= htmlspecialchars($e['sender_name']) ?></span><br>
                                                    <span class="text-muted small"><?= htmlspecialchars($e['sender_email']) ?></span>
                                                </td>
                                                <td class="text-muted small">
                                                    <?= htmlspecialchars($e['preview']) ?><?= strlen($e['preview']) >= 80 ? '…' : '' ?>
                                                </td>
                                                <td class="text-muted small text-nowrap">
                                                    <?= date('d M Y', strtotime($e['created_at'])) ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <!-- end recent enquiries -->

                </div>
                <!-- end main content -->

            </div>
        </div>

    </main>

    <?php include "../inc/footer.inc.php"; ?>

</body>
</html>
