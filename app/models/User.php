<?php
// Handles database operations related to users.
class User {
    private $db; // Database connection object.

    // Establishes a database connection upon instantiation.
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection(); // Get PDO connection.
        // Check for connection errors.
        if ($this->db === null) {
            throw new Exception("Database connection failed in User model.");
        }
    }

    // Creates a new user account in the database.
    public function createUserAccount($name, $group, $birthday, $role = 'student') { // Role parameter added.
        // Generate username from name.
        $username = strtolower(str_replace(' ', '', $name));
        // Generate email address.
        $email = !empty($username) && !empty($group) ? strtolower($username . '.' . $group . '@lpnu.ua') : null;

        // Validate required data before proceeding.
        if (empty($username) || empty($email) || empty($birthday)) {
             error_log("User creation failed: Missing required data (username, email, or birthday).");
             return false;
        }

        // Generate password from birthday.
        $passwordFromDate = str_replace('-', '', $birthday);
        // Validate generated password.
        if (empty($passwordFromDate)) {
            error_log("User creation failed: Could not generate password from birthday.");
            return false;
        }

        // Hash the generated password.
        $hashedPassword = password_hash($passwordFromDate, PASSWORD_DEFAULT);

        // Prepare and execute the SQL query to insert the new user.
        try {
            $query = 'INSERT INTO users (username, email, password, role) VALUES (:username, :email, :password, :role)';
            $stmt = $this->db->prepare($query);

            // Bind parameters to the prepared statement.
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->bindParam(':role', $role); // Bind the role.

            // Execute the statement and return success status.
            return $stmt->execute();
        } catch (PDOException $e) {
            // Log database errors and return false.
            error_log("Error creating user account: " . $e->getMessage());
            return false;
        }
    }
}
