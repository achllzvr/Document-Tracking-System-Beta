<?php
// Simple PDO database helper. Update credentials as needed.
$DB_HOST = '127.0.0.1';
$DB_NAME = 'CHED_document_repository';
$DB_USER = 'root';
$DB_PASS = '';

try {
    $dsn = "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4";
    $pdo = new PDO($dsn, $DB_USER, $DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (Exception $e) {
    // In production you would hide this
    echo "<h2>Database connection failed:</h2>" . htmlspecialchars($e->getMessage());
    exit;
}

function h($s) { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

?>
