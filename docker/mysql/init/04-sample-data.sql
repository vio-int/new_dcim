-- Sample Data for VIODCIM - Indonesian Data Centers
-- This script populates the database with realistic Indonesian data center data

-- ============================================
-- 1. LOCATIONS (Indonesian Data Centers)
-- ============================================

INSERT INTO location (name, slug, status, location_id, facility, asn, time_zone, description, physical_address, shipping_address, latitude, longitude, contact_name, contact_email, contact_no, tag, comment, created, is_deleted) VALUES
('Jakarta Intercon Plaza', 'jkt-intercon', 'Active', 'JKT-01', 'Jakarta Pusat', 'AS17995', 'WIB', 'Primary data center in Jakarta CBD', 'Jl. Teluk Betung No.10, Jakarta Pusat 10230', 'Jl. Teluk Betung No.10, Jakarta Pusat 10230', '-6.1865', '106.8340', 'Budi Santoso', 'budi.santoso@intercon.id', '+62-21-2358-9000', 'primary,jakarta,cbd', 'Main DC with 2MW power capacity', '2026-02-20', 'N'),
('Surabaya Cyber Building', 'sby-cyber', 'Active', 'SBY-01', 'Surabaya', 'AS17996', 'WIB', 'East Java regional data center', 'Jl. HR Muhammad No.168, Surabaya 60226', 'Jl. HR Muhammad No.168, Surabaya 60226', '-7.2905', '112.7688', 'Siti Rahayu', 'siti.rahayu@cyberdc.id', '+62-31-9900-1234', 'surabaya,east-java,regional', 'Regional hub for East Java region', '2026-02-20', 'N'),
('Bandung Techno Park', 'bdg-techno', 'Active', 'BDG-01', 'Bandung', 'AS17997', 'WIB', 'West Java data center facility', 'Jl. Gegerkalong Hilir No.47, Bandung 40152', 'Jl. Gegerkalong Hilir No.47, Bandung 40152', '-6.8733', '107.5860', 'Ahmad Wijaya', 'ahmad.wijaya@technopark.id', '+62-22-520-1234', 'bandung,west-java,tech', 'University district location', '2026-02-20', 'N'),
('Bali Nusa Dua', 'bali-nusadua', 'Active', 'BALI-01', 'Badung', 'AS17998', 'WITA', 'Bali disaster recovery site', 'Kawasan ITDC Lot N3, Nusa Dua 80363', 'Kawasan ITDC Lot N3, Nusa Dua 80363', '-8.8000', '115.2333', 'Made Sudarta', 'made.sudarta@balidc.id', '+62-361-300-1234', 'bali,dr-site,tourism', 'Disaster recovery and backup facility', '2026-02-20', 'N'),
('Medan Polonia', 'mdn-polonia', 'Maintenance', 'MDN-01', 'Medan', 'AS17999', 'WIB', 'North Sumatra regional DC', 'Jl. Imam Bonjol No.15, Medan 20112', 'Jl. Imam Bonjol No.15, Medan 20112', '3.5833', '98.6667', 'Dewi Kusuma', 'dewi.kusuma@poloniadc.id', '+62-61-451-1234', 'medan,north-sumatra,regional', 'Under maintenance - UPS upgrade', '2026-02-20', 'N'),
('Makassar Panakkukang', 'mks-panakkukang', 'Active', 'MKS-01', 'Makassar', 'AS18000', 'WITA', 'Sulawesi regional data center', 'Jl. Boulevard No.88, Makassar 90231', 'Jl. Boulevard No.88, Makassar 90231', '-5.1333', '119.4167', 'Abdul Rahman', 'abdul.rahman@mksdc.id', '+62-411-400-1234', 'makassar,sulawesi,regional', 'Eastern Indonesia hub', '2026-02-20', 'N');

-- ============================================
-- 2. DATA CENTERS (fac_DataCenter)
-- ============================================

INSERT INTO fac_DataCenter (DataCenterID, Name, SquareFootage, DeliveryAddress, Administrator, AdminPhone, AdminEmail, DrawingFileName, EntryLogging, MaxKW, Container, MapX, MapY) VALUES
(1, 'Jakarta Intercon Plaza DC', 50000, 'Jl. Teluk Betung No.10, Jakarta Pusat 10230', 'Budi Santoso', '+62-21-2358-9000', 'budi.santoso@intercon.id', 'jakarta_dc_floorplan.png', 1, 2000, 0, 0, 0),
(2, 'Surabaya Cyber Building DC', 35000, 'Jl. HR Muhammad No.168, Surabaya 60226', 'Siti Rahayu', '+62-31-9900-1234', 'siti.rahayu@cyberdc.id', 'surabaya_dc_floorplan.png', 1, 1500, 0, 0, 0),
(3, 'Bandung Techno Park DC', 25000, 'Jl. Gegerkalong Hilir No.47, Bandung 40152', 'Ahmad Wijaya', '+62-22-520-1234', 'ahmad.wijaya@technopark.id', 'bandung_dc_floorplan.png', 1, 1000, 0, 0, 0),
(4, 'Bali Nusa Dua DR Site', 20000, 'Kawasan ITDC Lot N3, Nusa Dua 80363', 'Made Sudarta', '+62-361-300-1234', 'made.sudarta@balidc.id', 'bali_dc_floorplan.png', 1, 800, 0, 0, 0);

-- ============================================
-- 3. ZONES
-- ============================================

INSERT INTO fac_Zone (ZoneID, DataCenterID, Description, MapX1, MapX2, MapY1, MapY2) VALUES
(1, 1, 'Ground Floor - Power & Cooling', 0, 100, 0, 100),
(2, 1, '1st Floor - Production Racks', 0, 100, 100, 200),
(3, 1, '2nd Floor - Storage & Network', 0, 100, 200, 300),
(4, 2, 'Ground Floor - Main Hall', 0, 100, 0, 100),
(5, 2, '1st Floor - Expansion Zone', 0, 100, 100, 200),
(6, 3, 'Single Floor DC', 0, 100, 0, 100),
(7, 4, 'DR Site Main Floor', 0, 100, 0, 100);

-- ============================================
-- 4. CABINET ROWS
-- ============================================

INSERT INTO fac_CabRow (CabRowID, Name, DataCenterID, ZoneID) VALUES
(1, 'Row A - Compute', 1, 2),
(2, 'Row B - Compute', 1, 2),
(3, 'Row C - Storage', 1, 2),
(4, 'Row D - Network', 1, 2),
(5, 'Row E - Power/Cooling', 1, 1),
(6, 'Row F - Legacy', 1, 3),
(7, 'Row A - Jakarta', 2, 4),
(8, 'Row B - Jakarta', 2, 4),
(9, 'Row A - Bandung', 3, 6),
(10, 'Row A - Bali DR', 4, 7);

-- ============================================
-- 5. CABINETS/RACKS
-- ============================================

INSERT INTO fac_Cabinet (CabinetID, DataCenterID, Location, LocationSortable, AssignedTo, ZoneID, CabRowID, CabinetHeight, Model, Keylock, MaxKW, MaxWeight, InstallationDate, MapX1, MapX2, FrontEdge, MapY1, MapY2, Notes, U1Position) VALUES
-- Jakarta Row A (Compute)
(1, 1, 'A01', 'A01', 1, 2, 1, 42, 'APC NetShelter SX 42U', 'A01-KEY', 15, 1500, '2024-01-15', 10, 20, 'Top', 110, 120, 'Production compute rack', 'Default'),
(2, 1, 'A02', 'A02', 1, 2, 1, 42, 'APC NetShelter SX 42U', 'A02-KEY', 15, 1500, '2024-01-15', 20, 30, 'Top', 110, 120, 'Production compute rack', 'Default'),
(3, 1, 'A03', 'A03', 1, 2, 1, 42, 'APC NetShelter SX 42U', 'A03-KEY', 15, 1500, '2024-01-15', 30, 40, 'Top', 110, 120, 'Production compute rack', 'Default'),
(4, 1, 'A04', 'A04', 1, 2, 1, 42, 'APC NetShelter SX 42U', 'A04-KEY', 15, 1500, '2024-01-15', 40, 50, 'Top', 110, 120, 'Production compute rack', 'Default'),
(5, 1, 'A05', 'A05', 1, 2, 1, 42, 'APC NetShelter SX 42U', 'A05-KEY', 15, 1500, '2024-01-15', 50, 60, 'Top', 110, 120, 'Production compute rack', 'Default'),

-- Jakarta Row B (Compute)
(6, 1, 'B01', 'B01', 1, 2, 2, 42, 'APC NetShelter SX 42U', 'B01-KEY', 15, 1500, '2024-01-15', 10, 20, 'Top', 130, 140, 'Production compute rack', 'Default'),
(7, 1, 'B02', 'B02', 1, 2, 2, 42, 'APC NetShelter SX 42U', 'B02-KEY', 15, 1500, '2024-01-15', 20, 30, 'Top', 130, 140, 'Production compute rack', 'Default'),
(8, 1, 'B03', 'B03', 1, 2, 2, 42, 'APC NetShelter SX 42U', 'B03-KEY', 15, 1500, '2024-01-15', 30, 40, 'Top', 130, 140, 'Production compute rack', 'Default'),

-- Jakarta Row C (Storage)
(9, 1, 'C01', 'C01', 1, 2, 3, 48, 'Dell 48U Enterprise Rack', 'C01-KEY', 20, 2000, '2024-02-01', 10, 20, 'Top', 150, 160, 'High-density storage rack', 'Default'),
(10, 1, 'C02', 'C02', 1, 2, 3, 48, 'Dell 48U Enterprise Rack', 'C02-KEY', 20, 2000, '2024-02-01', 20, 30, 'Top', 150, 160, 'High-density storage rack', 'Default'),

-- Jakarta Row D (Network)
(11, 1, 'D01', 'D01', 1, 2, 4, 42, 'APC NetShelter SX 42U', 'D01-KEY', 10, 1000, '2024-01-20', 10, 20, 'Top', 170, 180, 'Core network rack', 'Default'),
(12, 1, 'D02', 'D02', 1, 2, 4, 42, 'APC NetShelter SX 42U', 'D02-KEY', 10, 1000, '2024-01-20', 20, 30, 'Top', 170, 180, 'Edge network rack', 'Default'),

-- Surabaya
(13, 2, 'A01', 'A01', 1, 4, 7, 42, 'APC NetShelter SX 42U', 'S-A01-KEY', 12, 1200, '2024-03-01', 10, 20, 'Top', 10, 20, 'Surabaya main rack', 'Default'),
(14, 2, 'A02', 'A02', 1, 4, 7, 42, 'APC NetShelter SX 42U', 'S-A02-KEY', 12, 1200, '2024-03-01', 20, 30, 'Top', 10, 20, 'Surabaya compute rack', 'Default'),
(15, 2, 'B01', 'B01', 1, 4, 8, 42, 'APC NetShelter SX 42U', 'S-B01-KEY', 12, 1200, '2024-03-01', 10, 20, 'Top', 30, 40, 'Surabaya storage rack', 'Default'),

-- Bandung
(16, 3, 'A01', 'A01', 1, 6, 9, 42, 'APC NetShelter SX 42U', 'B-A01-KEY', 10, 1000, '2024-04-01', 10, 20, 'Top', 10, 20, 'Bandung main rack', 'Default'),
(17, 3, 'A02', 'A02', 1, 6, 9, 42, 'APC NetShelter SX 42U', 'B-A02-KEY', 10, 1000, '2024-04-01', 20, 30, 'Top', 10, 20, 'Bandung secondary rack', 'Default'),

-- Bali DR
(18, 4, 'DR01', 'DR01', 1, 7, 10, 42, 'APC NetShelter SX 42U', 'DR-01-KEY', 8, 800, '2024-05-01', 10, 20, 'Top', 10, 20, 'Bali DR primary rack', 'Default');

-- ============================================
-- 6. MANUFACTURERS
-- ============================================

INSERT INTO fac_Manufacturer (ManufacturerID, Name) VALUES
(1, 'Dell Technologies'),
(2, 'HPE (Hewlett Packard Enterprise)'),
(3, 'Cisco Systems'),
(4, 'Juniper Networks'),
(5, 'Arista Networks'),
(6, 'Huawei'),
(7, 'Lenovo'),
(8, 'Supermicro'),
(9, 'APC by Schneider Electric'),
(10, 'Huawei'),
(11, 'Fortinet'),
(12, 'Palo Alto Networks'),
(13, 'F5 Networks'),
(14, 'NetApp'),
(15, 'Pure Storage'),
(16, 'Intel'),
(17, 'AMD'),
(18, 'NVIDIA'),
(19, 'Seagate'),
(20, 'Western Digital');

-- ============================================
-- 7. DEVICE TEMPLATES
-- ============================================

INSERT INTO fac_DeviceTemplate (TemplateID, ManufacturerID, Model, Height, Weight, Wattage, DeviceType, PSCount, NumPorts, Notes) VALUES
-- Servers
(1, 1, 'PowerEdge R750', 2, 35, 750, 'Server', 2, 4, 'Dell 2U rack server - 3rd Gen Intel Xeon'),
(2, 1, 'PowerEdge R650', 1, 25, 500, 'Server', 2, 4, 'Dell 1U rack server - 3rd Gen Intel Xeon'),
(3, 2, 'ProLiant DL380 Gen10', 2, 32, 700, 'Server', 2, 4, 'HPE 2U rack server'),
(4, 2, 'ProLiant DL360 Gen10', 1, 22, 450, 'Server', 2, 4, 'HPE 1U rack server'),
(5, 8, 'SuperServer 2029U', 2, 30, 650, 'Server', 2, 4, 'Supermicro 2U server'),
(6, 7, 'ThinkSystem SR650', 2, 33, 700, 'Server', 2, 4, 'Lenovo 2U rack server'),

-- Storage
(7, 14, 'FAS2750', 4, 80, 1200, 'Storage Array', 2, 4, 'NetApp storage system'),
(8, 15, 'FlashArray//X50', 3, 60, 900, 'Storage Array', 2, 4, 'Pure Storage all-flash array'),
(9, 1, 'PowerVault ME4024', 2, 40, 500, 'Storage Array', 2, 4, 'Dell storage array'),

-- Network - Core/Aggregation
(10, 3, 'Nexus 93180YC-FX', 1, 18, 400, 'Switch', 2, 0, 'Cisco Nexus 9K switch - 48p 10/25G'),
(11, 3, 'Catalyst 9500-40X', 1, 15, 350, 'Switch', 2, 0, 'Cisco Catalyst core switch'),
(12, 5, 'DCS-7280SR2-48YC6', 1, 16, 380, 'Switch', 2, 0, 'Arista 7280R switch - 48p 25G'),
(13, 4, 'QFX5120-48Y', 1, 15, 360, 'Switch', 2, 0, 'Juniper QFX switch - 48p 25G'),

-- Network - Access
(14, 3, 'Catalyst 9300-48P', 1, 12, 250, 'Switch', 1, 0, 'Cisco Catalyst access switch'),
(15, 6, 'CloudEngine 6881-48S6CQ', 1, 14, 280, 'Switch', 2, 0, 'Huawei data center switch'),

-- Security
(16, 11, 'FortiGate 3600E', 2, 20, 450, 'Firewall', 2, 8, 'Fortinet next-gen firewall'),
(17, 12, 'PA-3260', 2, 22, 500, 'Firewall', 2, 8, 'Palo Alto Networks firewall'),

-- Load Balancer
(18, 13, 'BIG-IP i5800', 2, 24, 550, 'Load Balancer', 2, 8, 'F5 Networks load balancer'),

-- Power Distribution
(19, 9, 'APC Switched Rack PDU AP8941', 0, 8, 0, 'PDU', 0, 0, 'APC metered/switched PDU - 20A'),
(20, 9, 'APC Metered Rack PDU AP8868', 0, 7, 0, 'PDU', 0, 0, 'APC metered PDU - 16A'),

-- UPS
(21, 9, 'Smart-UPS SRT 5000VA', 4, 85, 0, 'UPS', 0, 0, 'APC online UPS - 5kVA'),
(22, 9, 'Symmetra PX 20kVA', 12, 300, 0, 'UPS', 0, 0, 'APC modular UPS - 20kVA');

-- ============================================
-- 8. DEPARTMENTS
-- ============================================

INSERT INTO fac_Department (DeptID, Name, ExecSponsor, Classification, DeptColor) VALUES
(1, 'IT Infrastructure', 'Budi Santoso', 'Technical', '#0066CC'),
(2, 'Network Operations', 'Ahmad Wijaya', 'Technical', '#009900'),
(3, 'Security Team', 'Siti Rahayu', 'Security', '#CC0000'),
(4, 'Database Administration', 'Dewi Kusuma', 'Technical', '#9900CC'),
(5, 'Application Support', 'Made Sudarta', 'Technical', '#FF6600'),
(6, 'Facilities Management', 'Abdul Rahman', 'Operations', '#666666');

-- ============================================
-- 9. PEOPLE/USERS
-- ============================================

INSERT INTO fac_People (PersonID, FirstName, LastName, Phone, Email, AdminOwnDevices, ReadAccess, WriteAccess, DeleteAccess, ContactAdmin, SiteAdmin, Disabled, DepartmentID) VALUES
(1, 'Budi', 'Santoso', '+62-21-2358-9001', 'budi.santoso@intercon.id', 1, 1, 1, 1, 1, 1, 0, 1),
(2, 'Siti', 'Rahayu', '+62-31-9900-1235', 'siti.rahayu@cyberdc.id', 1, 1, 1, 0, 0, 1, 0, 3),
(3, 'Ahmad', 'Wijaya', '+62-22-520-1235', 'ahmad.wijaya@technopark.id', 1, 1, 1, 0, 0, 1, 0, 2),
(4, 'Made', 'Sudarta', '+62-361-300-1235', 'made.sudarta@balidc.id', 1, 1, 1, 0, 0, 1, 0, 5),
(5, 'Dewi', 'Kusuma', '+62-61-451-1235', 'dewi.kusuma@poloniadc.id', 1, 1, 1, 0, 0, 0, 0, 4),
(6, 'Abdul', 'Rahman', '+62-411-400-1235', 'abdul.rahman@mksdc.id', 1, 1, 1, 0, 0, 1, 0, 6),
(7, 'Rini', 'Susanti', '+62-21-2358-9002', 'rini.susanti@intercon.id', 0, 1, 1, 0, 0, 0, 0, 1),
(8, 'Agus', 'Pratama', '+62-21-2358-9003', 'agus.pratama@intercon.id', 0, 1, 0, 0, 0, 0, 0, 2);

-- ============================================
-- 10. DEVICES (Servers, Network, Storage)
-- ============================================

INSERT INTO fac_Device (DeviceID, Label, SerialNo, AssetTag, PrimaryIP, SNMPVersion, SNMPCommunity, ESX, Owner, EscalationTimeID, EscalationID, PrimaryContact, Cabinet, Position, Height, Ports, FirstPortNum, TemplateID, NominalWatts, PowerSupplyCount, DeviceType, ChassisSlots, RearChassisSlots, ParentDevice, MfgDate, InstallDate, WarrantyCo, WarrantyExpire, Notes, Reservation, Rights, DepartmentID) VALUES

-- Jakarta A01 - Compute Servers
(1, 'JKT-APP-01', 'SN123456789', 'ASSET-001', '10.1.1.11', '2c', 'public', 0, 1, 0, 0, 1, 1, 1, 2, 4, 1, 1, 750, 2, 'Server', 0, 0, 0, '2024-01-15', '2024-01-20', 'Dell', '2027-01-20', 'Application server - Jakarta primary', 0, 'Device', 1),
(2, 'JKT-APP-02', 'SN123456790', 'ASSET-002', '10.1.1.12', '2c', 'public', 0, 1, 0, 0, 1, 1, 3, 2, 4, 1, 1, 750, 2, 'Server', 0, 0, 0, '2024-01-15', '2024-01-20', 'Dell', '2027-01-20', 'Application server - Jakarta primary', 0, 'Device', 1),
(3, 'JKT-WEB-01', 'SN123456791', 'ASSET-003', '10.1.1.13', '2c', 'public', 0, 1, 0, 0, 7, 1, 5, 1, 4, 1, 2, 500, 2, 'Server', 0, 0, 0, '2024-01-15', '2024-01-20', 'Dell', '2027-01-20', 'Web server - Jakarta', 0, 'Device', 1),
(4, 'JKT-WEB-02', 'SN123456792', 'ASSET-004', '10.1.1.14', '2c', 'public', 0, 1, 0, 0, 7, 1, 6, 1, 4, 1, 2, 500, 2, 'Server', 0, 0, 0, '2024-01-15', '2024-01-20', 'Dell', '2027-01-20', 'Web server - Jakarta', 0, 'Device', 1),

-- Jakarta A02 - More Compute
(5, 'JKT-DB-01', 'SN123456793', 'ASSET-005', '10.1.1.21', '2c', 'public', 0, 1, 0, 0, 5, 2, 1, 2, 4, 1, 3, 700, 2, 'Server', 0, 0, 0, '2024-01-15', '2024-01-20', 'HPE', '2027-01-20', 'Database server - Primary', 0, 'Device', 4),
(6, 'JKT-DB-02', 'SN123456794', 'ASSET-006', '10.1.1.22', '2c', 'public', 0, 1, 0, 0, 5, 2, 3, 2, 4, 1, 3, 700, 2, 'Server', 0, 0, 0, '2024-01-15', '2024-01-20', 'HPE', '2027-01-20', 'Database server - Secondary', 0, 'Device', 4),

-- Jakarta C01 - Storage
(7, 'JKT-STOR-01', 'SN987654321', 'ASSET-007', '10.1.1.31', '2c', 'public', 0, 1, 0, 0, 1, 9, 1, 4, 4, 1, 7, 1200, 2, 'Storage Array', 0, 0, 0, '2024-02-01', '2024-02-05', 'NetApp', '2027-02-05', 'Primary storage array - Jakarta', 0, 'Device', 1),
(8, 'JKT-STOR-02', 'SN987654322', 'ASSET-008', '10.1.1.32', '2c', 'public', 0, 1, 0, 0, 1, 9, 5, 4, 4, 1, 8, 900, 2, 'Storage Array', 0, 0, 0, '2024-02-01', '2024-02-05', 'Pure Storage', '2027-02-05', 'Flash storage array - Jakarta', 0, 'Device', 1),

-- Jakarta D01 - Network Core
(9, 'JKT-CORE-SW01', 'SN444555666', 'ASSET-009', '10.1.1.254', '2c', 'public', 0, 1, 0, 0, 3, 11, 1, 1, 0, 0, 10, 400, 2, 'Switch', 0, 0, 0, '2024-01-20', '2024-01-25', 'Cisco', '2027-01-25', 'Core switch - Jakarta DC', 0, 'Device', 2),
(10, 'JKT-EDGE-SW01', 'SN444555667', 'ASSET-010', '10.1.1.253', '2c', 'public', 0, 1, 0, 0, 3, 11, 2, 1, 0, 0, 12, 380, 2, 'Switch', 0, 0, 0, '2024-01-20', '2024-01-25', 'Arista', '2027-01-25', 'Edge switch - Jakarta DC', 0, 'Device', 2),
(11, 'JKT-FW-01', 'SN777888999', 'ASSET-011', '10.1.1.1', '2c', 'public', 0, 1, 0, 0, 2, 11, 3, 2, 8, 1, 16, 450, 2, 'Firewall', 0, 0, 0, '2024-01-20', '2024-01-25', 'Fortinet', '2027-01-25', 'Primary firewall - Jakarta', 0, 'Device', 3),
(12, 'JKT-LB-01', 'SN111222333', 'ASSET-012', '10.1.1.2', '2c', 'public', 0, 1, 0, 0, 2, 11, 5, 2, 8, 1, 18, 550, 2, 'Load Balancer', 0, 0, 0, '2024-01-20', '2024-01-25', 'F5', '2027-01-25', 'Load balancer - Jakarta', 0, 'Device', 1),

-- Surabaya
(13, 'SBY-APP-01', 'SN555666777', 'ASSET-013', '10.2.1.11', '2c', 'public', 0, 2, 0, 0, 2, 13, 1, 2, 4, 1, 1, 750, 2, 'Server', 0, 0, 0, '2024-03-01', '2024-03-05', 'Dell', '2027-03-05', 'Application server - Surabaya', 0, 'Device', 1),
(14, 'SBY-DB-01', 'SN555666778', 'ASSET-014', '10.2.1.21', '2c', 'public', 0, 2, 0, 0, 2, 13, 3, 2, 4, 1, 3, 700, 2, 'Server', 0, 0, 0, '2024-03-01', '2024-03-05', 'HPE', '2027-03-05', 'Database server - Surabaya', 0, 'Device', 4),
(15, 'SBY-SW-01', 'SN888999000', 'ASSET-015', '10.2.1.254', '2c', 'public', 0, 2, 0, 0, 2, 14, 1, 1, 0, 0, 14, 250, 1, 'Switch', 0, 0, 0, '2024-03-01', '2024-03-05', 'Cisco', '2027-03-05', 'Core switch - Surabaya', 0, 'Device', 2),

-- Bandung
(16, 'BDG-APP-01', 'SN999000111', 'ASSET-016', '10.3.1.11', '2c', 'public', 0, 3, 0, 0, 3, 16, 1, 2, 4, 1, 1, 750, 2, 'Server', 0, 0, 0, '2024-04-01', '2024-04-05', 'Dell', '2027-04-05', 'Application server - Bandung', 0, 'Device', 1),
(17, 'BDG-SW-01', 'SN222333444', 'ASSET-017', '10.3.1.254', '2c', 'public', 0, 3, 0, 0, 3, 16, 3, 1, 0, 0, 15, 280, 2, 'Switch', 0, 0, 0, '2024-04-01', '2024-04-05', 'Huawei', '2027-04-05', 'Core switch - Bandung', 0, 'Device', 2),

-- Bali DR
(18, 'BALI-DR-01', 'SN333444555', 'ASSET-018', '10.4.1.11', '2c', 'public', 0, 4, 0, 0, 4, 18, 1, 2, 4, 1, 1, 750, 2, 'Server', 0, 0, 0, '2024-05-01', '2024-05-05', 'Dell', '2027-05-05', 'DR server - Bali', 0, 'Device', 1),
(19, 'BALI-DR-02', 'SN333444556', 'ASSET-019', '10.4.1.12', '2c', 'public', 0, 4, 0, 0, 4, 18, 3, 2, 4, 1, 3, 700, 2, 'Server', 0, 0, 0, '2024-05-01', '2024-05-05', 'HPE', '2027-05-05', 'DR database - Bali', 0, 'Device', 4);

-- ============================================
-- 11. TAGS
-- ============================================

INSERT INTO fac_Tags (TagID, TagName) VALUES
(1, 'Production'),
(2, 'Development'),
(3, 'DR-Site'),
(4, 'Jakarta'),
(5, 'Surabaya'),
(6, 'Bandung'),
(7, 'Bali'),
(8, 'Database'),
(9, 'Web-Server'),
(10, 'Network'),
(11, 'Storage'),
(12, 'Security'),
(13, 'Core'),
(14, 'Edge');

-- ============================================
-- 12. DEVICE TAGS ASSIGNMENTS
-- ============================================

INSERT INTO fac_DeviceTags (DeviceID, TagID) VALUES
(1, 1), (1, 4), (1, 9),
(2, 1), (2, 4), (2, 9),
(3, 1), (3, 4), (3, 9),
(4, 1), (4, 4), (4, 9),
(5, 1), (5, 4), (5, 8),
(6, 1), (6, 4), (6, 8),
(7, 1), (7, 4), (7, 11),
(8, 1), (8, 4), (8, 11),
(9, 1), (9, 4), (9, 10), (9, 13),
(10, 1), (10, 4), (10, 10), (10, 14),
(11, 1), (11, 4), (11, 12),
(12, 1), (12, 4),
(13, 1), (13, 5),
(14, 1), (14, 5), (14, 8),
(15, 1), (15, 5), (15, 10),
(16, 1), (16, 6),
(17, 1), (17, 6), (17, 10),
(18, 3), (18, 7),
(19, 3), (19, 7), (19, 8);

-- ============================================
-- 13. POWER PANELS
-- ============================================

INSERT INTO fac_PowerPanel (PanelID, DataCenterID, PanelLabel, NumberOfCircuits, MaxCapacity, PanelVoltage) VALUES
(1, 1, 'PP-JKT-A', 42, 100, 380),
(2, 1, 'PP-JKT-B', 42, 100, 380),
(3, 2, 'PP-SBY-01', 24, 60, 380),
(4, 3, 'PP-BDG-01', 24, 40, 380),
(5, 4, 'PP-BALI-01', 16, 30, 380);

-- ============================================
-- 14. POWER DISTRIBUTION (PDUs)
-- ============================================

INSERT INTO fac_PowerDistribution (PDUID, Label, CabinetID, PanelID, BreakerSize, InputAmperage, FacID, MapX1, MapX2, MapY1, MapY2, Notes) VALUES
(1, 'PDU-A01-Left', 1, 1, 20, 16, 1, 10, 12, 110, 120, 'Left side PDU'),
(2, 'PDU-A01-Right', 1, 2, 20, 16, 1, 18, 20, 110, 120, 'Right side PDU'),
(3, 'PDU-A02-Left', 2, 1, 20, 16, 1, 20, 22, 110, 120, 'Left side PDU'),
(4, 'PDU-A02-Right', 2, 2, 20, 16, 1, 28, 30, 110, 120, 'Right side PDU'),
(5, 'PDU-C01-Left', 9, 1, 30, 24, 1, 10, 12, 150, 160, 'High-capacity PDU for storage'),
(6, 'PDU-SBY-01', 13, 3, 20, 16, 2, 10, 12, 10, 20, 'Surabaya main PDU');

-- ============================================
-- 15. COLORS FOR CABLING
-- ============================================

INSERT INTO fac_ColorCoding (ColorID, Name, DefaultNote) VALUES
(1, 'Blue', 'Network - Production'),
(2, 'Yellow', 'Network - Management'),
(3, 'Red', 'Power - Primary'),
(4, 'Orange', 'Power - Secondary'),
(5, 'Green', 'Storage Network'),
(6, 'Purple', 'Out of Band'),
(7, 'White', 'Cross Connect'),
(8, 'Gray', 'Future Use');

-- ============================================
-- 16. MEDIA TYPES
-- ============================================

INSERT INTO fac_MediaTypes (MediaID, MediaType, ColorID) VALUES
(1, 'Cat6 Ethernet', 1),
(2, 'Fiber OM4', 5),
(3, 'Fiber Single Mode', 1),
(4, 'Power C13-C14', 3),
(5, 'Power C19-C20', 4),
(6, 'Serial Console', 2);
