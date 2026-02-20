<?php
/**
 * Create Room Table (Fixed)
 * The 'rows' column name is a reserved keyword in MySQL
 */

require_once 'db.inc.php';

try {
    // Drop existing room table if exists
    $dbh->exec("DROP TABLE IF EXISTS room");
    
    // Create room table with backticks around reserved keywords
    $dbh->exec("CREATE TABLE room (
        id INT(11) NOT NULL AUTO_INCREMENT,
        name VARCHAR(255) NOT NULL,
        room_no VARCHAR(50) DEFAULT NULL,
        location_id INT(11) DEFAULT NULL,
        `rows` INT(11) DEFAULT 0,
        `columns` INT(11) DEFAULT 0,
        rows_per_rack INT(11) DEFAULT 0,
        group_columns INT(11) DEFAULT 0,
        group_rows INT(11) DEFAULT 0,
        picture VARCHAR(255) DEFAULT NULL,
        created DATE DEFAULT NULL,
        last_updated DATE DEFAULT NULL,
        is_deleted CHAR(1) DEFAULT 'N',
        PRIMARY KEY (id),
        KEY idx_location_id (location_id),
        KEY idx_is_deleted (is_deleted)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
    echo "<h1>✅ Room table created successfully!</h1>";
    echo "<p>The 'rows' and 'columns' columns were escaped with backticks (reserved keywords in MySQL)</p>";
    echo "<p><a href='debug.php'>Check Debug Page</a></p>";
    
} catch (Exception $e) {
    echo "<h1>❌ Error</h1>";
    echo "<p>" . $e->getMessage() . "</p>";
}
