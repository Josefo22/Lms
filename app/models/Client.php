<?php
class Client {
    private $conn;
    private $table = 'clients';

    // Propiedades
    public $client_id;
    public $client_name;
    public $contact_person;
    public $contact_email;
    public $contact_phone;
    public $address;
    public $created_at;
    public $updated_at;

    // Constructor
    public function __construct($db) {
        $this->conn = $db;
    }

    // Obtener todos los clientes
    public function read() {
        $query = 'SELECT * FROM ' . $this->table . ' ORDER BY client_name';
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt;
    }

    // Obtener un cliente específico
    public function read_single() {
        $query = 'SELECT * FROM ' . $this->table . ' WHERE client_id = :id';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->client_id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row) {
            $this->client_name = $row['client_name'];
            $this->contact_person = $row['contact_person'];
            $this->contact_email = $row['contact_email'];
            $this->contact_phone = $row['contact_phone'];
            $this->address = $row['address'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            
            return true;
        }
        
        return false;
    }

    // Crear un nuevo cliente
    public function create() {
        $query = 'INSERT INTO ' . $this->table . '
                (client_name, contact_person, contact_email, contact_phone, address)
                VALUES
                (:client_name, :contact_person, :contact_email, :contact_phone, :address)';
        
        $stmt = $this->conn->prepare($query);
        
        // Limpiar y asegurar los datos
        $this->client_name = htmlspecialchars(strip_tags($this->client_name));
        $this->contact_person = htmlspecialchars(strip_tags($this->contact_person));
        $this->contact_email = htmlspecialchars(strip_tags($this->contact_email));
        $this->contact_phone = htmlspecialchars(strip_tags($this->contact_phone));
        $this->address = htmlspecialchars(strip_tags($this->address));
        
        // Vincular parámetros
        $stmt->bindParam(':client_name', $this->client_name);
        $stmt->bindParam(':contact_person', $this->contact_person);
        $stmt->bindParam(':contact_email', $this->contact_email);
        $stmt->bindParam(':contact_phone', $this->contact_phone);
        $stmt->bindParam(':address', $this->address);
        
        if($stmt->execute()) {
            $this->client_id = $this->conn->lastInsertId();
            return true;
        }
        
        return false;
    }
    
    // Actualizar cliente existente
    public function update() {
        $query = 'UPDATE ' . $this->table . '
                SET 
                    client_name = :client_name,
                    contact_person = :contact_person,
                    contact_email = :contact_email,
                    contact_phone = :contact_phone,
                    address = :address,
                    updated_at = CURRENT_TIMESTAMP
                WHERE
                    client_id = :client_id';
        
        $stmt = $this->conn->prepare($query);
        
        // Limpiar y asegurar los datos
        $this->client_name = htmlspecialchars(strip_tags($this->client_name));
        $this->contact_person = htmlspecialchars(strip_tags($this->contact_person));
        $this->contact_email = htmlspecialchars(strip_tags($this->contact_email));
        $this->contact_phone = htmlspecialchars(strip_tags($this->contact_phone));
        $this->address = htmlspecialchars(strip_tags($this->address));
        $this->client_id = htmlspecialchars(strip_tags($this->client_id));
        
        // Vincular parámetros
        $stmt->bindParam(':client_name', $this->client_name);
        $stmt->bindParam(':contact_person', $this->contact_person);
        $stmt->bindParam(':contact_email', $this->contact_email);
        $stmt->bindParam(':contact_phone', $this->contact_phone);
        $stmt->bindParam(':address', $this->address);
        $stmt->bindParam(':client_id', $this->client_id);
        
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    // Eliminar cliente
    public function delete() {
        // Primero verificar si hay equipos asociados
        $checkQuery = 'SELECT COUNT(*) as count FROM hardware WHERE client_id = :client_id';
        $checkStmt = $this->conn->prepare($checkQuery);
        $checkStmt->bindParam(':client_id', $this->client_id);
        $checkStmt->execute();
        $result = $checkStmt->fetch(PDO::FETCH_ASSOC);
        
        if($result['count'] > 0) {
            return false; // No se puede eliminar porque tiene equipos asociados
        }
        
        // Si no hay equipos asociados, verificar usuarios
        $checkUserQuery = 'SELECT COUNT(*) as count FROM users WHERE client_id = :client_id';
        $checkUserStmt = $this->conn->prepare($checkUserQuery);
        $checkUserStmt->bindParam(':client_id', $this->client_id);
        $checkUserStmt->execute();
        $userResult = $checkUserStmt->fetch(PDO::FETCH_ASSOC);
        
        if($userResult['count'] > 0) {
            return false; // No se puede eliminar porque tiene usuarios asociados
        }
        
        // Ahora intentar eliminar las ubicaciones relacionadas
        $deleteLocQuery = 'DELETE FROM locations WHERE client_id = :client_id';
        $deleteLocStmt = $this->conn->prepare($deleteLocQuery);
        $deleteLocStmt->bindParam(':client_id', $this->client_id);
        $deleteLocStmt->execute();
        
        // Por último, eliminar el cliente
        $query = 'DELETE FROM ' . $this->table . ' WHERE client_id = :client_id';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':client_id', $this->client_id);
        
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }

    // Buscar clientes
    public function search($keyword) {
        $query = 'SELECT * FROM ' . $this->table . '
                WHERE 
                    client_name LIKE :keyword OR 
                    contact_person LIKE :keyword OR 
                    contact_email LIKE :keyword
                ORDER BY client_name';
        
        $keyword = '%' . $keyword . '%';
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':keyword', $keyword);
        $stmt->execute();
        
        return $stmt;
    }

    // Obtener distribución de hardware por cliente
    public function getHardwareDistribution() {
        $query = 'SELECT c.client_id, c.client_name, COUNT(h.hardware_id) as total
                  FROM ' . $this->table . ' c
                  LEFT JOIN hardware h ON c.client_id = h.client_id
                  GROUP BY c.client_id
                  ORDER BY total DESC';
                  
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt;
    }
    
    // Verificar si existe un cliente por nombre
    public function nameExists($name, $exclude_id = null) {
        $query = 'SELECT client_id FROM ' . $this->table . ' WHERE client_name = :name';
        
        if($exclude_id) {
            $query .= ' AND client_id != :exclude_id';
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':name', $name);
        
        if($exclude_id) {
            $stmt->bindParam(':exclude_id', $exclude_id);
        }
        
        $stmt->execute();
        
        if($stmt->rowCount() > 0) {
            return true;
        }
        
        return false;
    }
}
?> 