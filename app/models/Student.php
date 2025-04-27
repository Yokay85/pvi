<?php
class Student {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
         if ($this->db === null) {
             throw new Exception("Database connection failed in Student model.");
         }
    }

    public function getAllStudents() {
        try {
            $query = "SELECT * FROM students ORDER BY id ASC"; // Ensure consistent order
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching all students: " . $e->getMessage());
            return []; // Return empty array on error
        }
    }

    // New method for paginated student retrieval
    public function getStudentsWithPagination($page = 1, $perPage = 4) {
        try {
            // Calculate the offset
            $offset = ($page - 1) * $perPage;
            
            // Get paginated students
            $query = "SELECT * FROM students ORDER BY id ASC LIMIT :limit OFFSET :offset";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':limit', $perPage, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get total count for pagination
            $countQuery = "SELECT COUNT(*) FROM students";
            $countStmt = $this->db->prepare($countQuery);
            $countStmt->execute();
            $totalStudents = (int)$countStmt->fetchColumn();
            
            // Calculate total pages
            $totalPages = ceil($totalStudents / $perPage);
            
            return [
                'students' => $students,
                'totalStudents' => $totalStudents,
                'totalPages' => $totalPages,
                'currentPage' => $page,
                'perPage' => $perPage
            ];
        } catch (PDOException $e) {
            error_log("Error fetching paginated students: " . $e->getMessage());
            return [
                'students' => [],
                'totalStudents' => 0,
                'totalPages' => 0,
                'currentPage' => $page,
                'perPage' => $perPage
            ];
        }
    }

     public function getStudentById($id) {
         try {
             $query = "SELECT * FROM students WHERE id = :id LIMIT 1";
             $stmt = $this->db->prepare($query);
             $stmt->bindParam(':id', $id, PDO::PARAM_INT);
             $stmt->execute();
             return $stmt->fetch(PDO::FETCH_ASSOC);
         } catch (PDOException $e) {
             error_log("Error fetching student by ID {$id}: " . $e->getMessage());
             return false;
         }
     }

    public function checkStudentExists($name, $group) {
        try {
            $query = "SELECT COUNT(*) FROM students WHERE name = :name AND group_name = :group_name";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':group_name', $group);
            $stmt->execute();
            $count = (int)$stmt->fetchColumn();
            
            return $count > 0;
        } catch (PDOException $e) {
            error_log("Error checking if student exists: " . $e->getMessage());
            // Return true on error to prevent potential duplicates
            return true;
        }
    }

    public function addStudent($group, $name, $gender, $birthday) {
         try {
            $query = "INSERT INTO students (group_name, name, gender, birthday, status)
                      VALUES (:group_name, :name, :gender, :birthday, 'offline')"; // Default status
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':group_name', $group);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':gender', $gender);
            $stmt->bindParam(':birthday', $birthday);
            return $stmt->execute();
         } catch (PDOException $e) {
             error_log("Error adding student: " . $e->getMessage());
             return false;
         }
    }

    public function getLastInsertedStudent() {
         try {
            // Use LAST_INSERT_ID() which is safer and more efficient
            $lastId = $this->db->lastInsertId();
            if ($lastId) {
                return $this->getStudentById($lastId);
            }
            return false; // No ID inserted or error
         } catch (PDOException $e) {
             error_log("Error getting last inserted student ID: " . $e->getMessage());
             // Fallback attempt (less reliable)
             try {
                 $query = "SELECT * FROM students ORDER BY id DESC LIMIT 1";
                 $stmt = $this->db->prepare($query);
                 $stmt->execute();
                 return $stmt->fetch(PDO::FETCH_ASSOC);
             } catch (PDOException $e2) {
                 error_log("Fallback error getting last student: " . $e2->getMessage());
                 return false;
             }
         }
    }

     public function updateStudent($id, $data) {
         // Build the SET part of the query dynamically based on provided data
         $setClauses = [];
         $params = [':id' => $id];
         foreach ($data as $key => $value) {
             // Ensure only allowed columns are updated
             if (in_array($key, ['group_name', 'name', 'gender', 'birthday', 'status'])) {
                 $setClauses[] = "`" . $key . "` = :" . $key;
                 $params[':' . $key] = $value;
             }
         }

         if (empty($setClauses)) {
             error_log("Update student failed: No valid data provided for ID {$id}.");
             return false; // No valid fields to update
         }

         $query = "UPDATE students SET " . implode(', ', $setClauses) . " WHERE id = :id";

         try {
             $stmt = $this->db->prepare($query);
             // Bind all parameters
             foreach ($params as $param => $value) {
                 // Determine PDO type (optional but good practice)
                 $type = PDO::PARAM_STR;
                 if ($param === ':id') {
                     $type = PDO::PARAM_INT;
                 }
                 $stmt->bindValue($param, $value, $type);
             }
             return $stmt->execute();
         } catch (PDOException $e) {
             error_log("Error updating student ID {$id}: " . $e->getMessage());
             return false;
         }
     }


    public function updateStudentStatus($id, $status) {
        // This can be handled by the general updateStudent method now,
        // but kept here if specific status logic is needed later.
        return $this->updateStudent($id, ['status' => $status]);
    }

    public function deleteStudent($id) {
         try {
            $query = "DELETE FROM students WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
         } catch (PDOException $e) {
             error_log("Error deleting student ID {$id}: " . $e->getMessage());
             return false;
         }
    }
}