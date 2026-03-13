<?php
// shows the login form. picks up any success or error messages passed back from the processor
session_start();
$root      = "";
$pageTitle = "Login — sgCar";

// already logged in, no need to be here
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$errors  = $_SESSION['errors']  ?? [];
$success = $_SESSION['success'] ?? "";
unset($_SESSION['errors'], $_SESSION['success']);

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

                <h1 class="auth-title">Welcome Back</h1>
                <p class="auth-subtitle">
                    Don't have an account?
                    <a href="register.php" class="text-danger">Register here</a>
                </p>

                <!-- success message e.g. "Account created, please log in" -->
                <?php if ($success): ?>
                    <div class="alert alert-success" role="alert">
                        <?= htmlspecialchars($success) ?>
                    </div>
                <?php endif; ?>

                <!-- login error -->
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger" role="alert">
                        <?php foreach ($errors as $error): ?>
                            <p class="mb-0"><?= htmlspecialchars($error) ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <form action="process_login.php" method="post" novalidate>


                    <div class="mb-3">
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

                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label for="pwd" class="form-label mb-0">Password <span class="text-danger">*</span></label>
                            <a href="forgot-password.php" class="small text-muted">Forgot password?</a>
                        </div>
                        <input type="password"
                               id="pwd"
                               name="pwd"
                               class="form-control"
                               placeholder="Enter your password"
                               required
                               maxlength="72"
                               autocomplete="current-password">
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-sgcar btn-lg">Sign In</button>
                    </div>

                </form>

            </div>
        </div>
    </main>

    <?php include "inc/footer.inc.php"; ?>

</body>
</html>
