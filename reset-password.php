<?php
// shows the reset password form. validates the token from the url before displaying
session_start();
$root      = "";
$pageTitle = "Reset Password  -  sgCar";

$token = trim($_GET['token'] ?? '');
$valid = false;
$email = '';

if ($token !== '') {
    include "inc/db.inc.php";

    $stmt = $conn->prepare("SELECT email FROM password_resets WHERE token = ? AND expires_at > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($row = $res->fetch_assoc()) {
        $valid = true;
        $email = $row['email'];
    }

    $stmt->close();
    $conn->close();
}

$error = $_SESSION['rp_error'] ?? '';
unset($_SESSION['rp_error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <?php include "inc/head.inc.php"; ?>
</head>

<body class="d-flex flex-column min-vh-100">

    <?php include "inc/nav.inc.php"; ?>

    <main id="main-content" class="auth-section">
        <div class="container">
            <div class="auth-card">

                <?php if (!$valid): ?>

                    <!-- token is missing, expired, or already used -->
                    <h1 class="auth-title">Link Expired</h1>
                    <p class="auth-subtitle text-muted">
                        This reset link is invalid or has expired. Links are only valid for 1 hour.
                    </p>
                    <div class="d-grid mt-3">
                        <a href="forgot-password.php" class="btn btn-sgcar btn-lg">Request a New Link</a>
                    </div>

                <?php else: ?>

                    <!-- valid token -- show the new password form -->
                    <h1 class="auth-title">Reset Password</h1>
                    <p class="auth-subtitle">
                        Setting a new password for <strong><?= htmlspecialchars($email) ?></strong>
                    </p>

                    <?php if ($error): ?>
                        <div class="alert alert-danger" role="alert">
                            <?= htmlspecialchars($error) ?>
                        </div>
                    <?php endif; ?>

                    <form action="process-reset-password.php" method="post" novalidate>

                        <!-- carry the token through to the processor -->
                        <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

                        <div class="mb-3">
                            <label for="password" class="form-label">New Password <span class="text-danger">*</span></label>
                            <input type="password"
                                   id="password"
                                   name="password"
                                   class="form-control"
                                   placeholder="Minimum 8 characters"
                                   required
                                   minlength="8"
                                   maxlength="72"
                                   autocomplete="new-password">
                        </div>

                        <div class="mb-4">
                            <label for="confirm" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                            <input type="password"
                                   id="confirm"
                                   name="confirm"
                                   class="form-control"
                                   placeholder="Re-enter your new password"
                                   required
                                   maxlength="72"
                                   autocomplete="new-password">
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-sgcar btn-lg">Save New Password</button>
                        </div>

                    </form>

                <?php endif; ?>

            </div>
        </div>
    </main>

    <?php include "inc/footer.inc.php"; ?>

</body>
</html>
