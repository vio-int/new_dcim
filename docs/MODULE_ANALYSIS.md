# DCIM Module Analysis

## Core Architecture

### Class Structure
The application uses an object-oriented design with the following pattern:

```php
class EntityName {
    // Public properties
    var $PropertyName;
    
    // Constructor
    public function __construct($id=false) {
        if($id) { $this->ID = $id; }
        return $this;
    }
    
    // Sanitization method
    function MakeSafe() {
        $this->ID = intval($this->ID);
        $this->StringProperty = sanitize($this->StringProperty);
        // ... type casting and sanitization
    }
    
    // Database operations
    function Create() { }
    function Update() { }
    function Delete() { }
    static function Get($id) { }
}
```

### Key Classes Analyzed

## 1. Device Class (`classes/Device.class.php`)

**Purpose**: Manages all data center assets at the most granular level

**Key Properties**:
- `DeviceID`, `Label`, `SerialNo`, `AssetTag` - Basic identification
- `PrimaryIP`, `SNMPVersion`, `SNMPCommunity` - Network management
- `v3SecurityLevel`, `v3AuthProtocol`, `v3AuthPassphrase` - SNMPv3 security
- `Cabinet`, `Position`, `Height` - Physical location
- `TemplateID` - Device template reference
- `NominalWatts`, `PowerSupplyCount` - Power management
- `DeviceType` - Server, Appliance, Storage Array, Switch, Chassis, etc.
- `ParentDevice` - For chassis/blade relationships
- `Status` - Device lifecycle status
- `CustomValues` - Dynamic attributes

**Key Methods**:
- `MakeSafe()` - Comprehensive sanitization with validation arrays
- `Create()`, `Update()`, `Delete()` - CRUD operations
- `GetDevice()` - Factory method for retrieval
- `Search()` - Flexible search with loose/exact matching

**Notable Features**:
- Supports SNMPv1, v2c, and v3
- Hypervisor integration (ESX, ProxMox)
- API credentials storage for management interfaces
- Chassis slot management (front and rear)
- Device lifecycle tracking

## 2. Cabinet Class (`classes/Cabinet.class.php`)

**Purpose**: Represents racks/cabinets - the primary container for devices

**Key Properties**:
- `CabinetID`, `Location`, `LocationSortable` - Identification
- `DataCenterID`, `ZoneID`, `CabRowID` - Hierarchical location
- `CabinetHeight` - RU capacity
- `MaxKW`, `MaxWeight` - Capacity planning
- `MapX1`, `MapY1`, `MapX2`, `MapY2` - Floor map coordinates
- `FrontEdge` - Orientation (Top/Right/Left/Bottom)
- `U1Position` - RU numbering direction

**Key Methods**:
- `RowToObject()` - Static factory from database row
- `GetCabinet()` - Retrieve single cabinet
- `GetCabinets()` - List cabinets with optional filtering
- `CabinetOccupancy()` - Calculate space utilization
- `GetNextCabinet()` - Navigation helper

**Notable Features**:
- Supports 2-post, 4-post, and enclosed cabinets
- Visual floor map positioning
- Rights-based access control
- Cabinet row organization

## 3. DataCenter Class (`classes/DataCenter.class.php`)

**Purpose**: Top-level container for data center facilities

**Key Properties**:
- `DataCenterID`, `Name` - Identification
- `SquareFootage` - Physical size
- `DeliveryAddress` - Shipping location
- `Administrator` - Primary contact
- `MaxkW` - Power capacity
- `DrawingFileName` - Floor plan image
- `ContainerID` - Parent container (for nested facilities)
- `MapX`, `MapY` - Position on parent map

**Key Methods**:
- `RowToObject()` - Static factory
- `GetDataCenter()`, `GetDataCenterList()` - Retrieval
- `GetDCStatistics()` - Aggregated metrics
- `AddDCToMap()` - Visual map management

**Notable Features**:
- Hierarchical facility management (containers within containers)
- Floor plan visualization
- Statistics aggregation across all child cabinets

## 4. Supporting Classes (Overview)

### Asset Management
- `Asset.class.php` - IT asset tracking
- `AssetCategory.class.php` - Asset categorization
- `AssetSupplier.class.php` - Vendor management
- `AssetManage.class.php` - Asset lifecycle

### IPAM (IP Address Management)
- `IpamVLAN.class.php` - VLAN management
- `IpamPrefix.class.php` - IP prefix/subnet management
- `IpamIPAddress.class.php` - Individual IP addresses
- `IpamVRF.class.php` - VRF support
- `IPv4Allocation.class.php`, `IPv6Allocation.class.php` - IP assignment

### Power Management
- `PowerPanel.class.php` - Power distribution panels
- `PowerDistribution.class.php` - PDU/CDU management
- `PowerPorts.class.php` - Individual power connections
- `CDUTemplate.class.php` - CDU device templates

### Network
- `DevicePorts.class.php` - Network port management
- `InterfaceConn.class.php` - Network connections
- `ConnectionPath.class.php` - Path tracing between devices

### People & Access
- `People.class.php` - User management
- `Department.class.php` - Organizational structure
- `Projects.class.php` - Project/service catalog

## Database Layer Pattern

### Global Connection
```php
// db.inc.php establishes global $dbh PDO connection
global $dbh;
$dbh = new PDO($pdoconnect, $dbuser, $dbpass, $pdo_options);
```

### Query Pattern
```php
// Prepared statements (good)
$sth = $dbh->prepare($sql);
$sth->execute(array(':id' => $id));

// Direct queries (potential security risk)
$dbh->query("SELECT * FROM table WHERE id = $id");
```

### Sanitization
```php
// Global sanitize() function used throughout
function sanitize($input, $allow_html = false) {
    // HTML encoding and optional HTML stripping
}
```

## Frontend Architecture

### Page Structure
```
header.inc.php      → Navigation and common includes
[page].php          → Main content
footer (inline)     → Closing tags
```

### Key JavaScript Libraries
- jQuery (core dependency)
- jQuery UI (widgets and interactions)
- DataTables (grid displays)
- SweetAlert2 (dialogs)

### CSS Framework
- Custom CSS with Bootstrap-like classes
- Responsive design elements
- Print-specific stylesheets

## API Structure

### Location: `api/` folder
- `api/v1/` - REST API endpoints
- Uses same classes as web interface
- Returns JSON responses

### Authentication
- Supports Apache, LDAP, OAuth, SAML
- Configured in db.inc.php via AUTHENTICATION constant

## Identified Patterns & Conventions

1. **Naming**: Classes use PascalCase, files match class names
2. **Table Prefix**: All DB tables use `fac_` prefix
3. **ID Fields**: Primary keys named `ClassNameID`
4. **Safe Methods**: `MakeSafe()` for input, `MakeDisplay()` for output
5. **Static Factories**: `RowToObject()` for DB row conversion
6. **Global DB Handle**: `$dbh` used throughout
7. **Configuration**: Centralized in `config.inc.php` with Config class

## Potential Refactoring Areas

1. **Dependency Injection**: Replace global $dbh with injected dependencies
2. **ORM**: Consider Doctrine or Eloquent for database layer
3. **Validation**: Centralize validation logic (currently spread across MakeSafe methods)
4. **API Consistency**: Standardize API response formats
5. **Type Safety**: Add PHP 8+ type declarations
