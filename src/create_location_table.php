<?php
/**
 * Create Location Table and Load Sample Data
 * Run this script to create the missing location table and populate it
 */

require_once 'db.inc.php';

$errors = [];
$success = [];

try {
    // Start transaction
    $dbh->beginTransaction();
    
    // Create location table if it doesn't exist
    $createTableSQL = "CREATE TABLE IF NOT EXISTS location (
        id INT(11) NOT NULL AUTO_INCREMENT,
        name VARCHAR(255) NOT NULL,
        slug VARCHAR(255) DEFAULT NULL,
        status VARCHAR(50) DEFAULT 'Active',
        location_id VARCHAR(255) DEFAULT NULL,
        facility VARCHAR(255) DEFAULT NULL,
        asn VARCHAR(50) DEFAULT NULL,
        time_zone VARCHAR(50) DEFAULT NULL,
        description TEXT,
        physical_address TEXT,
        shipping_address TEXT,
        latitude VARCHAR(50) DEFAULT NULL,
        longitude VARCHAR(50) DEFAULT NULL,
        contact_name VARCHAR(255) DEFAULT NULL,
        contact_email VARCHAR(255) DEFAULT NULL,
        contact_no VARCHAR(50) DEFAULT NULL,
        tag VARCHAR(255) DEFAULT NULL,
        comment TEXT,
        created DATE DEFAULT NULL,
        last_updated DATE DEFAULT NULL,
        is_deleted CHAR(1) DEFAULT 'N',
        PRIMARY KEY (id),
        KEY idx_name (name),
        KEY idx_status (status),
        KEY idx_is_deleted (is_deleted)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    $dbh->exec($createTableSQL);
    $success[] = "Created location table";
    
    // Insert sample locations
    $dbh->exec("DELETE FROM location WHERE is_deleted='N'");
    $dbh->exec("INSERT INTO location (name, slug, status, location_id, facility, asn, time_zone, description, physical_address, shipping_address, latitude, longitude, contact_name, contact_email, contact_no, tag, comment, created, is_deleted) VALUES
    ('Jakarta Intercon Plaza', 'jkt-intercon', 'Active', 'JKT-01', 'Jakarta Pusat', 'AS17995', 'WIB', 'Primary data center in Jakarta CBD', 'Jl. Teluk Betung No.10, Jakarta Pusat 10230', 'Jl. Teluk Betung No.10, Jakarta Pusat 10230', '-6.1865', '106.8340', 'Budi Santoso', 'budi.santoso@intercon.id', '+62-21-2358-9000', 'primary,jakarta,cbd', 'Main DC with 2MW power capacity', '2026-02-20', 'N'),
    ('Surabaya Cyber Building', 'sby-cyber', 'Active', 'SBY-01', 'Surabaya', 'AS17996', 'WIB', 'East Java regional data center', 'Jl. HR Muhammad No.168, Surabaya 60226', 'Jl. HR Muhammad No.168, Surabaya 60226', '-7.2905', '112.7688', 'Siti Rahayu', 'siti.rahayu@cyberdc.id', '+62-31-9900-1234', 'surabaya,east-java,regional', 'Regional hub for East Java region', '2026-02-20', 'N'),
    ('Bandung Techno Park', 'bdg-techno', 'Active', 'BDG-01', 'Bandung', 'AS17997', 'WIB', 'West Java data center facility', 'Jl. Gegerkalong Hilir No.47, Bandung 40152', 'Jl. Gegerkalong Hilir No.47, Bandung 40152', '-6.8733', '107.5860', 'Ahmad Wijaya', 'ahmad.wijaya@technopark.id', '+62-22-520-1234', 'bandung,west-java,tech', 'University district location', '2026-02-20', 'N'),
    ('Bali Nusa Dua', 'bali-nusadua', 'Active', 'BALI-01', 'Badung', 'AS17998', 'WITA', 'Bali disaster recovery site', 'Kawasan ITDC Lot N3, Nusa Dua 80363', 'Kawasan ITDC Lot N3, Nusa Dua 80363', '-8.8000', '115.2333', 'Made Sudarta', 'made.sudarta@balidc.id', '+62-361-300-1234', 'bali,dr-site,tourism', 'Disaster recovery and backup facility', '2026-02-20', 'N')");
    $success[] = "Inserted 4 locations";
    
    // Commit transaction
    $dbh->commit();
    
    echo "<h1>✅ Location Table Created and Sample Data Loaded!</h1>";
    echo "<h2>Summary:</h2>";
    echo "<ul>";
    foreach ($success as $msg) {
        echo "<li>✓ $msg</li>";
    }
    echo "</ul>";
    echo "<p><a href='location_list.php'>View Locations</a></p>";
    
} catch (Exception $e) {
    // Rollback on error
    if ($dbh->inTransaction()) {
        $dbh->rollBack();
    }
    echo "<h1>❌ Error</h1>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
