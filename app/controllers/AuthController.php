<?php
require_once __DIR__ . '/../models/User.php';

class AuthController {
    private $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    // Handles user login.
    public function login() {
        $response = ['success' => false, 'message' => 'Invalid request method.'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $identifier = $_POST['identifier'] ?? '';
            $password = $_POST['password'] ?? '';

            if (empty($identifier) || empty($password)) {
                $response['message'] = 'Username/Email and password are required.';
            } else {
                $user = $this->userModel->verifyUser($identifier, $password);

                if ($user) {
                    // Start session if not already started
                    if (session_status() == PHP_SESSION_NONE) {
                        session_start();
                    }
                    // Store user data in session
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['user_role'] = $user['role']; // Store role

                    $response = ['success' => true, 'message' => 'Login successful.'];
                } else {
                    $response['message'] = 'Invalid username/email or password.';
                }
            }
        } else {
             // If accessed via GET, potentially show a login view or just return error for AJAX context
             // For now, just indicate invalid method for the expected POST request
             $response['message'] = 'Invalid request method. Please use POST.';
        }

        // Always return JSON
        header('Content-Type: application/json');
        echo json_encode($response);
        exit; // Important to prevent further output
    }

    // Handles user logout.
    public function logout() {
        // Start session if not already started
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        // Unset all session variables
        $_SESSION = array();

        // Destroy the session
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();

        // Redirect to the home page after logout
        header('Location: ' . URL_ROOT . '/public/index.php');
        exit;
    }
}
