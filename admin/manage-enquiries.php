<?php
// shows all buyer enquiries. admins can delete them, for example to remove spam
session_start();
$root      = "../";
$pageTitle = "Manage Enquiries — sgCar Admin";

include "admin-auth.inc.php";
include "../inc/db.inc.php";

// handle the delete action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $id = isset($_POST['enquiry_id']) ? (int)$_POST['enquiry_id'] : 0;
    if ($id > 0) {
        $del = $conn->prepare("DELETE FROM enquiries WHERE enquiry_id = ?");
        $del->bind_param("i", $id);
        $del->execute();
        $del->close();
    }
    $conn->close();
    header("Location: manage-enquiries.php");
    exit();
}

// fetch all enquiries
$enquiries = $conn->query("
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
    ORDER BY e.created_at DESC
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
                <h1 class="h4 fw-bold mb-0">Manage Enquiries</h1>
                <p class="text-muted small mb-0"><?= count($enquiries) ?> enquir<?= count($enquiries) !== 1 ? 'ies' : 'y' ?> total</p>
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
                                        <th>From</th>
                                        <th>Message</th>
                                        <th>Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($enquiries)): ?>
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-4">No enquiries yet.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($enquiries as $e): ?>
                                            <tr>
                                                <td class="text-muted small align-middle"><?= $e['enquiry_id'] ?></td>
                                                <td class="align-middle">
                                                    <a href="../car-detail.php?id=<?= $e['car_id'] ?>" target="_blank" class="text-decoration-none fw-semibold">
                                                        <?= htmlspecialchars($e['year'] . ' ' . $e['brand'] . ' ' . $e['model']) ?>
                                                    </a>
                                                </td>
                                                <td class="align-middle">
                                                    <span class="fw-semibold"><?= htmlspecialchars($e['sender_name']) ?></span><br>
                                                    <a href="mailto:<?= htmlspecialchars($e['sender_email']) ?>" class="text-muted small">
                                                        <?= htmlspecialchars($e['sender_email']) ?>
                                                    </a>
                                                </td>
                                                <td class="align-middle" style="max-width:300px;">
                                                    <!-- short preview with a read more link for long messages -->
                                                    <div>
                                                        <?php $preview = mb_substr($e['message'], 0, 100); ?>
                                                        <span class="enquiry-preview small text-muted">
                                                            <?= htmlspecialchars($preview) ?><?= mb_strlen($e['message']) > 100 ? '…' : '' ?>
                                                        </span>
                                                        <?php if (mb_strlen($e['message']) > 100): ?>
                                                            <a class="d-block small" href="#" onclick="
                                                                this.previousElementSibling.textContent = this.dataset.full;
                                                                this.remove(); return false;"
                                                               data-full="<?= htmlspecialchars($e['message']) ?>">
                                                                Read more
                                                            </a>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                                <td class="align-middle text-muted small text-nowrap">
                                                    <?= date('d M Y', strtotime($e['created_at'])) ?>
                                                </td>
                                                <td class="align-middle">
                                                    <form method="post"
                                                          onsubmit="return confirm('Delete this enquiry permanently?');">
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="enquiry_id" value="<?= $e['enquiry_id'] ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
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
