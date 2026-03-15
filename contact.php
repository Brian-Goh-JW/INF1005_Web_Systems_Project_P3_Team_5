<?php
// contact page — builds a mailto link from subject and message
session_start();
$root      = "";
$pageTitle = "Contact Us — sgCar";
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

        <div class="py-5 text-white text-center" style="background: linear-gradient(135deg, var(--sgcar-dark) 0%, #16213e 100%);">
            <div class="container">
                <h1 class="display-5 fw-bold mb-2">Contact Us</h1>
                <p class="lead mb-0" style="color: rgba(255,255,255,0.75);">We'd love to hear from you</p>
            </div>
        </div>

        <div class="container py-5">
            <div class="row justify-content-center">
                <div class="col-12 col-md-6">
                    <h2 class="h4 fw-bold mb-4 text-center">Get In Touch</h2>
                    <address class="text-muted" style="font-style:normal; line-height:3;">
                        <p>
                            <span class="material-icons" style="vertical-align:middle; color:var(--sgcar-red);">location_on</span>
                            &nbsp;78 Orchard Road, Singapore 557803
                        </p>
                        <p>
                            <span class="material-icons" style="vertical-align:middle; color:var(--sgcar-red);">email</span>
                            &nbsp;<a href="mailto:enquiry@sgcar.com" class="text-decoration-none">enquiry@sgcar.com</a>
                        </p>
                        <p>
                            <span class="material-icons" style="vertical-align:middle; color:var(--sgcar-red);">phone</span>
                            &nbsp;+65 6850 3346
                        </p>
                        <p>
                            <span class="material-icons" style="vertical-align:middle; color:var(--sgcar-red);">schedule</span>
                            &nbsp;Mon &ndash; Sat, 9am &ndash; 6pm
                        </p>
                    </address>
                </div>
            </div>
        </div>

    </main>

    <?php include "inc/footer.inc.php"; ?>


</body>
</html>
