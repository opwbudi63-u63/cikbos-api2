<?php
// Menggunakan SQLite biar gak butuh koneksi MySQL/cPanel
try {
    $conn = new PDO("sqlite:" . __DIR__ . "/database.db");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Auto create table
    $conn->exec("CREATE TABLE IF NOT EXISTS keys (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        license_key TEXT UNIQUE NOT NULL,
        max_device INTEGER DEFAULT 1,
        devices TEXT DEFAULT '',
        expired_at DATETIME NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(["success" => false, "message" => "DB_ERROR: " . $e->getMessage()]);
    exit;
}
?>
