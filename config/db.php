<?php
require_once __DIR__ . '/config.php';

try {
    // Create a new database connection using PDO
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS
    );

    // Set error reporting
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "✅ Database connected successfully!";
} catch (PDOException $e) {
    die("❌ DB connection failed: " . $e->getMessage());
}
