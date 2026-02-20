<?php
/**
 * Insert Sample Data for Room, Rack, Device
 */

require_once 'db.inc.php';

try {
    $dbh->beginTransaction();
    
    // Insert Rooms
    $dbh->exec("INSERT INTO room (name, room_no, location_id, `rows`, `columns`, rows_per_rack, group_columns, group_rows, created, is_deleted) VALUES
        ('Server Room A', 'A-101', 1, 10, 8, 42, 2, 2, '2026-02-20', 'N'),
        ('Server Room B', 'B-201', 1, 8, 6, 42, 2, 2, '2026-02-20', 'N'),
        ('Network Room', 'N-301', 2, 5, 4, 42, 1, 1, '2026-02-20', 'N')");
    echo "✓ Inserted 3 rooms<br>";
    
    // Insert Racks
    $dbh->exec("INSERT INTO rack (name, site_id, room_id, group_no, row_position, facility, type, height, model, created, is_deleted) VALUES
        ('Rack-A01', 1, 1, 'A', '01', 'Jakarta', 'Server', '42U', 'APC NetShelter', '2026-02-20', 'N'),
        ('Rack-A02', 1, 1, 'A', '02', 'Jakarta', 'Server', '42U', 'APC NetShelter', '2026-02-20', 'N'),
        ('Rack-A03', 1, 1, 'A', '03', 'Jakarta', 'Server', '42U', 'APC NetShelter', '2026-02-20', 'N'),
        ('Rack-B01', 1, 2, 'B', '01', 'Jakarta', 'Storage', '48U', 'Dell Enterprise', '2026-02-20', 'N'),
        ('Rack-N01', 2, 3, 'N', '01', 'Surabaya', 'Network', '42U', 'APC NetShelter', '2026-02-20', 'N')");
    echo "✓ Inserted 5 racks<br>";
    
    // Insert Devices
    $dbh->exec("INSERT INTO device (label, serial_no, asset_tag, primary_ip, device_type, height, nominal_watts, cabinet, position, install_date, warranty_co, created, is_deleted) VALUES
        ('JKT-APP-01', 'SN123456789', 'AST-001', '10.1.1.11', 'Server', 2, 750, 1, 1, '2024-01-20', 'Dell', '2026-02-20', 'N'),
        ('JKT-DB-01', 'SN123456790', 'AST-002', '10.1.1.21', 'Server', 2, 700, 1, 3, '2024-01-20', 'HPE', '2026-02-20', 'N'),
        ('JKT-WEB-01', 'SN123456791', 'AST-003', '10.1.1.31', 'Server', 1, 500, 2, 1, '2024-01-20', 'Dell', '2026-02-20', 'N'),
        ('JKT-CORE-SW', 'SN444555666', 'AST-004', '10.1.1.254', 'Switch', 1, 400, 5, 1, '2024-01-20', 'Cisco', '2026-02-20', 'N')");
    echo "✓ Inserted 4 devices<br>";
    
    // Insert Manufactures
    $dbh->exec("INSERT INTO manufacture (id, name) VALUES
        (1, 'Dell Technologies'),
        (2, 'HPE'),
        (3, 'Cisco Systems'),
        (4, 'Arista Networks'),
        (5, 'Fortinet')");
    echo "✓ Inserted 5 manufacturers<br>";
    
    $dbh->commit();
    
    echo "<h2>✅ Sample data inserted successfully!</h2>";
    echo "<p><a href='room.php'>View Rooms</a> | <a href='rack.php'>View Racks</a> | <a href='device.php'>View Devices</a></p>";
    
} catch (Exception $e) {
    $dbh->rollBack();
    echo "<h2>❌ Error</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
}
