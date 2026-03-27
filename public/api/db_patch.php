<?php
require_once __DIR__ . '/../../src/Config/config.php';
try {
    $pdo = new PDO('mysql:host='.FRONT18_DB_HOST.';dbname='.FRONT18_DB_NAME, FRONT18_DB_USER, FRONT18_DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    try {
        $pdo->exec("ALTER TABLE saas_origins ADD COLUMN blur_amount INT NOT NULL DEFAULT 25 AFTER display_mode"); // Changed AFTER wp_rules to AFTER display_mode to match original context
        echo "Coluna 'blur_amount' adicionada.<br>";
    } catch(PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column') !== false) {
            echo "Coluna 'blur_amount' ja existe.<br>";
        } else {
            throw $e; // Re-throw other exceptions
        }
    }

    try {
        $pdo->exec("ALTER TABLE saas_origins ADD COLUMN blur_selector TEXT NULL AFTER blur_amount"); // Changed VARCHAR(255) DEFAULT '...' to TEXT NULL
        echo "Coluna 'blur_selector' adicionada.<br>";
    } catch(PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column') !== false) {
            echo "Coluna 'blur_selector' ja existe.<br>";
        } else {
            throw $e; // Re-throw other exceptions
        }
    }

    try {
        $pdo->exec("ALTER TABLE saas_origins ADD COLUMN protected_media_ids LONGTEXT NULL AFTER blur_selector");
        echo "Coluna 'protected_media_ids' adicionada.<br>";
    } catch(PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate column') !== false) {
            echo "Coluna 'protected_media_ids' ja existe.<br>";
        } else {
            throw $e; // Re-throw other exceptions
        }
    }

    echo "<br>Patch aplicado com sucesso!";

} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
