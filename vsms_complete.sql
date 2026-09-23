-- =============================================
-- VSMS - Vehicle Service Management System
-- Database Schema (Completed / Full Working Version)
--
-- Fixes applied to your uploaded vsms.sql:
--   1. Added sample `services` rows (was empty) — including
--      TWO services on the same start_date so the "only 2
--      lifting machines per day" limit is visible immediately.
--   2. Added sample `invoices` rows (was empty) — so Billing
--      and the printable Receipt page have data to show.
--   3. Kept your `service_parts` table (not used by the current
--      4 modules yet, but left in place for future use).
--   4. Kept your existing admin hash. If login fails, just run
--      fix_password.php once after import — it will reset
--      admin@vsms.com / admin123 safely.
--
-- Note: the "2 lifting machines per day" rule is enforced in
-- the application layer (config/db.php -> MAX_LIFTS_PER_DAY),
-- not in this schema — no table change was needed for it.
-- =============================================

CREATE DATABASE IF NOT EXISTS vsms_db;
USE vsms_db;

-- Users Table
CREATE TABLE IF NOT EXISTS users (
  user_id    INT AUTO_INCREMENT PRIMARY KEY,
  name       VARCHAR(100) NOT NULL,
  email      VARCHAR(100) NOT NULL UNIQUE,
  password   VARCHAR(255) NOT NULL,
  role       ENUM('Admin','Manager','Mechanic') DEFAULT 'Mechanic',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Default admin user (password: admin123)
-- If this hash doesn't verify on your server, run fix_password.php once.
INSERT INTO users (name, email, password, role) VALUES
('Admin', 'admin@vsms.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin');

-- Customers Table
CREATE TABLE IF NOT EXISTS customers (
  customer_id INT AUTO_INCREMENT PRIMARY KEY,
  name        VARCHAR(100) NOT NULL,
  phone       VARCHAR(20),
  email       VARCHAR(100),
  address     TEXT,
  created_at  DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Vehicles Table
CREATE TABLE IF NOT EXISTS vehicles (
  vehicle_id      INT AUTO_INCREMENT PRIMARY KEY,
  customer_id     INT NOT NULL,
  make            VARCHAR(50) NOT NULL,
  model           VARCHAR(50) NOT NULL,
  year            YEAR,
  registration_no VARCHAR(30) NOT NULL UNIQUE,
  color           VARCHAR(30),
  mileage         INT DEFAULT 0,
  created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (customer_id) REFERENCES customers(customer_id) ON DELETE CASCADE
);

-- Mechanics Table
CREATE TABLE IF NOT EXISTS mechanics (
  mechanic_id     INT AUTO_INCREMENT PRIMARY KEY,
  name            VARCHAR(100) NOT NULL,
  phone           VARCHAR(20),
  specialization  VARCHAR(100),
  status          ENUM('Active','Inactive') DEFAULT 'Active',
  created_at      DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Services Table (start_date = the lifting-machine booking date)
CREATE TABLE IF NOT EXISTS services (
  service_id   INT AUTO_INCREMENT PRIMARY KEY,
  vehicle_id   INT NOT NULL,
  mechanic_id  INT,
  service_type VARCHAR(100) NOT NULL,
  description  TEXT,
  status       ENUM('Pending','In Progress','Completed','Cancelled') DEFAULT 'Pending',
  start_date   DATE,
  end_date     DATE,
  created_at   DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (vehicle_id)  REFERENCES vehicles(vehicle_id)  ON DELETE CASCADE,
  FOREIGN KEY (mechanic_id) REFERENCES mechanics(mechanic_id) ON DELETE SET NULL
);

-- Spare Parts (Inventory) Table
CREATE TABLE IF NOT EXISTS spare_parts (
  part_id         INT AUTO_INCREMENT PRIMARY KEY,
  part_name       VARCHAR(100) NOT NULL,
  part_number     VARCHAR(50),
  quantity        INT DEFAULT 0,
  unit_price      DECIMAL(10,2) DEFAULT 0.00,
  low_stock_alert INT DEFAULT 5,
  created_at      DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Service Parts (parts consumed by a service) — kept for future use
CREATE TABLE IF NOT EXISTS service_parts (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  service_id INT NOT NULL,
  part_id    INT NOT NULL,
  quantity   INT DEFAULT 1,
  FOREIGN KEY (service_id) REFERENCES services(service_id) ON DELETE CASCADE,
  FOREIGN KEY (part_id)    REFERENCES spare_parts(part_id) ON DELETE CASCADE
);

-- Invoices Table
CREATE TABLE IF NOT EXISTS invoices (
  invoice_id     INT AUTO_INCREMENT PRIMARY KEY,
  service_id     INT NOT NULL,
  labor_cost     DECIMAL(10,2) DEFAULT 0.00,
  parts_cost     DECIMAL(10,2) DEFAULT 0.00,
  total_amount   DECIMAL(10,2) DEFAULT 0.00,
  payment_status ENUM('Unpaid','Paid','Partial') DEFAULT 'Unpaid',
  payment_date   DATE,
  notes          TEXT,
  created_at     DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (service_id) REFERENCES services(service_id) ON DELETE CASCADE
);

-- =============================================
-- Sample Data
-- =============================================

INSERT INTO customers (name, phone, email, address) VALUES
('John Silva',      '0771234567', 'john@email.com',  '12 Main St, Colombo'),
('Amal Perera',     '0779876543', 'amal@email.com',  '45 Galle Rd, Galle'),
('Nimal Fernando',  '0761111222', 'nimal@email.com', '78 Kandy Rd, Kandy');

INSERT INTO mechanics (name, phone, specialization, status) VALUES
('Suresh Kumar',    '0712345678', 'Engine Repair',      'Active'),
('Ravi Bandara',    '0723456789', 'Body & Paint',       'Active'),
('Prasad Wijesiri', '0734567890', 'Electrical Systems', 'Active');

INSERT INTO vehicles (customer_id, make, model, year, registration_no, color, mileage) VALUES
(1, 'Toyota', 'Corolla', 2018, 'WP-1234', 'White',  45000),
(2, 'Honda',  'Civic',   2020, 'SP-5678', 'Black',  30000),
(3, 'Nissan', 'Sunny',   2016, 'KL-9012', 'Silver', 62000);

INSERT INTO spare_parts (part_name, part_number, quantity, unit_price, low_stock_alert) VALUES
('Engine Oil Filter',   'OF-001', 25,  450.00, 5),
('Air Filter',          'AF-002', 18,  650.00, 5),
('Brake Pads (Set)',    'BP-003',  8, 2800.00, 3),
('Spark Plugs (Set)',   'SP-004', 30, 1200.00, 5),
('Wiper Blades (Pair)', 'WB-005',  4,  800.00, 5),
('Battery 60Ah',        'BT-006',  2,15000.00, 2);

-- Services — two of these share the SAME start_date (today) to
-- immediately demonstrate the "2 lifting machines/day" limit:
-- try adding a 3rd new service for today's date in the app and
-- it should be blocked as "Lifting bay fully booked".
INSERT INTO services (vehicle_id, mechanic_id, service_type, description, status, start_date, end_date) VALUES
(1, 1, 'Full Service',      'Complete vehicle service, oil & filter change', 'Completed',   CURDATE() - INTERVAL 5 DAY, CURDATE() - INTERVAL 5 DAY),
(2, 2, 'Brake Replacement', 'Front brake pads changed',                      'Completed',   CURDATE() - INTERVAL 2 DAY, CURDATE() - INTERVAL 2 DAY),
(3, 3, 'Electrical Check',  'Battery and wiring inspection',                 'In Progress', CURDATE(), NULL),
(1, 2, 'Wheel Alignment',   'Front & rear alignment and balancing',          'Pending',     CURDATE(), NULL);
-- ^ Note: rows 3 and 4 both start CURDATE() -> lifting bay is now 2/2 (full) for today.

-- Invoices for the two completed services above
INSERT INTO invoices (service_id, labor_cost, parts_cost, total_amount, payment_status, payment_date, notes) VALUES
(1, 5000.00, 2500.00, 7500.00, 'Paid',   CURDATE() - INTERVAL 5 DAY, 'Paid in cash'),
(2, 3000.00, 5600.00, 8600.00, 'Unpaid', NULL,                        'Awaiting customer payment');
