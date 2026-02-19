# DCIM Security Fixes Summary

This document summarizes the security vulnerabilities fixed in the DCIM codebase.

## Files Modified/Created

### 1. CRITICAL-001: Hardcoded Database Credentials (db.inc.php)

**Problem:** Database credentials were hardcoded in `db.inc.php` with plaintext passwords visible in source code.

**Solution:**
- Moved credentials to environment variables
- Added `.env` file support for local development
- Updated `db.inc.php` to use `getenv()` for credential retrieval
- Added fallback values only for development (production requires env vars)

**Changes:**
- Removed hardcoded `$dbhost`, `$dbname`, `$dbuser`, `$dbpass` values
- Added `.env` file loader
- Credentials now read from: `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`

### 2. HIGH-010: Error Reporting (db.inc.php)

**Problem:** `error_reporting(0)` suppressed all errors, hiding security issues and making debugging difficult.

**Solution:**
- Removed `error_reporting(0)`
- Implemented environment-based error handling
- Production: Log errors to file, don't display to users
- Development: Display all errors for debugging
- Added proper PDO error mode (ERRMODE_EXCEPTION)

**Changes:**
- Added `$appEnv` and `$appDebug` checks
- Errors logged to `logs/error.log` in production
- Generic error message shown to users in production
- Detailed errors shown only in development

### 3. CRITICAL-002 & CRITICAL-003: SQL Injection (config.inc.php)

**Problem:** `UpdateParameter()` and `RevertToDefault()` methods used string concatenation for SQL queries, allowing SQL injection.

**Solution:**
- Converted both methods to use PDO prepared statements
- Used parameterized queries with bound values
- Added proper error handling with try/catch blocks

**Changes in `UpdateParameter($parameter, $value)`:**
```php
// BEFORE (VULNERABLE):
$sql="UPDATE fac_Config SET Value=\"$value\" WHERE Parameter=\"$parameter\";";
$dbh->query($sql);

// AFTER (SECURE):
$sql = "UPDATE fac_Config SET Value=:value WHERE Parameter=:parameter";
$sth = $dbh->prepare($sql);
$sth->execute(array(':value' => $value, ':parameter' => $parameter));
```

**Changes in `RevertToDefault($parameter)`:**
```php
// BEFORE (VULNERABLE):
$sql="UPDATE fac_Config SET Value=DefaultVal WHERE Parameter=\"$parameter\";";
$dbh->query($sql);

// AFTER (SECURE):
$sql = "UPDATE fac_Config SET Value=DefaultVal WHERE Parameter=:parameter";
$sth = $dbh->prepare($sql);
$sth->execute(array(':parameter' => $parameter));
```

### 4. CRITICAL-005: CSRF Protection (csrf.inc.php - NEW FILE)

**Problem:** No CSRF protection on forms, allowing attackers to perform actions on behalf of authenticated users.

**Solution:**
- Created new `csrf.inc.php` with CSRF token generation and validation
- Tokens are cryptographically secure (32 bytes from `random_bytes()`)
- Tokens expire after 1 hour (configurable)
- Added timing-safe comparison using `hash_equals()`

**Functions Added:**
- `csrf_token()` - Generate or retrieve token
- `csrf_token_field()` - Output HTML hidden input
- `csrf_validate($maxAge)` - Validate token on POST
- `csrf_regenerate()` - Regenerate token (login/logout)
- `csrf_ajax_token()` - Get token for AJAX requests

**Integration:**
- `db.inc.php` now includes `csrf.inc.php`
- All forms must include `csrf_token_field()`
- All POST handlers must call `csrf_validate()`

### 5. HIGH-011 & HIGH-012: XSS Vulnerabilities (security.inc.php - NEW FILE)

**Problem:** User input was output directly without escaping, allowing XSS attacks.

**Solution:**
- Created `security.inc.php` with proper escaping functions
- `html_escape()` - For HTML output (uses `htmlspecialchars()`)
- `js_escape()` - For JavaScript contexts (uses `json_encode()`)
- `css_escape()` - For CSS contexts
- `url_escape()` - For URL parameters
- Convenience functions `e()` and `ee()` for quick escaping

**Files Updated:**
- `device.php` - All user output now uses `html_escape()`
- `asset.php` - All user output now uses `html_escape()`

**Examples:**
```php
// BEFORE (VULNERABLE):
echo "<option value='$mfgRow->PortID'>$mfgRow->Name</option>";
echo "<input value='$mfg->Name'>";

// AFTER (SECURE):
echo "<option value='" . html_escape($mfgRow->PortID) . "'>" . html_escape($mfgRow->Name) . "</option>";
echo "<input value='" . html_escape($mfg->Name) . "'>";
```

### 6. Form Updates for CSRF Protection

**device.php and asset.php:**
- Added `require_once("security.inc.php")`
- Added CSRF validation at top of POST handling
- Added `csrf_token_field()` to all forms
- Added CSRF token to AJAX requests

## Files Created

1. **`.env.example`** - Template for environment variables
2. **`csrf.inc.php`** - CSRF protection functions
3. **`security.inc.php`** - XSS protection functions

## Files Modified

1. **`db.inc.php`** - Environment-based credentials, proper error handling
2. **`config.inc.php`** - Prepared statements for SQL injection prevention
3. **`device.php`** - CSRF tokens, XSS escaping
4. **`asset.php`** - CSRF tokens, XSS escaping

## Deployment Instructions

1. **Copy environment file:**
   ```bash
   cp .env.example .env
   ```

2. **Edit `.env` with production credentials:**
   ```
   DB_HOST=localhost
   DB_NAME=dcim_production
   DB_USER=dcim_user
   DB_PASS=your_secure_random_password
   APP_ENV=production
   APP_DEBUG=false
   ```

3. **Set proper file permissions:**
   ```bash
   chmod 600 .env
   mkdir -p logs
   chmod 750 logs
   ```

4. **For production servers (alternative to .env):**
   Set environment variables in web server config:
   - Apache: `SetEnv DB_PASS your_password` in VirtualHost
   - Nginx: `fastcgi_param DB_PASS your_password;`

5. **Verify CSRF protection:**
   - All forms should include hidden `csrf_token` field
   - AJAX requests should include token in data

## Security Checklist

- [ ] Database credentials moved to environment variables
- [ ] `.env` file has restrictive permissions (600)
- [ ] Error reporting configured for production
- [ ] All SQL queries use prepared statements
- [ ] CSRF tokens added to all forms
- [ ] CSRF validation on all POST handlers
- [ ] All user output escaped with `html_escape()`
- [ ] Session security settings configured
