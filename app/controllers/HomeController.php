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
        $this->userModel = new User(); // Keep userModel if needed for user creation/deletion link
    }

    // Fetches students with pagination and displays the main student listing view.
    public function index() {
        // Get page number from query string or default to 1
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        if ($page < 1) $page = 1;
        
        // Get paginated students data (4 students per page)
        $paginationData = $this->studentModel->getStudentsWithPagination($page, 4);
        $students = $paginationData['students'];
        
        // Pass pagination data to the view
        require_once __DIR__ . '/../views/Home/index.php';
    }

    // Handles the submission of the add student form.
    public function addStudent() {
        // Check if user is admin before allowing add
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
             header('Content-Type: application/json');
             echo json_encode(['success' => false, 'message' => 'Permission denied.']);
             exit;
        }

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
            $fullName = trim($surname . ' ' . $firstName); // Corrected order: Surname FirstName
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
                // Check if student already exists
                if ($this->studentModel->checkStudentExists($fullName, $group)) {
                    $response = [
                        'success' => false,
                        'message' => 'Student with this name and group already exists.'
                    ];
                } else {
                    // Generate username and email to check for user duplicates
                    $username = strtolower(str_replace(' ', '', $fullName));
                    $email = !empty($username) && !empty($group) ? strtolower($username . '.' . $group . '@lpnu.ua') : null;
                    
                    // Check if user account already exists
                    if ($this->userModel->checkUserExists($username, $email)) {
                        $response = [
                            'success' => false,
                            'message' => 'User account with this username or email already exists.'
                        ];
                    } else {
                        // Attempt to add the student to the database.
                        if ($this->studentModel->addStudent($group, $fullName, $gender, $birthday)) {
                            // Attempt to create a corresponding user account.
                            // Note: Using $fullName (Surname Name) for username generation might be inconsistent if User model expects "Name Surname". Adjust if needed.
                            $userCreated = $this->userModel->createUserAccount($fullName, $group, $birthday, $role);

                            if ($userCreated) {
                                // If both student and user are created successfully.
                                $lastStudent = $this->studentModel->getLastInsertedStudent(); // Fetch the newly added student with ID
                                $response = [
                                    'success' => true,
                                    'student' => $lastStudent, // Send back the full student object including ID
                                    'message' => 'Student and user account created successfully.'
                                ];
                            } else {
                                // If user creation failed after student was added.
                                // Consider rolling back student creation or marking it as incomplete.
                                // For now, just report the error.
                                $lastStudentId = $this->studentModel->getLastInsertedStudent()['id'] ?? null;
                                if ($lastStudentId) {
                                     // Attempt to delete the orphaned student record
                                     $this->studentModel->deleteStudent($lastStudentId);
                                     error_log("User account creation failed for student: {$fullName}, group: {$group}. Rolled back student creation.");
                                     $response['message'] = 'Failed to create user account. Student creation rolled back.';
                                } else {
                                     error_log("User account creation failed for student: {$fullName}, group: {$group}. Could not get last student ID to rollback.");
                                     $response['message'] = 'Student added, but failed to create user account and could not rollback. Please check logs.';
                                }
                                 $response['success'] = false; // Ensure success is false
                            }
                        } else {
                            // If adding the student failed.
                            $response = [
                                'success' => false,
                                'message' => 'Error adding student to the database.'
                            ];
                        }
                    }
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

     // Handles updating an existing student.
     public function updateStudent() {
         // Check if user is admin
         if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
             header('Content-Type: application/json');
             echo json_encode(['success' => false, 'message' => 'Permission denied.']);
             exit;
         }

         $response = ['success' => false, 'message' => 'Invalid request.'];

         if ($_SERVER['REQUEST_METHOD'] === 'POST') {
             $id = $_POST['id'] ?? null;
             $group = $_POST['group'] ?? '';
             $firstName = $_POST['name'] ?? '';
             $surname = $_POST['surname'] ?? '';
             $fullName = trim($surname . ' ' . $firstName); // Corrected order
             $gender = $_POST['gender'] ?? '';
             $birthday = $_POST['birthday'] ?? '';
             $role = $_POST['role'] ?? 'student'; // Get role for potential user update

             // Basic validation
             $dateObj = DateTime::createFromFormat('Y-m-d', $birthday);
             if (empty($id) || empty($group) || empty($firstName) || empty($surname) || empty($gender) || empty($birthday) || empty($role)) {
                 $response['message'] = 'All fields are required.';
             } elseif (!$dateObj || $dateObj->format('Y-m-d') !== $birthday) {
                 $response['message'] = 'Invalid date format. Please use YYYY-MM-DD.';
             } else {
                 // --- Get current student data BEFORE updating ---
                 $currentStudent = $this->studentModel->getStudentById($id);
                 if (!$currentStudent) {
                     $response['message'] = 'Student not found.';
                     header('Content-Type: application/json');
                     echo json_encode($response);
                     exit;
                 }
                 // --- Store old data needed for user update ---
                 $oldFullName = $currentStudent['name'];
                 $oldGroup = $currentStudent['group_name'];
                 
                 // Check if we're actually changing name or group
                 $nameOrGroupChanged = ($oldFullName !== $fullName || $oldGroup !== $group);
                 
                 // If name or group changed, check for duplicates
                 if ($nameOrGroupChanged && $this->studentModel->checkStudentExists($fullName, $group)) {
                     $response['message'] = 'Another student with this name and group already exists.';
                     header('Content-Type: application/json');
                     echo json_encode($response);
                     exit;
                 }
                 
                 // Generate new username and email to check for potential conflicts
                 $newUsername = strtolower(str_replace(' ', '', $fullName));
                 $newEmail = !empty($newUsername) && !empty($group) ? strtolower($newUsername . '.' . $group . '@lpnu.ua') : null;
                 
                 // Generate old username and email for comparison
                 $oldUsername = strtolower(str_replace(' ', '', $oldFullName));
                 $oldEmail = !empty($oldUsername) && !empty($oldGroup) ? strtolower($oldUsername . '.' . $oldGroup . '@lpnu.ua') : null;
                 
                 // Only check for user conflicts if username or email would change
                 if (($oldUsername !== $newUsername || $oldEmail !== $newEmail) && 
                     $this->userModel->checkUserExists($newUsername, $newEmail)) {
                     // Check if it's not just a conflict with the user's own existing account
                     if ($oldUsername !== $newUsername && $oldEmail !== $newEmail) {
                         $response['message'] = 'Cannot update: another user account with this username or email already exists.';
                         header('Content-Type: application/json');
                         echo json_encode($response);
                         exit;
                     }
                 }

                 // Prepare data array for student model update
                 $studentData = [
                     'group_name' => $group,
                     'name' => $fullName,
                     'gender' => $gender,
                     'birthday' => $birthday,
                 ];

                 // --- Update Student Record ---
                 if ($this->studentModel->updateStudent($id, $studentData)) {
                     // --- Attempt to Update Associated User Account ---
                     $userUpdated = $this->userModel->updateUserAccountByStudentInfo(
                         $oldFullName,
                         $oldGroup,
                         $fullName, // new name
                         $group,    // new group
                         $birthday, // new birthday (for password)
                         $role      // new role
                     );

                     if (!$userUpdated) {
                         // Log error but consider the student update successful for now
                         error_log("Student ID {$id} updated, but failed to update associated user account.");
                         // Optionally add a warning to the response
                         $response['warning'] = 'Student record updated, but associated user account update failed. Please check logs.';
                     }

                     // Fetch the updated student data to send back
                     $updatedStudent = $this->studentModel->getStudentById($id);

                     $response = [
                         'success' => true,
                         'message' => 'Student updated successfully.' . (isset($response['warning']) ? ' ' . $response['warning'] : ''),
                         'student' => $updatedStudent // Send back updated data
                     ];
                 } else {
                     $response['message'] = 'Error updating student in the database.';
                 }
             }
         }

         header('Content-Type: application/json');
         echo json_encode($response);
         exit;
     }

     // Handles deleting a student.
     public function deleteStudent() {
         // Check if user is admin
         if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
             header('Content-Type: application/json');
             echo json_encode(['success' => false, 'message' => 'Permission denied.']);
             exit;
         }

         $response = ['success' => false, 'message' => 'Invalid request.'];

         if ($_SERVER['REQUEST_METHOD'] === 'POST') {
             $id = $_POST['id'] ?? null;

             if (empty($id)) {
                 $response['message'] = 'Student ID is required.';
             } else {
                 // Get student info BEFORE deleting the student record
                 $studentInfo = $this->studentModel->getStudentById($id);

                 if ($studentInfo) {
                     // Attempt to delete the associated user account
                     $userDeleted = $this->userModel->deleteUserAccountByStudentInfo($studentInfo['name'], $studentInfo['group_name']);
                     if (!$userDeleted) {
                         // Log the error but proceed with student deletion anyway, or handle differently?
                         error_log("Failed to delete user account associated with student ID: {$id}. Proceeding with student deletion.");
                         // Optionally: Set a specific response message if user deletion fails but student deletion succeeds.
                         // $response['warning'] = 'Student deleted, but associated user account could not be removed.';
                     }
                 } else {
                     error_log("Could not retrieve student info for ID: {$id} before attempting user deletion.");
                     // Decide if you want to stop the process if student info can't be fetched
                 }


                 // Proceed to delete the student record
                 if ($this->studentModel->deleteStudent($id)) {
                     $response = ['success' => true, 'message' => 'Student deleted successfully.'];
                     // Add the warning if user deletion failed
                     // if (isset($response['warning'])) {
                     //     $response['message'] .= ' ' . $response['warning'];
                     // }
                 } else {
                     $response['message'] = 'Error deleting student from the database.';
                 }
             }
         }

         header('Content-Type: application/json');
         echo json_encode($response);
         exit;
     }
}
