-- Location table for VIODCIM
-- This table stores data center location information

DROP TABLE IF EXISTS location;
CREATE TABLE location (
  id INT(11) NOT NULL AUTO_INCREMENT,
  name VARCHAR(255) NOT NULL,
  slug VARCHAR(255) DEFAULT NULL,
  status VARCHAR(50) DEFAULT 'Active',
  location_id VARCHAR(255) DEFAULT NULL,
  facility VARCHAR(255) DEFAULT NULL,
  asn VARCHAR(50) DEFAULT NULL,
  time_zone VARCHAR(50) DEFAULT NULL,
  description TEXT,
  physical_address TEXT,
  shipping_address TEXT,
  latitude VARCHAR(50) DEFAULT NULL,
  longitude VARCHAR(50) DEFAULT NULL,
  contact_name VARCHAR(255) DEFAULT NULL,
  contact_email VARCHAR(255) DEFAULT NULL,
  contact_no VARCHAR(50) DEFAULT NULL,
  tag VARCHAR(255) DEFAULT NULL,
  comment TEXT,
  created DATE DEFAULT NULL,
  last_updated DATE DEFAULT NULL,
  is_deleted CHAR(1) DEFAULT 'N',
  PRIMARY KEY (id),
  KEY idx_name (name),
  KEY idx_status (status),
  KEY idx_is_deleted (is_deleted)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
