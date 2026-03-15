<?php
// shows the profile page. lets the logged-in user update their name, email, and password
session_start();
$root      = "";
$pageTitle = "My Profile - sgCar";

include "inc/auth.inc.php";

$user_id = (int)$_SESSION['user_id'];

$success = $_SESSION['profile_success'] ?? '';
$errors  = $_SESSION['profile_errors']  ?? [];
$form    = $_SESSION['profile_data']    ?? [];
unset($_SESSION['profile_success'], $_SESSION['profile_errors'], $_SESSION['profile_data']);

include "inc/db.inc.php";

// fetch current user details to pre-fill the form
$stmt = $conn->prepare("SELECT fname, lname, email FROM users WHERE user_id = ? LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();
$conn->close();

if (!$user) {
    header("Location: logout.php");
    exit();
}

// use flashed form data on validation failure, otherwise use db values
$fname = $form['fname'] ?? $user['fname'];
$lname = $form['lname'] ?? $user['lname'];
$email = $form['email'] ?? $user['email'];
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
                <h1 class="h4 fw-bold mb-0">My Profile</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 small">
                        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">My Profile</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="container py-4">
            <div class="row justify-content-center">
                <div class="col-12 col-md-7 col-lg-6">

                    <?php if ($success): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?= htmlspecialchars($success) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0 ps-3">
                                <?php foreach ($errors as $e): ?>
                                    <li><?= htmlspecialchars($e) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <!-- profile avatar -->
                    <div class="text-center mb-4">
                        <span class="material-icons text-muted" style="font-size:5rem;">account_circle</span>
                        <h2 class="h5 fw-bold mt-2 mb-0">
                            <?= htmlspecialchars($user['fname'] . ' ' . $user['lname']) ?>
                        </h2>
                        <p class="text-muted small"><?= htmlspecialchars($user['email']) ?></p>
                    </div>

                    <form action="process-profile.php" method="post" novalidate>

                        <h3 class="h6 fw-bold text-muted text-uppercase mb-3" style="letter-spacing:.05em;">
                            Personal Details
                        </h3>

                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <label for="fname" class="form-label">First Name</label>
                                <input type="text" id="fname" name="fname" class="form-control"
                                       value="<?= htmlspecialchars($fname) ?>" maxlength="45">
                            </div>
                            <div class="col-6">
                                <label for="lname" class="form-label">Last Name <span class="text-danger">*</span></label>
                                <input type="text" id="lname" name="lname" class="form-control"
                                       value="<?= htmlspecialchars($lname) ?>" required maxlength="45">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                            <input type="email" id="email" name="email" class="form-control"
                                   value="<?= htmlspecialchars($email) ?>" required maxlength="100">
                        </div>

                        <hr>

                        <h3 class="h6 fw-bold text-muted text-uppercase mb-1" style="letter-spacing:.05em;">
                            Change Password
                        </h3>
                        <p class="text-muted small mb-3">Leave blank to keep your current password.</p>

                        <div class="mb-3">
                            <label for="current_pwd" class="form-label">Current Password</label>
                            <input type="password" id="current_pwd" name="current_pwd"
                                   class="form-control" maxlength="255" autocomplete="current-password">
                        </div>
                        <div class="mb-3">
                            <label for="new_pwd" class="form-label">New Password</label>
                            <input type="password" id="new_pwd" name="new_pwd"
                                   class="form-control" maxlength="255" autocomplete="new-password">
                            <div class="form-text">Minimum 8 characters.</div>
                        </div>
                        <div class="mb-4">
                            <label for="confirm_pwd" class="form-label">Confirm New Password</label>
                            <input type="password" id="confirm_pwd" name="confirm_pwd"
                                   class="form-control" maxlength="255" autocomplete="new-password">
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-sgcar">Save Changes</button>
                            <a href="dashboard.php" class="btn btn-outline-secondary">Cancel</a>
                        </div>

                    </form>

                </div>
            </div>
        </div>
    </main>

    <?php include "inc/footer.inc.php"; ?>

</body>
</html>
