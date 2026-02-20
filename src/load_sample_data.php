<?php
/**
 * Sample Data Loader for VIODCIM
 * Run this script to populate the database with Indonesian data center sample data
 * 
 * WARNING: This will truncate existing data and insert fresh sample data!
 */

require_once 'db.inc.php';

// Check if user is admin (simple check - modify as needed)
// In production, add proper authentication!

$errors = [];
$success = [];

try {
    // Start transaction
    $dbh->beginTransaction();
    
    // ============================================
    // 1. LOCATIONS (Indonesian Data Centers)
    // ============================================
    $dbh->exec("DELETE FROM location WHERE is_deleted='N'");
    $dbh->exec("INSERT INTO location (name, slug, status, location_id, facility, asn, time_zone, description, physical_address, shipping_address, latitude, longitude, contact_name, contact_email, contact_no, tag, comment, created, is_deleted) VALUES
    ('Jakarta Intercon Plaza', 'jkt-intercon', 'Active', 'JKT-01', 'Jakarta Pusat', 'AS17995', 'WIB', 'Primary data center in Jakarta CBD', 'Jl. Teluk Betung No.10, Jakarta Pusat 10230', 'Jl. Teluk Betung No.10, Jakarta Pusat 10230', '-6.1865', '106.8340', 'Budi Santoso', 'budi.santoso@intercon.id', '+62-21-2358-9000', 'primary,jakarta,cbd', 'Main DC with 2MW power capacity', '2026-02-20', 'N'),
    ('Surabaya Cyber Building', 'sby-cyber', 'Active', 'SBY-01', 'Surabaya', 'AS17996', 'WIB', 'East Java regional data center', 'Jl. HR Muhammad No.168, Surabaya 60226', 'Jl. HR Muhammad No.168, Surabaya 60226', '-7.2905', '112.7688', 'Siti Rahayu', 'siti.rahayu@cyberdc.id', '+62-31-9900-1234', 'surabaya,east-java,regional', 'Regional hub for East Java region', '2026-02-20', 'N'),
    ('Bandung Techno Park', 'bdg-techno', 'Active', 'BDG-01', 'Bandung', 'AS17997', 'WIB', 'West Java data center facility', 'Jl. Gegerkalong Hilir No.47, Bandung 40152', 'Jl. Gegerkalong Hilir No.47, Bandung 40152', '-6.8733', '107.5860', 'Ahmad Wijaya', 'ahmad.wijaya@technopark.id', '+62-22-520-1234', 'bandung,west-java,tech', 'University district location', '2026-02-20', 'N'),
    ('Bali Nusa Dua', 'bali-nusadua', 'Active', 'BALI-01', 'Badung', 'AS17998', 'WITA', 'Bali disaster recovery site', 'Kawasan ITDC Lot N3, Nusa Dua 80363', 'Kawasan ITDC Lot N3, Nusa Dua 80363', '-8.8000', '115.2333', 'Made Sudarta', 'made.sudarta@balidc.id', '+62-361-300-1234', 'bali,dr-site,tourism', 'Disaster recovery and backup facility', '2026-02-20', 'N')");
    $success[] = "Inserted 4 locations";
    
    // ============================================
    // 2. DATA CENTERS
    // ============================================
    $dbh->exec("DELETE FROM fac_DataCenter WHERE DataCenterID IN (1,2,3,4)");
    $dbh->exec("INSERT INTO fac_DataCenter (DataCenterID, Name, SquareFootage, DeliveryAddress, Administrator, AdminPhone, AdminEmail, DrawingFileName, EntryLogging, MaxKW, Container, MapX, MapY) VALUES
    (1, 'Jakarta Intercon Plaza DC', 50000, 'Jl. Teluk Betung No.10, Jakarta Pusat 10230', 'Budi Santoso', '+62-21-2358-9000', 'budi.santoso@intercon.id', 'jakarta_dc_floorplan.png', 1, 2000, 0, 0, 0),
    (2, 'Surabaya Cyber Building DC', 35000, 'Jl. HR Muhammad No.168, Surabaya 60226', 'Siti Rahayu', '+62-31-9900-1234', 'siti.rahayu@cyberdc.id', 'surabaya_dc_floorplan.png', 1, 1500, 0, 0, 0),
    (3, 'Bandung Techno Park DC', 25000, 'Jl. Gegerkalong Hilir No.47, Bandung 40152', 'Ahmad Wijaya', '+62-22-520-1234', 'ahmad.wijaya@technopark.id', 'bandung_dc_floorplan.png', 1, 1000, 0, 0, 0),
    (4, 'Bali Nusa Dua DR Site', 20000, 'Kawasan ITDC Lot N3, Nusa Dua 80363', 'Made Sudarta', '+62-361-300-1234', 'made.sudarta@balidc.id', 'bali_dc_floorplan.png', 1, 800, 0, 0, 0)");
    $success[] = "Inserted 4 data centers";
    
    // ============================================
    // 3. ZONES
    // ============================================
    $dbh->exec("DELETE FROM fac_Zone WHERE ZoneID IN (1,2,3,4,5,6,7)");
    $dbh->exec("INSERT INTO fac_Zone (ZoneID, DataCenterID, Description, MapX1, MapX2, MapY1, MapY2) VALUES
    (1, 1, 'Ground Floor - Power & Cooling', 0, 100, 0, 100),
    (2, 1, '1st Floor - Production Racks', 0, 100, 100, 200),
    (3, 1, '2nd Floor - Storage & Network', 0, 100, 200, 300),
    (4, 2, 'Ground Floor - Main Hall', 0, 100, 0, 100),
    (5, 2, '1st Floor - Expansion Zone', 0, 100, 100, 200),
    (6, 3, 'Single Floor DC', 0, 100, 0, 100),
    (7, 4, 'DR Site Main Floor', 0, 100, 0, 100)");
    $success[] = "Inserted 7 zones";
    
    // ============================================
    // 4. CABINET ROWS
    // ============================================
    $dbh->exec("DELETE FROM fac_CabRow WHERE CabRowID IN (1,2,3,4,5,6,7,8,9,10)");
    $dbh->exec("INSERT INTO fac_CabRow (CabRowID, Name, DataCenterID, ZoneID) VALUES
    (1, 'Row A - Compute', 1, 2),
    (2, 'Row B - Compute', 1, 2),
    (3, 'Row C - Storage', 1, 2),
    (4, 'Row D - Network', 1, 2),
    (5, 'Row E - Power/Cooling', 1, 1),
    (6, 'Row F - Legacy', 1, 3),
    (7, 'Row A - Jakarta', 2, 4),
    (8, 'Row B - Jakarta', 2, 4),
    (9, 'Row A - Bandung', 3, 6),
    (10, 'Row A - Bali DR', 4, 7)");
    $success[] = "Inserted 10 cabinet rows";
    
    // ============================================
    // 5. CABINETS/RACKS
    // ============================================
    $dbh->exec("DELETE FROM fac_Cabinet WHERE CabinetID IN (1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18)");
    $dbh->exec("INSERT INTO fac_Cabinet (CabinetID, DataCenterID, Location, LocationSortable, AssignedTo, ZoneID, CabRowID, CabinetHeight, Model, Keylock, MaxKW, MaxWeight, InstallationDate, MapX1, MapX2, FrontEdge, MapY1, MapY2, Notes, U1Position) VALUES
    (1, 1, 'A01', 'A01', 1, 2, 1, 42, 'APC NetShelter SX 42U', 'A01-KEY', 15, 1500, '2024-01-15', 10, 20, 'Top', 110, 120, 'Production compute rack', 'Default'),
    (2, 1, 'A02', 'A02', 1, 2, 1, 42, 'APC NetShelter SX 42U', 'A02-KEY', 15, 1500, '2024-01-15', 20, 30, 'Top', 110, 120, 'Production compute rack', 'Default'),
    (3, 1, 'A03', 'A03', 1, 2, 1, 42, 'APC NetShelter SX 42U', 'A03-KEY', 15, 1500, '2024-01-15', 30, 40, 'Top', 110, 120, 'Production compute rack', 'Default'),
    (9, 1, 'C01', 'C01', 1, 2, 3, 48, 'Dell 48U Enterprise Rack', 'C01-KEY', 20, 2000, '2024-02-01', 10, 20, 'Top', 150, 160, 'High-density storage rack', 'Default'),
    (11, 1, 'D01', 'D01', 1, 2, 4, 42, 'APC NetShelter SX 42U', 'D01-KEY', 10, 1000, '2024-01-20', 10, 20, 'Top', 170, 180, 'Core network rack', 'Default'),
    (13, 2, 'A01', 'A01', 1, 4, 7, 42, 'APC NetShelter SX 42U', 'S-A01-KEY', 12, 1200, '2024-03-01', 10, 20, 'Top', 10, 20, 'Surabaya main rack', 'Default'),
    (16, 3, 'A01', 'A01', 1, 6, 9, 42, 'APC NetShelter SX 42U', 'B-A01-KEY', 10, 1000, '2024-04-01', 10, 20, 'Top', 10, 20, 'Bandung main rack', 'Default'),
    (18, 4, 'DR01', 'DR01', 1, 7, 10, 42, 'APC NetShelter SX 42U', 'DR-01-KEY', 8, 800, '2024-05-01', 10, 20, 'Top', 10, 20, 'Bali DR primary rack', 'Default')");
    $success[] = "Inserted 8 cabinets/racks";
    
    // ============================================
    // 6. MANUFACTURERS
    // ============================================
    $dbh->exec("DELETE FROM fac_Manufacturer WHERE ManufacturerID IN (1,2,3,4,5,6,7,8,9,10,11,12,13,14,15)");
    $dbh->exec("INSERT INTO fac_Manufacturer (ManufacturerID, Name) VALUES
    (1, 'Dell Technologies'),
    (2, 'HPE (Hewlett Packard Enterprise)'),
    (3, 'Cisco Systems'),
    (4, 'Juniper Networks'),
    (5, 'Arista Networks'),
    (6, 'Huawei'),
    (7, 'Lenovo'),
    (8, 'Supermicro'),
    (9, 'APC by Schneider Electric'),
    (11, 'Fortinet'),
    (12, 'Palo Alto Networks'),
    (13, 'F5 Networks'),
    (14, 'NetApp'),
    (15, 'Pure Storage')");
    $success[] = "Inserted 14 manufacturers";
    
    // ============================================
    // 7. DEVICE TEMPLATES
    // ============================================
    $dbh->exec("DELETE FROM fac_DeviceTemplate WHERE TemplateID IN (1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22)");
    $dbh->exec("INSERT INTO fac_DeviceTemplate (TemplateID, ManufacturerID, Model, Height, Weight, Wattage, DeviceType, PSCount, NumPorts, Notes) VALUES
    (1, 1, 'PowerEdge R750', 2, 35, 750, 'Server', 2, 4, 'Dell 2U rack server - 3rd Gen Intel Xeon'),
    (2, 1, 'PowerEdge R650', 1, 25, 500, 'Server', 2, 4, 'Dell 1U rack server - 3rd Gen Intel Xeon'),
    (3, 2, 'ProLiant DL380 Gen10', 2, 32, 700, 'Server', 2, 4, 'HPE 2U rack server'),
    (7, 14, 'FAS2750', 4, 80, 1200, 'Storage Array', 2, 4, 'NetApp storage system'),
    (10, 3, 'Nexus 93180YC-FX', 1, 18, 400, 'Switch', 2, 0, 'Cisco Nexus 9K switch - 48p 10/25G'),
    (12, 5, 'DCS-7280SR2-48YC6', 1, 16, 380, 'Switch', 2, 0, 'Arista 7280R switch - 48p 25G'),
    (16, 11, 'FortiGate 3600E', 2, 20, 450, 'Firewall', 2, 8, 'Fortinet next-gen firewall'),
    (18, 13, 'BIG-IP i5800', 2, 24, 550, 'Load Balancer', 2, 8, 'F5 Networks load balancer')");
    $success[] = "Inserted 8 device templates";
    
    // ============================================
    // 8. DEPARTMENTS
    // ============================================
    $dbh->exec("DELETE FROM fac_Department WHERE DeptID IN (1,2,3,4,5,6)");
    $dbh->exec("INSERT INTO fac_Department (DeptID, Name, ExecSponsor, Classification, DeptColor) VALUES
    (1, 'IT Infrastructure', 'Budi Santoso', 'Technical', '#0066CC'),
    (2, 'Network Operations', 'Ahmad Wijaya', 'Technical', '#009900'),
    (3, 'Security Team', 'Siti Rahayu', 'Security', '#CC0000'),
    (4, 'Database Administration', 'Dewi Kusuma', 'Technical', '#9900CC'),
    (5, 'Application Support', 'Made Sudarta', 'Technical', '#FF6600'),
    (6, 'Facilities Management', 'Abdul Rahman', 'Operations', '#666666')");
    $success[] = "Inserted 6 departments";
    
    // ============================================
    // 9. PEOPLE/USERS
    // ============================================
    $dbh->exec("DELETE FROM fac_People WHERE PersonID IN (1,2,3,4,5,6,7,8)");
    $dbh->exec("INSERT INTO fac_People (PersonID, FirstName, LastName, Phone, Email, AdminOwnDevices, ReadAccess, WriteAccess, DeleteAccess, ContactAdmin, SiteAdmin, Disabled, DepartmentID) VALUES
    (1, 'Budi', 'Santoso', '+62-21-2358-9001', 'budi.santoso@intercon.id', 1, 1, 1, 1, 1, 1, 0, 1),
    (2, 'Siti', 'Rahayu', '+62-31-9900-1235', 'siti.rahayu@cyberdc.id', 1, 1, 1, 0, 0, 1, 0, 3),
    (3, 'Ahmad', 'Wijaya', '+62-22-520-1235', 'ahmad.wijaya@technopark.id', 1, 1, 1, 0, 0, 1, 0, 2),
    (4, 'Made', 'Sudarta', '+62-361-300-1235', 'made.sudarta@balidc.id', 1, 1, 1, 0, 0, 1, 0, 5),
    (7, 'Rini', 'Susanti', '+62-21-2358-9002', 'rini.susanti@intercon.id', 0, 1, 1, 0, 0, 0, 0, 1),
    (8, 'Agus', 'Pratama', '+62-21-2358-9003', 'agus.pratama@intercon.id', 0, 1, 0, 0, 0, 0, 0, 2)");
    $success[] = "Inserted 6 users";
    
    // ============================================
    // 10. DEVICES
    // ============================================
    $dbh->exec("DELETE FROM fac_Device WHERE DeviceID IN (1,2,3,4,5,6,7,8,9,10,11,12,13,14,15)");
    $dbh->exec("INSERT INTO fac_Device (DeviceID, Label, SerialNo, AssetTag, PrimaryIP, SNMPVersion, SNMPCommunity, ESX, Owner, EscalationTimeID, EscalationID, PrimaryContact, Cabinet, Position, Height, Ports, FirstPortNum, TemplateID, NominalWatts, PowerSupplyCount, DeviceType, ChassisSlots, RearChassisSlots, ParentDevice, MfgDate, InstallDate, WarrantyCo, WarrantyExpire, Notes, Reservation, Rights, DepartmentID) VALUES
    (1, 'JKT-APP-01', 'SN123456789', 'ASSET-001', '10.1.1.11', '2c', 'public', 0, 1, 0, 0, 1, 1, 1, 2, 4, 1, 1, 750, 2, 'Server', 0, 0, 0, '2024-01-15', '2024-01-20', 'Dell', '2027-01-20', 'Application server - Jakarta primary', 0, 'Device', 1),
    (2, 'JKT-APP-02', 'SN123456790', 'ASSET-002', '10.1.1.12', '2c', 'public', 0, 1, 0, 0, 1, 1, 3, 2, 4, 1, 1, 750, 2, 'Server', 0, 0, 0, '2024-01-15', '2024-01-20', 'Dell', '2027-01-20', 'Application server - Jakarta primary', 0, 'Device', 1),
    (3, 'JKT-WEB-01', 'SN123456791', 'ASSET-003', '10.1.1.13', '2c', 'public', 0, 1, 0, 0, 7, 1, 5, 1, 4, 1, 2, 500, 2, 'Server', 0, 0, 0, '2024-01-15', '2024-01-20', 'Dell', '2027-01-20', 'Web server - Jakarta', 0, 'Device', 1),
    (5, 'JKT-DB-01', 'SN123456793', 'ASSET-005', '10.1.1.21', '2c', 'public', 0, 1, 0, 0, 5, 2, 1, 2, 4, 1, 3, 700, 2, 'Server', 0, 0, 0, '2024-01-15', '2024-01-20', 'HPE', '2027-01-20', 'Database server - Primary', 0, 'Device', 4),
    (7, 'JKT-STOR-01', 'SN987654321', 'ASSET-007', '10.1.1.31', '2c', 'public', 0, 1, 0, 0, 1, 9, 1, 4, 4, 1, 7, 1200, 2, 'Storage Array', 0, 0, 0, '2024-02-01', '2024-02-05', 'NetApp', '2027-02-05', 'Primary storage array - Jakarta', 0, 'Device', 1),
    (9, 'JKT-CORE-SW01', 'SN444555666', 'ASSET-009', '10.1.1.254', '2c', 'public', 0, 1, 0, 0, 3, 11, 1, 1, 0, 0, 10, 400, 2, 'Switch', 0, 0, 0, '2024-01-20', '2024-01-25', 'Cisco', '2027-01-25', 'Core switch - Jakarta DC', 0, 'Device', 2),
    (10, 'JKT-EDGE-SW01', 'SN444555667', 'ASSET-010', '10.1.1.253', '2c', 'public', 0, 1, 0, 0, 3, 11, 2, 1, 0, 0, 12, 380, 2, 'Switch', 0, 0, 0, '2024-01-20', '2024-01-25', 'Arista', '2027-01-25', 'Edge switch - Jakarta DC', 0, 'Device', 2),
    (11, 'JKT-FW-01', 'SN777888999', 'ASSET-011', '10.1.1.1', '2c', 'public', 0, 1, 0, 0, 2, 11, 3, 2, 8, 1, 16, 450, 2, 'Firewall', 0, 0, 0, '2024-01-20', '2024-01-25', 'Fortinet', '2027-01-25', 'Primary firewall - Jakarta', 0, 'Device', 3),
    (13, 'SBY-APP-01', 'SN555666777', 'ASSET-013', '10.2.1.11', '2c', 'public', 0, 2, 0, 0, 2, 13, 1, 2, 4, 1, 1, 750, 2, 'Server', 0, 0, 0, '2024-03-01', '2024-03-05', 'Dell', '2027-03-05', 'Application server - Surabaya', 0, 'Device', 1),
    (16, 'BDG-APP-01', 'SN999000111', 'ASSET-016', '10.3.1.11', '2c', 'public', 0, 3, 0, 0, 3, 16, 1, 2, 4, 1, 1, 750, 2, 'Server', 0, 0, 0, '2024-04-01', '2024-04-05', 'Dell', '2027-04-05', 'Application server - Bandung', 0, 'Device', 1),
    (18, 'BALI-DR-01', 'SN333444555', 'ASSET-018', '10.4.1.11', '2c', 'public', 0, 4, 0, 0, 4, 18, 1, 2, 4, 1, 1, 750, 2, 'Server', 0, 0, 0, '2024-05-01', '2024-05-05', 'Dell', '2027-05-05', 'DR server - Bali', 0, 'Device', 1)");
    $success[] = "Inserted 11 devices";
    
    // Commit transaction
    $dbh->commit();
    
    echo "<h1>✅ Sample Data Loaded Successfully!</h1>";
    echo "<h2>Summary:</h2>";
    echo "<ul>";
    foreach ($success as $msg) {
        echo "<li>✓ $msg</li>";
    }
    echo "</ul>";
    echo "<p><a href='index.php'>Go to Dashboard</a></p>";
    
} catch (Exception $e) {
    // Rollback on error
    if ($dbh->inTransaction()) {
        $dbh->rollBack();
    }
    echo "<h1>❌ Error Loading Sample Data</h1>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
