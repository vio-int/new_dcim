<?php
/**
 * Insert Sample Data for Capacity and Assets
 */

require_once 'db.inc.php';

try {
    $dbh->beginTransaction();
    
    // Insert capacity records
    $dbh->exec("INSERT INTO capacity (name, location_id, total_capacity, used_capacity, available_capacity, created, is_deleted) VALUES
        ('Jakarta DC Capacity', 1, 1000, 650, 350, '2026-02-20', 'N'),
        ('Surabaya DC Capacity', 2, 800, 400, 400, '2026-02-20', 'N')");
    echo "✓ Inserted 2 capacity records<br>";
    
    // Check if assets exist
    $count = $dbh->query("SELECT COUNT(*) FROM assets")->fetchColumn();
    if ($count == 0) {
        // Insert sample assets
        $dbh->exec("INSERT INTO assets (name, status, department_id, label, serial_no, asset_tag, primary_ip, manufacture_date, install_date, company, expire_date, rack_id, device_id, height, position, half_depth, back_side, data_ports, watts, weight, location_id, room_id, model_id, supplier_id, status_id, created, is_deleted) VALUES
            ('Server Dell R750', 'Active', 1, 'SRV-001', 'SN123456789', 'AST-001', '10.1.1.11', '2023-01-15', '2024-01-20', 'Dell', '2027-01-20', 1, 1, 2, 1, 'N', 'N', 4, 750, 35, 1, 1, 1, 1, 1, '2026-02-20', 'N'),
            ('Server HPE DL380', 'Active', 1, 'SRV-002', 'SN123456790', 'AST-002', '10.1.1.12', '2023-01-15', '2024-01-20', 'HPE', '2027-01-20', 1, 2, 2, 3, 'N', 'N', 4, 700, 32, 1, 1, 2, 2, 1, '2026-02-20', 'N')");
        echo "✓ Inserted 2 assets<br>";
    } else {
        // Update existing assets with the relationship IDs
        $dbh->exec("UPDATE assets SET location_id=1, room_id=1, model_id=1, supplier_id=1, status_id=1 WHERE id=1");
        echo "✓ Updated existing assets<br>";
    }
    
    $dbh->commit();
    
    echo "<h2>✅ Sample data inserted!</h2>";
    echo "<p><a href='capacity_list.php?type=Locations'>Capacity List</a> | <a href='asset_list.php'>Asset List</a></p>";
    
} catch (Exception $e) {
    $dbh->rollBack();
    echo "<h2>❌ Error</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
}
