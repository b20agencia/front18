<?php
require_once __DIR__ . '/../../src/Config/config.php';
try {
    $pdo = new PDO('mysql:host='.FRONT18_DB_HOST.';dbname='.FRONT18_DB_NAME, FRONT18_DB_USER, FRONT18_DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("ALTER TABLE saas_origins ADD COLUMN blur_amount INT DEFAULT 25 AFTER display_mode;");
    $pdo->exec("ALTER TABLE saas_origins ADD COLUMN blur_selector VARCHAR(255) DEFAULT 'img, video, iframe, [data-front18=\"locked\"]' AFTER blur_amount;");
    echo "SUCCESS";
} catch(PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column') !== false) {
        echo "SUCCESS (Already exists)";
    } else {
        echo "Error: " . $e->getMessage();
    }
}
