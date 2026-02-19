# DCIM PHP 8.x Migration Guide

This document provides instructions for migrating the DCIM codebase from older PHP versions to PHP 8.x compatibility.

## Summary of Changes

The following changes were made to ensure PHP 8.x compatibility:

### 1. each() Function Removal (11 occurrences fixed)

**Problem:** The `each()` function was deprecated in PHP 7.2 and removed in PHP 8.0.

**Solution:** Converted all `while(list($key, $val) = each($array))` loops to `foreach($array as $key => $val)`.

**Files Modified:**
- `storageroom.php` (line 84)
- `report_cabinets.php` (line 196)
- `classes/DataCenter.class.php` (line 627)
- `classes/Container.class.php` (lines 215, 223, 280, 301, 351, 376, 431, 456)
- `vendor/setasign/fpdi/fpdi.php` (line 574)
- `vendor/mpdf/mpdf/mpdf.php` (lines 10894, 13040, 26351, 26353, 31096)

**Example Change:**
```php
// OLD (PHP 5.x/7.x)
while(list($devID,$device)=each($devList)){
    // ...
}

// NEW (PHP 8.x compatible)
foreach($devList as $devID=>$device){
    // ...
}
```

### 2. Magic Quotes Functions (2 occurrences fixed)

**Problem:** `get_magic_quotes_runtime()` and `set_magic_quotes_runtime()` were deprecated in PHP 7.4 and removed in PHP 8.0.

**Solution:** Added function existence checks before calling these functions.

**Files Modified:**
- `fpdf.php` (lines 1056-1057)

**Example Change:**
```php
// OLD
if(get_magic_quotes_runtime())
    @set_magic_quotes_runtime(0);

// NEW
if(function_exists('get_magic_quotes_runtime') && get_magic_quotes_runtime())
    @set_magic_quotes_runtime(0);
```

### 3. Old Constructor Style

**Status:** No old-style constructors found in the codebase.

The codebase already uses `__construct()` for all class constructors. Old-style constructors (functions with the same name as the class) were not found.

### 4. var Declarations

**Status:** No `var $property` declarations found.

The codebase already uses proper visibility modifiers (`public`, `protected`, `private`) for class properties.

### 5. Deprecated Functions

**Status:** No deprecated ereg(), split(), or mysql_* functions found in main codebase.

The codebase uses modern alternatives:
- `preg_match()` instead of `ereg()`
- `preg_split()` or `explode()` instead of `split()`
- PDO/MySQLi instead of `mysql_*` functions

## Compatibility Shim

A compatibility shim file (`php8_compat_shim.php`) is provided for backward compatibility. This file includes fallback implementations for removed functions.

### Using the Shim

Include the shim file early in your application bootstrap:

```php
<?php
// At the top of db.inc.php or your main entry point
require_once('php8_compat_shim.php');
?>
```

### Functions Provided by Shim

- `each()` - Array iterator function
- `get_magic_quotes_runtime()` - Always returns false
- `set_magic_quotes_runtime()` - No-op function
- `get_magic_quotes_gpc()` - Always returns false
- `ereg()`, `eregi()` - Maps to `preg_match()`
- `ereg_replace()`, `eregi_replace()` - Maps to `preg_replace()`
- `split()`, `spliti()` - Maps to `preg_split()` or `explode()`
- `sql_regcase()` - Creates case-insensitive regex patterns
- `mysql_*` functions - Maps to `mysqli_*` equivalents (emergency use only)
- `create_function()` - Uses anonymous functions

**Note:** The shim is provided as a safety net. It is recommended to properly migrate code rather than rely on shims.

## Upgrade Instructions

### Prerequisites

- PHP 8.0 or higher
- MySQL 5.7+ or MariaDB 10.2+
- Apache 2.4+ or Nginx

### Step-by-Step Upgrade

1. **Backup your current installation**
   ```bash
   cp -r /var/www/dcim /var/www/dcim-backup
   mysqldump -u root -p dcim_db > dcim_db_backup.sql
   ```

2. **Update PHP to version 8.0 or higher**
   ```bash
   # Ubuntu/Debian
   sudo apt update
   sudo apt install php8.1 php8.1-mysql php8.1-gd php8.1-mbstring php8.1-xml php8.1-curl
   
   # CentOS/RHEL
   sudo dnf module reset php
   sudo dnf module enable php:8.1
   sudo dnf install php php-mysqlnd php-gd php-mbstring php-xml php-curl
   ```

3. **Deploy the fixed codebase**
   ```bash
   # Copy the PHP 8.x compatible files
   cp -r /path/to/php8-fixes/* /var/www/dcim/
   ```

4. **Update configuration**
   - Ensure `db.inc.php` has correct database credentials
   - Verify file permissions are correct

5. **Test the installation**
   - Access the DCIM web interface
   - Check PHP error logs for any remaining issues
   - Test key functionality (device management, reports, etc.)

### Post-Upgrade Verification

Run the following checks to verify the upgrade:

```bash
# Check PHP version
php -v

# Verify syntax of modified files
php -l storageroom.php
php -l report_cabinets.php
php -l classes/DataCenter.class.php
php -l classes/Container.class.php

# Check for remaining each() usage
grep -rn "while.*list.*each" /var/www/dcim --include="*.php" | grep -v vendor

# Check for deprecated functions
grep -rn "\bereg\b\|\bsplit\b\|\bmysql_" /var/www/dcim --include="*.php" | grep -v vendor
```

## Breaking Changes to Be Aware Of

### PHP 8.0 Breaking Changes

1. **each() removed** - All `each()` calls have been converted to `foreach()`
2. **Magic quotes functions removed** - Added function existence checks
3. **Error reporting changes** - Many warnings are now errors
4. **String to number comparison changes** - Non-strict comparisons between strings and numbers work differently

### PHP 8.1+ Additional Changes

1. **Return type compatibility** - Incompatible return types in inheritance now throw errors
2. **null to non-nullable internal function parameters** - Deprecated, will become errors
3. **Resource to Object migration** - Many resources converted to objects

## Troubleshooting

### Issue: "Call to undefined function each()"

**Solution:** Ensure all files have been updated to use `foreach()` instead of `each()`. Check that the vendor directory was also updated.

### Issue: "Uncaught Error: Call to undefined function get_magic_quotes_runtime()"

**Solution:** The fpdf.php file should have the function existence check. If not, manually add it or include the compatibility shim.

### Issue: "Deprecated: Function mysql_*() is deprecated"

**Solution:** The codebase should already use PDO/MySQLi. If you see this error, check for any custom modifications that might use old mysql functions.

### Issue: "Fatal error: Uncaught TypeError"

**Solution:** PHP 8 is stricter about type coercion. Check the error message for the specific file and line, and ensure proper type handling.

## Support

For issues related to this PHP 8.x migration:

1. Check the error logs: `/var/log/apache2/error.log` or `/var/log/nginx/error.log`
2. Verify all files were properly copied
3. Test with the compatibility shim included
4. Review PHP 8 migration guides for specific error messages

## References

- [PHP 8.0 Migration Guide](https://www.php.net/manual/en/migration80.php)
- [PHP 8.1 Migration Guide](https://www.php.net/manual/en/migration81.php)
- [PHP 8.2 Migration Guide](https://www.php.net/manual/en/migration82.php)
- [Deprecated Features in PHP 7.x](https://www.php.net/manual/en/migration74.deprecated.php)
