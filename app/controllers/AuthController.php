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
            $result = $this->user->login($email, $password);
            
            if($result) {
                // Crear sesión con los datos actualizados
                $_SESSION['user_id'] = $result['user_id'];
                $_SESSION['username'] = $result['first_name'] . ' ' . $result['last_name']; // Nombre completo como username
                $_SESSION['email'] = $result['email'];
                $_SESSION['job_title'] = $result['job_title'];
                $_SESSION['department'] = $result['department'];
                $_SESSION['status'] = $result['status'];
                
                return true;
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
            $this->user->user_id = $_SESSION['user_id'];
            $this->user->read_single();
            return $this->user;
        }
        return null;
    }
}
?> 