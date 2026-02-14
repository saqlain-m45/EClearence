<?php
require_once 'config/db.php';

echo "Seeding Database...\n";

// 1. Create Admin User
$adminName = "System Admin";
$adminEmail = "admin@university.edu";
$adminPass = password_hash("admin123", PASSWORD_BCRYPT);
$adminRole = "admin";

$sql = "INSERT IGNORE INTO users (name, email, password, role) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->execute([$adminName, $adminEmail, $adminPass, $adminRole]);
echo "Admin user created (admin@university.edu / admin123)\n";

// 2. Create Departments & Department Heads
$departments = [
    ['Head of Department', 'hod', 'hod@university.edu'],
    ['Library', 'library', 'library@university.edu'],
    ['Director Academics', 'academics', 'academics@university.edu'],
    ['Admissions Section', 'admissions', 'admissions@university.edu'],
    ['ICT', 'ict', 'ict@university.edu'],
    ['University Cafeteria', 'cafeteria', 'cafeteria@university.edu'],
    ['CDC', 'cdc', 'cdc@university.edu'],
    ['Chief Proctor', 'proctor', 'proctor@university.edu'],
    ['Accounts Section', 'accounts', 'accounts@university.edu'],
    ['Hostel Manager', 'hostel', 'hostel@university.edu']
];

foreach ($departments as $dept) {
    // Insert Department if not exists
    $deptName = $dept[0];
    $deptSlug = $dept[1];
    $deptEmail = $dept[2];

    $checkDept = $conn->prepare("SELECT id FROM departments WHERE slug = ?");
    $checkDept->execute([$deptSlug]);
    $deptId = $checkDept->fetchColumn();

    if (!$deptId) {
        $insertDept = $conn->prepare("INSERT INTO departments (name, slug) VALUES (?, ?)");
        $insertDept->execute([$deptName, $deptSlug]);
        $deptId = $conn->lastInsertId();
        echo "Department '$deptName' created.\n";
    }

    // Create Department User
    $deptPass = password_hash("password", PASSWORD_BCRYPT);
    $checkUser = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $checkUser->execute([$deptEmail]);
    
    if (!$checkUser->fetch()) {
        $insertUser = $conn->prepare("INSERT INTO users (name, email, password, role, department_id) VALUES (?, ?, ?, 'department', ?)");
        $insertUser->execute([$deptName, $deptEmail, $deptPass, $deptId]);
        echo "User '$deptEmail' created.\n";
    }
}

// 3. Create Sample Student
$studentName = "John Doe";
$studentEmail = "student@university.edu";
$studentPass = password_hash("student123", PASSWORD_BCRYPT);
$studentReg = "2023-CS-101";

$checkStudent = $conn->prepare("SELECT id FROM users WHERE email = ?");
$checkStudent->execute([$studentEmail]);
$studentUserId = $checkStudent->fetchColumn();

if (!$studentUserId) {
    $insertStudentUser = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'student')");
    $insertStudentUser->execute([$studentName, $studentEmail, $studentPass]);
    $studentUserId = $conn->lastInsertId();

    $insertStudentDetails = $conn->prepare("INSERT INTO students (user_id, reg_no, discipline, semester, is_boarder, phone) VALUES (?, ?, 'Computer Science', '8th', 1, '1234567890')");
    $insertStudentDetails->execute([$studentUserId, $studentReg]);
    echo "Student 'John Doe' created.\n";
}

echo "Database seeding completed!\n";
?>
