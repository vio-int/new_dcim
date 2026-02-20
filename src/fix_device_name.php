<?php
/**
 * Fix Device Table - Rename label to name
 */

require_once 'db.inc.php';

try {
    // Rename label column to name
    $dbh->exec("ALTER TABLE device CHANGE label name VARCHAR(255) NOT NULL");
    
    echo "✓ Renamed 'label' column to 'name'<br>";
    
    echo "<h2>✅ Device table fixed!</h2>";
    echo "<p><a href='device_list.php'>View Device List</a></p>";
    
} catch (Exception $e) {
    echo "<h2>❌ Error</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
}
