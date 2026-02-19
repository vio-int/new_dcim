<?php
/**
 * VIO DCIM - Database Connection
 * 
 * SECURITY FIXES APPLIED:
 * - CRITICAL-001: Removed hardcoded credentials, now uses environment variables
 * - HIGH-010: Removed error_reporting(0), added proper error handling
 */

// Not everyone will have the ability to (or, let's face it, attention span) set the PHP directive session.auto_start = 1
// so we will simply start a session here (which will not cause any issues if auto_start is already set to 1) unless
// we are being invoked from the command line

if (php_sapi_name() != "cli") {
    session_start();
}

// Set to true if you want to skip the installer check
$devMode = false;

// CRITICAL-001: Load environment variables from .env file if it exists
// In production, environment variables should be set at the web server level
if (file_exists(__DIR__ . '/.env')) {
    $envLines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($envLines as $line) {
        if (strpos($line, '#') === 0 || strpos($line, '=') === false) {
            continue;
        }
        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        if (!empty($key) && getenv($key) === false) {
            putenv("$key=$value");
            $_ENV[$key] = $value;
        }
    }
}

// CRITICAL-001: Get database credentials from environment variables
// Fallback values are only for development - production MUST set env vars
$dbhost = getenv('DB_HOST') ?: 'localhost';
$dbname = getenv('DB_NAME') ?: 'dcim';
$dbuser = getenv('DB_USER') ?: 'dcim_user';
$dbpass = getenv('DB_PASS') ?: '';

// HIGH-010: Proper error handling based on environment
// In production, log errors but don't display them
// In development, display errors for debugging
$appEnv = getenv('APP_ENV') ?: 'production';
$appDebug = getenv('APP_DEBUG') === 'true';

if ($appEnv === 'production' && !$appDebug) {
    // Production: Log errors, don't display
    error_reporting(E_ALL);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    ini_set('error_log', __DIR__ . '/logs/error.log');
} else {
    // Development: Display all errors
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
}

$locale = "en_US";
$codeset = "UTF-8";

$pdo_options = array(
    PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
    PDO::ATTR_PERSISTENT => true,
    // HIGH-010: Set PDO error mode to exception for proper error handling
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
);

try {
    $pdoconnect = "mysql:host=$dbhost;dbname=$dbname;charset=utf8mb4";
    $dbh = new PDO($pdoconnect, $dbuser, $dbpass, $pdo_options);
} catch (PDOException $e) {
    // HIGH-010: Log detailed error but show generic message to user
    error_log("Database connection failed: " . $e->getMessage());
    
    // Don't expose sensitive details in production
    if ($appEnv === 'production' && !$appDebug) {
        die("Error: Unable to connect to the database. Please contact the administrator.");
    } else {
        printf("Error! %s\n", $e->getMessage());
        die();
    }
}

// Make sure that you only have ONE of these authentication types uncommented.  You will get an error if you
// try to define the same name twice.
if (!defined("AUTHENTICATION")) {
    define("AUTHENTICATION", "Apache");

    /* If you want to use Oauth authentication, comment the above 3 lines, uncomment the next 4 lines
       and place your authentication handler in login.php (create symbolic link). 
       ex.  cp oauth/login_with_google.php oauth/login.php
    */
    // define( "AUTHENTICATION", "Oauth" );

    /* If you want to use Saml authentication, comment the above defines for AUTHENTICATION,
       uncomment the Saml define below
    */
    // define( "AUTHENTICATION", "Saml" );

    /* 	LDAP authentication and authorization, which is far from simple.
    	Don't even try to enable this unless you know how to query
    	your LDAP server.  */
    // define( "AUTHENTICATION", "LDAP" );
}

require_once('config.inc.php');
$config = new Config();

// CRITICAL-005: Initialize CSRF protection
require_once('csrf.inc.php');
?>
