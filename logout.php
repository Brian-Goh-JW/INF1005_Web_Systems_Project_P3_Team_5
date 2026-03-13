<?php
// logs the user out. clears the session and redirects home
session_start();

// remove all session variables
session_unset();

// destroy the session
session_destroy();

// send them home
header("Location: index.php");
exit();
