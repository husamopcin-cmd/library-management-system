<?php

// Bu dosyayı kopyala: cp includes/db.example.php includes/db.php
// Sonra kendi bilgilerini gir.

define('DB_HOST', 'localhost');          // örn: sql210.byethost12.com
define('DB_USER', 'db_kullanici_adi');   // örn: b12_12345678
define('DB_PASS', 'db_sifreniz');
define('DB_NAME', 'db_adi');             // örn: b12_12345678_library
define('SITE_NAME', 'LibraryMS');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$conn->set_charset('utf8mb4');

if ($conn->connect_error) {
    die('<div style="font-family:sans-serif;padding:2rem;color:#c0392b;">
        <h3>Database Connection Error</h3>
        <p>Please make sure the database is set up correctly.</p>
        <code>' . $conn->connect_error . '</code>
    </div>');
}
