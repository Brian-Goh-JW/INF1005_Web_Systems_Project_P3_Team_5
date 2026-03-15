<?php
// checks if the user is logged in. if not, redirects them to the login page

if (!isset($_SESSION['user_id'])) {
    header("Location: " . $root . "login.php");
    exit();
}
