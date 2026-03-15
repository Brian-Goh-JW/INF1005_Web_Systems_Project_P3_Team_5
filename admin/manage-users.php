<?php
// shows all registered users. admins can promote users to admin or demote them back
session_start();
$root      = "../";
$pageTitle = "Manage Users — sgCar Admin";

include "admin-auth.inc.php";
include "../inc/db.inc.php";

// handle the role toggle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'toggle_role') {
    $targetId = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;

    // safety check — an admin cannot remove their own access
    if ($targetId > 0 && $targetId !== (int)$_SESSION['user_id']) {
        $r = $conn->prepare("SELECT role FROM users WHERE user_id = ?");
        $r->bind_param("i", $targetId);
        $r->execute();
        $row = $r->get_result()->fetch_assoc();
        $r->close();

        if ($row) {
            $newRole = ($row['role'] === 'admin') ? 'user' : 'admin';
            $u = $conn->prepare("UPDATE users SET role = ? WHERE user_id = ?");
            $u->bind_param("si", $newRole, $targetId);
            $u->execute();
            $u->close();
        }
    }

    $conn->close();
    header("Location: manage-users.php");
    exit();
}

// fetch all users with their listing count
$users = $conn->query("
    SELECT
        u.user_id,
        u.fname,
        u.lname,
        u.email,
        u.role,
        u.created_at,
        COUNT(c.car_id) AS listing_count
    FROM users u
    LEFT JOIN cars c ON u.user_id = c.user_id
    GROUP BY u.user_id
    ORDER BY u.created_at DESC
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
                <h1 class="h4 fw-bold mb-0">Manage Users</h1>
                <p class="text-muted small mb-0"><?= count($users) ?> registered user<?= count($users) !== 1 ? 's' : '' ?></p>
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
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Listings</th>
                                        <th>Joined</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($users)): ?>
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-4">No users found.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($users as $user): ?>
                                            <?php $isSelf = ((int)$user['user_id'] === (int)$_SESSION['user_id']); // flag the current admin's row. ?>
                                            <tr <?= $isSelf ? 'class="table-active"' : '' ?>>
                                                <td class="text-muted small align-middle"><?= $user['user_id'] ?></td>
                                                <td class="align-middle fw-semibold">
                                                    <?= htmlspecialchars($user['fname'] . ' ' . $user['lname']) ?>
                                                    <?php if ($isSelf): ?>
                                                        <span class="badge bg-secondary ms-1 small">You</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="align-middle text-muted small"><?= htmlspecialchars($user['email']) ?></td>
                                                <td class="align-middle">
                                                    <?php if ($user['role'] === 'admin'): ?>
                                                        <span class="badge" style="background-color:var(--sgcar-red-dark)">Admin</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-light text-dark border">User</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="align-middle text-center"><?= $user['listing_count'] ?></td>
                                                <td class="align-middle text-muted small text-nowrap">
                                                    <?= date('d M Y', strtotime($user['created_at'])) ?>
                                                </td>
                                                <td class="align-middle">
                                                    <?php if (!$isSelf): // hide the role toggle on your own row. ?>
                                                        <form method="post"
                                                              onsubmit="return confirm('Change this user\'s role?');">
                                                            <input type="hidden" name="action" value="toggle_role">
                                                            <input type="hidden" name="user_id" value="<?= $user['user_id'] ?>">
                                                            <button type="submit"
                                                                    class="btn btn-sm <?= $user['role'] === 'admin' ? 'btn-outline-warning' : 'btn-outline-primary' ?>">
                                                                <?= $user['role'] === 'admin' ? 'Demote' : 'Make Admin' ?>
                                                            </button>
                                                        </form>
                                                    <?php else: ?>
                                                        <span class="text-muted small">—</span>
                                                    <?php endif; ?>
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
