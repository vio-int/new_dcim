<?php
/**
 * Check and fix assets table structure
 */

require_once 'db.inc.php';

echo "<h1>Checking Assets Table Structure</h1>";

// Show current columns
echo "<h2>Current Columns:</h2>";
try {
    $stmt = $dbh->query("SHOW COLUMNS FROM assets");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "<ul>";
    foreach ($columns as $col) {
        echo "<li>$col</li>";
    }
    echo "</ul>";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

// Required columns
$required = ['location_id', 'room_id', 'model_id', 'supplier_id', 'status_id'];
$missing = [];

foreach ($required as $col) {
    if (!in_array($col, $columns)) {
        $missing[] = $col;
    }
}

if (empty($missing)) {
    echo "<p>✅ All required columns exist!</p>";
} else {
    echo "<h2>Missing Columns:</h2>";
    echo "<ul>";
    foreach ($missing as $col) {
        echo "<li>$col</li>";
    }
    echo "</ul>";
    
    // Add missing columns
    echo "<h2>Adding Missing Columns:</h2>";
    foreach ($missing as $col) {
        try {
            $sql = "ALTER TABLE assets ADD COLUMN $col INT(11) DEFAULT NULL";
            $dbh->exec($sql);
            echo "✓ Added $col<br>";
        } catch (Exception $e) {
            echo "✗ Error adding $col: " . $e->getMessage() . "<br>";
        }
    }
}

// Update data
echo "<h2>Updating Data:</h2>";
try {
    $dbh->exec("UPDATE assets SET location_id=1, room_id=1, model_id=1, supplier_id=1, status_id=1 WHERE id=1");
    echo "✓ Updated asset 1<br>";
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "<br>";
}

echo "<p><a href='debug_capacity_asset.php'>Check Debug</a></p>";
