<?php
require_once '../config/db.php';
require_once '../config/cors.php';

class StudentController {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getProfile($userId) {
        $query = "SELECT u.name, u.email, u.profile_image, s.* 
                  FROM students s 
                  JOIN users u ON s.user_id = u.id 
                  WHERE u.id = :uid";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':uid', $userId);
        $stmt->execute();
        $profile = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($profile) {
            echo json_encode(['status' => 'success', 'data' => $profile]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Profile not found']);
        }
    }

    public function submitClearance() {
        $data = json_decode(file_get_contents("php://input"));
        
        if (!isset($data->student_id) || !isset($data->purpose)) {
            echo json_encode(['status' => 'error', 'message' => 'Missing fields']);
            return;
        }

        // Check if pending request exists
        $check = "SELECT id FROM clearance_requests WHERE student_id = :sid AND status IN ('pending', 'in_progress')";
        $stmt = $this->conn->prepare($check);
        $stmt->bindParam(':sid', $data->student_id);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            echo json_encode(['status' => 'error', 'message' => 'You already have an active clearance request']);
            return;
        }

        try {
            $this->conn->beginTransaction();

            // Create Request
            $sql = "INSERT INTO clearance_requests (student_id, purpose, status) VALUES (:sid, :purpose, 'pending')";
            $reqStmt = $this->conn->prepare($sql);
            $reqStmt->bindParam(':sid', $data->student_id);
            $reqStmt->bindParam(':purpose', $data->purpose);
            $reqStmt->execute();
            $requestId = $this->conn->lastInsertId();

            // Get Departments
            $depts = $this->conn->query("SELECT id, slug FROM departments")->fetchAll(PDO::FETCH_ASSOC);
            
            // Create Steps
            $stepSql = "INSERT INTO clearance_steps (request_id, department_id, status) VALUES (:rid, :did, 'pending')";
            $stepStmt = $this->conn->prepare($stepSql);

            foreach ($depts as $dept) {
                // Skip Hostel for Day Scholars (logic to be refined later if 'is_boarder' is checked)
                // For now, add all steps, logic to skip can be in approval or submission
                $stepStmt->bindParam(':rid', $requestId);
                $stepStmt->bindParam(':did', $dept['id']);
                $stepStmt->execute();
            }

            // Create Fees entries (Dummy for now, in real scenario fetched from Accounts)
            $fees = ['university_fee', 'transcript_fee', 'degree_fee'];
            $feeSql = "INSERT INTO fees (student_id, fee_type, amount, status) VALUES (:sid, :type, 5000.00, 'outstanding')";
            $feeStmt = $this->conn->prepare($feeSql);
            foreach ($fees as $fee) {
                $feeStmt->bindParam(':sid', $data->student_id);
                $feeStmt->bindParam(':type', $fee);
                $feeStmt->execute();
            }

            $this->conn->commit();
            echo json_encode(['status' => 'success', 'message' => 'Clearance request submitted successfully', 'request_id' => $requestId]);

        } catch (Exception $e) {
            $this->conn->rollBack();
            echo json_encode(['status' => 'error', 'message' => 'Submission failed: ' . $e->getMessage()]);
        }
    }

    public function getStatus($studentId) {
        // Get Active Request
        $q = "SELECT * FROM clearance_requests WHERE student_id = :sid ORDER BY id DESC LIMIT 1";
        $stmt = $this->conn->prepare($q);
        $stmt->bindParam(':sid', $studentId);
        $stmt->execute();
        $request = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$request) {
            echo json_encode(['status' => 'success', 'data' => null]);
            return;
        }

        // Get Steps
        $sQ = "SELECT cs.*, d.name as dept_name, d.slug 
               FROM clearance_steps cs 
               JOIN departments d ON cs.department_id = d.id 
               WHERE cs.request_id = :rid";
        $sStmt = $this->conn->prepare($sQ);
        $sStmt->bindParam(':rid', $request['id']);
        $sStmt->execute();
        $steps = $sStmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'status' => 'success',
            'data' => [
                'request' => $request,
                'steps' => $steps
            ]
        ]);
    }
}

// Router
if (isset($_GET['action'])) {
    $student = new StudentController($conn);
    $data = json_decode(file_get_contents("php://input"));
    $id = isset($_GET['id']) ? $_GET['id'] : (isset($data->student_id) ? $data->student_id : null);

    switch ($_GET['action']) {
        case 'profile':
            if ($id) $student->getProfile($id); // Pass USER_ID here
            break;
        case 'submit':
            $student->submitClearance();
            break;
        case 'status':
             // Get student ID from user ID (need a lookup)
             // For simplicity, let's assume 'id' param is student_id for now or fetch it
            if ($id) $student->getStatus($id);
            break;
        default:
             echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
    }
}
?>
