<?php
session_start();
$root      = "";
$pageTitle = "About Us — sgCar";
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
                <h1 class="display-5 fw-bold mb-2">About sgCar</h1>
                <p class="lead mb-0" style="color: rgba(255,255,255,0.75);">
                    Singapore's most trusted car marketplace
                </p>
            </div>
        </div>

        <!-- mission -->
        <section class="py-5" aria-label="Our mission">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-12 col-lg-8 text-center">
                        <h2 class="section-title mb-3">Our Mission</h2>
                        <p class="text-muted" style="font-size:1.05rem; line-height:1.9;">
                            At sgCar, we believe buying or selling a car should be simple, transparent, and stress-free.
                            We connect thousands of buyers and sellers across Singapore every day —
                            with no middlemen, no hidden fees, and no nonsense.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- values -->
        <section class="py-5 bg-light" aria-label="Our values">
            <div class="container">
                <h2 class="section-title text-center mb-5">Why Choose sgCar</h2>
                <div class="row g-4 text-center">
                    <div class="col-12 col-md-4">
                        <span class="material-icons text-danger" style="font-size:3rem;">verified</span>
                        <h3 class="h5 fw-bold mt-3">Verified Listings</h3>
                        <p class="text-muted">Every listing is reviewed to ensure accurate and honest information for buyers.</p>
                    </div>
                    <div class="col-12 col-md-4">
                        <span class="material-icons text-danger" style="font-size:3rem;">lock</span>
                        <h3 class="h5 fw-bold mt-3">Secure Platform</h3>
                        <p class="text-muted">Your data is protected. We never share your personal information with third parties.</p>
                    </div>
                    <div class="col-12 col-md-4">
                        <span class="material-icons text-danger" style="font-size:3rem;">support_agent</span>
                        <h3 class="h5 fw-bold mt-3">Local Support</h3>
                        <p class="text-muted">Our team is based in Singapore and available to help you Monday to Saturday.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- contact -->
        <section class="py-5" aria-label="Contact information">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-12 col-md-6 text-center">
                        <h2 class="section-title mb-4">Get In Touch</h2>
                        <address class="text-muted" style="font-style:normal; line-height:2;">
                            <p>
                                <span class="material-icons" style="vertical-align:middle; color:var(--sgcar-red);">location_on</span>
                                78 Orchard Road, Singapore 557803
                            </p>
                            <p>
                                <span class="material-icons" style="vertical-align:middle; color:var(--sgcar-red);">email</span>
                                <a href="mailto:enquiry@sgcar.com" class="text-decoration-none">enquiry@sgcar.com</a>
                            </p>
                            <p>
                                <span class="material-icons" style="vertical-align:middle; color:var(--sgcar-red);">schedule</span>
                                Mon – Sat, 9am – 6pm
                            </p>
                        </address>
                        <a href="listings.php" class="btn btn-sgcar mt-2">Browse Cars</a>
                    </div>
                </div>
            </div>
        </section>

    </main>

    <?php include "inc/footer.inc.php"; ?>

</body>
</html>
