# DCIM Codebase Security Audit Report

**Date:** 2026-02-19  
**Scope:** /tmp/dcim_php  
**Classification:** CONFIDENTIAL

---

## Executive Summary

This security audit identified **multiple critical and high-severity vulnerabilities** in the DCIM codebase. The most significant issues include SQL injection vulnerabilities, hardcoded credentials, XSS vulnerabilities, and missing CSRF protection. Immediate remediation is strongly recommended.

| Severity | Count |
|----------|-------|
| Critical | 5 |
| High | 12 |
| Medium | 8 |
| Low | 4 |

---

## 1. SQL Injection Vulnerabilities

### CRITICAL-001: Direct SQL String Concatenation in db.inc.php
**File:** `/tmp/dcim_php/db.inc.php`  
**Line:** 9-17  
**Severity:** Critical

**Description:** Hardcoded database credentials with weak password.

```php
if($_SERVER['HTTP_HOST'] == "localhost")
{
    $dbhost = 'localhost';
    $dbname = 'yanto_dcim';
    $dbuser = 'root';
    $dbpass = '';
} else {
    $dbhost = 'localhost';
    $dbname = 'yanto_dcim';
    $dbuser = 'root';
    $dbpass = 'Admin1@#4';
}
```

**Impact:** Hardcoded credentials expose database access. Weak password can be brute-forced.

---

### CRITICAL-002: SQL Injection in config.inc.php UpdateParameter()
**File:** `/tmp/dcim_php/config.inc.php`  
**Line:** 44-53  
**Severity:** Critical

**Description:** Direct variable interpolation in SQL without sanitization.

```php
static function UpdateParameter($parameter,$value){
    global $dbh;

    if(is_null($parameter) || is_null($value)){
        return false;
    }else{
        $sql="UPDATE fac_Config SET Value=\"$value\" WHERE Parameter=\"$parameter\";";
        if($dbh->query($sql)){
            return true;
        }else{
            return false;
        }
    }
}
```

**Impact:** Attackers can inject arbitrary SQL through `$parameter` or `$value`.

---

### CRITICAL-003: SQL Injection in config.inc.php RevertToDefault()
**File:** `/tmp/dcim_php/config.inc.php`  
**Line:** 56-64  
**Severity:** Critical

```php
static function RevertToDefault($parameter){
    global $dbh;
    
    if($parameter=='none'){
        $sql='UPDATE fac_Config SET Value=DefaultVal;';
    }else{
        $sql="UPDATE fac_Config SET Value=DefaultVal WHERE Parameter=\"$parameter\";";
    }
    
    $dbh->query($sql);
    return;
}
```

---

### HIGH-001: SQL Injection in Rack.class.php GetOrderByID()
**File:** `/tmp/dcim_php/classes/Rack.class.php`  
**Line:** 140-151  
**Severity:** High

```php
function GetOrderByID(){
    $this->MakeSafe();

    $sql="SELECT * FROM rack WHERE is_deleted='N' AND is_simulation='N' AND id=$this->PortID;";
    
    if($row=$this->query($sql)->fetch()){
            foreach(Rack::RowToObject($row) as $prop => $value){
                    $this->$prop=$value;
            }
            return true;
    }else{
            return false;
    }
}
```

**Note:** While `MakeSafe()` calls `intval()` on PortID, the `GetObjectByName()` method is vulnerable:

```php
function GetObjectByName(){
    $this->MakeSafe();

    $sql="SELECT * FROM rack WHERE is_deleted='N' AND is_simulation='N' AND ucase(Name)=ucase('".$this->Name."');";
    // ...
}
```

---

### HIGH-002: SQL Injection in Rack.class.php CreateObject()
**File:** `/tmp/dcim_php/classes/Rack.class.php`  
**Line:** 395-420  
**Severity:** High

```php
function CreateObject(){
    global $dbh;

    $this->MakeSafe();
    // ...
    $sql="INSERT INTO rack SET name=\"$this->Name\", site_id=".$ins_location_id['locationID'].";
    // ...
    if(!$dbh->exec($sql)){
        error_log( "SQL Error: " . $sql );
        return false;
    }
}
```

**Impact:** Multiple fields are directly interpolated into SQL without proper parameterization.

---

### HIGH-003: SQL Injection in Asset.class.php CreateObject()
**File:** `/tmp/dcim_php/classes/Asset.class.php`  
**Line:** 315-345  
**Severity:** High

```php
function CreateObject(){
    global $dbh;

    $this->MakeSafe();
    // ...
    $sql="INSERT INTO assets SET name=\"$this->Name\", asset_tag=\"$this->Asset_tag\", ...";
    // ...
}
```

---

### HIGH-004: SQL Injection in Device_new.class.php CreateObject()
**File:** `/tmp/dcim_php/classes/Device_new.class.php`  
**Line:** 315-340  
**Severity:** High

```php
function CreateObject(){
    global $dbh;

    $this->MakeSafe();
    // ...
    $sql="INSERT INTO device SET name=\"$this->Name\", site_id=\"$this->Site\", ...";
    // ...
}
```

---

### HIGH-005: SQL Injection in Location.class.php UpdateObject()
**File:** `/tmp/dcim_php/classes/Location.class.php`  
**Line:** 280-295  
**Severity:** High

```php
function UpdateObject(){
    $this->MakeSafe();

    $sql="UPDATE location SET name=\"$this->Name\", slug=\"$this->Slug\", status=\"$this->Status\", ... WHERE id=$this->PortID;";
    // ...
}
```

---

### HIGH-006: SQL Injection in Model.class.php CreateObject()
**File:** `/tmp/dcim_php/classes/Model.class.php`  
**Line:** 230-260  
**Severity:** High

```php
function CreateObject($params = array()){
    global $dbh;

    $this->MakeSafe();
    // ...
    $sql="INSERT INTO asset_model SET name=\"$this->Name\", manufacture_id=\"$this->Manufacture\", ...";
    // ...
}
```

---

### HIGH-007: SQL Injection in Room.class.php CreateObject()
**File:** `/tmp/dcim_php/classes/Room.class.php`  
**Line:** 220-235  
**Severity:** High

```php
function CreateObject(){
    global $dbh;

    $this->MakeSafe();
    $picture_name = str_replace(" ", "_", $this->Front_pic);
    $sql="INSERT INTO room SET name=\"$this->Name\", room_no=\"$this->RoomNo\", ...";
    // ...
}
```

---

### HIGH-008: SQL Injection in get_rack.php
**File:** `/tmp/dcim_php/get_rack.php`  
**Line:** 6-20  
**Severity:** High

```php
$location_id = $_POST['location_id'];
$html_content = "<option value=''>-- select --</option>";
$respone = array();

$rack=new Rack();

$RackList = $rack->GetLocationRackList($location_id);
```

**Impact:** `$location_id` is passed directly to `GetLocationRackList()` which uses it unsanitized in SQL.

---

### HIGH-009: SQL Injection in get_positions.php
**File:** `/tmp/dcim_php/get_positions.php`  
**Line:** 6-15  
**Severity:** High

```php
$rack_id = $_POST['rack_id'];
$selected_id = $_POST['selected'];
// ...
$filter = array();
$filter['rack'] = $rack_id;

$RackList = $rack->GetRackOne($filter);
```

---

### MEDIUM-001: SQL Injection in Manufacture.class.php
**File:** `/tmp/dcim_php/classes/Manufacture.class.php`  
**Line:** 95-106  
**Severity:** Medium

```php
function GetObjectByName(){
    $this->MakeSafe();

    $sql="SELECT * FROM manufacture WHERE ucase(Name)=ucase('".$this->Name."');";
    // ...
}
```

---

## 2. Hardcoded Credentials and Sensitive Data Exposure

### CRITICAL-004: Hardcoded Database Password
**File:** `/tmp/dcim_php/db.inc.php`  
**Line:** 15  
**Severity:** Critical

```php
$dbpass = 'Admin1@#4';
```

**Impact:** Database password is hardcoded in source code, exposing it to anyone with file access.

---

### HIGH-010: Error Reporting Disabled
**File:** `/tmp/dcim_php/db.inc.php`  
**Line:** 2  
**Severity:** High

```php
error_reporting(0);
```

**Impact:** All errors are suppressed, making debugging difficult and potentially hiding security issues.

---

### MEDIUM-002: SQL Errors Logged with Full Query
**File:** Multiple files  
**Example:** `/tmp/dcim_php/classes/Rack.class.php` Line 418  
**Severity:** Medium

```php
if(!$dbh->exec($sql)){
    error_log( "SQL Error: " . $sql );
    return false;
}
```

**Impact:** Full SQL queries (potentially containing sensitive data) are written to error logs.

---

## 3. XSS (Cross-Site Scripting) Vulnerabilities

### HIGH-011: Unescaped Output in device.php
**File:** `/tmp/dcim_php/device.php`  
**Line:** 45  
**Severity:** High

```php
$html_content .= "<option value='" . $_GET['position'] . "' selected>" . $_GET['position'] . "</option>";
```

**Impact:** User-controlled `$_GET['position']` is output without sanitization.

---

### HIGH-012: Unescaped Output in asset.php
**File:** `/tmp/dcim_php/asset.php`  
**Line:** Multiple locations  
**Severity:** High

```php
echo "<option value=\"$val->PortID\">$val->Name</option>\n";
```

**Impact:** Database values output directly without HTML encoding.

---

### MEDIUM-003: Unescaped Output in rack.php
**File:** `/tmp/dcim_php/rack.php`  
**Line:** 290  
**Severity:** Medium

```php
echo "<option value=\"$mfgRow->PortID\"$selected>$mfgRow->Name</option>\n";
```

---

### MEDIUM-004: Unescaped Output in search.php
**File:** `/tmp/dcim_php/search.php`  
**Line:** 35  
**Severity:** Medium

```php
$searchTerm=preg_replace("/[[:cntrl:]]/","",$_REQUEST['search']);
// ...
$title=__("Serial number search results for")." &quot;$searchTerm&quot;";
```

**Note:** While `sanitize()` is called, the output in HTML context may still be vulnerable.

---

## 4. CSRF (Cross-Site Request Forgery) Protection Gaps

### CRITICAL-005: No CSRF Protection on State-Changing Operations
**Files:** Multiple  
**Severity:** Critical

**Affected Files:**
- `/tmp/dcim_php/device.php` - Create/Update/Delete operations
- `/tmp/dcim_php/asset.php` - Create/Update/Delete operations
- `/tmp/dcim_php/rack.php` - Create/Update/Delete operations
- `/tmp/dcim_php/ajax_create_model.php` - Model creation
- `/tmp/dcim_php/ajax_create_status.php` - Status creation

**Example from device.php:**
```php
if (isset($_POST["action"]) && (($_POST["action"] == "Create") || ($_POST["action"] == "Update"))) {
    $mfg->PortID = $_POST["PortID"];
    // ... no CSRF token validation ...
    $mfg->CreateObject();
}
```

**Impact:** Attackers can trick authenticated users into performing unintended actions.

---

### HIGH-013: AJAX Endpoints Lack CSRF Protection
**File:** `/tmp/dcim_php/device.php`  
**Line:** 48-60  
**Severity:** High

```php
if (isset($_POST['action']) && $_POST["action"] == "Delete") {
    header('Content-Type: application/json');
    $response = false;
    if (isset($_POST["TransferTo"])) {
        $mfg->PortID = $_POST['PortID'];
        if ($mfg->DeleteObject($_POST["TransferTo"])) {
            $response = true;
        }
    }
    echo json_encode($response);
    exit;
}
```

---

## 5. Authentication/Authorization Weaknesses

### HIGH-014: Weak Authorization Check Pattern
**File:** `/tmp/dcim_php/device.php`  
**Line:** 7-11  
**Severity:** High

```php
if (!$person->SiteAdmin) {
    // No soup for you.
    header('Location: ' . redirect());
    exit;
}
```

**Impact:** Only checks for SiteAdmin role; no granular permission checks.

---

### MEDIUM-005: Missing Authentication on AJAX Endpoints
**File:** `/tmp/dcim_php/get_rack.php`  
**Line:** 1-25  
**Severity:** Medium

```php
require_once( "db.inc.php" );
require_once( "facilities.inc.php" );

$location_id = $_POST['location_id'];
// No authentication check!
```

---

### MEDIUM-006: Missing Authentication on get_positions.php
**File:** `/tmp/dcim_php/get_positions.php`  
**Line:** 1-15  
**Severity:** Medium

No authentication check before processing POST data.

---

## 6. File Upload Vulnerabilities

### HIGH-015: Insecure File Upload in Device_new.class.php
**File:** `/tmp/dcim_php/classes/Device_new.class.php`  
**Line:** 85-115  
**Severity:** High

```php
// FILE UPLOAD CODE START
if(!empty($_FILES['front_picture'])){
    $frontfile_name = $_FILES['front_picture'];
    $fronttmp_name = $_FILES["front_picture"]["tmp_name"];
    $frontFileName = $_FILES["front_picture"]["name"];
    // ...
    $fronttarget_file = _PATH.DIRECTORY_SEPARATOR.'uploads/devices/' . $frontFileName;

    if ($fronttmp_name !="" && file_put_contents($fronttarget_file, $data)) {
        $m->Front_pic = $frontFileName;
    }
}
```

**Issues:**
1. No file extension validation
2. No MIME type verification
3. Original filename is preserved (path traversal risk)
4. No file size limits
5. Files stored in web-accessible directory

---

### HIGH-016: Insecure File Upload in Asset.class.php
**File:** `/tmp/dcim_php/classes/Asset.class.php`  
**Line:** 95-115  
**Severity:** High

Similar vulnerabilities as Device_new.class.php.

---

### MEDIUM-007: Insecure File Upload in Room.class.php
**File:** `/tmp/dcim_php/classes/Room.class.php`  
**Line:** 55-75  
**Severity:** Medium

```php
// FILE UPLOAD CODE START
if(!empty($_FILES)){
    $frontfile_name = $_FILES['front_picture'];
    $fronttmp_name = $_FILES["front_picture"]["tmp_name"];
    $frontFileName = str_replace(" ", "_", $_FILES["front_picture"]["name"]);
    // ...
}
```

---

### MEDIUM-008: Insecure File Upload in Model.class.php
**File:** `/tmp/dcim_php/classes/Model.class.php`  
**Line:** 65-85  
**Severity:** Medium

---

## 7. Additional Security Issues

### LOW-001: Information Disclosure via Error Messages
**File:** `/tmp/dcim_php/classes/Rack.class.php`  
**Line:** 418  
**Severity:** Low

```php
error_log( "SQL Error: " . $sql );
```

---

### LOW-002: Deprecated PHP Functions
**File:** `/tmp/dcim_php/misc.inc.php`  
**Line:** 15-35  
**Severity:** Low

Use of `addslashes()` for SQL escaping is deprecated and unsafe.

```php
$clean=addslashes($clean);
```

---

### LOW-003: Weak Session Management
**File:** `/tmp/dcim_php/db.inc.php`  
**Line:** 6-8  
**Severity:** Low

```php
if ( php_sapi_name() != "cli" ) {
      session_start();
}
```

No session regeneration or secure session configuration.

---

### LOW-004: Missing Input Validation on Numeric Fields
**File:** Multiple  
**Severity:** Low

Many numeric fields accept string values without proper validation.

---

## Recommendations

### Immediate Actions (Critical/High)

1. **Implement Prepared Statements**: Replace all direct SQL string concatenation with PDO prepared statements using parameter binding.

2. **Remove Hardcoded Credentials**: Move database credentials to environment variables or a separate configuration file outside web root.

3. **Implement CSRF Protection**: Add CSRF tokens to all forms and validate them on submission.

4. **Fix File Upload Security**: 
   - Validate file extensions against a whitelist
   - Verify MIME types
   - Generate random filenames
   - Store uploads outside web root or restrict access

5. **Implement Output Encoding**: Use `htmlspecialchars()` when outputting user-controlled data.

### Medium Priority

1. **Add Authentication Checks**: Ensure all endpoints verify user authentication.
2. **Implement Proper Error Handling**: Remove error suppression and implement proper logging.
3. **Add Input Validation**: Validate all input types (numeric, date, email, etc.).

### Low Priority

1. **Update Deprecated Functions**: Replace `addslashes()` with proper prepared statements.
2. **Implement Secure Session Management**: Add session regeneration and secure flags.

---

## Appendix: Vulnerable Code Patterns

### Pattern 1: Direct SQL Interpolation
```php
// VULNERABLE
$sql="SELECT * FROM table WHERE id=$user_input";

// SECURE
$stmt = $dbh->prepare("SELECT * FROM table WHERE id=:id");
$stmt->execute([':id' => $user_input]);
```

### Pattern 2: Unescaped Output
```php
// VULNERABLE
echo "<div>$user_input</div>";

// SECURE
echo "<div>" . htmlspecialchars($user_input, ENT_QUOTES, 'UTF-8') . "</div>";
```

### Pattern 3: Missing CSRF Protection
```php
// VULNERABLE
if ($_POST['action'] == 'delete') {
    deleteRecord($_POST['id']);
}

// SECURE
if ($_POST['action'] == 'delete' && validateCsrfToken($_POST['csrf_token'])) {
    deleteRecord($_POST['id']);
}
```

---

*End of Report*
