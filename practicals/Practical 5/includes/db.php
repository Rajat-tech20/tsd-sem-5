<?php
// ============================================================
//  DATABASE CONNECTION — PDO
//  includes/db.php
// ============================================================

define('DB_HOST', 'localhost');
define('DB_NAME', 'chronicle_db');
define('DB_USER', 'root');   // XAMPP default user
define('DB_PASS', '');       // XAMPP default password (empty)

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    // In production, log this error — never expose it to users
    die(json_encode(['error' => 'Database connection failed.']));
}
