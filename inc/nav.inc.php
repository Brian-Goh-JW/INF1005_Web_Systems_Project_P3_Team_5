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
                <?php if (isset($_SESSION['user_id'])):
                    // count unread enquiries for the inbox badge
                    $unreadCount = 0;
                    $config = parse_ini_file('/var/www/private/db-config.ini');
                    if ($config) {
                        $navConn = new mysqli($config['servername'], $config['username'], $config['password'], $config['dbname']);
                        if (!$navConn->connect_error) {
                            $navStmt = $navConn->prepare("
                                SELECT
                                    (SELECT COUNT(*) FROM messages m JOIN enquiries e ON m.enquiry_id = e.enquiry_id JOIN cars c ON e.car_id = c.car_id WHERE c.user_id = ? AND e.is_read = 0 AND m.sender_user_id != ?)
                                  + (SELECT COUNT(*) FROM messages m JOIN enquiries e ON m.enquiry_id = e.enquiry_id WHERE e.sender_user_id = ? AND e.buyer_unread = 1 AND m.sender_user_id != ?)
                            ");
                            $navUserId = (int)$_SESSION['user_id'];
                            $navStmt->bind_param("iiii", $navUserId, $navUserId, $navUserId, $navUserId);
                            $navStmt->execute();
                            $navStmt->bind_result($unreadCount);
                            $navStmt->fetch();
                            $navStmt->close();
                            $navConn->close();
                        }
                    }
                ?>

                    <!-- logged in: Sell My Car + quick links + profile dropdown -->
                    <li class="nav-item">
                        <a class="nav-link" href="<?= $root ?>post-listing.php">
                            <span class="material-icons nav-icon" aria-hidden="true">add_circle</span>
                            Sell My Car
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= $root ?>dashboard.php">
                            <span class="material-icons nav-icon" aria-hidden="true">dashboard</span>
                            Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link position-relative" href="<?= $root ?>inbox.php">
                            <span class="material-icons nav-icon" aria-hidden="true">inbox</span>
                            Inbox
                            <?php if ($unreadCount > 0): ?>
                                <span class="nav-badge" id="inbox-badge"><?= $unreadCount > 9 ? '9+' : $unreadCount ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= $root ?>saved-cars.php">
                            <span class="material-icons nav-icon" aria-hidden="true">favorite</span>
                            Saved
                        </a>
                    </li>
                    <!-- profile dropdown — profile and logout only -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button"
                           data-bs-toggle="dropdown" aria-expanded="false"
                           aria-label="Account menu">
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
                                <a class="dropdown-item" href="<?= $root ?>profile.php">
                                    <span class="material-icons nav-icon" aria-hidden="true">account_circle</span>
                                    My Profile
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

<?php if (isset($_SESSION['user_id'])): ?>
<script>
// poll the unread count every 30 seconds and update the inbox badge without a page reload
(function () {
    function updateBadge() {
        fetch('<?= $root ?>api/unread-count.php')
            .then(function(r) { return r.json(); })
            .then(function(data) {
                var badge = document.getElementById('inbox-badge');
                var count = data.count;
                if (count > 0) {
                    if (!badge) {
                        // create the badge if it doesn't exist yet
                        badge = document.createElement('span');
                        badge.id = 'inbox-badge';
                        badge.className = 'nav-badge';
                        var inboxLink = document.querySelector('a[href*="inbox.php"]');
                        if (inboxLink) inboxLink.appendChild(badge);
                    }
                    badge.textContent = count > 9 ? '9+' : count;
                } else if (badge) {
                    badge.remove();
                }
            })
            .catch(function() {}); // silently ignore errors
    }
    setInterval(updateBadge, 30000); // every 30 seconds
}());
</script>
<?php endif; ?>
