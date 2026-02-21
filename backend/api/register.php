<?php
require_once '../config/db.php';
require_once '../config/cors.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit();
}

$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';
$reg_no = $_POST['reg_no'] ?? '';
$father_name = $_POST['father_name'] ?? '';
$cnic = $_POST['cnic'] ?? '';
$dob = $_POST['dob'] ?? '';
$discipline = $_POST['discipline'] ?? '';
$semester = $_POST['semester'] ?? '';
$hostel_name = $_POST['hostel_name'] ?? null; // Optional
$fee_slip_id = $_POST['fee_slip_id'] ?? null; // Optional

if (empty($name) || empty($email) || empty($password) || empty($reg_no)) {
    echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
    exit();
}

// 1. Handle File Upload (Optional)
$profile_image_path = null;
if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
    $upload_dir = '../uploads/profiles/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $file_ext = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
    $file_name = uniqid('profile_') . '.' . $file_ext;
    $target_path = $upload_dir . $file_name;
    
    if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_path)) {
        $profile_image_path = 'backend/uploads/profiles/' . $file_name;
    }
}

try {
    $conn->beginTransaction();

    // 1. Create User
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, profile_image) VALUES (?, ?, ?, 'student', ?)");
    $stmt->execute([$name, $email, $hashed_password, $profile_image_path]);
    $user_id = $conn->lastInsertId();

    // 2. Create Student Details
    $stmt = $conn->prepare("INSERT INTO students (user_id, reg_no, discipline, semester, father_name, cnic, dob, hostel_name, fee_slip_id, profile_image_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$user_id, $reg_no, $discipline, $semester, $father_name, $cnic, $dob, $hostel_name, $fee_slip_id, $profile_image_path]);

    $conn->commit();
    echo json_encode(['status' => 'success', 'message' => 'Registration successful']);

} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
         echo json_encode(['status' => 'error', 'message' => 'Email or Registration Number already exists']);
    } else {
         echo json_encode(['status' => 'error', 'message' => 'Registration failed: ' . $e->getMessage()]);
    }
}
?>

