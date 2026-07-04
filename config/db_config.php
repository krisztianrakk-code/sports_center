<?php
/**
 * Database Configuration and Connection Provider
 * Path: config/db_config.php
 */

define('DB_HOST', 'localhost');
define('DB_USER', 'bt');                   // 'root' helyett 'bt'
define('DB_PASS', 'nEQqNiW0WpnEmGt');      // Üres helyett a szerver jelszava
define('DB_NAME', 'bt');

try {
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    // Globális PDO objektum létrehozása
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}