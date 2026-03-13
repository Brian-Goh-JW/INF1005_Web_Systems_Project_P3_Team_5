<?php
// checks if the user is a logged-in admin. redirects them out if not
if (!isset($_SESSION['user_id'])) {
    header("Location: {$root}login.php");
    exit();
}
if ($_SESSION['role'] !== 'admin') {
    header("Location: {$root}index.php");
    exit();
}
