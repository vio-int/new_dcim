-- PoC Tables for DCIM Revival
-- These tables are used by the Node.js PoC backend

-- Cabinets table (simplified for PoC)
CREATE TABLE IF NOT EXISTS cabinets (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  location VARCHAR(100) NOT NULL,
  capacity INT NOT NULL DEFAULT 42,
  current_load INT NOT NULL DEFAULT 0,
  status ENUM('active', 'maintenance', 'offline') DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Power metrics table for sensor data
CREATE TABLE IF NOT EXISTS power_metrics (
  id INT AUTO_INCREMENT PRIMARY KEY,
  cabinet_id INT NOT NULL,
  power_consumption FLOAT NOT NULL,
  voltage FLOAT NOT NULL DEFAULT 220.0,
  current FLOAT NOT NULL,
  temperature FLOAT NOT NULL,
  timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (cabinet_id) REFERENCES cabinets(id) ON DELETE CASCADE,
  INDEX idx_cabinet_time (cabinet_id, timestamp)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert sample cabinets
INSERT INTO cabinets (name, location, capacity, current_load, status) VALUES
('Rack-A01', 'Row A, Position 1', 42, 28, 'active'),
('Rack-A02', 'Row A, Position 2', 42, 35, 'active'),
('Rack-A03', 'Row A, Position 3', 42, 15, 'active'),
('Rack-B01', 'Row B, Position 1', 42, 40, 'active'),
('Rack-B02', 'Row B, Position 2', 42, 22, 'maintenance'),
('Rack-B03', 'Row B, Position 3', 42, 8, 'offline'),
('Rack-C01', 'Row C, Position 1', 42, 30, 'active'),
('Rack-C02', 'Row C, Position 2', 42, 25, 'active');

-- Insert sample power metrics (initial readings)
INSERT INTO power_metrics (cabinet_id, power_consumption, voltage, current, temperature) VALUES
(1, 2450.5, 220.1, 11.13, 24.5),
(2, 3120.0, 219.8, 14.20, 27.2),
(3, 1850.0, 220.5, 8.39, 22.1),
(4, 3850.0, 218.9, 17.59, 31.5),
(5, 2100.0, 220.2, 9.54, 25.0),
(6, 950.0, 221.0, 4.30, 20.5),
(7, 2750.0, 219.5, 12.53, 26.8),
(8, 2300.0, 220.3, 10.44, 24.2);