-- SMART BUDGET360 - Sales & Marketing Budget Monitoring System
-- Compatible with MySQL 8+ / MariaDB 10.4+ (XAMPP)

SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';
SET time_zone = '+08:00';
SET FOREIGN_KEY_CHECKS = 0;

CREATE DATABASE IF NOT EXISTS smart_budget360 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE smart_budget360;

DROP TABLE IF EXISTS approval_history;
DROP TABLE IF EXISTS approvals;
DROP TABLE IF EXISTS expense_receipts;
DROP TABLE IF EXISTS expenses;
DROP TABLE IF EXISTS budgets;
DROP TABLE IF EXISTS campaign_expenses;
DROP TABLE IF EXISTS campaigns;
DROP TABLE IF EXISTS sales_records;
DROP TABLE IF EXISTS sales_targets;
DROP TABLE IF EXISTS reports;
DROP TABLE IF EXISTS audit_logs;
DROP TABLE IF EXISTS settings;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS budget_categories;
DROP TABLE IF EXISTS departments;
DROP TABLE IF EXISTS roles;

CREATE TABLE roles (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(60) NOT NULL UNIQUE,
  description VARCHAR(255) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE departments (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL UNIQUE,
  code VARCHAR(20) NOT NULL UNIQUE,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  first_name VARCHAR(80) NOT NULL,
  last_name VARCHAR(80) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  role_id INT UNSIGNED NOT NULL,
  department_id INT UNSIGNED NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_users_role FOREIGN KEY (role_id) REFERENCES roles(id),
  CONSTRAINT fk_users_department FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL,
  INDEX idx_users_role (role_id), INDEX idx_users_department (department_id)
) ENGINE=InnoDB;

CREATE TABLE budget_categories (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL UNIQUE,
  description VARCHAR(255) NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE budgets (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  budget_code VARCHAR(30) NOT NULL UNIQUE,
  budget_year SMALLINT UNSIGNED NOT NULL,
  department_id INT UNSIGNED NOT NULL,
  category_id INT UNSIGNED NOT NULL,
  description VARCHAR(500) NOT NULL,
  allocated_amount DECIMAL(15,2) NOT NULL DEFAULT 0,
  approval_status ENUM('Pending','Approved','Rejected') NOT NULL DEFAULT 'Pending',
  created_by INT UNSIGNED NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_budget_department FOREIGN KEY (department_id) REFERENCES departments(id),
  CONSTRAINT fk_budget_category FOREIGN KEY (category_id) REFERENCES budget_categories(id),
  CONSTRAINT fk_budget_creator FOREIGN KEY (created_by) REFERENCES users(id),
  INDEX idx_budget_year_status (budget_year,approval_status), INDEX idx_budget_department (department_id)
) ENGINE=InnoDB;

CREATE TABLE expenses (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  expense_code VARCHAR(30) NOT NULL UNIQUE,
  expense_date DATE NOT NULL,
  department_id INT UNSIGNED NOT NULL,
  category_id INT UNSIGNED NOT NULL,
  description VARCHAR(500) NOT NULL,
  amount DECIMAL(15,2) NOT NULL DEFAULT 0,
  requested_by INT UNSIGNED NOT NULL,
  status ENUM('Pending','Approved','Rejected') NOT NULL DEFAULT 'Pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_expense_department FOREIGN KEY (department_id) REFERENCES departments(id),
  CONSTRAINT fk_expense_category FOREIGN KEY (category_id) REFERENCES budget_categories(id),
  CONSTRAINT fk_expense_requester FOREIGN KEY (requested_by) REFERENCES users(id),
  INDEX idx_expense_date_status (expense_date,status), INDEX idx_expense_department (department_id)
) ENGINE=InnoDB;

CREATE TABLE expense_receipts (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  expense_id INT UNSIGNED NOT NULL,
  original_name VARCHAR(255) NOT NULL,
  stored_name VARCHAR(255) NOT NULL UNIQUE,
  mime_type VARCHAR(100) NOT NULL,
  file_size INT UNSIGNED NOT NULL,
  uploaded_by INT UNSIGNED NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_receipt_expense FOREIGN KEY (expense_id) REFERENCES expenses(id) ON DELETE CASCADE,
  CONSTRAINT fk_receipt_uploader FOREIGN KEY (uploaded_by) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE approvals (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  request_type ENUM('budget','expense') NOT NULL,
  request_id INT UNSIGNED NOT NULL,
  current_stage ENUM('department_manager','finance') NOT NULL DEFAULT 'department_manager',
  status ENUM('Pending','Approved','Rejected') NOT NULL DEFAULT 'Pending',
  submitted_by INT UNSIGNED NOT NULL,
  remarks TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_approval_submitter FOREIGN KEY (submitted_by) REFERENCES users(id),
  UNIQUE KEY uq_approval_request (request_type,request_id),
  INDEX idx_approval_queue (status,current_stage)
) ENGINE=InnoDB;

CREATE TABLE approval_history (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  approval_id INT UNSIGNED NOT NULL,
  stage VARCHAR(50) NOT NULL,
  action VARCHAR(50) NOT NULL,
  action_by INT UNSIGNED NOT NULL,
  remarks TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_history_approval FOREIGN KEY (approval_id) REFERENCES approvals(id) ON DELETE CASCADE,
  CONSTRAINT fk_history_actor FOREIGN KEY (action_by) REFERENCES users(id),
  INDEX idx_history_approval (approval_id)
) ENGINE=InnoDB;

CREATE TABLE sales_targets (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  salesperson_id INT UNSIGNED NOT NULL,
  department_id INT UNSIGNED NOT NULL,
  target_month DATE NOT NULL,
  target_amount DECIMAL(15,2) NOT NULL DEFAULT 0,
  created_by INT UNSIGNED NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_target_salesperson FOREIGN KEY (salesperson_id) REFERENCES users(id),
  CONSTRAINT fk_target_department FOREIGN KEY (department_id) REFERENCES departments(id),
  CONSTRAINT fk_target_creator FOREIGN KEY (created_by) REFERENCES users(id),
  UNIQUE KEY uq_sales_target (salesperson_id,target_month)
) ENGINE=InnoDB;

CREATE TABLE sales_records (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  salesperson_id INT UNSIGNED NOT NULL,
  department_id INT UNSIGNED NOT NULL,
  sales_month DATE NOT NULL,
  sales_target DECIMAL(15,2) NOT NULL DEFAULT 0,
  actual_sales DECIMAL(15,2) NOT NULL DEFAULT 0,
  created_by INT UNSIGNED NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_sales_salesperson FOREIGN KEY (salesperson_id) REFERENCES users(id),
  CONSTRAINT fk_sales_department FOREIGN KEY (department_id) REFERENCES departments(id),
  CONSTRAINT fk_sales_creator FOREIGN KEY (created_by) REFERENCES users(id),
  UNIQUE KEY uq_sales_record (salesperson_id,sales_month),
  INDEX idx_sales_month (sales_month)
) ENGINE=InnoDB;

CREATE TABLE campaigns (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  campaign_name VARCHAR(180) NOT NULL,
  campaign_type VARCHAR(80) NOT NULL,
  department_id INT UNSIGNED NOT NULL,
  start_date DATE NOT NULL,
  end_date DATE NOT NULL,
  budget DECIMAL(15,2) NOT NULL DEFAULT 0,
  actual_spending DECIMAL(15,2) NOT NULL DEFAULT 0,
  generated_sales DECIMAL(15,2) NOT NULL DEFAULT 0,
  status ENUM('Planned','Active','Completed','Cancelled') NOT NULL DEFAULT 'Planned',
  created_by INT UNSIGNED NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_campaign_department FOREIGN KEY (department_id) REFERENCES departments(id),
  CONSTRAINT fk_campaign_creator FOREIGN KEY (created_by) REFERENCES users(id),
  INDEX idx_campaign_dates (start_date,end_date), INDEX idx_campaign_status (status)
) ENGINE=InnoDB;

CREATE TABLE campaign_expenses (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  campaign_id INT UNSIGNED NOT NULL,
  expense_date DATE NOT NULL,
  description VARCHAR(500) NOT NULL,
  amount DECIMAL(15,2) NOT NULL DEFAULT 0,
  created_by INT UNSIGNED NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_campaign_expense_campaign FOREIGN KEY (campaign_id) REFERENCES campaigns(id) ON DELETE CASCADE,
  CONSTRAINT fk_campaign_expense_creator FOREIGN KEY (created_by) REFERENCES users(id),
  INDEX idx_campaign_expense_date (expense_date)
) ENGINE=InnoDB;

CREATE TABLE reports (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  report_type VARCHAR(60) NOT NULL,
  date_from DATE NULL,
  date_to DATE NULL,
  generated_by INT UNSIGNED NOT NULL,
  export_format VARCHAR(20) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_report_user FOREIGN KEY (generated_by) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE audit_logs (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NULL,
  action VARCHAR(60) NOT NULL,
  module VARCHAR(80) NOT NULL,
  description TEXT NULL,
  ip_address VARCHAR(45) NULL,
  user_agent VARCHAR(255) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_audit_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_audit_created (created_at), INDEX idx_audit_user (user_id), INDEX idx_audit_module (module)
) ENGINE=InnoDB;

CREATE TABLE settings (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  setting_key VARCHAR(100) NOT NULL UNIQUE,
  setting_value TEXT NULL,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Roles
INSERT INTO roles (id,name,description) VALUES
(1,'Administrator','Full system access'),
(2,'Finance Manager','Budget, expense, finance approval and reports'),
(3,'Sales Manager','Sales management and department approval'),
(4,'Marketing Manager','Campaign management and department approval'),
(5,'Staff','Dashboard and expense requests'),
(6,'Viewer','Read-only dashboard and reports');

-- Departments
INSERT INTO departments (id,name,code) VALUES
(1,'Sales Division','SALES'),
(2,'Marketing Division','MKTG'),
(3,'Finance Department','FIN'),
(4,'Corporate Services','CORP');

-- Users. Default admin credentials: admin / admin123
INSERT INTO users (id,username,password_hash,first_name,last_name,email,role_id,department_id,is_active) VALUES
(1,'admin','$2y$12$3Akhig0i.ofa83UGYvBzrun1VUT0d8gTNUZd1xBsnZNuCLB6SVHXC','System','Administrator','admin@smartbudget360.local',1,3,1),
(2,'finance.manager','$2y$12$tNvdX4kgiUL0fay2GJI/euOXzrymBGnX2O79r9aVicHqzKU7lb.Qy','Fiona','Reyes','finance@smartbudget360.local',2,3,1),
(3,'sales.manager','$2y$12$uUvoxpOGUDof7HbZ9rWg/eWNz1qvOulMYOiVHfEQrRqgvNqw7RYSK','Samuel','Cruz','sales@smartbudget360.local',3,1,1),
(4,'marketing.manager','$2y$12$dpp9Y1ORQ8iG9PsZOXSlIuNT2mxfF1XcMAditn3DMCMXAg2a6QVim','Mara','Santos','marketing@smartbudget360.local',4,2,1),
(5,'staff.demo','$2y$12$k/3iy/mbIaab8uS4jopB9.ZJ1TGCNkomn5d7hUVznxyosTU3w1Lfi','Andrea','Lopez','staff@smartbudget360.local',5,2,1),
(6,'viewer.demo','$2y$12$qW/hpaETxYxZlxMWf2RCheb9GZT9/ltmDigw2tNIaQJZf4SVPhwgW','Victor','Lim','viewer@smartbudget360.local',6,4,1);

-- Budget Categories
INSERT INTO budget_categories (id,name,description) VALUES
(1,'Digital Advertising','Paid search, social media and display advertising'),
(2,'Events & Activations','Trade shows, launches and customer events'),
(3,'Promotions','Sales promotions, incentives and merchandising'),
(4,'Travel & Client Meetings','Sales travel, client visits and field activities'),
(5,'Creative & Production','Design, content, printing and video production'),
(6,'Market Research','Research, surveys and competitive intelligence'),
(7,'Tools & Subscriptions','Sales and marketing software subscriptions');

-- Approved sample budgets
INSERT INTO budgets (id,budget_code,budget_year,department_id,category_id,description,allocated_amount,approval_status,created_by,created_at) VALUES
(1,'BUD-202601-0001',2026,2,1,'FY2026 paid digital media allocation',1800000.00,'Approved',1,'2026-01-05 09:00:00'),
(2,'BUD-202601-0002',2026,2,2,'Brand activations and trade events',1200000.00,'Approved',1,'2026-01-05 09:10:00'),
(3,'BUD-202601-0003',2026,1,4,'Sales travel and strategic client meetings',900000.00,'Approved',1,'2026-01-05 09:20:00'),
(4,'BUD-202601-0004',2026,1,3,'Sales incentives and field promotions',1400000.00,'Approved',1,'2026-01-05 09:30:00'),
(5,'BUD-202601-0005',2026,2,5,'Creative production and marketing collateral',750000.00,'Approved',1,'2026-01-05 09:40:00'),
(6,'BUD-202608-0006',2026,2,6,'Q3 customer research program',350000.00,'Pending',4,'2026-08-12 14:30:00');

-- Expenses
INSERT INTO expenses (id,expense_code,expense_date,department_id,category_id,description,amount,requested_by,status,created_at) VALUES
(1,'EXP-202601-0001','2026-01-22',2,1,'Meta Ads January campaign',145000.00,4,'Approved','2026-01-22 10:15:00'),
(2,'EXP-202602-0002','2026-02-18',2,5,'Product launch video production',98000.00,4,'Approved','2026-02-18 11:10:00'),
(3,'EXP-202603-0003','2026-03-12',1,4,'North Luzon strategic account visits',76500.00,3,'Approved','2026-03-12 16:00:00'),
(4,'EXP-202604-0004','2026-04-08',1,3,'Dealer incentive materials',120000.00,3,'Approved','2026-04-08 09:25:00'),
(5,'EXP-202605-0005','2026-05-20',2,2,'Healthcare Expo booth participation',210000.00,4,'Approved','2026-05-20 13:45:00'),
(6,'EXP-202606-0006','2026-06-15',2,1,'Search and display campaign',168000.00,4,'Approved','2026-06-15 15:15:00'),
(7,'EXP-202607-0007','2026-07-11',1,4,'Key account client presentation trip',82500.00,3,'Approved','2026-07-11 10:20:00'),
(8,'EXP-202608-0008','2026-08-12',2,5,'August product photography',65000.00,5,'Pending','2026-08-12 15:20:00');

-- Approval queue for current pending requests
INSERT INTO approvals (id,request_type,request_id,current_stage,status,submitted_by,created_at) VALUES
(1,'budget',6,'department_manager','Pending',4,'2026-08-12 14:30:00'),
(2,'expense',8,'department_manager','Pending',5,'2026-08-12 15:20:00');
INSERT INTO approval_history (approval_id,stage,action,action_by,remarks,created_at) VALUES
(1,'request','Submitted',4,'Budget submitted for approval','2026-08-12 14:30:00'),
(2,'request','Submitted',5,'Expense submitted for approval','2026-08-12 15:20:00');

-- Sales targets and performance
INSERT INTO sales_targets (salesperson_id,department_id,target_month,target_amount,created_by) VALUES
(3,1,'2026-06-01',900000.00,3),(3,1,'2026-07-01',950000.00,3),(3,1,'2026-08-01',1000000.00,3),
(5,2,'2026-06-01',500000.00,3),(5,2,'2026-07-01',520000.00,3),(5,2,'2026-08-01',550000.00,3);
INSERT INTO sales_records (salesperson_id,department_id,sales_month,sales_target,actual_sales,created_by) VALUES
(3,1,'2026-06-01',900000.00,935000.00,3),(3,1,'2026-07-01',950000.00,1015000.00,3),(3,1,'2026-08-01',1000000.00,780000.00,3),
(5,2,'2026-06-01',500000.00,455000.00,3),(5,2,'2026-07-01',520000.00,540000.00,3),(5,2,'2026-08-01',550000.00,390000.00,3);

-- Marketing campaigns + expenses
INSERT INTO campaigns (id,campaign_name,campaign_type,department_id,start_date,end_date,budget,actual_spending,generated_sales,status,created_by) VALUES
(1,'Q1 Digital Demand Gen','Digital',2,'2026-01-10','2026-03-31',500000.00,382000.00,1180000.00,'Completed',4),
(2,'Healthcare Expo 2026','Event',2,'2026-05-18','2026-05-22',350000.00,284000.00,760000.00,'Completed',4),
(3,'Mid-Year Social Conversion Push','Social Media',2,'2026-07-01','2026-08-31',420000.00,238000.00,625000.00,'Active',4);
INSERT INTO campaign_expenses (campaign_id,expense_date,description,amount,created_by) VALUES
(1,'2026-01-15','Paid social media placements',145000.00,4),(1,'2026-02-05','Search campaign spend',132000.00,4),(1,'2026-03-05','Retargeting and creative variants',105000.00,4),
(2,'2026-05-18','Booth package and venue',210000.00,4),(2,'2026-05-19','Event collateral and logistics',74000.00,4),
(3,'2026-07-05','Social media ad spend',118000.00,4),(3,'2026-08-05','Influencer and performance media',120000.00,4);

-- Settings
INSERT INTO settings (setting_key,setting_value) VALUES
('company_name','SMART BUDGET360 Demo Company'),('company_address','Philippines'),('currency','PHP'),('fiscal_year_start','1'),('receipt_required','optional'),('budget_warning_threshold','80');

-- Initial audit records
INSERT INTO audit_logs (user_id,action,module,description,ip_address,user_agent,created_at) VALUES
(1,'INSTALL','System','SMART BUDGET360 database initialized','127.0.0.1','Database Seeder','2026-08-13 08:00:00');

SET FOREIGN_KEY_CHECKS = 1;
