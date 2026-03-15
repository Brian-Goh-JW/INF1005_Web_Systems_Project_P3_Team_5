<!-- shared footer, included on every page -->
<footer class="sgcar-footer mt-auto">
    <div class="container">
        <div class="row gy-4">

            <!-- brand blurb -->
            <div class="col-12 col-md-4">
                <p class="footer-brand">
                    <span class="brand-sg">sg</span><span class="brand-car">Car</span>
                </p>
                <p class="footer-tagline">Singapore's trusted platform for buying and selling cars.</p>
            </div>

            <!-- quick links -->
            <div class="col-6 col-md-2">
                <p class="footer-heading">Quick Links</p>
                <ul class="footer-links list-unstyled">
                    <li><a href="<?= $root ?>index.php">Home</a></li>
                    <li><a href="<?= $root ?>listings.php">Browse Cars</a></li>
                    <li><a href="<?= $root ?>about.php">About Us</a></li>
                    <li><a href="<?= $root ?>terms.php">Terms &amp; Conditions</a></li>
                    <li><a href="<?= $root ?>contact.php">Contact Us</a></li>
                </ul>
            </div>

            <!-- account links -->
            <div class="col-6 col-md-2">
                <p class="footer-heading">Account</p>
                <ul class="footer-links list-unstyled">
                    <li><a href="<?= $root ?>register.php">Register</a></li>
                    <li><a href="<?= $root ?>login.php">Login</a></li>
                    <li><a href="<?= $root ?>post-listing.php">Sell My Car</a></li>
                </ul>
            </div>

            <!-- contact details — using <address> is the semantically correct tag for this -->
            <div class="col-12 col-md-4">
                <p class="footer-heading">Contact</p>
                <address class="footer-address">
                    <p>
                        <span class="material-icons footer-icon" aria-hidden="true">location_on</span>
                        78 Orchard Road, Singapore 557803
                    </p>
                    <p>
                        <span class="material-icons footer-icon" aria-hidden="true">email</span>
                        <a href="mailto:enquiry@sgcar.com">enquiry@sgcar.com</a>
                    </p>
                    <p>
                        <span class="material-icons footer-icon" aria-hidden="true">phone</span>
                        +65 6850 3346
                    </p>
                </address>
            </div>

        </div>

        <hr class="footer-divider">

        <div class="footer-bottom d-flex flex-column flex-md-row justify-content-between align-items-center">
            <p class="mb-0">
                &copy; <?= date('Y') ?> sgCar. All rights reserved.
            </p>
        </div>

    </div>
</footer>
