<?php
/**
 * Debug script to check why Room, Rack, Device pages are blank
 */

require_once 'db.inc.php';

echo "<h1>VIODCIM Debug</h1>";

// Check if tables exist
echo "<h2>Tables Check:</h2>";
$tables = ['location', 'room', 'rack', 'device', 'assets', 'manufacture', 'ipam_prefix', 'ipam_vlan'];
foreach ($tables as $table) {
    try {
        $result = $dbh->query("SHOW TABLES LIKE '$table'");
        if ($result && $result->rowCount() > 0) {
            echo "✓ Table '$table' exists<br>";
            
            // Count rows
            $count = $dbh->query("SELECT COUNT(*) FROM $table WHERE is_deleted='N'")->fetchColumn();
            echo "  - Rows: $count<br>";
        } else {
            echo "✗ Table '$table' MISSING!<br>";
        }
    } catch (Exception $e) {
        echo "✗ Error checking '$table': " . $e->getMessage() . "<br>";
    }
}

// Check location data
echo "<h2>Location Data:</h2>";
try {
    $stmt = $dbh->query("SELECT * FROM location WHERE is_deleted='N' LIMIT 5");
    if ($stmt) {
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "Found " . count($rows) . " locations:<br>";
        foreach ($rows as $row) {
            echo "- {$row['name']} (ID: {$row['id']})<br>";
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "<br>";
}

// Check room data
echo "<h2>Room Data:</h2>";
try {
    $stmt = $dbh->query("SELECT * FROM room WHERE is_deleted='N' LIMIT 5");
    if ($stmt) {
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "Found " . count($rows) . " rooms:<br>";
        foreach ($rows as $row) {
            echo "- {$row['name']} (ID: {$row['id']}, Location: {$row['location_id']})<br>";
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "<br>";
}

// Check rack data
echo "<h2>Rack Data:</h2>";
try {
    $stmt = $dbh->query("SELECT * FROM rack WHERE is_deleted='N' LIMIT 5");
    if ($stmt) {
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "Found " . count($rows) . " racks:<br>";
        foreach ($rows as $row) {
            echo "- {$row['name']} (ID: {$row['id']})<br>";
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "<br>";
}

// Check PHP errors
echo "<h2>PHP Error Log:</h2>";
$error_log = ini_get('error_log');
echo "Error log path: $error_log<br>";

// Test Room class
echo "<h2>Testing Room Class:</h2>";
try {
    require_once 'classes/Room.class.php';
    $rooms = Room::GetRoomList();
    echo "Room::GetRoomList() returned " . count($rooms) . " rooms<br>";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "<br>";
}

// Test Rack class
echo "<h2>Testing Rack Class:</h2>";
try {
    require_once 'classes/Rack.class.php';
    $racks = Rack::GetRackList();
    echo "Rack::GetRackList() returned " . count($racks) . " racks<br>";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "<br>";
}

// Test Device class
echo "<h2>Testing Device Class:</h2>";
try {
    require_once 'classes/Device.class.php';
    $devices = Device::GetDeviceList();
    echo "Device::GetDeviceList() returned " . count($devices) . " devices<br>";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "<br>";
}

echo "<h2>Next Steps:</h2>";
echo "<p>If tables are empty, run: <a href='load_sample_data.php'>load_sample_data.php</a></p>";
