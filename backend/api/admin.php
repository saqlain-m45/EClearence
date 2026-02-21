<?php
require_once '../config/db.php';
require_once '../config/cors.php';

class AdminController {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getUsers() {
        $query = "SELECT u.id, u.name, u.email, u.role, d.name as department_name 
                  FROM users u 
                  LEFT JOIN departments d ON u.department_id = d.id 
                  ORDER BY u.role, u.name";
        $stmt = $this->conn->query($query);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['status' => 'success', 'data' => $users]);
    }

    public function verifyCertificate($code) {
        $query = "SELECT cr.completed_date, s.reg_no, u.name 
                  FROM clearance_requests cr
                  JOIN students s ON cr.student_id = s.id
                  JOIN users u ON s.user_id = u.id
                  WHERE cr.verification_code = :code AND cr.status = 'completed'";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':code', $code);
        $stmt->execute();
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($res) {
             echo json_encode(['status' => 'success', 'data' => $res]);
        } else {
             echo json_encode(['status' => 'error', 'message' => 'Invalid Certificate Code']);
        }
    }
}

// Router
if (isset($_GET['action'])) {
    $admin = new AdminController($conn);
    switch ($_GET['action']) {
        case 'users':
            $admin->getUsers();
            break;
        case 'verify':
            $data = json_decode(file_get_contents("php://input"));
            $code = $data->code ?? '';
            $admin->verifyCertificate($code);
            break;
        default:
             echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'No action specified']);
}
?>

