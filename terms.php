<?php
// shows the terms and conditions page
session_start();
$root      = "";
$pageTitle = "Terms & Conditions — sgCar";
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

        <!-- page hero -->
        <div class="py-5 text-white text-center" style="background: linear-gradient(135deg, var(--sgcar-dark) 0%, #16213e 100%);">
            <div class="container">
                <h1 class="display-5 fw-bold mb-2">Terms &amp; Conditions</h1>
                <p class="lead mb-0" style="color: rgba(255,255,255,0.75);">
                    Last updated: <?= date('d F Y') ?>
                </p>
            </div>
        </div>

        <!-- content -->
        <section class="py-5">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-12 col-lg-8">

                        <p class="text-muted mb-5">
                            Please read these terms carefully before using sgCar. By registering an account or using any part of this platform, you agree to be bound by the terms below.
                        </p>

                        <h2 class="h5 fw-bold mb-2">1. Acceptance of Terms</h2>
                        <p class="text-muted mb-4">
                            By accessing or using sgCar, you confirm that you are at least 18 years old and legally able to enter into a binding agreement. If you do not agree to these terms, please do not use the platform.
                        </p>

                        <h2 class="h5 fw-bold mb-2">2. Use of the Platform</h2>
                        <p class="text-muted mb-4">
                            sgCar is an online marketplace for buying and selling used cars in Singapore. You may use the platform only for lawful purposes. You must not use sgCar to post false, misleading, or fraudulent listings, harass other users, or attempt to circumvent any security measures.
                        </p>

                        <h2 class="h5 fw-bold mb-2">3. Listings &amp; Content</h2>
                        <p class="text-muted mb-4">
                            You are solely responsible for the accuracy of any listing you post. sgCar does not verify the details of individual listings and accepts no liability for transactions between buyers and sellers. We reserve the right to remove any listing that violates these terms without prior notice.
                        </p>

                        <h2 class="h5 fw-bold mb-2">4. Account Responsibility</h2>
                        <p class="text-muted mb-4">
                            You are responsible for maintaining the confidentiality of your account credentials. Any activity that occurs under your account is your responsibility. Please notify us immediately if you suspect unauthorised access.
                        </p>

                        <h2 class="h5 fw-bold mb-2">5. Privacy</h2>
                        <p class="text-muted mb-4">
                            We collect only the information necessary to operate the platform — your name, email address, and any listing details you submit. We do not sell or share your personal data with third parties for marketing purposes.
                        </p>

                        <h2 class="h5 fw-bold mb-2">6. Disclaimer</h2>
                        <p class="text-muted mb-4">
                            sgCar is provided on an "as is" basis. We make no warranties regarding the availability, accuracy, or reliability of the platform. We are not a party to any transaction between buyers and sellers and accept no liability for any loss arising from use of this platform.
                        </p>

                        <h2 class="h5 fw-bold mb-2">7. Changes to These Terms</h2>
                        <p class="text-muted mb-4">
                            We may update these terms from time to time. The date at the top of this page reflects the most recent revision. Continued use of sgCar after changes are posted constitutes your acceptance of the updated terms.
                        </p>

                        <h2 class="h5 fw-bold mb-2">8. Contact</h2>
                        <p class="text-muted mb-0">
                            If you have any questions about these terms, please contact us at
                            <a href="mailto:enquiry@sgcar.com" class="text-danger text-decoration-none">enquiry@sgcar.com</a>.
                        </p>

                    </div>
                </div>
            </div>
        </section>

    </main>

    <?php include "inc/footer.inc.php"; ?>

</body>
</html>
