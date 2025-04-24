<?php
require_once __DIR__ . '/../models/Student.php';
require_once __DIR__ . '/../models/User.php';

// Handles requests related to the home page and student management.
class HomeController {
    private $studentModel;
    private $userModel;

    // Initializes Student and User models.
    public function __construct() {
        $this->studentModel = new Student();
        $this->userModel = new User();
    }

    // Fetches all students and displays the main student listing view.
    public function index() {
        $students = $this->studentModel->getAllStudents();
        require_once __DIR__ . '/../views/Home/index.php';
    }

    // Handles the submission of the add student form.
    public function addStudent() {
        // Initialize a default response array.
        $response = [
            'success' => false,
            'message' => 'An unexpected error occurred.'
        ];

        // Check if the request method is POST.
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Retrieve and sanitize form data.
            $group = $_POST['group'] ?? '';
            $firstName = $_POST['name'] ?? '';
            $surname = $_POST['surname'] ?? '';
            $fullName = trim($firstName . ' ' . $surname);
            $gender = $_POST['gender'] ?? '';
            $birthday = $_POST['birthday'] ?? '';
            $role = $_POST['role'] ?? 'student';

            // Validate the birthday date format.
            $dateObj = DateTime::createFromFormat('Y-m-d', $birthday);
            if (!$dateObj || $dateObj->format('Y-m-d') !== $birthday) {
                $response = [
                    'success' => false,
                    'message' => 'Invalid date format. Please use YYYY-MM-DD.'
                ];
            // Validate that required fields are not empty.
            } elseif (empty($group) || empty($firstName) || empty($surname) || empty($gender) || empty($birthday) || empty($role)) {
                $response = [
                    'success' => false,
                    'message' => 'All fields are required.'
                ];
            } else {
                // Attempt to add the student to the database.
                if ($this->studentModel->addStudent($group, $fullName, $gender, $birthday)) {
                    // Attempt to create a corresponding user account.
                    $userCreated = $this->userModel->createUserAccount($fullName, $group, $birthday, $role);

                    if ($userCreated) {
                        // If both student and user are created successfully.
                        $lastStudent = $this->studentModel->getLastInsertedStudent();
                        $response = [
                            'success' => true,
                            'student' => $lastStudent,
                            'message' => 'Student and user account created successfully.'
                        ];
                    } else {
                        // If user creation failed after student was added.
                        $response = [
                            'success' => false,
                            'message' => 'Student added, but failed to create user account. Please check logs or contact admin.'
                        ];
                        // Log the error.
                        error_log("User account creation failed for student: {$fullName}, group: {$group}");
                    }
                } else {
                    // If adding the student failed.
                    $response = [
                        'success' => false,
                        'message' => 'Error adding student to the database.'
                    ];
                }
            }
        } else {
            // If the request method is not POST.
            $response = [
                'success' => false,
                'message' => 'Invalid request method.'
            ];
        }

        // Set header to application/json and output the response as JSON.
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
}
