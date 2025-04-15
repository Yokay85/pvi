<?php
class Database {
    private $host = '127.0.0.1';
    private $db_name = 'student_manager';
    private $username = 'root';
    private $password = '753951Vdo';
    private $socket = '/tmp/mysql.sock';
    private $conn = null;

    public function getConnection() {
        try {
            $this->conn = new PDO(
//                "mysql:host=$this->host;dbname=$this->db_name;unix_socket=$this->socket;charset=utf8mb4",
                "mysql:host=$this->host;dbname=$this->db_name;charset=utf8mb4",
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $this->conn;
        } catch (PDOException $e) {
            echo "Connection Error: " . $e->getMessage();
            return null;
        }
    }
}