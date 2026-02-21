<?php
require_once '../config/db.php';
require_once '../config/cors.php';

class AuthController {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function login() {
        $data = json_decode(file_get_contents("php://input"));

        if (!isset($data->email) || !isset($data->password)) {
            echo json_encode(['status' => 'error', 'message' => 'Missing credentials']);
            return;
        }

        $query = "SELECT * FROM users WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $data->email);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($data->password, $user['password'])) {
            session_start();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['name'] = $user['name'];
            
            // Get specific IDs based on role
            $extraData = [];
            if ($user['role'] == 'student') {
                $stuQuery = "SELECT id, reg_no FROM students WHERE user_id = :uid";
                $stuStmt = $this->conn->prepare($stuQuery);
                $stuStmt->bindParam(':uid', $user['id']);
                $stuStmt->execute();
                $student = $stuStmt->fetch(PDO::FETCH_ASSOC);
                $extraData = $student ? $student : [];
            } else if ($user['role'] == 'department') {
                $deptQuery = "SELECT * FROM departments WHERE id = :did";
                $deptStmt = $this->conn->prepare($deptQuery);
                $deptStmt->bindParam(':did', $user['department_id']);
                $deptStmt->execute();
                $dept = $deptStmt->fetch(PDO::FETCH_ASSOC);
                $extraData['department'] = $dept;
            }

            echo json_encode([
                'status' => 'success',
                'message' => 'Login successful',
                'user' => array_merge([
                    'id' => $user['id'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'role' => $user['role'],
                    'profile_image' => $user['profile_image']
                ], $extraData)
            ]);
        } else {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Invalid email or password']);
        }
    }

    public function checkSession() {
        session_start();
        if (isset($_SESSION['user_id'])) {
            echo json_encode([
                'status' => 'success', 
                'user' => [
                    'id' => $_SESSION['user_id'],
                    'role' => $_SESSION['role'],
                    'name' => $_SESSION['name']
                ]
            ]);
        } else {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Not authenticated']);
        }
    }

    public function logout() {
        session_start();
        session_destroy();
        echo json_encode(['status' => 'success', 'message' => 'Logged out']);
    }
}

// Simple Router for Auth
if (isset($_GET['action'])) {
    $auth = new AuthController($conn);
    switch ($_GET['action']) {
        case 'login':
            $auth->login();
            break;
        case 'check':
            $auth->checkSession();
            break;
        case 'logout':
            $auth->logout();
            break;
        default:
            echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'No action specified']);
}
?>

