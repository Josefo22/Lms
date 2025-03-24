<?php
require_once 'config/database.php';
require_once 'app/models/User.php';

class AuthController {
    private $database;
    private $db;
    private $user;

    public function __construct() {
        $this->database = new Database();
        $this->db = $this->database->connect();
        $this->user = new User($this->db);
    }

    public function login($email, $password) {
        try {
            // Verificar si el usuario existe y está activo
            $query = 'SELECT * FROM users WHERE email = :email AND status = "Active"';
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            if($stmt->rowCount() > 0) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Verificar la contraseña
                if(password_verify($password, $user['password'])) {
                    // Crear sesión con los datos del usuario
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['username'] = $user['first_name'] . ' ' . $user['last_name'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['job_title'] = $user['job_title'];
                    $_SESSION['department'] = $user['department'];
                    $_SESSION['status'] = $user['status'];
                    $_SESSION['employee_id'] = $user['employee_id'];
                    
                    return true;
                }
            }
            
            return false;
        } catch (Exception $e) {
            // Guardar error para depuración
            error_log('Error de login: ' . $e->getMessage());
            return false;
        }
    }
    
    public function logout() {
        // Destruir todas las variables de sesión
        $_SESSION = array();
        
        // Destruir la sesión
        session_destroy();
        
        return true;
    }
    
    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
    
    public function getCurrentUser() {
        if($this->isLoggedIn()) {
            $query = 'SELECT * FROM users WHERE user_id = :user_id';
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':user_id', $_SESSION['user_id']);
            $stmt->execute();
            
            if($stmt->rowCount() > 0) {
                return $stmt->fetch(PDO::FETCH_ASSOC);
            }
        }
        return null;
    }
}
?> 