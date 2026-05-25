<?php

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'library_db');
define('SITE_NAME', 'LibraryMS');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$conn->set_charset('utf8mb4');

if ($conn->connect_error) {
    die('<div style="font-family:sans-serif;padding:2rem;color:#c0392b;">
        <h3>Database Connection Error</h3>
        <p>Please make sure XAMPP is running and you have imported the database.sql file.</p>
        <code>' . $conn->connect_error . '</code>
    </div>');
}
