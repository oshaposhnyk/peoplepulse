# Database Schema Design
## IT Employee Management System

**Version:** 1.0  
**Date:** December 7, 2025  
**Status:** Final

---

## Table of Contents

1. [Overview](#overview)
2. [Database Design Principles](#database-design-principles)
3. [Employee Context Tables](#employee-context-tables)
4. [Team Context Tables](#team-context-tables)
5. [Equipment Context Tables](#equipment-context-tables)
6. [Leave Context Tables](#leave-context-tables)
7. [Identity & Access Tables](#identity--access-tables)
8. [System Tables](#system-tables)
9. [Indexes Strategy](#indexes-strategy)
10. [Entity Relationship Diagram](#entity-relationship-diagram)

---

## Overview

### Database Technology
- **DBMS:** PostgreSQL 15+ (or MySQL 8.0+)
- **Character Set:** UTF-8
- **Collation:** utf8mb4_unicode_ci (MySQL) / en_US.UTF-8 (PostgreSQL)
- **Engine:** InnoDB (MySQL) / Default (PostgreSQL)

### Schema Statistics
- **Total Tables:** 24
- **Employee Context:** 3 tables
- **Team Context:** 3 tables
- **Equipment Context:** 4 tables
- **Leave Context:** 3 tables
- **Identity Context:** 3 tables
- **System/Audit:** 8 tables

---

## Database Design Principles

### 1. Naming Conventions
- **Tables:** Snake_case, plural (e.g., `employees`, `team_members`)
- **Columns:** Snake_case (e.g., `first_name`, `created_at`)
- **Primary Keys:** `id` (BIGINT UNSIGNED, auto-increment)
- **Foreign Keys:** `{table}_id` (e.g., `employee_id`, `team_id`)
- **Indexes:** `idx_{table}_{column(s)}` or `fk_{table}_{reference}`
- **Unique Constraints:** `uq_{table}_{column(s)}`

### 2. Standard Columns
All tables include:
```sql
id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
deleted_at TIMESTAMP NULL (for soft deletes)
```

### 3. Data Integrity
- Foreign key constraints enforced
- NOT NULL constraints where appropriate
- UNIQUE constraints for business keys
- CHECK constraints for valid values
- Default values where sensible

### 4. Performance Optimization
- Indexes on foreign keys
- Indexes on frequently queried columns
- Composite indexes for common query patterns
- Partitioning for large historical tables (future)

### 5. Soft Deletes
Tables with soft deletes:
- `employees` - Retain terminated employee records
- `teams` - Retain disbanded team history
- `equipment` - Retain decommissioned equipment records
- `users` - Retain deactivated user accounts

---

## Employee Context Tables

### Table: `employees`

Core employee information and current state.

```sql
CREATE TABLE employees (
    -- Primary Key
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    
    -- Business Key
    employee_id VARCHAR(20) NOT NULL UNIQUE COMMENT 'EMP-YYYY-XXXX format',
    
    -- Personal Information
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    middle_name VARCHAR(100) NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    phone VARCHAR(20) NOT NULL,
    date_of_birth DATE NULL,
    
    -- Personal Address
    address_street VARCHAR(255) NULL,
    address_city VARCHAR(100) NULL,
    address_state VARCHAR(100) NULL,
    address_zip_code VARCHAR(20) NULL,
    address_country VARCHAR(100) NULL DEFAULT 'USA',
    
    -- Emergency Contact
    emergency_contact_name VARCHAR(200) NULL,
    emergency_contact_phone VARCHAR(20) NULL,
    emergency_contact_relationship VARCHAR(50) NULL,
    
    -- Employment Information
    position VARCHAR(100) NOT NULL,
    department VARCHAR(100) NOT NULL,
    employment_type ENUM('Full-time', 'Part-time', 'Contract', 'Intern') NOT NULL DEFAULT 'Full-time',
    employment_status ENUM('Active', 'Terminated', 'OnLeave') NOT NULL DEFAULT 'Active',
    
    -- Compensation
    salary_amount DECIMAL(12, 2) NOT NULL,
    salary_currency CHAR(3) NOT NULL DEFAULT 'USD',
    salary_frequency ENUM('Annual', 'Monthly', 'Hourly') NOT NULL DEFAULT 'Annual',
    
    -- Location
    office_location VARCHAR(100) NOT NULL,
    work_location_type ENUM('Office', 'Remote', 'Hybrid') NOT NULL DEFAULT 'Office',
    
    -- Remote Work
    remote_work_enabled BOOLEAN NOT NULL DEFAULT FALSE,
    remote_work_policy JSON NULL COMMENT 'Remote work configuration',
    
    -- Dates
    hire_date DATE NOT NULL,
    start_date DATE NOT NULL,
    termination_date DATE NULL,
    last_working_day DATE NULL,
    
    -- Termination Details
    termination_type VARCHAR(50) NULL,
    termination_reason TEXT NULL,
    
    -- Photo
    photo_url VARCHAR(500) NULL,
    
    -- Metadata
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    created_by VARCHAR(20) NULL,
    updated_by VARCHAR(20) NULL,
    
    -- Indexes
    INDEX idx_employees_email (email),
    INDEX idx_employees_status (employment_status),
    INDEX idx_employees_position (position),
    INDEX idx_employees_department (department),
    INDEX idx_employees_location (office_location),
    INDEX idx_employees_hire_date (hire_date),
    INDEX idx_employees_deleted_at (deleted_at),
    
    -- Constraints
    CONSTRAINT chk_employees_email CHECK (email LIKE '%@%.%'),
    CONSTRAINT chk_employees_salary CHECK (salary_amount >= 0),
    CONSTRAINT chk_employees_hire_date CHECK (hire_date <= CURRENT_DATE)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Table: `employee_position_history`

Track all position and salary changes over time.

```sql
CREATE TABLE employee_position_history (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    
    -- Foreign Key
    employee_id BIGINT UNSIGNED NOT NULL,
    
    -- Position Information
    previous_position VARCHAR(100) NOT NULL,
    new_position VARCHAR(100) NOT NULL,
    previous_department VARCHAR(100) NOT NULL,
    new_department VARCHAR(100) NOT NULL,
    
    -- Salary Information
    previous_salary DECIMAL(12, 2) NOT NULL,
    new_salary DECIMAL(12, 2) NOT NULL,
    salary_currency CHAR(3) NOT NULL DEFAULT 'USD',
    salary_change_percentage DECIMAL(5, 2) NULL,
    
    -- Change Details
    effective_date DATE NOT NULL,
    reason TEXT NOT NULL,
    approved_by VARCHAR(20) NULL,
    
    -- Metadata
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by VARCHAR(20) NULL,
    
    -- Foreign Keys
    CONSTRAINT fk_position_history_employee 
        FOREIGN KEY (employee_id) REFERENCES employees(id) 
        ON DELETE CASCADE,
    
    -- Indexes
    INDEX idx_position_history_employee (employee_id),
    INDEX idx_position_history_effective_date (effective_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Table: `employee_location_history`

Track all office location changes.

```sql
CREATE TABLE employee_location_history (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    
    -- Foreign Key
    employee_id BIGINT UNSIGNED NOT NULL,
    
    -- Location Information
    previous_location VARCHAR(100) NOT NULL,
    new_location VARCHAR(100) NOT NULL,
    
    -- Change Details
    effective_date DATE NOT NULL,
    reason TEXT NOT NULL,
    is_temporary BOOLEAN NOT NULL DEFAULT FALSE,
    expected_return_date DATE NULL,
    
    -- Metadata
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by VARCHAR(20) NULL,
    
    -- Foreign Keys
    CONSTRAINT fk_location_history_employee 
        FOREIGN KEY (employee_id) REFERENCES employees(id) 
        ON DELETE CASCADE,
    
    -- Indexes
    INDEX idx_location_history_employee (employee_id),
    INDEX idx_location_history_effective_date (effective_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## Team Context Tables

### Table: `teams`

Core team information.

```sql
CREATE TABLE teams (
    -- Primary Key
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    
    -- Business Key
    team_id VARCHAR(20) NOT NULL UNIQUE COMMENT 'TEAM-XXXX format',
    
    -- Team Information
    name VARCHAR(100) NOT NULL,
    description TEXT NULL,
    type VARCHAR(50) NOT NULL COMMENT 'Development, QA, DevOps, etc.',
    department VARCHAR(100) NOT NULL,
    
    -- Hierarchy
    parent_team_id BIGINT UNSIGNED NULL,
    
    -- Configuration
    max_size INT NULL COMMENT 'Maximum number of members',
    
    -- Status
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    disbanded_at TIMESTAMP NULL,
    disbanded_reason TEXT NULL,
    
    -- Metadata
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    created_by VARCHAR(20) NULL,
    updated_by VARCHAR(20) NULL,
    
    -- Foreign Keys
    CONSTRAINT fk_teams_parent 
        FOREIGN KEY (parent_team_id) REFERENCES teams(id) 
        ON DELETE SET NULL,
    
    -- Indexes
    INDEX idx_teams_name (name),
    INDEX idx_teams_type (type),
    INDEX idx_teams_department (department),
    INDEX idx_teams_parent (parent_team_id),
    INDEX idx_teams_active (is_active),
    INDEX idx_teams_deleted_at (deleted_at),
    
    -- Constraints
    CONSTRAINT uq_teams_name UNIQUE (name, deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Table: `team_members`

Current team membership (many-to-many with additional attributes).

```sql
CREATE TABLE team_members (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    
    -- Foreign Keys
    team_id BIGINT UNSIGNED NOT NULL,
    employee_id BIGINT UNSIGNED NOT NULL,
    
    -- Member Information
    role ENUM('Member', 'TeamLead', 'TechLead') NOT NULL DEFAULT 'Member',
    allocation_percentage INT NOT NULL DEFAULT 100,
    
    -- Dates
    assigned_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    removed_at TIMESTAMP NULL,
    
    -- Metadata
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    assigned_by VARCHAR(20) NULL,
    removed_by VARCHAR(20) NULL,
    removal_reason TEXT NULL,
    
    -- Foreign Keys
    CONSTRAINT fk_team_members_team 
        FOREIGN KEY (team_id) REFERENCES teams(id) 
        ON DELETE CASCADE,
    CONSTRAINT fk_team_members_employee 
        FOREIGN KEY (employee_id) REFERENCES employees(id) 
        ON DELETE CASCADE,
    
    -- Indexes
    INDEX idx_team_members_team (team_id),
    INDEX idx_team_members_employee (employee_id),
    INDEX idx_team_members_role (role),
    INDEX idx_team_members_active (team_id, removed_at),
    
    -- Constraints
    CONSTRAINT uq_team_members_active 
        UNIQUE (team_id, employee_id, removed_at),
    CONSTRAINT chk_team_members_allocation 
        CHECK (allocation_percentage BETWEEN 1 AND 100)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Table: `team_assignment_history`

Historical record of all team assignments (for reporting).

```sql
CREATE TABLE team_assignment_history (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    
    -- Foreign Keys
    team_id BIGINT UNSIGNED NOT NULL,
    employee_id BIGINT UNSIGNED NOT NULL,
    
    -- Assignment Details
    role VARCHAR(50) NOT NULL,
    allocation_percentage INT NOT NULL,
    assigned_at TIMESTAMP NOT NULL,
    removed_at TIMESTAMP NULL,
    assignment_duration_days INT NULL,
    
    -- Reason
    assignment_reason TEXT NULL,
    removal_reason TEXT NULL,
    
    -- Metadata
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_assignment_history_team (team_id),
    INDEX idx_assignment_history_employee (employee_id),
    INDEX idx_assignment_history_dates (assigned_at, removed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## Equipment Context Tables

### Table: `equipment`

All hardware assets in inventory.

```sql
CREATE TABLE equipment (
    -- Primary Key
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    
    -- Business Keys
    asset_tag VARCHAR(30) NOT NULL UNIQUE COMMENT 'ASSET-YYYY-XXXX format',
    serial_number VARCHAR(100) NOT NULL UNIQUE,
    
    -- Equipment Information
    equipment_type VARCHAR(50) NOT NULL COMMENT 'Laptop, Desktop, Monitor, etc.',
    brand VARCHAR(100) NOT NULL,
    model VARCHAR(100) NOT NULL,
    
    -- Specifications (JSON for flexibility)
    specifications JSON NULL COMMENT 'CPU, RAM, Storage, etc.',
    
    -- Purchase Information
    purchase_date DATE NOT NULL,
    purchase_price DECIMAL(10, 2) NOT NULL,
    purchase_currency CHAR(3) NOT NULL DEFAULT 'USD',
    supplier VARCHAR(200) NULL,
    
    -- Warranty
    warranty_expiry_date DATE NULL,
    warranty_provider VARCHAR(200) NULL,
    
    -- Status
    status ENUM('Available', 'Assigned', 'InMaintenance', 'Decommissioned') 
        NOT NULL DEFAULT 'Available',
    condition ENUM('New', 'Good', 'Fair', 'Poor', 'Damaged') 
        NOT NULL DEFAULT 'New',
    
    -- Current Assignment (denormalized for performance)
    current_assignee_id BIGINT UNSIGNED NULL,
    assigned_at TIMESTAMP NULL,
    
    -- Location
    physical_location VARCHAR(200) NULL COMMENT 'Office or warehouse location',
    
    -- Decommission
    decommissioned_at TIMESTAMP NULL,
    decommission_reason TEXT NULL,
    disposal_method VARCHAR(100) NULL,
    
    -- Metadata
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    created_by VARCHAR(20) NULL,
    updated_by VARCHAR(20) NULL,
    
    -- Foreign Keys
    CONSTRAINT fk_equipment_assignee 
        FOREIGN KEY (current_assignee_id) REFERENCES employees(id) 
        ON DELETE SET NULL,
    
    -- Indexes
    INDEX idx_equipment_asset_tag (asset_tag),
    INDEX idx_equipment_serial (serial_number),
    INDEX idx_equipment_type (equipment_type),
    INDEX idx_equipment_status (status),
    INDEX idx_equipment_assignee (current_assignee_id),
    INDEX idx_equipment_purchase_date (purchase_date),
    INDEX idx_equipment_deleted_at (deleted_at),
    
    -- Constraints
    CONSTRAINT chk_equipment_price CHECK (purchase_price >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Table: `equipment_assignments`

Complete history of all equipment assignments.

```sql
CREATE TABLE equipment_assignments (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    
    -- Foreign Keys
    equipment_id BIGINT UNSIGNED NOT NULL,
    employee_id BIGINT UNSIGNED NOT NULL,
    
    -- Assignment Details
    assigned_at TIMESTAMP NOT NULL,
    expected_return_date DATE NULL,
    returned_at TIMESTAMP NULL,
    
    -- Condition Tracking
    condition_at_issue ENUM('New', 'Good', 'Fair', 'Poor') NOT NULL,
    condition_at_return ENUM('New', 'Good', 'Fair', 'Poor', 'Damaged') NULL,
    
    -- Accessories
    accessories_issued JSON NULL COMMENT 'List of accessories given',
    accessories_returned JSON NULL COMMENT 'List of accessories returned',
    
    -- Damage
    damage_reported BOOLEAN NOT NULL DEFAULT FALSE,
    damage_description TEXT NULL,
    damage_photos JSON NULL COMMENT 'Array of photo URLs',
    employee_liable BOOLEAN NULL,
    
    -- Digital Signature
    employee_signature VARCHAR(500) NULL COMMENT 'Digital signature hash',
    
    -- Metadata
    issued_by VARCHAR(20) NULL,
    received_by VARCHAR(20) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Foreign Keys
    CONSTRAINT fk_assignments_equipment 
        FOREIGN KEY (equipment_id) REFERENCES equipment(id) 
        ON DELETE CASCADE,
    CONSTRAINT fk_assignments_employee 
        FOREIGN KEY (employee_id) REFERENCES employees(id) 
        ON DELETE CASCADE,
    
    -- Indexes
    INDEX idx_assignments_equipment (equipment_id),
    INDEX idx_assignments_employee (employee_id),
    INDEX idx_assignments_dates (assigned_at, returned_at),
    INDEX idx_assignments_active (equipment_id, returned_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Table: `equipment_maintenance`

Maintenance records for equipment.

```sql
CREATE TABLE equipment_maintenance (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    
    -- Foreign Key
    equipment_id BIGINT UNSIGNED NOT NULL,
    
    -- Maintenance Details
    maintenance_type VARCHAR(50) NOT NULL COMMENT 'Cleaning, Repair, Upgrade, etc.',
    description TEXT NOT NULL,
    
    -- Scheduling
    scheduled_date DATE NOT NULL,
    completed_date DATE NULL,
    expected_duration_days INT NOT NULL DEFAULT 1,
    actual_duration_days INT NULL,
    
    -- Service Provider
    service_provider VARCHAR(200) NOT NULL,
    is_external_vendor BOOLEAN NOT NULL DEFAULT FALSE,
    
    -- Cost
    estimated_cost DECIMAL(10, 2) NULL,
    actual_cost DECIMAL(10, 2) NULL,
    cost_currency CHAR(3) NOT NULL DEFAULT 'USD',
    
    -- Status
    status ENUM('Scheduled', 'InProgress', 'Completed', 'Cancelled') 
        NOT NULL DEFAULT 'Scheduled',
    
    -- Work Details
    work_performed TEXT NULL,
    parts_replaced JSON NULL,
    warranty_work BOOLEAN NOT NULL DEFAULT FALSE,
    
    -- Metadata
    scheduled_by VARCHAR(20) NULL,
    completed_by VARCHAR(20) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Foreign Keys
    CONSTRAINT fk_maintenance_equipment 
        FOREIGN KEY (equipment_id) REFERENCES equipment(id) 
        ON DELETE CASCADE,
    
    -- Indexes
    INDEX idx_maintenance_equipment (equipment_id),
    INDEX idx_maintenance_scheduled_date (scheduled_date),
    INDEX idx_maintenance_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Table: `equipment_transfers`

Direct transfers between employees (subset of assignments).

```sql
CREATE TABLE equipment_transfers (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    
    -- Foreign Keys
    equipment_id BIGINT UNSIGNED NOT NULL,
    from_employee_id BIGINT UNSIGNED NOT NULL,
    to_employee_id BIGINT UNSIGNED NOT NULL,
    
    -- Transfer Details
    transfer_date TIMESTAMP NOT NULL,
    reason TEXT NOT NULL,
    condition VARCHAR(50) NOT NULL,
    data_wiped BOOLEAN NOT NULL DEFAULT FALSE,
    
    -- Approval
    requires_approval BOOLEAN NOT NULL DEFAULT FALSE,
    approved_by VARCHAR(20) NULL,
    approved_at TIMESTAMP NULL,
    
    -- Metadata
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by VARCHAR(20) NULL,
    
    -- Foreign Keys
    CONSTRAINT fk_transfers_equipment 
        FOREIGN KEY (equipment_id) REFERENCES equipment(id) 
        ON DELETE CASCADE,
    CONSTRAINT fk_transfers_from_employee 
        FOREIGN KEY (from_employee_id) REFERENCES employees(id) 
        ON DELETE CASCADE,
    CONSTRAINT fk_transfers_to_employee 
        FOREIGN KEY (to_employee_id) REFERENCES employees(id) 
        ON DELETE CASCADE,
    
    -- Indexes
    INDEX idx_transfers_equipment (equipment_id),
    INDEX idx_transfers_from (from_employee_id),
    INDEX idx_transfers_to (to_employee_id),
    INDEX idx_transfers_date (transfer_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## Leave Context Tables

### Table: `leave_requests`

All leave requests (vacation, sick, etc.).

```sql
CREATE TABLE leave_requests (
    -- Primary Key
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    
    -- Business Key
    leave_id VARCHAR(30) NOT NULL UNIQUE COMMENT 'LEAVE-YYYY-XXXX format',
    
    -- Foreign Key
    employee_id BIGINT UNSIGNED NOT NULL,
    
    -- Leave Details
    leave_type ENUM('Vacation', 'Sick', 'Unpaid', 'Bereavement', 'Parental', 'Personal') 
        NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    total_days DECIMAL(4, 1) NOT NULL COMMENT 'Supports half days',
    working_days DECIMAL(4, 1) NOT NULL,
    
    -- Request Details
    reason TEXT NULL,
    contact_during_leave VARCHAR(100) NULL,
    backup_person_id BIGINT UNSIGNED NULL,
    
    -- Status
    status ENUM('Pending', 'Approved', 'Rejected', 'Cancelled', 'Completed') 
        NOT NULL DEFAULT 'Pending',
    
    -- Approval
    approved_by BIGINT UNSIGNED NULL,
    approved_at TIMESTAMP NULL,
    approval_notes TEXT NULL,
    
    -- Rejection
    rejected_by BIGINT UNSIGNED NULL,
    rejected_at TIMESTAMP NULL,
    rejection_reason TEXT NULL,
    
    -- Cancellation
    cancelled_at TIMESTAMP NULL,
    cancellation_reason TEXT NULL,
    
    -- Metadata
    requested_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Foreign Keys
    CONSTRAINT fk_leave_requests_employee 
        FOREIGN KEY (employee_id) REFERENCES employees(id) 
        ON DELETE CASCADE,
    CONSTRAINT fk_leave_requests_backup 
        FOREIGN KEY (backup_person_id) REFERENCES employees(id) 
        ON DELETE SET NULL,
    CONSTRAINT fk_leave_requests_approver 
        FOREIGN KEY (approved_by) REFERENCES employees(id) 
        ON DELETE SET NULL,
    CONSTRAINT fk_leave_requests_rejecter 
        FOREIGN KEY (rejected_by) REFERENCES employees(id) 
        ON DELETE SET NULL,
    
    -- Indexes
    INDEX idx_leave_requests_employee (employee_id),
    INDEX idx_leave_requests_type (leave_type),
    INDEX idx_leave_requests_status (status),
    INDEX idx_leave_requests_dates (start_date, end_date),
    INDEX idx_leave_requests_approver (approved_by),
    
    -- Constraints
    CONSTRAINT chk_leave_requests_dates CHECK (end_date >= start_date),
    CONSTRAINT chk_leave_requests_days CHECK (total_days > 0 AND working_days > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Table: `leave_balances`

Current leave balance by employee and type.

```sql
CREATE TABLE leave_balances (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    
    -- Foreign Key
    employee_id BIGINT UNSIGNED NOT NULL,
    
    -- Balance Period
    year INT NOT NULL,
    
    -- Leave Type
    leave_type VARCHAR(50) NOT NULL,
    
    -- Balance Tracking
    opening_balance DECIMAL(5, 1) NOT NULL DEFAULT 0,
    accrued DECIMAL(5, 1) NOT NULL DEFAULT 0,
    used DECIMAL(5, 1) NOT NULL DEFAULT 0,
    pending DECIMAL(5, 1) NOT NULL DEFAULT 0 COMMENT 'In pending requests',
    adjusted DECIMAL(5, 1) NOT NULL DEFAULT 0 COMMENT 'Manual adjustments',
    carried_over DECIMAL(5, 1) NOT NULL DEFAULT 0,
    forfeited DECIMAL(5, 1) NOT NULL DEFAULT 0,
    available DECIMAL(5, 1) GENERATED ALWAYS AS 
        (opening_balance + accrued + adjusted + carried_over - used - pending - forfeited) STORED,
    
    -- Accrual Configuration
    accrual_rate DECIMAL(4, 2) NOT NULL COMMENT 'Days per month',
    max_carry_over DECIMAL(5, 1) NOT NULL DEFAULT 0,
    max_balance DECIMAL(5, 1) NULL COMMENT 'Maximum balance cap',
    
    -- Metadata
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Foreign Keys
    CONSTRAINT fk_leave_balances_employee 
        FOREIGN KEY (employee_id) REFERENCES employees(id) 
        ON DELETE CASCADE,
    
    -- Indexes
    INDEX idx_leave_balances_employee (employee_id),
    INDEX idx_leave_balances_year (year),
    INDEX idx_leave_balances_type (leave_type),
    
    -- Constraints
    CONSTRAINT uq_leave_balances_employee_year_type 
        UNIQUE (employee_id, year, leave_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Table: `leave_accruals`

History of leave balance accruals.

```sql
CREATE TABLE leave_accruals (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    
    -- Foreign Key
    employee_id BIGINT UNSIGNED NOT NULL,
    leave_balance_id BIGINT UNSIGNED NOT NULL,
    
    -- Accrual Details
    leave_type VARCHAR(50) NOT NULL,
    accrual_period VARCHAR(7) NOT NULL COMMENT 'YYYY-MM format',
    accrued_days DECIMAL(5, 1) NOT NULL,
    
    -- Balance Snapshot
    balance_before DECIMAL(5, 1) NOT NULL,
    balance_after DECIMAL(5, 1) NOT NULL,
    
    -- Accrual Type
    accrual_type ENUM('Scheduled', 'Manual', 'Adjustment', 'CarryOver') 
        NOT NULL DEFAULT 'Scheduled',
    reason TEXT NULL,
    
    -- Metadata
    accrued_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by VARCHAR(20) NULL,
    
    -- Foreign Keys
    CONSTRAINT fk_leave_accruals_employee 
        FOREIGN KEY (employee_id) REFERENCES employees(id) 
        ON DELETE CASCADE,
    CONSTRAINT fk_leave_accruals_balance 
        FOREIGN KEY (leave_balance_id) REFERENCES leave_balances(id) 
        ON DELETE CASCADE,
    
    -- Indexes
    INDEX idx_leave_accruals_employee (employee_id),
    INDEX idx_leave_accruals_period (accrual_period),
    INDEX idx_leave_accruals_type (leave_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## Identity & Access Tables

### Table: `users`

User accounts for system access.

```sql
CREATE TABLE users (
    -- Primary Key
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    
    -- Foreign Key
    employee_id BIGINT UNSIGNED NOT NULL UNIQUE,
    
    -- Credentials
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL COMMENT 'bcrypt hashed',
    remember_token VARCHAR(100) NULL,
    
    -- Password Management
    password_changed_at TIMESTAMP NULL,
    password_expires_at TIMESTAMP NULL,
    password_history JSON NULL COMMENT 'Last 3 password hashes',
    
    -- Role
    role ENUM('Admin', 'Employee') NOT NULL DEFAULT 'Employee',
    permissions JSON NULL COMMENT 'Additional granular permissions',
    
    -- Account Status
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    is_locked BOOLEAN NOT NULL DEFAULT FALSE,
    locked_until TIMESTAMP NULL,
    
    -- Login Tracking
    failed_login_attempts INT NOT NULL DEFAULT 0,
    last_login_at TIMESTAMP NULL,
    last_login_ip VARCHAR(45) NULL,
    
    -- Email Verification
    email_verified_at TIMESTAMP NULL,
    verification_token VARCHAR(255) NULL,
    
    -- Multi-Factor Authentication
    mfa_enabled BOOLEAN NOT NULL DEFAULT FALSE,
    mfa_secret VARCHAR(255) NULL,
    mfa_backup_codes JSON NULL,
    
    -- Metadata
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    
    -- Foreign Keys
    CONSTRAINT fk_users_employee 
        FOREIGN KEY (employee_id) REFERENCES employees(id) 
        ON DELETE CASCADE,
    
    -- Indexes
    INDEX idx_users_email (email),
    INDEX idx_users_role (role),
    INDEX idx_users_active (is_active),
    INDEX idx_users_deleted_at (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Table: `sessions`

Active user sessions.

```sql
CREATE TABLE sessions (
    id VARCHAR(255) PRIMARY KEY,
    
    -- Foreign Key
    user_id BIGINT UNSIGNED NULL,
    
    -- Session Data
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    payload LONGTEXT NOT NULL,
    
    -- Device Info
    device_type VARCHAR(50) NULL,
    device_os VARCHAR(50) NULL,
    device_browser VARCHAR(50) NULL,
    
    -- Timestamps
    last_activity INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_sessions_user_id (user_id),
    INDEX idx_sessions_last_activity (last_activity)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Table: `password_reset_tokens`

Tokens for password reset functionality.

```sql
CREATE TABLE password_reset_tokens (
    email VARCHAR(255) PRIMARY KEY,
    token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,
    
    INDEX idx_password_reset_tokens_token (token),
    INDEX idx_password_reset_tokens_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## System Tables

### Table: `audit_logs`

Comprehensive audit trail of all system changes.

```sql
CREATE TABLE audit_logs (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    
    -- Event Information
    event_type VARCHAR(100) NOT NULL COMMENT 'e.g., employee.hired, leave.approved',
    event_category VARCHAR(50) NOT NULL COMMENT 'Employee, Team, Equipment, Leave, Auth',
    
    -- Actor
    user_id BIGINT UNSIGNED NULL,
    user_email VARCHAR(255) NULL,
    user_role VARCHAR(50) NULL,
    
    -- Target
    resource_type VARCHAR(50) NOT NULL COMMENT 'Employee, Team, Equipment, etc.',
    resource_id VARCHAR(50) NOT NULL,
    
    -- Changes
    action ENUM('CREATE', 'UPDATE', 'DELETE', 'READ') NOT NULL,
    changes JSON NULL COMMENT 'Old and new values',
    
    -- Request Context
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    request_id VARCHAR(100) NULL COMMENT 'For tracing',
    
    -- Result
    result ENUM('Success', 'Failure') NOT NULL DEFAULT 'Success',
    error_message TEXT NULL,
    
    -- Additional Context
    notes TEXT NULL,
    metadata JSON NULL,
    
    -- Timestamp
    occurred_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Foreign Keys
    CONSTRAINT fk_audit_logs_user 
        FOREIGN KEY (user_id) REFERENCES users(id) 
        ON DELETE SET NULL,
    
    -- Indexes
    INDEX idx_audit_logs_user (user_id),
    INDEX idx_audit_logs_resource (resource_type, resource_id),
    INDEX idx_audit_logs_event_type (event_type),
    INDEX idx_audit_logs_occurred_at (occurred_at),
    INDEX idx_audit_logs_category (event_category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Table: `domain_events`

Storage for domain events (optional event sourcing).

```sql
CREATE TABLE domain_events (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    
    -- Event Identity
    event_id VARCHAR(36) NOT NULL UNIQUE COMMENT 'UUID',
    event_type VARCHAR(100) NOT NULL,
    event_version VARCHAR(10) NOT NULL DEFAULT '1.0',
    
    -- Aggregate
    aggregate_type VARCHAR(50) NOT NULL,
    aggregate_id VARCHAR(50) NOT NULL,
    
    -- Causation & Correlation
    causation_id VARCHAR(36) NULL,
    correlation_id VARCHAR(36) NULL,
    
    -- Actor
    user_id BIGINT UNSIGNED NULL,
    
    -- Event Data
    payload JSON NOT NULL,
    metadata JSON NULL,
    
    -- Status
    is_processed BOOLEAN NOT NULL DEFAULT FALSE,
    processed_at TIMESTAMP NULL,
    processing_errors TEXT NULL,
    
    -- Timestamp
    occurred_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_domain_events_event_id (event_id),
    INDEX idx_domain_events_event_type (event_type),
    INDEX idx_domain_events_aggregate (aggregate_type, aggregate_id),
    INDEX idx_domain_events_occurred_at (occurred_at),
    INDEX idx_domain_events_processed (is_processed),
    INDEX idx_domain_events_correlation (correlation_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Table: `failed_jobs`

Failed queue jobs for retry/analysis.

```sql
CREATE TABLE failed_jobs (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    uuid VARCHAR(36) NOT NULL UNIQUE,
    connection TEXT NOT NULL,
    queue TEXT NOT NULL,
    payload LONGTEXT NOT NULL,
    exception LONGTEXT NOT NULL,
    failed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_failed_jobs_failed_at (failed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Table: `jobs`

Queue jobs table.

```sql
CREATE TABLE jobs (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    queue VARCHAR(255) NOT NULL,
    payload LONGTEXT NOT NULL,
    attempts TINYINT UNSIGNED NOT NULL,
    reserved_at INT UNSIGNED NULL,
    available_at INT UNSIGNED NOT NULL,
    created_at INT UNSIGNED NOT NULL,
    
    INDEX idx_jobs_queue_reserved_at (queue, reserved_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Table: `notifications`

User notifications.

```sql
CREATE TABLE notifications (
    id CHAR(36) PRIMARY KEY,
    type VARCHAR(255) NOT NULL,
    notifiable_type VARCHAR(255) NOT NULL,
    notifiable_id BIGINT UNSIGNED NOT NULL,
    data TEXT NOT NULL,
    read_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_notifications_notifiable (notifiable_type, notifiable_id),
    INDEX idx_notifications_read_at (read_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Table: `cache`

Cache table for database cache driver.

```sql
CREATE TABLE cache (
    key VARCHAR(255) PRIMARY KEY,
    value MEDIUMTEXT NOT NULL,
    expiration INT NOT NULL,
    
    INDEX idx_cache_expiration (expiration)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Table: `cache_locks`

Cache locks table.

```sql
CREATE TABLE cache_locks (
    key VARCHAR(255) PRIMARY KEY,
    owner VARCHAR(255) NOT NULL,
    expiration INT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Table: `migrations`

Laravel migrations tracking.

```sql
CREATE TABLE migrations (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    migration VARCHAR(255) NOT NULL,
    batch INT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## Indexes Strategy

### Primary Indexes (Automatically Created)
- Primary keys on all tables
- Unique constraints on business keys (employee_id, asset_tag, etc.)

### Foreign Key Indexes
All foreign keys have indexes for join performance:
- `employee_id` columns
- `team_id` columns
- `equipment_id` columns
- `user_id` columns

### Query-Specific Indexes

**Employee Context:**
```sql
-- Search employees by email
INDEX idx_employees_email (email)

-- Filter by status
INDEX idx_employees_status (employment_status)

-- Filter by position/department
INDEX idx_employees_position (position)
INDEX idx_employees_department (department)

-- Hire date queries (reporting)
INDEX idx_employees_hire_date (hire_date)
```

**Team Context:**
```sql
-- Active team membership queries
INDEX idx_team_members_active (team_id, removed_at)

-- Find all teams for employee
INDEX idx_team_members_employee (employee_id)

-- Team hierarchy queries
INDEX idx_teams_parent (parent_team_id)
```

**Equipment Context:**
```sql
-- Find available equipment
INDEX idx_equipment_status (status)

-- Find equipment by type
INDEX idx_equipment_type (equipment_type)

-- Current assignments
INDEX idx_assignments_active (equipment_id, returned_at)

-- Assignment history
INDEX idx_assignments_dates (assigned_at, returned_at)
```

**Leave Context:**
```sql
-- Find leave requests by status
INDEX idx_leave_requests_status (status)

-- Leave calendar queries
INDEX idx_leave_requests_dates (start_date, end_date)

-- Approval workflow
INDEX idx_leave_requests_approver (approved_by)
```

**Audit Logs:**
```sql
-- Query by resource
INDEX idx_audit_logs_resource (resource_type, resource_id)

-- Query by event type
INDEX idx_audit_logs_event_type (event_type)

-- Time-based queries
INDEX idx_audit_logs_occurred_at (occurred_at)
```

### Composite Indexes

For frequently combined filters:

```sql
-- Active employees by department
CREATE INDEX idx_employees_active_dept 
    ON employees(employment_status, department) 
    WHERE deleted_at IS NULL;

-- Team members with role
CREATE INDEX idx_team_members_team_role 
    ON team_members(team_id, role) 
    WHERE removed_at IS NULL;

-- Available equipment by type
CREATE INDEX idx_equipment_available_type 
    ON equipment(status, equipment_type) 
    WHERE status = 'Available';
```

---

## Entity Relationship Diagram

### Core Relationships

```
┌──────────────┐
│   employees  │
└───────┬──────┘
        │
        │ 1:1
        ↓
┌──────────────┐         ┌─────────────────┐
│    users     │         │  team_members   │
└──────────────┘         └────────┬────────┘
                                  │
                         ┌────────┴────────┐
                         │                 │
                     M:1 │             M:1 │
                         ↓                 ↓
                  ┌──────────┐      ┌──────────┐
                  │  teams   │      │employees │
                  └──────────┘      └────┬─────┘
                                         │
                                     1:M │
                                         ↓
                              ┌──────────────────────┐
                              │ equipment_assignments│
                              └──────────┬───────────┘
                                         │
                                     M:1 │
                                         ↓
                                  ┌──────────┐
                                  │equipment │
                                  └──────────┘
```

### Leave Management

```
┌──────────────┐
│  employees   │
└───────┬──────┘
        │
        │ 1:M
        ├──────────────────────┐
        │                      │
        ↓                      ↓
┌──────────────┐    ┌──────────────────┐
│leave_requests│    │  leave_balances  │
└──────────────┘    └─────────┬────────┘
                              │
                          1:M │
                              ↓
                    ┌──────────────────┐
                    │ leave_accruals   │
                    └──────────────────┘
```

### Audit Trail

```
All Tables ──────┐
                 │
                 │ Trigger/Event
                 ↓
          ┌──────────────┐
          │  audit_logs  │
          └──────────────┘
                 ↑
                 │ References
                 │
          ┌──────────────┐
          │    users     │
          └──────────────┘
```

---

## Summary

### Database Statistics

- **Total Tables:** 24
- **Total Indexes:** 100+
- **Total Foreign Keys:** 35+
- **Soft Delete Tables:** 5
- **JSON Columns:** 15+ (for flexibility)

### Key Design Decisions

✅ **Normalization:** 3NF for transactional tables  
✅ **Denormalization:** Current assignment in equipment table for performance  
✅ **Soft Deletes:** Employee, team, equipment, user records  
✅ **History Tables:** Comprehensive audit trail  
✅ **JSON Columns:** For flexible specifications and metadata  
✅ **Generated Columns:** For calculated values (leave available balance)  
✅ **Indexes:** Strategic indexing for query performance  
✅ **Constraints:** Enforce data integrity at database level  

### Performance Considerations

- Indexes on all foreign keys
- Indexes on frequently queried columns
- Composite indexes for common query patterns
- Partitioning strategy for audit logs (future)
- Read replicas for reporting queries (future)

---

**Document Status:** ✅ Complete  
**Next Step:** Design REST API structure (Task 1.7)

