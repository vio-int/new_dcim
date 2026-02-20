<?php
/**
 * Fix Assets Table - Add missing columns properly
 */

require_once 'db.inc.php';

try {
    // Check and add columns one by one
    $columns = [
        'location_id' => 'INT(11) DEFAULT NULL AFTER manufacture_id',
        'room_id' => 'INT(11) DEFAULT NULL AFTER location_id',
        'model_id' => 'INT(11) DEFAULT NULL AFTER room_id',
        'supplier_id' => 'INT(11) DEFAULT NULL AFTER model_id',
        'status_id' => 'INT(11) DEFAULT NULL AFTER supplier_id'
    ];
    
    foreach ($columns as $col => $def) {
        try {
            $dbh->exec("ALTER TABLE assets ADD COLUMN $col $def");
            echo "✓ Added column: $col<br>";
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate column') !== false) {
                echo "✓ Column already exists: $col<br>";
            } else {
                throw $e;
            }
        }
    }
    
    // Update existing assets
    $dbh->exec("UPDATE assets SET location_id=1, room_id=1, model_id=1, supplier_id=1, status_id=1 WHERE id=1");
    
    echo "<h2>✅ Assets table fixed!</h2>";
    echo "<p><a href='debug_capacity_asset.php'>Check Debug</a> | <a href='asset_list.php'>Asset List</a></p>";
    
} catch (Exception $e) {
    echo "<h2>❌ Error</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
}
