<?php
// connects to the database. include this on any page that needs to run queries

$config = parse_ini_file('/var/www/private/db-config.ini');

if (!$config) {
    die("Error: Could not read the database configuration file.");
}

$conn = new mysqli(
    $config['servername'],
    $config['username'],
    $config['password'],
    $config['dbname']
);

if ($conn->connect_error) {
    die("Error: Database connection failed — " . $conn->connect_error);
}

$conn->set_charset('utf8');