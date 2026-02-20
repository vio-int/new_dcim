<?php
/**
 * Debug Capacity and Asset Pages
 */

require_once 'db.inc.php';

echo "<h1>Debug Capacity & Asset Pages</h1>";

// Check capacity table
echo "<h2>Capacity Table:</h2>";
try {
    $stmt = $dbh->query("SELECT * FROM capacity WHERE is_deleted='N' LIMIT 5");
    if ($stmt) {
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "Found " . count($rows) . " capacity records<br>";
        foreach ($rows as $row) {
            echo "- {$row['name']} (ID: {$row['id']})<br>";
        }
    } else {
        echo "Error querying capacity table<br>";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "<br>";
}

// Check assets with joins
echo "<h2>Assets with Joins:</h2>";
try {
    $sql = "SELECT a.*, l.name as location_name, m.name as model_name, s.name as supplier_name, ss.status as status_name
            FROM assets a
            LEFT JOIN location l ON(l.id=a.location_id)
            LEFT JOIN asset_model m ON(m.id=a.model_id)
            LEFT JOIN asset_supplier s ON(s.id=a.supplier_id)
            LEFT JOIN asset_status ss ON(ss.id=a.status_id)
            WHERE a.is_deleted='N' LIMIT 5";
    $stmt = $dbh->query($sql);
    if ($stmt) {
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "Found " . count($rows) . " assets<br>";
        foreach ($rows as $row) {
            echo "- {$row['name']} (Location: {$row['location_name']}, Model: {$row['model_name']})<br>";
        }
    } else {
        echo "Error querying assets<br>";
        $error = $dbh->errorInfo();
        echo "SQL Error: " . print_r($error, true) . "<br>";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "<br>";
}

// Test Capacity class
echo "<h2>Testing Capacity Class:</h2>";
try {
    require_once 'classes/Capacity.class.php';
    $Capacity = new Capacity();
    $filter = array();
    $result = $Capacity->GetLocationCapacityRows($filter);
    echo "GetLocationCapacityRows returned: " . (is_array($result) ? count($result) . " rows" : "not an array") . "<br>";
    if (is_array($result) && count($result) > 0) {
        echo "First row: " . print_r($result[0], true) . "<br>";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "<br>";
}

// Test Asset class
echo "<h2>Testing Asset Class:</h2>";
try {
    require_once 'classes/Asset.class.php';
    $filter = array();
    $filter['sort_on'] = 'a.id';
    $filter['sort_by'] = 'Asc';
    $result = Asset::GetAssetListRow($filter);
    echo "GetAssetListRow returned: " . (is_array($result) ? count($result) . " rows" : "not an array") . "<br>";
    if (is_array($result) && count($result) > 0) {
        echo "First row: " . print_r($result[0], true) . "<br>";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "<br>";
    echo "Trace: " . $e->getTraceAsString() . "<br>";
}
