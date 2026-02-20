<?php
/**
 * Fix Device Table - Add missing columns
 */

require_once 'db.inc.php';

try {
    // Add missing columns to device table
    $dbh->exec("ALTER TABLE device 
        ADD COLUMN site_id INT(11) DEFAULT NULL AFTER parent_device,
        ADD COLUMN rack_id INT(11) DEFAULT NULL AFTER site_id,
        ADD COLUMN manufacture_id INT(11) DEFAULT NULL AFTER rack_id");
    
    echo "✓ Added site_id, rack_id, manufacture_id columns to device table<br>";
    
    // Update existing devices with proper IDs
    $dbh->exec("UPDATE device SET site_id = 1, rack_id = 1, manufacture_id = 1 WHERE id = 1");
    $dbh->exec("UPDATE device SET site_id = 1, rack_id = 1, manufacture_id = 2 WHERE id = 2");
    $dbh->exec("UPDATE device SET site_id = 1, rack_id = 2, manufacture_id = 1 WHERE id = 3");
    $dbh->exec("UPDATE device SET site_id = 2, rack_id = 5, manufacture_id = 3 WHERE id = 4");
    
    echo "✓ Updated existing devices with site/rack/manufacture IDs<br>";
    
    echo "<h2>✅ Device table fixed!</h2>";
    echo "<p><a href='device_list.php'>View Device List</a></p>";
    
} catch (Exception $e) {
    echo "<h2>❌ Error</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
}
