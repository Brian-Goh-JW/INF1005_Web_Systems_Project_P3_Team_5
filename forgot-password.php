<?php
// shows the forgot password form. users enter their email to receive a password reset link
session_start();
$root      = "";
$pageTitle = "Forgot Password â sgCar";

// already logged in, no need to be here
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$msg   = $_SESSION['fp_msg']   ?? '';
$error = $_SESSION['fp_error'] ?? '';
unset($_SESSION['fp_msg'], $_SESSION['fp_error']);
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

                <h1 class="auth-title">Forgot Password</h1>
                <p class="auth-subtitle">Enter your email and we'll send you a reset link.</p>

                <?php if ($msg): ?>
                    <div class="alert alert-success" role="alert">
                        <?= htmlspecialchars($msg) ?>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger" role="alert">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form action="process-forgot-password.php" method="post" novalidate>

                    <div class="mb-4">
                        <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                        <input type="email"
                               id="email"
                               name="email"
                               class="form-control"
                               placeholder="you@email.com"
                               required
                               maxlength="100"
                               autocomplete="email">
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-sgcar btn-lg">Send Reset Link</button>
                    </div>

                </form>

                <hr class="my-3">

                <p class="text-center small text-muted mb-0">
                    Remembered it? <a href="login.php" class="text-danger">Back to Login</a>
                </p>

            </div>
        </div>
    </main>

    <?php include "inc/footer.inc.php"; ?>

</body>
</html>
