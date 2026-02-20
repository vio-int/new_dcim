<?php
/**
 * Fix Capacity and Asset Tables
 */

require_once 'db.inc.php';

try {
    $dbh->beginTransaction();
    
    // Create capacity table
    $dbh->exec("CREATE TABLE IF NOT EXISTS capacity (
        id INT(11) NOT NULL AUTO_INCREMENT,
        name VARCHAR(255) NOT NULL,
        location_id INT(11) DEFAULT NULL,
        room_id INT(11) DEFAULT NULL,
        rack_id INT(11) DEFAULT NULL,
        total_capacity INT(11) DEFAULT 0,
        used_capacity INT(11) DEFAULT 0,
        available_capacity INT(11) DEFAULT 0,
        created DATE DEFAULT NULL,
        last_updated DATE DEFAULT NULL,
        is_deleted CHAR(1) DEFAULT 'N',
        PRIMARY KEY (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "✓ Created capacity table<br>";
    
    // Add missing columns to assets table
    $dbh->exec("ALTER TABLE assets 
        ADD COLUMN IF NOT EXISTS location_id INT(11) DEFAULT NULL AFTER manufacture_id,
        ADD COLUMN IF NOT EXISTS room_id INT(11) DEFAULT NULL AFTER location_id,
        ADD COLUMN IF NOT EXISTS model_id INT(11) DEFAULT NULL AFTER room_id,
        ADD COLUMN IF NOT EXISTS supplier_id INT(11) DEFAULT NULL AFTER model_id,
        ADD COLUMN IF NOT EXISTS status_id INT(11) DEFAULT NULL AFTER supplier_id");
    echo "✓ Added columns to assets table<br>";
    
    // Create asset_model table
    $dbh->exec("CREATE TABLE IF NOT EXISTS asset_model (
        id INT(11) NOT NULL AUTO_INCREMENT,
        name VARCHAR(255) NOT NULL,
        description TEXT,
        created DATE DEFAULT NULL,
        last_updated DATE DEFAULT NULL,
        is_deleted CHAR(1) DEFAULT 'N',
        PRIMARY KEY (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "✓ Created asset_model table<br>";
    
    // Create asset_supplier table
    $dbh->exec("CREATE TABLE IF NOT EXISTS asset_supplier (
        id INT(11) NOT NULL AUTO_INCREMENT,
        name VARCHAR(255) NOT NULL,
        contact_name VARCHAR(255) DEFAULT NULL,
        contact_email VARCHAR(255) DEFAULT NULL,
        contact_phone VARCHAR(50) DEFAULT NULL,
        address TEXT,
        created DATE DEFAULT NULL,
        last_updated DATE DEFAULT NULL,
        is_deleted CHAR(1) DEFAULT 'N',
        PRIMARY KEY (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "✓ Created asset_supplier table<br>";
    
    // Insert sample data
    $dbh->exec("INSERT INTO asset_model (name, description, created) VALUES 
        ('PowerEdge R750', 'Dell 2U Server', '2026-02-20'),
        ('ProLiant DL380', 'HPE 2U Server', '2026-02-20')");
    echo "✓ Inserted asset models<br>";
    
    $dbh->exec("INSERT INTO asset_supplier (name, contact_name, created) VALUES 
        ('Dell Indonesia', 'Budi Santoso', '2026-02-20'),
        ('HPE Indonesia', 'Siti Rahayu', '2026-02-20')");
    echo "✓ Inserted asset suppliers<br>";
    
    $dbh->exec("INSERT INTO asset_status (status, description, created) VALUES 
        ('Active', 'Asset is active', '2026-02-20'),
        ('Maintenance', 'Under maintenance', '2026-02-20')");
    echo "✓ Inserted asset status<br>";
    
    // Update assets with proper IDs
    $dbh->exec("UPDATE assets SET location_id=1, model_id=1, supplier_id=1, status_id=1 WHERE id=1");
    
    $dbh->commit();
    
    echo "<h2>✅ Tables fixed!</h2>";
    echo "<p><a href='capacity_list.php?type=Locations'>Capacity List</a> | <a href='asset_list.php'>Asset List</a></p>";
    
} catch (Exception $e) {
    $dbh->rollBack();
    echo "<h2>❌ Error</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
}
