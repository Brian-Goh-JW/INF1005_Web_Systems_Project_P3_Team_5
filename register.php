<?php
// shows the registration form. displays errors or old form values passed back from process_register.php
session_start();
$root      = "";
$pageTitle = "Register — sgCar";

// grab any errors or old form values passed back from the processor
$errors    = $_SESSION['errors']    ?? [];
$formData  = $_SESSION['form_data'] ?? [];
$success   = $_SESSION['success']   ?? "";

// clear them now that we've read them. they're single-use
unset($_SESSION['errors'], $_SESSION['form_data'], $_SESSION['success']);

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

                <!-- title -->
                <h1 class="auth-title">Create an Account</h1>
                <p class="auth-subtitle">
                    Already have an account?
                    <a href="login.php" class="text-danger">Sign in here</a>
                </p>

                <!-- success message (shown after a redirect, e.g. if needed) -->
                <?php if ($success): ?>
                    <div class="alert alert-success" role="alert">
                        <?= htmlspecialchars($success) ?>
                    </div>
                <?php endif; ?>

                <!-- error summary — lists all validation errors at once -->
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger" role="alert">
                        <strong>Please fix the following:</strong>
                        <ul class="mb-0 mt-1">
                            <?php foreach ($errors as $error): ?>
                                <li><?= htmlspecialchars($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <!-- registration form
                     action points to process_register.php which handles all
                     the validation, sanitization and DB insert server-side. -->
                <form action="process_register.php" method="post" novalidate>


                    <!-- first name is optional — some people only have one name -->
                    <div class="mb-3">
                        <label for="fname" class="form-label">First Name <span class="text-muted small">(optional)</span></label>
                        <input type="text"
                               id="fname"
                               name="fname"
                               class="form-control"
                               placeholder="e.g. Wei Ming"
                               maxlength="45"
                               value="<?= htmlspecialchars($formData['fname'] ?? '') ?>">
                    </div>

                    <div class="mb-3">
                        <label for="lname" class="form-label">Last Name <span class="text-danger">*</span></label>
                        <input type="text"
                               id="lname"
                               name="lname"
                               class="form-control"
                               placeholder="e.g. Tan"
                               required
                               maxlength="45"
                               value="<?= htmlspecialchars($formData['lname'] ?? '') ?>">
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                        <input type="email"
                               id="email"
                               name="email"
                               class="form-control"
                               placeholder="you@email.com"
                               required
                               maxlength="100"
                               value="<?= htmlspecialchars($formData['email'] ?? '') ?>">
                    </div>

                    <div class="mb-3">
                        <label for="pwd" class="form-label">Password <span class="text-danger">*</span></label>
                        <input type="password"
                               id="pwd"
                               name="pwd"
                               class="form-control"
                               placeholder="Minimum 8 characters"
                               required
                               minlength="8"
                               maxlength="72">
                        <div class="form-text">At least 8 characters.</div>
                    </div>

                    <div class="mb-3">
                        <label for="pwd_confirm" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                        <input type="password"
                               id="pwd_confirm"
                               name="pwd_confirm"
                               class="form-control"
                               placeholder="Re-enter your password"
                               required
                               maxlength="72">
                    </div>

                    <div class="mb-4 form-check">
                        <input type="checkbox"
                               id="agree"
                               name="agree"
                               class="form-check-input"
                               required>
                        <label class="form-check-label" for="agree">
                            I agree to the <a href="terms.php" class="text-danger">Terms &amp; Conditions</a>
                        </label>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-sgcar btn-lg">Create Account</button>
                    </div>

                </form>

            </div>
        </div>
    </main>

    <?php include "inc/footer.inc.php"; ?>

</body>
</html>
