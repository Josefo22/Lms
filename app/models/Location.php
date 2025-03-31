<?php
class Location {
    private $conn;
    private $table = 'locations';

    // Propiedades
    public $location_id;
    public $location_name;
    public $address;
    public $city;
    public $state;
    public $postal_code;
    public $country;
    public $contact_person;
    public $contact_phone;
    public $contact_email;
    public $notes;
    public $client_id;
    public $client_name;
    public $created_at;
    public $updated_at;

    // Constructor
    public function __construct($db) {
        $this->conn = $db;
    }

    // Obtener todas las ubicaciones
    public function read() {
        $query = 'SELECT l.*, c.client_name 
                  FROM ' . $this->table . ' l
                  LEFT JOIN clients c ON l.client_id = c.client_id
                  ORDER BY l.location_name';
                  
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt;
    }

    // Obtener ubicaciones por cliente
    public function readByClient($client_id) {
        $query = 'SELECT l.*, c.client_name 
                  FROM ' . $this->table . ' l
                  LEFT JOIN clients c ON l.client_id = c.client_id
                  WHERE l.client_id = :client_id
                  ORDER BY l.location_name';
                  
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':client_id', $client_id);
        $stmt->execute();
        
        return $stmt;
    }

    // Obtener una ubicación específica
    public function read_single() {
        $query = 'SELECT l.*, c.client_name 
                  FROM ' . $this->table . ' l
                  LEFT JOIN clients c ON l.client_id = c.client_id 
                  WHERE l.location_id = :id';
                  
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->location_id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row) {
            $this->location_name = $row['location_name'];
            $this->address = $row['address'];
            $this->city = $row['city'];
            $this->state = $row['state'];
            $this->postal_code = $row['postal_code'];
            $this->country = $row['country'];
            $this->contact_person = $row['contact_person'];
            $this->contact_phone = $row['contact_phone'];
            $this->contact_email = $row['contact_email'];
            $this->notes = $row['notes'];
            $this->client_id = $row['client_id'];
            $this->client_name = $row['client_name'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            
            return true;
        }
        
        return false;
    }

    // Crear una nueva ubicación
    public function create() {
        $query = 'INSERT INTO ' . $this->table . ' 
                  (location_name, address, city, state, postal_code, country, 
                   contact_person, contact_phone, contact_email, notes, client_id) 
                  VALUES 
                  (:location_name, :address, :city, :state, :postal_code, :country, 
                   :contact_person, :contact_phone, :contact_email, :notes, :client_id)';
                  
        $stmt = $this->conn->prepare($query);
        
        // Limpiar datos
        $this->location_name = htmlspecialchars(strip_tags($this->location_name));
        $this->address = htmlspecialchars(strip_tags($this->address));
        $this->city = htmlspecialchars(strip_tags($this->city));
        $this->state = htmlspecialchars(strip_tags($this->state));
        $this->postal_code = htmlspecialchars(strip_tags($this->postal_code));
        $this->country = htmlspecialchars(strip_tags($this->country));
        $this->contact_person = htmlspecialchars(strip_tags($this->contact_person));
        $this->contact_phone = htmlspecialchars(strip_tags($this->contact_phone));
        $this->contact_email = htmlspecialchars(strip_tags($this->contact_email));
        $this->notes = htmlspecialchars(strip_tags($this->notes));
        
        // Bind params
        $stmt->bindParam(':location_name', $this->location_name);
        $stmt->bindParam(':address', $this->address);
        $stmt->bindParam(':city', $this->city);
        $stmt->bindParam(':state', $this->state);
        $stmt->bindParam(':postal_code', $this->postal_code);
        $stmt->bindParam(':country', $this->country);
        $stmt->bindParam(':contact_person', $this->contact_person);
        $stmt->bindParam(':contact_phone', $this->contact_phone);
        $stmt->bindParam(':contact_email', $this->contact_email);
        $stmt->bindParam(':notes', $this->notes);
        $stmt->bindParam(':client_id', $this->client_id);
        
        // Ejecutar
        if($stmt->execute()) {
            $this->location_id = $this->conn->lastInsertId();
            return true;
        }
        
        return false;
    }

    // Actualizar ubicación existente
    public function update() {
        $query = 'UPDATE ' . $this->table . ' SET 
                  location_name = :location_name,
                  address = :address,
                  city = :city,
                  state = :state,
                  postal_code = :postal_code,
                  country = :country,
                  contact_person = :contact_person,
                  contact_phone = :contact_phone,
                  contact_email = :contact_email,
                  notes = :notes,
                  client_id = :client_id
                  WHERE location_id = :location_id';
                  
        $stmt = $this->conn->prepare($query);
        
        // Limpiar datos
        $this->location_id = htmlspecialchars(strip_tags($this->location_id));
        $this->location_name = htmlspecialchars(strip_tags($this->location_name));
        $this->address = htmlspecialchars(strip_tags($this->address));
        $this->city = htmlspecialchars(strip_tags($this->city));
        $this->state = htmlspecialchars(strip_tags($this->state));
        $this->postal_code = htmlspecialchars(strip_tags($this->postal_code));
        $this->country = htmlspecialchars(strip_tags($this->country));
        $this->contact_person = htmlspecialchars(strip_tags($this->contact_person));
        $this->contact_phone = htmlspecialchars(strip_tags($this->contact_phone));
        $this->contact_email = htmlspecialchars(strip_tags($this->contact_email));
        $this->notes = htmlspecialchars(strip_tags($this->notes));
        
        // Bind params
        $stmt->bindParam(':location_id', $this->location_id);
        $stmt->bindParam(':location_name', $this->location_name);
        $stmt->bindParam(':address', $this->address);
        $stmt->bindParam(':city', $this->city);
        $stmt->bindParam(':state', $this->state);
        $stmt->bindParam(':postal_code', $this->postal_code);
        $stmt->bindParam(':country', $this->country);
        $stmt->bindParam(':contact_person', $this->contact_person);
        $stmt->bindParam(':contact_phone', $this->contact_phone);
        $stmt->bindParam(':contact_email', $this->contact_email);
        $stmt->bindParam(':notes', $this->notes);
        $stmt->bindParam(':client_id', $this->client_id);
        
        // Ejecutar
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }

    // Eliminar ubicación
    public function delete() {
        // Verificar si hay hardware asociado a esta ubicación
        $query = 'SELECT COUNT(*) as count FROM hardware WHERE location_id = :location_id';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':location_id', $this->location_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row['count'] > 0) {
            return false; // No se puede eliminar si hay hardware asociado
        }
        
        // Proceder con eliminación
        $query = 'DELETE FROM ' . $this->table . ' WHERE location_id = :location_id';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':location_id', $this->location_id);
        
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }

    // Contar hardware por ubicación
    public function countHardwareByLocation() {
        $query = 'SELECT l.location_id, l.location_name, COUNT(h.hardware_id) as total
                  FROM ' . $this->table . ' l
                  LEFT JOIN hardware h ON l.location_id = h.location_id
                  GROUP BY l.location_id
                  ORDER BY total DESC';
                  
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt;
    }
}
?> 