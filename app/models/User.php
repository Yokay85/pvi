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

    // Checks if a user with the given username or email already exists.
    public function checkUserExists($username, $email) {
        try {
            $query = 'SELECT COUNT(*) FROM users WHERE username = :username OR email = :email';
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            $count = (int)$stmt->fetchColumn();
            
            return $count > 0;
        } catch (PDOException $e) {
            error_log("Error checking if user exists: " . $e->getMessage());
            // Return true (as if user exists) to prevent creation on error - safer approach
            return true;
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

    // Verifies user credentials against the database.
    public function verifyUser($usernameOrEmail, $password) {
        try {
            // Query to find user by username or email.
            $query = 'SELECT id, username, password, role FROM users WHERE username = :identifier OR email = :identifier LIMIT 1'; // Added role to SELECT
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':identifier', $usernameOrEmail);
            $stmt->execute();

            if ($stmt->rowCount() == 1) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                // Verify the provided password against the stored hash.
                if (password_verify($password, $user['password'])) {
                    // Password is correct, return user data (excluding password).
                    return ['id' => $user['id'], 'username' => $user['username'], 'role' => $user['role']]; // Return role
                }
            }
            // User not found or password incorrect.
            return false;
        } catch (PDOException $e) {
            error_log("Error verifying user: " . $e->getMessage());
            return false;
        }
    }

    // Updates user account details based on student information changes.
    // Needs old info to find the user and new info for the updates.
    public function updateUserAccountByStudentInfo($oldName, $oldGroup, $newName, $newGroup, $newBirthday, $newRole) {
        // Derive old username/email to find the user
        $oldUsername = strtolower(str_replace(' ', '', $oldName));
        $oldEmail = !empty($oldUsername) && !empty($oldGroup) ? strtolower($oldUsername . '.' . $oldGroup . '@lpnu.ua') : null;

        if (empty($oldUsername) || empty($oldEmail)) {
            error_log("User update failed: Could not derive identifier for old student info: {$oldName}, group: {$oldGroup}.");
            return false; // Cannot identify the user to update
        }

        // Derive new details
        $newUsername = strtolower(str_replace(' ', '', $newName));
        $newEmail = !empty($newUsername) && !empty($newGroup) ? strtolower($newUsername . '.' . $newGroup . '@lpnu.ua') : null;
        $newPasswordFromDate = str_replace('-', '', $newBirthday);

        // Validate new required data
        if (empty($newUsername) || empty($newEmail) || empty($newPasswordFromDate) || empty($newRole)) {
             error_log("User update failed: Missing required new data (username, email, birthday, or role).");
             return false;
        }
        $newHashedPassword = password_hash($newPasswordFromDate, PASSWORD_DEFAULT);


        try {
            // Find user by old username or email
            $findQuery = 'SELECT id FROM users WHERE username = :username OR email = :email LIMIT 1';
            $findStmt = $this->db->prepare($findQuery);
            $findStmt->bindParam(':username', $oldUsername);
            $findStmt->bindParam(':email', $oldEmail);
            $findStmt->execute();
            $user = $findStmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                error_log("User update failed: No user found for old student info: {$oldName}, group: {$oldGroup}. Attempting to create user instead.");
                // If user not found, maybe create them?
                return $this->createUserAccount($newName, $newGroup, $newBirthday, $newRole);
            }

            // User found, proceed with update
            $updateQuery = 'UPDATE users SET username = :newUsername, email = :newEmail, password = :newPassword, role = :newRole WHERE id = :id';
            $updateStmt = $this->db->prepare($updateQuery);
            $updateStmt->bindParam(':newUsername', $newUsername);
            $updateStmt->bindParam(':newEmail', $newEmail);
            $updateStmt->bindParam(':newPassword', $newHashedPassword);
            $updateStmt->bindParam(':newRole', $newRole);
            $updateStmt->bindParam(':id', $user['id'], PDO::PARAM_INT);

            $success = $updateStmt->execute();

            if (!$success) {
                 error_log("User update failed during execution for user ID: {$user['id']}.");
            }
            return $success;

        } catch (PDOException $e) {
            // Check for duplicate entry error (e.g., if new username/email already exists)
            if ($e->getCode() == 23000) { // Integrity constraint violation
                 error_log("User update failed for user ID {$user['id']}: New username '{$newUsername}' or email '{$newEmail}' might already exist. Error: " . $e->getMessage());
            } else {
                error_log("Error updating user account for old student {$oldName}, group {$oldGroup}: " . $e->getMessage());
            }
            return false;
        }
    }


    // Deletes a user account based on student name and group.
    public function deleteUserAccountByStudentInfo($name, $group) {
        // Derive username and email like in createUserAccount
        $username = strtolower(str_replace(' ', '', $name));
        $email = !empty($username) && !empty($group) ? strtolower($username . '.' . $group . '@lpnu.ua') : null;

        if (empty($username) || empty($email)) {
            error_log("User deletion failed: Could not derive identifier for student: {$name}, group: {$group}.");
            return false; // Cannot identify user
        }

        try {
            // Find user by username or email to ensure we delete the correct one
            $findQuery = 'SELECT id FROM users WHERE username = :username OR email = :email LIMIT 1';
            $findStmt = $this->db->prepare($findQuery);
            $findStmt->bindParam(':username', $username);
            $findStmt->bindParam(':email', $email);
            $findStmt->execute();
            $user = $findStmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                error_log("User deletion failed: No user found for student: {$name}, group: {$group} (derived username: {$username}, email: {$email}).");
                return true; // Or false? If user doesn't exist, deletion is technically successful in a way. Let's return true.
            }

            // User found, proceed with deletion
            $deleteQuery = 'DELETE FROM users WHERE id = :id';
            $deleteStmt = $this->db->prepare($deleteQuery);
            $deleteStmt->bindParam(':id', $user['id'], PDO::PARAM_INT);
            $success = $deleteStmt->execute();

            if (!$success) {
                 error_log("User deletion failed during execution for user ID: {$user['id']} (student: {$name}, group: {$group}).");
            }
            return $success;

        } catch (PDOException $e) {
            error_log("Error deleting user account for student {$name}, group {$group}: " . $e->getMessage());
            return false;
        }
    }
}
