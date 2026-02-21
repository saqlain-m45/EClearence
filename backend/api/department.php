<?php
require_once '../config/db.php';
require_once '../config/cors.php';

class DepartmentController {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getPendingRequests($deptId) {
        // Fetch clearance steps assigned to this dept (Include s.id as student_id)
        $query = "SELECT cs.id as step_id, cs.status as step_status, 
                         cr.id as request_id, cr.purpose, cr.request_date,
                         s.id as student_id, s.reg_no, s.discipline, s.semester, s.father_name, s.hostel_name, s.fee_slip_id, s.profile_image_path,
                         u.name as student_name
                  FROM clearance_steps cs
                  JOIN clearance_requests cr ON cs.request_id = cr.id
                  JOIN students s ON cr.student_id = s.id
                  JOIN users u ON s.user_id = u.id
                  WHERE cs.department_id = :did AND cs.status = 'pending'";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':did', $deptId);
        $stmt->execute();
        $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['status' => 'success', 'data' => $requests]);
    }

    public function updateRequestStatus() {
        $data = json_decode(file_get_contents("php://input"));

        if (!isset($data->step_id) || !isset($data->status) || !isset($data->user_id)) {
            echo json_encode(['status' => 'error', 'message' => 'Missing fields']);
            return;
        }

        $validStatuses = ['approved', 'rejected'];
        if (!in_array($data->status, $validStatuses)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid status']);
            return;
        }

        try {
            $this->conn->beginTransaction();

            $query = "UPDATE clearance_steps 
                      SET status = :status, remarks = :remarks, approved_by = :uid, updated_at = NOW() 
                      WHERE id = :sid";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':status', $data->status);
            $remarks = isset($data->remarks) ? $data->remarks : '';
            $stmt->bindParam(':remarks', $remarks);
            $stmt->bindParam(':uid', $data->user_id);
            $stmt->bindParam(':sid', $data->step_id);
            $stmt->execute();

            // Check if all steps are approved for this request
            // First get request_id
            $ridQ = "SELECT request_id FROM clearance_steps WHERE id = :sid";
            $ridStmt = $this->conn->prepare($ridQ);
            $ridStmt->bindParam(':sid', $data->step_id);
            $ridStmt->execute();
            $requestId = $ridStmt->fetchColumn();

            if ($requestId) {
                // Check pending steps
                $checkQ = "SELECT COUNT(*) FROM clearance_steps WHERE request_id = :rid AND status != 'approved'";
                $checkStmt = $this->conn->prepare($checkQ);
                $checkStmt->bindParam(':rid', $requestId);
                $checkStmt->execute();
                $pendingCount = $checkStmt->fetchColumn();

                if ($pendingCount == 0) {
                    // All approved! Mark request as completed & Generate Code
                    $verifyCode = 'CRT-' . date('Y') . '-' . mt_rand(1000, 9999) . $requestId;
                    $compQ = "UPDATE clearance_requests SET status = 'completed', completed_date = NOW(), verification_code = :vcode WHERE id = :rid";
                    $compStmt = $this->conn->prepare($compQ);
                    $compStmt->bindParam(':rid', $requestId);
                    $compStmt->bindParam(':vcode', $verifyCode);
                    $compStmt->execute();
                }
            }

            $this->conn->commit();
            echo json_encode(['status' => 'success', 'message' => 'Request updated successfully']);

        } catch (Exception $e) {
            $this->conn->rollBack();
            echo json_encode(['status' => 'error', 'message' => 'Update failed: ' . $e->getMessage()]);
        }
    }
}

// Router
if (isset($_GET['action'])) {
    $dept = new DepartmentController($conn);
    $data = json_decode(file_get_contents("php://input"));
    $id = isset($_GET['id']) ? $_GET['id'] : (isset($data->dept_id) ? $data->dept_id : null);

    switch ($_GET['action']) {
        case 'pending':
            if ($id) {
                $dept->getPendingRequests($id);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Department ID missing']);
            }
            break;
        case 'history':
            if ($id) {
                $query = "SELECT cs.id as step_id, cs.status, cs.updated_at, cs.remarks,
                         u.name as student_name, s.reg_no
                  FROM clearance_steps cs
                  JOIN clearance_requests cr ON cs.request_id = cr.id
                  JOIN students s ON cr.student_id = s.id
                  JOIN users u ON s.user_id = u.id
                  WHERE cs.department_id = ? AND cs.status != 'pending'
                  ORDER BY cs.updated_at DESC LIMIT 50";
                $stmt = $conn->prepare($query);
                $stmt->execute([$id]);
                echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
            } else {
                 echo json_encode(['status' => 'error', 'message' => 'Department ID missing']);
            }
            break;
        case 'update':
            $dept->updateRequestStatus();
            break;
        default:
             echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'No action specified']);
}
?>

