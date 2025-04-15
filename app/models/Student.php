<?php
class Student {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function getAllStudents() {
        $query = "SELECT * FROM students";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addStudent($group, $name, $gender, $birthday) {
        $query = "INSERT INTO students (group_name, name, gender, birthday, status) 
                  VALUES (:group_name, :name, :gender, :birthday, 'offline')";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':group_name', $group);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':gender', $gender);
        $stmt->bindParam(':birthday', $birthday);
        return $stmt->execute();
    }

    public function getLastInsertedStudent() {
        $query = "SELECT * FROM students ORDER BY id DESC LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateStudentStatus($id, $status) {
        $query = "UPDATE students SET status = :status WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function deleteStudent($id) {
        $query = "DELETE FROM students WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}