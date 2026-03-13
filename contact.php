<?php
// shows the contact page with a message form and contact details
session_start();
$root      = "";
$pageTitle = "Contact Us — sgCar";

$success = $_SESSION['contact_success'] ?? '';
$errors  = $_SESSION['contact_errors']  ?? [];
$form    = $_SESSION['contact_data']    ?? [];
unset($_SESSION['contact_success'], $_SESSION['contact_errors'], $_SESSION['contact_data']);
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
                <h1 class="display-5 fw-bold mb-2">Contact Us</h1>
                <p class="lead mb-0" style="color: rgba(255,255,255,0.75);">
                    We'd love to hear from you
                </p>
            </div>
        </div>

        <div class="container py-5">
            <div class="row g-5">

                <!-- contact form -->
                <div class="col-12 col-lg-7">
                    <h2 class="h4 fw-bold mb-4">Send Us a Message</h2>

                    <?php if ($success): ?>
                        <div class="alert alert-success" role="alert">
                            <?= htmlspecialchars($success) ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger" role="alert">
                            <ul class="mb-0">
                                <?php foreach ($errors as $e): ?>
                                    <li><?= htmlspecialchars($e) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form action="process-contact.php" method="post" novalidate>
                        <div class="row g-3">

                            <div class="col-12 col-md-6">
                                <label for="contact-name" class="form-label">Your Name <span class="text-danger">*</span></label>
                                <input type="text"
                                       id="contact-name"
                                       name="sender_name"
                                       class="form-control"
                                       placeholder="e.g. Ahmad Bin Ali"
                                       required
                                       maxlength="100"
                                       value="<?= htmlspecialchars($form['sender_name'] ?? '') ?>">
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="contact-email" class="form-label">Email Address <span class="text-danger">*</span></label>
                                <input type="email"
                                       id="contact-email"
                                       name="sender_email"
                                       class="form-control"
                                       placeholder="you@email.com"
                                       required
                                       maxlength="100"
                                       value="<?= htmlspecialchars($form['sender_email'] ?? '') ?>">
                            </div>

                            <div class="col-12">
                                <label for="contact-subject" class="form-label">Subject</label>
                                <input type="text"
                                       id="contact-subject"
                                       name="subject"
                                       class="form-control"
                                       placeholder="e.g. Question about a listing"
                                       maxlength="200"
                                       value="<?= htmlspecialchars($form['subject'] ?? '') ?>">
                            </div>

                            <div class="col-12">
                                <label for="contact-message" class="form-label">Message <span class="text-danger">*</span></label>
                                <textarea id="contact-message"
                                          name="message"
                                          class="form-control"
                                          rows="6"
                                          required
                                          maxlength="2000"
                                          placeholder="How can we help you?"><?= htmlspecialchars($form['message'] ?? '') ?></textarea>
                            </div>

                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-sgcar btn-lg">
                                <span class="material-icons btn-icon" aria-hidden="true">send</span>
                                Send Message
                            </button>
                        </div>
                    </form>
                </div>

                <!-- contact details sidebar -->
                <div class="col-12 col-lg-4 offset-lg-1">
                    <h2 class="h4 fw-bold mb-4">Get In Touch</h2>
                    <address class="text-muted" style="font-style:normal; line-height:2.5;">
                        <p>
                            <span class="material-icons" style="vertical-align:middle; color:var(--sgcar-red);">location_on</span>
                            &nbsp;78 Orchard Road, Singapore 557803
                        </p>
                        <p>
                            <span class="material-icons" style="vertical-align:middle; color:var(--sgcar-red);">email</span>
                            &nbsp;<a href="mailto:enquiry@sgcar.com" class="text-decoration-none">enquiry@sgcar.com</a>
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
