<?php
/**
 * PHP 8.x Compatibility Shim for DCIM
 * 
 * This file provides backward compatibility for deprecated/removed PHP functions
 * when running on older PHP versions or for edge cases.
 * 
 * Include this file early in your application bootstrap (e.g., in db.inc.php)
 */

// Prevent multiple inclusions
if (defined('DCIM_PHP8_COMPAT_LOADED')) {
    return;
}
define('DCIM_PHP8_COMPAT_LOADED', true);

/**
 * Shim for each() function removed in PHP 8.0
 * 
 * Note: This is a basic shim. The recommended approach is to convert
 * while(list($k,$v)=each($array)) loops to foreach($array as $k=>$v).
 * 
 * This shim is provided as a last resort for any missed cases.
 */
if (!function_exists('each')) {
    function each(array &$array) {
        if (empty($array)) {
            return false;
        }
        
        $key = key($array);
        $value = current($array);
        
        if ($key === null) {
            return false;
        }
        
        next($array);
        
        return array(
            1 => $value,
            'value' => $value,
            0 => $key,
            'key' => $key
        );
    }
}

/**
 * Shim for get_magic_quotes_runtime() - removed in PHP 8.0
 * Always returns false since magic quotes were removed in PHP 5.4
 */
if (!function_exists('get_magic_quotes_runtime')) {
    function get_magic_quotes_runtime() {
        return false;
    }
}

/**
 * Shim for set_magic_quotes_runtime() - removed in PHP 8.0
 * No-op since magic quotes don't exist anymore
 */
if (!function_exists('set_magic_quotes_runtime')) {
    function set_magic_quotes_runtime($new_setting) {
        return true;
    }
}

/**
 * Shim for get_magic_quotes_gpc() - removed in PHP 8.0
 * Always returns false since magic quotes were removed in PHP 5.4
 */
if (!function_exists('get_magic_quotes_gpc')) {
    function get_magic_quotes_gpc() {
        return false;
    }
}

/**
 * Shim for ereg() - removed in PHP 7.0
 * Maps to preg_match()
 */
if (!function_exists('ereg')) {
    function ereg($pattern, $string, &$regs = null) {
        return preg_match('/' . $pattern . '/', $string, $regs);
    }
}

/**
 * Shim for eregi() - removed in PHP 7.0
 * Maps to preg_match() with case-insensitive flag
 */
if (!function_exists('eregi')) {
    function eregi($pattern, $string, &$regs = null) {
        return preg_match('/' . $pattern . '/i', $string, $regs);
    }
}

/**
 * Shim for ereg_replace() - removed in PHP 7.0
 * Maps to preg_replace()
 */
if (!function_exists('ereg_replace')) {
    function ereg_replace($pattern, $replacement, $string) {
        return preg_replace('/' . $pattern . '/', $replacement, $string);
    }
}

/**
 * Shim for eregi_replace() - removed in PHP 7.0
 * Maps to preg_replace() with case-insensitive flag
 */
if (!function_exists('eregi_replace')) {
    function eregi_replace($pattern, $replacement, $string) {
        return preg_replace('/' . $pattern . '/i', $replacement, $string);
    }
}

/**
 * Shim for split() - removed in PHP 7.0
 * Maps to preg_split() or explode() for simple delimiters
 */
if (!function_exists('split')) {
    function split($pattern, $string, $limit = -1) {
        // If pattern is a simple string (not regex), use explode for better performance
        if (preg_match('/^[a-zA-Z0-9\\s]+$/', $pattern)) {
            return explode($pattern, $string, $limit > 0 ? $limit : PHP_INT_MAX);
        }
        return preg_split('/' . $pattern . '/', $string, $limit);
    }
}

/**
 * Shim for spliti() - removed in PHP 7.0
 * Maps to preg_split() with case-insensitive flag
 */
if (!function_exists('spliti')) {
    function spliti($pattern, $string, $limit = -1) {
        return preg_split('/' . $pattern . '/i', $string, $limit);
    }
}

/**
 * Shim for sql_regcase() - removed in PHP 7.0
 * Creates a case-insensitive regex pattern from a string
 */
if (!function_exists('sql_regcase')) {
    function sql_regcase($string) {
        $chars = str_split($string);
        $result = '';
        foreach ($chars as $char) {
            $lower = strtolower($char);
            $upper = strtoupper($char);
            if ($lower !== $upper) {
                $result .= '[' . $lower . $upper . ']';
            } else {
                $result .= $char;
            }
        }
        return $result;
    }
}

/**
 * Shim for mysql_* functions - removed in PHP 7.0
 * These are basic shims that map to mysqli_* functions.
 * 
 * IMPORTANT: These shims are provided for emergency compatibility only.
 * It is strongly recommended to migrate to PDO or mysqli properly.
 */
if (!function_exists('mysql_connect') && extension_loaded('mysqli')) {
    // Global connection resource for shim functions
    global $_dcim_mysql_conn;
    $_dcim_mysql_conn = null;

    function mysql_connect($server = null, $username = null, $password = null, $new_link = false, $client_flags = 0) {
        global $_dcim_mysql_conn;
        $_dcim_mysql_conn = mysqli_connect($server, $username, $password);
        return $_dcim_mysql_conn;
    }

    function mysql_select_db($database_name, $link_identifier = null) {
        global $_dcim_mysql_conn;
        $link = $link_identifier ?: $_dcim_mysql_conn;
        return mysqli_select_db($link, $database_name);
    }

    function mysql_query($query, $link_identifier = null) {
        global $_dcim_mysql_conn;
        $link = $link_identifier ?: $_dcim_mysql_conn;
        return mysqli_query($link, $query);
    }

    function mysql_fetch_array($result, $result_type = MYSQLI_BOTH) {
        return mysqli_fetch_array($result, $result_type);
    }

    function mysql_fetch_assoc($result) {
        return mysqli_fetch_assoc($result);
    }

    function mysql_fetch_row($result) {
        return mysqli_fetch_row($result);
    }

    function mysql_fetch_object($result, $class_name = 'stdClass', $params = []) {
        return mysqli_fetch_object($result, $class_name, $params);
    }

    function mysql_num_rows($result) {
        return mysqli_num_rows($result);
    }

    function mysql_affected_rows($link_identifier = null) {
        global $_dcim_mysql_conn;
        $link = $link_identifier ?: $_dcim_mysql_conn;
        return mysqli_affected_rows($link);
    }

    function mysql_insert_id($link_identifier = null) {
        global $_dcim_mysql_conn;
        $link = $link_identifier ?: $_dcim_mysql_conn;
        return mysqli_insert_id($link);
    }

    function mysql_error($link_identifier = null) {
        global $_dcim_mysql_conn;
        $link = $link_identifier ?: $_dcim_mysql_conn;
        return mysqli_error($link);
    }

    function mysql_errno($link_identifier = null) {
        global $_dcim_mysql_conn;
        $link = $link_identifier ?: $_dcim_mysql_conn;
        return mysqli_errno($link);
    }

    function mysql_real_escape_string($string, $link_identifier = null) {
        global $_dcim_mysql_conn;
        $link = $link_identifier ?: $_dcim_mysql_conn;
        return mysqli_real_escape_string($link, $string);
    }

    function mysql_escape_string($string) {
        return addslashes($string);
    }

    function mysql_close($link_identifier = null) {
        global $_dcim_mysql_conn;
        $link = $link_identifier ?: $_dcim_mysql_conn;
        return mysqli_close($link);
    }

    function mysql_free_result($result) {
        return mysqli_free_result($result);
    }

    function mysql_get_server_info($link_identifier = null) {
        global $_dcim_mysql_conn;
        $link = $link_identifier ?: $_dcim_mysql_conn;
        return mysqli_get_server_info($link);
    }

    function mysql_set_charset($charset, $link_identifier = null) {
        global $_dcim_mysql_conn;
        $link = $link_identifier ?: $_dcim_mysql_conn;
        return mysqli_set_charset($link, $charset);
    }
}

/**
 * Shim for create_function() - deprecated in PHP 7.2, removed in PHP 8.0
 * Uses anonymous functions instead
 */
if (!function_exists('create_function')) {
    function create_function($args, $code) {
        return create_function_fallback($args, $code);
    }
    
    // Helper to create a callable from args and code strings
    function create_function_fallback($args, $code) {
        // This is a simplified shim - complex cases may need manual conversion
        return eval('return function(' . $args . ') {' . $code . '};');
    }
}
