<?php
/**
 * HTML Escaping and Security Functions
 * 
 * HIGH-011 & HIGH-012: XSS Prevention
 */

/**
 * Properly escape HTML entities to prevent XSS attacks
 * 
 * @param string $text The text to escape
 * @param bool $doubleEncode Whether to encode existing HTML entities (default: true)
 * @return string The escaped text
 */
function html_escape($text, $doubleEncode = true) {
    if (is_null($text)) {
        return '';
    }
    
    // Use htmlspecialchars with UTF-8 encoding and double quotes
    return htmlspecialchars(
        (string) $text,
        ENT_QUOTES | ENT_HTML5,
        'UTF-8',
        $doubleEncode
    );
}

/**
 * Escape text for use in JavaScript strings
 * 
 * @param string $text The text to escape
 * @return string The escaped text safe for JavaScript
 */
function js_escape($text) {
    if (is_null($text)) {
        return '';
    }
    
    return json_encode((string) $text, JSON_UNESCAPED_UNICODE);
}

/**
 * Escape text for use in CSS
 * 
 * @param string $text The text to escape
 * @return string The escaped text safe for CSS
 */
function css_escape($text) {
    if (is_null($text)) {
        return '';
    }
    
    // CSS escaping - remove control characters and escape special chars
    $text = preg_replace('/[\x00-\x1F\x7F]/', '', (string) $text);
    return str_replace(
        array('<', '>', '"', "'", '&'),
        array('\\3c ', '\\3e ', '\\22 ', '\\27 ', '\\26 '),
        $text
    );
}

/**
 * Escape text for use in URL parameters
 * 
 * @param string $text The text to escape
 * @return string The URL-encoded text
 */
function url_escape($text) {
    if (is_null($text)) {
        return '';
    }
    
    return urlencode((string) $text);
}

/**
 * Sanitize output for display in HTML context
 * This is a convenience wrapper around html_escape
 * 
 * @param mixed $data Data to display
 * @return string Sanitized string
 */
function e($data) {
    return html_escape($data);
}

/**
 * Output escaped text directly (echo wrapper)
 * 
 * @param mixed $data Data to output
 */
function ee($data) {
    echo html_escape($data);
}
?>
