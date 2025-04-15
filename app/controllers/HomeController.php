<?php
require_once __DIR__ . '/../models/Student.php';

class HomeController {
    private $studentModel;

    public function __construct() {
        $this->studentModel = new Student();
    }

    public function index() {
        $students = $this->studentModel->getAllStudents();
        require_once __DIR__ . '/../views/Home/index.php';
    }

    public function addStudent() {
        // Initialize a default response
        $response = [
            'success' => false,
            'message' => 'An unexpected error occurred.'
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $group = $_POST['group'] ?? '';
            $name = ($_POST['name'] ?? '') . ' ' . ($_POST['surname'] ?? '');
            $gender = $_POST['gender'] ?? '';
            $birthday = $_POST['birthday'] ?? '';

            // Validate the date format
            if (!DateTime::createFromFormat('Y-m-d', $birthday)) {
                $response = [
                    'success' => false,
                    'message' => 'Invalid date format.'
                ];
            } elseif (empty($group) || empty($name) || empty($gender) || empty($birthday)) {
                $response = [
                    'success' => false,
                    'message' => 'All fields are required.'
                ];
            } else {
                // Add the student to the database
                if ($this->studentModel->addStudent($group, $name, $gender, $birthday)) {
                    $lastStudent = $this->studentModel->getLastInsertedStudent();
                    $response = [
                        'success' => true,
                        'student' => $lastStudent
                    ];
                } else {
                    $response = [
                        'success' => false,
                        'message' => 'Error adding student to the database.'
                    ];
                }
            }
        } else {
            $response = [
                'success' => false,
                'message' => 'Invalid request method.'
            ];
        }

        // Always return a JSON response
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
}