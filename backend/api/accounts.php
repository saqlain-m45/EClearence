<?php
require_once '../config/db.php';
require_once '../config/cors.php';

class AccountsController {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getStudentFees($studentId) {
        $query = "SELECT * FROM fees WHERE student_id = :sid";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':sid', $studentId);
        $stmt->execute();
        $fees = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['status' => 'success', 'data' => $fees]);
    }

    public function updateFeeStatus() {
        $data = json_decode(file_get_contents("php://input"));

        if (!isset($data->fee_id) || !isset($data->status)) {
            echo json_encode(['status' => 'error', 'message' => 'Missing fields']);
            return;
        }

        try {
            $query = "UPDATE fees SET status = :status, updated_at = NOW() WHERE id = :fid";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':status', $data->status);
            $stmt->bindParam(':fid', $data->fee_id);
            $stmt->execute();

            echo json_encode(['status' => 'success', 'message' => 'Fee status updated']);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Update failed']);
        }
    }
}

// Router
if (isset($_GET['action'])) {
    $accounts = new AccountsController($conn);
    $data = json_decode(file_get_contents("php://input"));
    $id = isset($_GET['id']) ? $_GET['id'] : (isset($data->student_id) ? $data->student_id : null);

    switch ($_GET['action']) {
        case 'fees':
            if ($id) {
                $accounts->getStudentFees($id);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Student ID missing']);
            }
            break;
        case 'update':
            $accounts->updateFeeStatus();
            break;
        default:
             echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'No action specified']);
}
?>

