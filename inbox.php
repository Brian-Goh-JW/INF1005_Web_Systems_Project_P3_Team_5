<?php
// shows the seller inbox — all enquiries received on the user's own listings
session_start();
$root      = "";
$pageTitle = "My Inbox — sgCar";

include "inc/auth.inc.php";

$user_id = (int)$_SESSION['user_id'];

// optional filter: show enquiries for a specific car only (linked from dashboard)
$filterCar = isset($_GET['car']) ? (int)$_GET['car'] : 0;

include "inc/db.inc.php";

// build query — join enquiries to cars and filter by the car owner
$conditions = ["c.user_id = ?"];
$types      = "i";
$params     = [$user_id];

if ($filterCar > 0) {
    $conditions[] = "e.car_id = ?";
    $types       .= "i";
    $params[]     = $filterCar;
}

$whereClause = implode(" AND ", $conditions);

$stmt = $conn->prepare("
    SELECT
        e.enquiry_id,
        e.sender_name,
        e.sender_email,
        e.message,
        e.created_at,
        c.car_id,
        c.brand,
        c.model,
        c.year
    FROM enquiries e
    JOIN cars c ON e.car_id = c.car_id
    WHERE $whereClause
    ORDER BY e.created_at DESC
");
$stmt->bind_param($types, ...$params);
$stmt->execute();
$enquiries = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// if filtering by car, fetch the car title for the heading
$filterCarTitle = '';
if ($filterCar > 0 && !empty($enquiries)) {
    $first = $enquiries[0];
    $filterCarTitle = $first['year'] . ' ' . $first['brand'] . ' ' . $first['model'];
}

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
                <h1 class="h4 fw-bold mb-0">My Inbox</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 small">
                        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Inbox</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="container py-4">

            <!-- filter notice -->
            <?php if ($filterCar > 0): ?>
                <div class="alert alert-info d-flex align-items-center gap-2 py-2">
                    <span class="material-icons" style="font-size:1.1rem;">filter_list</span>
                    <span>
                        Showing enquiries for <strong><?= htmlspecialchars($filterCarTitle) ?></strong>.
                        <a href="inbox.php" class="ms-1">Show all</a>
                    </span>
                </div>
            <?php endif; ?>

            <p class="text-muted small mb-3">
                <strong><?= count($enquiries) ?></strong>
                enquir<?= count($enquiries) !== 1 ? 'ies' : 'y' ?> received
            </p>

            <?php if (empty($enquiries)): ?>
                <div class="text-center py-5 text-muted border rounded">
                    <span class="material-icons" style="font-size:3rem;">inbox</span>
                    <p class="mt-2">No enquiries yet. When buyers message you, they'll appear here.</p>
                </div>
            <?php else: ?>

                <div class="d-flex flex-column gap-3">
                    <?php foreach ($enquiries as $e): ?>
                        <div class="card shadow-sm border-0">
                            <div class="card-body">

                                <!-- car this enquiry is for -->
                                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-2">
                                    <a href="car-detail.php?id=<?= (int)$e['car_id'] ?>"
                                       class="text-decoration-none fw-semibold text-dark small">
                                        <span class="material-icons" style="font-size:0.9rem;vertical-align:middle;color:var(--sgcar-red);">directions_car</span>
                                        <?= htmlspecialchars($e['year'] . ' ' . $e['brand'] . ' ' . $e['model']) ?>
                                    </a>
                                    <span class="text-muted small text-nowrap">
                                        <?= date('d M Y, g:ia', strtotime($e['created_at'])) ?>
                                    </span>
                                </div>

                                <!-- sender details -->
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <span class="material-icons text-muted" style="font-size:1.5rem;">account_circle</span>
                                    <div>
                                        <p class="mb-0 fw-semibold"><?= htmlspecialchars($e['sender_name']) ?></p>
                                        <a href="mailto:<?= htmlspecialchars($e['sender_email']) ?>"
                                           class="text-muted small text-decoration-none">
                                            <?= htmlspecialchars($e['sender_email']) ?>
                                        </a>
                                    </div>
                                </div>

                                <!-- message -->
                                <p class="mb-0 text-muted" style="line-height:1.7;">
                                    <?= nl2br(htmlspecialchars($e['message'])) ?>
                                </p>

                                <!-- quick reply link -->
                                <div class="mt-3">
                                    <a href="mailto:<?= htmlspecialchars($e['sender_email']) ?>?subject=Re: <?= urlencode($e['year'] . ' ' . $e['brand'] . ' ' . $e['model']) ?>"
                                       class="btn btn-sm btn-sgcar">
                                        <span class="material-icons btn-icon" aria-hidden="true">reply</span>
                                        Reply via Email
                                    </a>
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
