<!-- shared nav bar. session-aware: shows account dropdown when logged in, login/register when not -->

<!-- skip navigation — only visible when focused by a keyboard user -->
<a class="skip-link" href="#main-content">Skip to main content</a>

<nav class="navbar navbar-expand-lg sgcar-navbar" aria-label="Main navigation">
    <div class="container">

        <!-- brand logo -->
        <a class="navbar-brand" href="<?= $root ?>index.php">
            <span class="brand-sg">sg</span><span class="brand-car">Car</span>
        </a>

        <!-- hamburger button — only visible on mobile, collapses the menu -->
        <button class="navbar-toggler" type="button"
                data-bs-toggle="collapse"
                data-bs-target="#mainNavbar"
                aria-controls="mainNavbar"
                aria-expanded="false"
                aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- the actual nav links — collapses on small screens -->
        <div class="collapse navbar-collapse" id="mainNavbar">

            <!-- left side links -->
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="<?= $root ?>index.php">
                        <span class="material-icons nav-icon" aria-hidden="true">home</span>
                        Home
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= $root ?>listings.php">
                        <span class="material-icons nav-icon" aria-hidden="true">directions_car</span>
                        Browse Cars
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= $root ?>about.php">
                        <span class="material-icons nav-icon" aria-hidden="true">info</span>
                        About
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= $root ?>contact.php">
                        <span class="material-icons nav-icon" aria-hidden="true">mail</span>
                        Contact
                    </a>
                </li>
            </ul>

            <!-- right side — changes based on whether the user is logged in -->
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-lg-center">
                <?php if (isset($_SESSION['user_id'])): ?>

                    <!-- logged in: show Sell My Car + account dropdown -->
                    <li class="nav-item">
                        <a class="nav-link" href="<?= $root ?>post-listing.php">
                            <span class="material-icons nav-icon" aria-hidden="true">add_circle</span>
                            Sell My Car
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button"
                           data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="material-icons nav-icon" aria-hidden="true">account_circle</span>
                            <?= htmlspecialchars($_SESSION['fname'] ?? 'Account') ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                                <li>
                                    <a class="dropdown-item" href="<?= $root ?>admin/index.php">
                                        <span class="material-icons nav-icon" aria-hidden="true">admin_panel_settings</span>
                                        Admin Panel
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                            <?php endif; ?>
                            <li>
                                <a class="dropdown-item" href="<?= $root ?>dashboard.php">
                                    <span class="material-icons nav-icon" aria-hidden="true">dashboard</span>
                                    My Dashboard
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="<?= $root ?>inbox.php">
                                    <span class="material-icons nav-icon" aria-hidden="true">inbox</span>
                                    My Inbox
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="<?= $root ?>saved-cars.php">
                                    <span class="material-icons nav-icon" aria-hidden="true">favorite</span>
                                    Saved Cars
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item" href="<?= $root ?>logout.php">
                                    <span class="material-icons nav-icon" aria-hidden="true">logout</span>
                                    Logout
                                </a>
                            </li>
                        </ul>
                    </li>

                <?php else: ?>

                    <!-- not logged in: show Login link and Register button -->
                    <li class="nav-item">
                        <a class="nav-link" href="<?= $root ?>login.php">
                            <span class="material-icons nav-icon" aria-hidden="true">login</span>
                            Login
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-sgcar ms-lg-2" href="<?= $root ?>register.php">
                            Register
                        </a>
                    </li>

                <?php endif; ?>
            </ul>

        </div><!-- end collapsible nav -->
    </div><!-- end container -->
</nav>
