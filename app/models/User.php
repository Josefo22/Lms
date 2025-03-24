<?php
class User {
    // Conexión a la base de datos y tabla
    private $conn;
    private $table_name = "users";

    // Propiedades
    public $user_id;
    public $employee_id;
    public $first_name;
    public $last_name;
    public $email;
    public $password;
    public $phone;
    public $job_title;
    public $department;
    public $client_id;
    public $location_id;
    public $is_remote;
    public $status;
    public $created_at;
    public $updated_at;
    
    // Propiedades adicionales para relaciones
    public $client_name;
    public $contact_person;
    public $contact_email;
    public $contact_phone;
    public $location_name;
    public $location_address;
    public $location_city;
    public $location_state;
    public $location_country;

    // Constructor con conexión a BD
    public function __construct($db) {
        $this->conn = $db;
    }

    // Leer todos los usuarios
    public function read() {
        // Query para seleccionar todos los registros
        $query = "SELECT u.*, 
                  c.client_name, 
                  l.location_name
                  FROM " . $this->table_name . " u
                  LEFT JOIN clients c ON u.client_id = c.client_id
                  LEFT JOIN locations l ON u.location_id = l.location_id
                  ORDER BY u.user_id DESC";

        // Preparar statement
        $stmt = $this->conn->prepare($query);

        // Ejecutar query
        $stmt->execute();

        return $stmt;
    }

    // Crear un nuevo usuario
    public function create() {
        // Query
        $query = "INSERT INTO " . $this->table_name . "
                  SET employee_id=:employee_id, 
                      first_name=:first_name, 
                      last_name=:last_name, 
                      email=:email, 
                      password=:password, 
                      phone=:phone,
                      job_title=:job_title,
                      department=:department,
                      client_id=:client_id,
                      location_id=:location_id,
                      is_remote=:is_remote,
                      status=:status";

        // Preparar statement
        $stmt = $this->conn->prepare($query);

        // Sanear datos
        $this->employee_id = htmlspecialchars(strip_tags($this->employee_id));
        $this->first_name = htmlspecialchars(strip_tags($this->first_name));
        $this->last_name = htmlspecialchars(strip_tags($this->last_name));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->phone = htmlspecialchars(strip_tags($this->phone));
        $this->job_title = htmlspecialchars(strip_tags($this->job_title));
        $this->department = htmlspecialchars(strip_tags($this->department));
        $this->status = htmlspecialchars(strip_tags($this->status));

        // Hash de la contraseña
        $password_hash = password_hash($this->password, PASSWORD_BCRYPT);

        // Vincular valores
        $stmt->bindParam(":employee_id", $this->employee_id);
        $stmt->bindParam(":first_name", $this->first_name);
        $stmt->bindParam(":last_name", $this->last_name);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":password", $password_hash);
        $stmt->bindParam(":phone", $this->phone);
        $stmt->bindParam(":job_title", $this->job_title);
        $stmt->bindParam(":department", $this->department);
        $stmt->bindParam(":client_id", $this->client_id);
        $stmt->bindParam(":location_id", $this->location_id);
        $stmt->bindParam(":is_remote", $this->is_remote);
        $stmt->bindParam(":status", $this->status);

        // Ejecutar query
        if($stmt->execute()) {
            return true;
        }

        return false;
    }

    // Leer un solo usuario
    public function readOne() {
        // Query para leer un registro
        $query = "SELECT u.*, 
                  c.client_name, c.contact_person, c.contact_email, c.contact_phone,
                  l.location_name, l.address, l.city, l.state, l.country
                  FROM " . $this->table_name . " u
                  LEFT JOIN clients c ON u.client_id = c.client_id
                  LEFT JOIN locations l ON u.location_id = l.location_id
                  WHERE u.user_id = ?
                  LIMIT 0,1";

        // Preparar statement
        $stmt = $this->conn->prepare($query);

        // Vincular ID
        $stmt->bindParam(1, $this->user_id);

        // Ejecutar query
        $stmt->execute();

        // Obtener fila
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        // Si hay resultados
        if($row) {
            // Asignar valores a propiedades
            $this->user_id = $row['user_id'];
            $this->employee_id = $row['employee_id'];
            $this->first_name = $row['first_name'];
            $this->last_name = $row['last_name'];
            $this->email = $row['email'];
            $this->phone = $row['phone'];
            $this->job_title = $row['job_title'];
            $this->department = $row['department'];
            $this->client_id = $row['client_id'];
            $this->client_name = $row['client_name'];
            $this->contact_person = $row['contact_person'];
            $this->contact_email = $row['contact_email'];
            $this->contact_phone = $row['contact_phone'];
            $this->location_id = $row['location_id'];
            $this->location_name = $row['location_name'];
            $this->location_address = $row['address'];
            $this->location_city = $row['city'];
            $this->location_state = $row['state'];
            $this->location_country = $row['country'];
            $this->is_remote = $row['is_remote'];
            $this->status = $row['status'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            
            return true;
        }

        return false;
    }

    // Actualizar usuario
    public function update() {
        // Query
        $query = "UPDATE " . $this->table_name . "
                  SET employee_id=:employee_id, 
                      first_name=:first_name, 
                      last_name=:last_name, 
                      email=:email, 
                      phone=:phone,
                      job_title=:job_title,
                      department=:department,
                      client_id=:client_id,
                      location_id=:location_id,
                      is_remote=:is_remote,
                      status=:status
                  WHERE user_id=:user_id";

        // Preparar statement
        $stmt = $this->conn->prepare($query);

        // Sanear datos
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        $this->employee_id = htmlspecialchars(strip_tags($this->employee_id));
        $this->first_name = htmlspecialchars(strip_tags($this->first_name));
        $this->last_name = htmlspecialchars(strip_tags($this->last_name));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->phone = htmlspecialchars(strip_tags($this->phone));
        $this->job_title = htmlspecialchars(strip_tags($this->job_title));
        $this->department = htmlspecialchars(strip_tags($this->department));
        $this->status = htmlspecialchars(strip_tags($this->status));

        // Vincular valores
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":employee_id", $this->employee_id);
        $stmt->bindParam(":first_name", $this->first_name);
        $stmt->bindParam(":last_name", $this->last_name);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":phone", $this->phone);
        $stmt->bindParam(":job_title", $this->job_title);
        $stmt->bindParam(":department", $this->department);
        $stmt->bindParam(":client_id", $this->client_id);
        $stmt->bindParam(":location_id", $this->location_id);
        $stmt->bindParam(":is_remote", $this->is_remote);
        $stmt->bindParam(":status", $this->status);

        // Ejecutar query
        if($stmt->execute()) {
            return true;
        }

        return false;
    }

    // Cambiar contraseña
    public function updatePassword() {
        // Query
        $query = "UPDATE " . $this->table_name . "
                  SET password=:password
                  WHERE user_id=:user_id";

        // Preparar statement
        $stmt = $this->conn->prepare($query);

        // Sanear datos
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));

        // Hash de la contraseña
        $password_hash = password_hash($this->password, PASSWORD_BCRYPT);

        // Vincular valores
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":password", $password_hash);

        // Ejecutar query
        if($stmt->execute()) {
            return true;
        }

        return false;
    }

    // Eliminar usuario
    public function delete() {
        // Query
        $query = "DELETE FROM " . $this->table_name . " WHERE user_id = ?";

        // Preparar statement
        $stmt = $this->conn->prepare($query);

        // Sanear user_id
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));

        // Vincular ID
        $stmt->bindParam(1, $this->user_id);

        // Ejecutar query
        if($stmt->execute()) {
            return true;
        }

        return false;
    }

    // Leer usuarios de un cliente
    public function readByClient($client_id) {
        // Query
        $query = "SELECT u.*, 
                  l.location_name
                  FROM " . $this->table_name . " u
                  LEFT JOIN locations l ON u.location_id = l.location_id
                  WHERE u.client_id = ?
                  ORDER BY u.last_name, u.first_name";

        // Preparar statement
        $stmt = $this->conn->prepare($query);

        // Vincular ID
        $stmt->bindParam(1, $client_id);

        // Ejecutar query
        $stmt->execute();

        return $stmt;
    }

    // Leer usuarios de una ubicación
    public function readByLocation($location_id) {
        // Query
        $query = "SELECT u.*, 
                  c.client_name
                  FROM " . $this->table_name . " u
                  LEFT JOIN clients c ON u.client_id = c.client_id
                  WHERE u.location_id = ?
                  ORDER BY u.last_name, u.first_name";

        // Preparar statement
        $stmt = $this->conn->prepare($query);

        // Vincular ID
        $stmt->bindParam(1, $location_id);

        // Ejecutar query
        $stmt->execute();

        return $stmt;
    }

    // Verificar si es personal de IT
    public function isITStaff() {
        // Query
        $query = "SELECT COUNT(*) as count FROM itstaff WHERE user_id = ?";

        // Preparar statement
        $stmt = $this->conn->prepare($query);

        // Vincular ID
        $stmt->bindParam(1, $this->user_id);

        // Ejecutar query
        $stmt->execute();

        // Obtener fila
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return ($row['count'] > 0);
    }

    // Obtener personal de IT
    public function readITStaff() {
        // Query
        $query = "SELECT u.*, i.role, i.department as it_department
                  FROM " . $this->table_name . " u
                  JOIN itstaff i ON u.user_id = i.user_id
                  ORDER BY u.last_name, u.first_name";

        // Preparar statement
        $stmt = $this->conn->prepare($query);

        // Ejecutar query
        $stmt->execute();

        return $stmt;
    }

    // Autenticar usuario
    public function login() {
        // Query para verificar si el email existe
        $query = "SELECT user_id, first_name, last_name, email, password, status FROM " . $this->table_name . " WHERE email = ? LIMIT 0,1";

        // Preparar statement
        $stmt = $this->conn->prepare($query);

        // Sanear email
        $this->email = htmlspecialchars(strip_tags($this->email));

        // Vincular email
        $stmt->bindParam(1, $this->email);

        // Ejecutar query
        $stmt->execute();

        // Obtener fila
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        // Si hay resultados
        if($row) {
            // Verificar si el usuario está activo
            if($row['status'] != 'Active') {
                return false;
            }

            // Verificar la contraseña
            if(password_verify($this->password, $row['password'])) {
                // Asignar valores a propiedades
                $this->user_id = $row['user_id'];
                $this->first_name = $row['first_name'];
                $this->last_name = $row['last_name'];
                return true;
            }
        }

        return false;
    }

    // Buscar usuarios
    public function search($keywords) {
        // Query
        $query = "SELECT u.*, 
                  c.client_name, 
                  l.location_name
                  FROM " . $this->table_name . " u
                  LEFT JOIN clients c ON u.client_id = c.client_id
                  LEFT JOIN locations l ON u.location_id = l.location_id
                  WHERE u.first_name LIKE ? OR 
                        u.last_name LIKE ? OR 
                        u.email LIKE ? OR 
                        u.employee_id LIKE ? OR 
                        u.job_title LIKE ? OR
                        u.department LIKE ? OR
                        c.client_name LIKE ? OR
                        l.location_name LIKE ?
                  ORDER BY u.last_name, u.first_name";

        // Preparar statement
        $stmt = $this->conn->prepare($query);

        // Sanitizar keywords
        $keywords = htmlspecialchars(strip_tags($keywords));
        $keywords = "%{$keywords}%";

        // Vincular keywords
        $stmt->bindParam(1, $keywords);
        $stmt->bindParam(2, $keywords);
        $stmt->bindParam(3, $keywords);
        $stmt->bindParam(4, $keywords);
        $stmt->bindParam(5, $keywords);
        $stmt->bindParam(6, $keywords);
        $stmt->bindParam(7, $keywords);
        $stmt->bindParam(8, $keywords);

        // Ejecutar query
        $stmt->execute();

        return $stmt;
    }
}
?> 