-- Create missing VIODCIM tables (without fac_ prefix)
-- These tables are required by the VIODCIM PHP code

-- Room table
DROP TABLE IF EXISTS room;
CREATE TABLE room (
  id INT(11) NOT NULL AUTO_INCREMENT,
  name VARCHAR(255) NOT NULL,
  room_no VARCHAR(50) DEFAULT NULL,
  location_id INT(11) DEFAULT NULL,
  rows INT(11) DEFAULT 0,
  columns INT(11) DEFAULT 0,
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Rack table
DROP TABLE IF EXISTS rack;
CREATE TABLE rack (
  id INT(11) NOT NULL AUTO_INCREMENT,
  name VARCHAR(255) NOT NULL,
  site_id INT(11) DEFAULT NULL,
  room_id INT(11) DEFAULT NULL,
  group_no VARCHAR(50) DEFAULT NULL,
  row_position VARCHAR(50) DEFAULT NULL,
  facility VARCHAR(255) DEFAULT NULL,
  serial_no VARCHAR(100) DEFAULT NULL,
  is_descending CHAR(1) DEFAULT 'N',
  type VARCHAR(50) DEFAULT NULL,
  width VARCHAR(50) DEFAULT NULL,
  height VARCHAR(50) DEFAULT NULL,
  position VARCHAR(50) DEFAULT NULL,
  model VARCHAR(100) DEFAULT NULL,
  key_info VARCHAR(255) DEFAULT NULL,
  max_kw VARCHAR(50) DEFAULT NULL,
  max_weight VARCHAR(50) DEFAULT NULL,
  installed_at DATE DEFAULT NULL,
  assign_to INT(11) DEFAULT NULL,
  tag VARCHAR(255) DEFAULT NULL,
  comment TEXT,
  x1 INT(11) DEFAULT 0,
  x2 INT(11) DEFAULT 0,
  y1 INT(11) DEFAULT 0,
  y2 INT(11) DEFAULT 0,
  mapzoom INT(11) DEFAULT 100,
  created DATE DEFAULT NULL,
  last_updated DATE DEFAULT NULL,
  is_deleted CHAR(1) DEFAULT 'N',
  is_simulation CHAR(1) DEFAULT 'N',
  PRIMARY KEY (id),
  KEY idx_room_id (room_id),
  KEY idx_site_id (site_id),
  KEY idx_is_deleted (is_deleted)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Device table
DROP TABLE IF EXISTS device;
CREATE TABLE device (
  id INT(11) NOT NULL AUTO_INCREMENT,
  label VARCHAR(255) NOT NULL,
  serial_no VARCHAR(100) DEFAULT NULL,
  asset_tag VARCHAR(100) DEFAULT NULL,
  primary_ip VARCHAR(50) DEFAULT NULL,
  snmp_version VARCHAR(10) DEFAULT '2c',
  snmp_community VARCHAR(100) DEFAULT 'public',
  esx TINYINT(1) DEFAULT 0,
  owner INT(11) DEFAULT NULL,
  escalation_time_id INT(11) DEFAULT 0,
  escalation_id INT(11) DEFAULT 0,
  primary_contact INT(11) DEFAULT NULL,
  cabinet INT(11) DEFAULT NULL,
  position INT(11) DEFAULT NULL,
  height INT(11) DEFAULT 1,
  ports INT(11) DEFAULT 0,
  first_port_num INT(11) DEFAULT 1,
  template_id INT(11) DEFAULT NULL,
  nominal_watts INT(11) DEFAULT 0,
  power_supply_count INT(11) DEFAULT 1,
  device_type VARCHAR(50) DEFAULT 'Server',
  chassis_slots INT(11) DEFAULT 0,
  rear_chassis_slots INT(11) DEFAULT 0,
  parent_device INT(11) DEFAULT 0,
  mfg_date DATE DEFAULT NULL,
  install_date DATE DEFAULT NULL,
  warranty_co VARCHAR(100) DEFAULT NULL,
  warranty_expire DATE DEFAULT NULL,
  notes TEXT,
  reservation TINYINT(1) DEFAULT 0,
  rights VARCHAR(50) DEFAULT 'Device',
  department_id INT(11) DEFAULT NULL,
  created DATE DEFAULT NULL,
  last_updated DATE DEFAULT NULL,
  is_deleted CHAR(1) DEFAULT 'N',
  is_simulation CHAR(1) DEFAULT 'N',
  PRIMARY KEY (id),
  KEY idx_cabinet (cabinet),
  KEY idx_is_deleted (is_deleted)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Assets table
DROP TABLE IF EXISTS assets;
CREATE TABLE assets (
  id INT(11) NOT NULL AUTO_INCREMENT,
  name VARCHAR(255) NOT NULL,
  status VARCHAR(50) DEFAULT 'Active',
  department_id INT(11) DEFAULT NULL,
  label VARCHAR(100) DEFAULT NULL,
  serial_no VARCHAR(100) DEFAULT NULL,
  asset_tag VARCHAR(100) DEFAULT NULL,
  primary_ip VARCHAR(50) DEFAULT NULL,
  manufacture_date DATE DEFAULT NULL,
  install_date DATE DEFAULT NULL,
  company VARCHAR(100) DEFAULT NULL,
  expire_date DATE DEFAULT NULL,
  rack_id INT(11) DEFAULT NULL,
  device_id INT(11) DEFAULT NULL,
  height INT(11) DEFAULT 1,
  position INT(11) DEFAULT NULL,
  half_depth CHAR(1) DEFAULT 'N',
  back_side CHAR(1) DEFAULT 'N',
  data_ports INT(11) DEFAULT 0,
  watts INT(11) DEFAULT 0,
  weight INT(11) DEFAULT 0,
  power_connection VARCHAR(255) DEFAULT NULL,
  device_role VARCHAR(100) DEFAULT NULL,
  snmp_version VARCHAR(10) DEFAULT NULL,
  snmp_community VARCHAR(100) DEFAULT NULL,
  snmp_failure INT(11) DEFAULT 0,
  next_main_date DATE DEFAULT NULL,
  created DATE DEFAULT NULL,
  last_updated DATE DEFAULT NULL,
  is_deleted CHAR(1) DEFAULT 'N',
  is_simulation CHAR(1) DEFAULT 'N',
  PRIMARY KEY (id),
  KEY idx_rack_id (rack_id),
  KEY idx_is_deleted (is_deleted)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Manufacture table
DROP TABLE IF EXISTS manufacture;
CREATE TABLE manufacture (
  id INT(11) NOT NULL AUTO_INCREMENT,
  name VARCHAR(255) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- IPAM tables
DROP TABLE IF EXISTS ipam_prefix;
CREATE TABLE ipam_prefix (
  id INT(11) NOT NULL AUTO_INCREMENT,
  prefix VARCHAR(50) NOT NULL,
  vrf_id INT(11) DEFAULT NULL,
  site_id INT(11) DEFAULT NULL,
  vlan_id INT(11) DEFAULT NULL,
  role_id INT(11) DEFAULT NULL,
  status VARCHAR(50) DEFAULT 'Active',
  description TEXT,
  created DATE DEFAULT NULL,
  last_updated DATE DEFAULT NULL,
  is_deleted CHAR(1) DEFAULT 'N',
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS ipam_vlan;
CREATE TABLE ipam_vlan (
  id INT(11) NOT NULL AUTO_INCREMENT,
  vid INT(11) NOT NULL,
  name VARCHAR(100) DEFAULT NULL,
  site_id INT(11) DEFAULT NULL,
  status VARCHAR(50) DEFAULT 'Active',
  description TEXT,
  created DATE DEFAULT NULL,
  last_updated DATE DEFAULT NULL,
  is_deleted CHAR(1) DEFAULT 'N',
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Asset category table
DROP TABLE IF EXISTS asset_category;
CREATE TABLE asset_category (
  id INT(11) NOT NULL AUTO_INCREMENT,
  name VARCHAR(255) NOT NULL,
  description TEXT,
  created DATE DEFAULT NULL,
  last_updated DATE DEFAULT NULL,
  is_deleted CHAR(1) DEFAULT 'N',
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Asset status table
DROP TABLE IF EXISTS asset_status;
CREATE TABLE asset_status (
  id INT(11) NOT NULL AUTO_INCREMENT,
  status VARCHAR(100) NOT NULL,
  description TEXT,
  created DATE DEFAULT NULL,
  last_updated DATE DEFAULT NULL,
  is_deleted CHAR(1) DEFAULT 'N',
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
