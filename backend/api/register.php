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

// ... file upload code remains ... 

// ... user creation code remains ...

    // 2. Create Student Details
    $stmt = $conn->prepare("INSERT INTO students (user_id, reg_no, discipline, semester, father_name, cnic, dob, hostel_name, fee_slip_id, profile_image_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$user_id, $reg_no, $discipline, $semester, $father_name, $cnic, $dob, $hostel_name, $fee_slip_id, $profile_image_path]);

    $conn->commit();
    echo json_encode(['status' => 'success', 'message' => 'Registration successful']);

} catch (Exception $e) {
    $conn->rollBack();
    if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
         echo json_encode(['status' => 'error', 'message' => 'Email or Registration Number already exists']);
    } else {
         echo json_encode(['status' => 'error', 'message' => 'Registration failed: ' . $e->getMessage()]);
    }
}
?>
