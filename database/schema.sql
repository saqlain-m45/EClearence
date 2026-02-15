-- Database Schema for E-Clearance System
CREATE DATABASE IF NOT EXISTS e_clearance_db;
USE e_clearance_db;
-- 1. Departments Table
CREATE TABLE IF NOT EXISTS departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    slug VARCHAR(100) NOT NULL UNIQUE,
    -- e.g., 'library', 'accounts'
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
-- 2. Users Table (Admin, Students, Dept Heads)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    -- Hashed
    role ENUM('admin', 'student', 'department') NOT NULL,
    department_id INT NULL,
    -- Linked if role is 'department'
    profile_image VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE
    SET NULL
);
-- 3. Students Table (Extends User for extra details)
CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    reg_no VARCHAR(50) NOT NULL UNIQUE,
    discipline VARCHAR(100) NOT NULL,
    semester VARCHAR(20) NOT NULL,
    father_name VARCHAR(100) NOT NULL,
    cnic VARCHAR(20) NOT NULL,
    dob DATE NOT NULL,
    hostel_name VARCHAR(100) NULL,
    fee_slip_id VARCHAR(50) NULL,
    is_boarder BOOLEAN DEFAULT FALSE,
    -- Hostel Status
    phone VARCHAR(20) NULL,
    profile_image_path VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
-- 4. Clearance Requests Table
CREATE TABLE IF NOT EXISTS clearance_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    purpose ENUM(
        'degree',
        'provisional_certificate',
        'transcript',
        'admission_cancellation',
        'hostel_cancellation',
        'synopsis_submission',
        'thesis_submission'
    ) NOT NULL,
    status ENUM(
        'pending',
        'in_progress',
        'completed',
        'rejected'
    ) DEFAULT 'pending',
    request_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_date TIMESTAMP NULL,
    certificate_path VARCHAR(255) NULL,
    -- Path to generated PDF
    verification_code VARCHAR(100) NULL UNIQUE,
    -- For QR Code
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
);
-- 5. Clearance Steps (Tracks progress per department)
CREATE TABLE IF NOT EXISTS clearance_steps (
    id INT AUTO_INCREMENT PRIMARY KEY,
    request_id INT NOT NULL,
    department_id INT NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    approved_by INT NULL,
    -- User ID of the approver
    remarks TEXT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (request_id) REFERENCES clearance_requests(id) ON DELETE CASCADE,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE CASCADE,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE
    SET NULL
);
-- 6. Fees Table (Managed by Accounts)
CREATE TABLE IF NOT EXISTS fees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    fee_type ENUM(
        'university_fee',
        'hostel_fee',
        'transcript_fee',
        'degree_fee'
    ) NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    status ENUM('paid', 'outstanding') DEFAULT 'outstanding',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
);
-- 7. Notifications Table
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
-- 8. Audit Logs
CREATE TABLE IF NOT EXISTS audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    action VARCHAR(255) NOT NULL,
    details TEXT NULL,
    ip_address VARCHAR(45) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE
    SET NULL
);
-- Initial Data Seeding for Departments
INSERT IGNORE INTO departments (name, slug)
VALUES ('Head of Department', 'hod'),
    ('Library', 'library'),
    ('Director Academics', 'academics'),
    ('Admissions Section', 'admissions'),
    ('ICT', 'ict'),
    ('University Cafeteria', 'cafeteria'),
    ('CDC', 'cdc'),
    ('Chief Proctor', 'proctor'),
    ('Accounts Section', 'accounts'),
    ('Hostel Manager', 'hostel');