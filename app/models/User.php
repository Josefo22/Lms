<?php
class User {
    private $conn;
    private $table = 'users';

    // Propiedades del usuario según la estructura actual
    public $user_id;
    public $username;
    public $password;
    public $employee_id;
    public $first_name;
    public $last_name;
    public $email;
    public $phone;
    public $job_title;
    public $department;
    public $client_id;
    public $location_id;
    public $is_remote;
    public $status;
    public $role;
    public $created_at;
    public $updated_at;

    // Constructor con conexión a DB
    public function __construct($db) {
        $this->conn = $db;
    }

    // Login usuario usando el email
    public function login($email, $password) {
        $query = 'SELECT * FROM ' . $this->table . ' WHERE email = :email';
        $stmt = $this->conn->prepare($query);
        
        // Bind param 
        $stmt->bindParam(':email', $email);
        
        $stmt->execute();
        
        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // En un sistema real, usar password_verify
            if(password_verify($password, $row['password'])) {
                return $row;
            }
        }
        
        return false;
    }

    // Obtener todos los usuarios
    public function read($client_id = null) {
        // Consulta base
        $query = 'SELECT u.*, c.client_name, l.location_name 
                  FROM ' . $this->table . ' u
                  LEFT JOIN clients c ON u.client_id = c.client_id
                  LEFT JOIN locations l ON u.location_id = l.location_id';
        
        // Filtrar por cliente si se proporciona
        if($client_id) {
            $query .= ' WHERE u.client_id = :client_id';
        }
        
        $query .= ' ORDER BY u.last_name, u.first_name';
        
        $stmt = $this->conn->prepare($query);
        
        // Bind param si es necesario
        if($client_id) {
            $stmt->bindParam(':client_id', $client_id);
        }
        
        $stmt->execute();
        
        return $stmt;
    }

    // Obtener un solo usuario por ID
    public function read_single() {
        $query = 'SELECT u.*, c.client_name, l.location_name 
                  FROM ' . $this->table . ' u
                  LEFT JOIN clients c ON u.client_id = c.client_id
                  LEFT JOIN locations l ON u.location_id = l.location_id
                  WHERE u.user_id = :id';
        
        $stmt = $this->conn->prepare($query);
        
        // Bind param
        $stmt->bindParam(':id', $this->user_id);
        
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row) {
            $this->username = $row['username'] ?? null;
            $this->employee_id = $row['employee_id'];
            $this->first_name = $row['first_name'];
            $this->last_name = $row['last_name'];
            $this->email = $row['email'];
            $this->phone = $row['phone'];
            $this->job_title = $row['job_title'];
            $this->department = $row['department'];
            $this->client_id = $row['client_id'];
            $this->location_id = $row['location_id'];
            $this->is_remote = $row['is_remote'];
            $this->status = $row['status'];
            $this->role = $row['role'] ?? 'user';
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            return true;
        }
        
        return false;
    }

    // Crear un nuevo usuario
    public function create() {
        $query = 'INSERT INTO ' . $this->table . '
                 (username, password, employee_id, first_name, last_name, email, phone, 
                  job_title, department, client_id, location_id, is_remote, status, role) 
                  VALUES 
                 (:username, :password, :employee_id, :first_name, :last_name, :email, :phone, 
                  :job_title, :department, :client_id, :location_id, :is_remote, :status, :role)';
        
        $stmt = $this->conn->prepare($query);
        
        // Limpiar datos
        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->employee_id = htmlspecialchars(strip_tags($this->employee_id));
        $this->first_name = htmlspecialchars(strip_tags($this->first_name));
        $this->last_name = htmlspecialchars(strip_tags($this->last_name));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->phone = htmlspecialchars(strip_tags($this->phone));
        $this->job_title = htmlspecialchars(strip_tags($this->job_title));
        $this->department = htmlspecialchars(strip_tags($this->department));
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->role = htmlspecialchars(strip_tags($this->role));
        
        // Hash password
        $hashed_password = password_hash($this->password, PASSWORD_DEFAULT);
        
        // Bind params
        $stmt->bindParam(':username', $this->username);
        $stmt->bindParam(':password', $hashed_password);
        $stmt->bindParam(':employee_id', $this->employee_id);
        $stmt->bindParam(':first_name', $this->first_name);
        $stmt->bindParam(':last_name', $this->last_name);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':phone', $this->phone);
        $stmt->bindParam(':job_title', $this->job_title);
        $stmt->bindParam(':department', $this->department);
        $stmt->bindParam(':client_id', $this->client_id);
        $stmt->bindParam(':location_id', $this->location_id);
        $stmt->bindParam(':is_remote', $this->is_remote);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':role', $this->role);
        
        // Ejecutar query
        if($stmt->execute()) {
            $this->user_id = $this->conn->lastInsertId();
            return true;
        }
        
        return false;
    }

    // Actualizar usuario
    public function update() {
        // Primero verificar si la contraseña se va a actualizar
        $password_query = '';
        if($this->password && !empty($this->password)) {
            $password_query = ', password = :password';
        }
        
        $query = 'UPDATE ' . $this->table . '
                SET username = :username, 
                    employee_id = :employee_id,
                    first_name = :first_name,
                    last_name = :last_name,
                    email = :email,
                    phone = :phone,
                    job_title = :job_title,
                    department = :department,
                    client_id = :client_id,
                    location_id = :location_id,
                    is_remote = :is_remote,
                    status = :status,
                    role = :role,
                    updated_at = NOW()' 
                    . $password_query . '
                WHERE user_id = :user_id';
        
        $stmt = $this->conn->prepare($query);
        
        // Limpiar datos
        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->employee_id = htmlspecialchars(strip_tags($this->employee_id));
        $this->first_name = htmlspecialchars(strip_tags($this->first_name));
        $this->last_name = htmlspecialchars(strip_tags($this->last_name));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->phone = htmlspecialchars(strip_tags($this->phone));
        $this->job_title = htmlspecialchars(strip_tags($this->job_title));
        $this->department = htmlspecialchars(strip_tags($this->department));
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->role = htmlspecialchars(strip_tags($this->role));
        
        // Bind params
        $stmt->bindParam(':username', $this->username);
        $stmt->bindParam(':employee_id', $this->employee_id);
        $stmt->bindParam(':first_name', $this->first_name);
        $stmt->bindParam(':last_name', $this->last_name);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':phone', $this->phone);
        $stmt->bindParam(':job_title', $this->job_title);
        $stmt->bindParam(':department', $this->department);
        $stmt->bindParam(':client_id', $this->client_id);
        $stmt->bindParam(':location_id', $this->location_id);
        $stmt->bindParam(':is_remote', $this->is_remote);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':role', $this->role);
        $stmt->bindParam(':user_id', $this->user_id);
        
        // Bind password si se va a actualizar
        if($this->password && !empty($this->password)) {
            $hashed_password = password_hash($this->password, PASSWORD_DEFAULT);
            $stmt->bindParam(':password', $hashed_password);
        }
        
        // Ejecutar query
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }

    // Eliminar usuario
    public function delete() {
        // Comprobar si el usuario tiene hardware asignado
        $check_query = 'SELECT COUNT(*) FROM hardware WHERE current_user_id = :user_id';
        $check_stmt = $this->conn->prepare($check_query);
        $check_stmt->bindParam(':user_id', $this->user_id);
        $check_stmt->execute();
        
        if($check_stmt->fetchColumn() > 0) {
            return false; // No se puede eliminar si tiene hardware asignado
        }
        
        // Proceder con la eliminación
        $query = 'DELETE FROM ' . $this->table . ' WHERE user_id = :user_id';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $this->user_id);
        
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    // Buscar usuarios por nombre, email o ID de empleado
    public function search($search_term, $client_id = null) {
        // Consulta base
        $query = 'SELECT u.*, c.client_name, l.location_name 
                  FROM ' . $this->table . ' u
                  LEFT JOIN clients c ON u.client_id = c.client_id
                  LEFT JOIN locations l ON u.location_id = l.location_id
                  WHERE (u.first_name LIKE :search 
                     OR u.last_name LIKE :search 
                     OR u.email LIKE :search 
                     OR u.employee_id LIKE :search)';
        
        // Filtrar por cliente si se proporciona
        if($client_id) {
            $query .= ' AND u.client_id = :client_id';
        }
        
        $query .= ' ORDER BY u.last_name, u.first_name';
        
        $stmt = $this->conn->prepare($query);
        
        // Preparar término de búsqueda
        $search_term = "%{$search_term}%";
        $stmt->bindParam(':search', $search_term);
        
        // Bind client_id si es necesario
        if($client_id) {
            $stmt->bindParam(':client_id', $client_id);
        }
        
        $stmt->execute();
        
        return $stmt;
    }
    
    // Verificar si el email ya existe (para evitar duplicados)
    public function emailExists($email, $exclude_id = null) {
        $query = 'SELECT COUNT(*) FROM ' . $this->table . ' WHERE email = :email';
        
        // Excluir el ID actual si estamos actualizando
        if($exclude_id) {
            $query .= ' AND user_id != :exclude_id';
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        
        if($exclude_id) {
            $stmt->bindParam(':exclude_id', $exclude_id);
        }
        
        $stmt->execute();
        
        return $stmt->fetchColumn() > 0;
    }
    
    // Obtener usuarios por cliente
    public function getByClient($client_id) {
        $query = 'SELECT * FROM ' . $this->table . ' WHERE client_id = :client_id ORDER BY last_name, first_name';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':client_id', $client_id);
        $stmt->execute();
        
        return $stmt;
    }
}
?> 