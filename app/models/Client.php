<?php
class Client {
    private $conn;
    private $table_name = "clients";

    public $client_id;
    public $client_name;
    public $contact_person;
    public $contact_email;
    public $contact_phone;
    public $address;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function read() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY client_name";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                SET
                    client_name = :client_name,
                    contact_person = :contact_person,
                    contact_email = :contact_email,
                    contact_phone = :contact_phone,
                    address = :address";

        $stmt = $this->conn->prepare($query);

        $this->client_name = htmlspecialchars(strip_tags($this->client_name));
        $this->contact_person = htmlspecialchars(strip_tags($this->contact_person));
        $this->contact_email = htmlspecialchars(strip_tags($this->contact_email));
        $this->contact_phone = htmlspecialchars(strip_tags($this->contact_phone));
        $this->address = htmlspecialchars(strip_tags($this->address));

        $stmt->bindParam(":client_name", $this->client_name);
        $stmt->bindParam(":contact_person", $this->contact_person);
        $stmt->bindParam(":contact_email", $this->contact_email);
        $stmt->bindParam(":contact_phone", $this->contact_phone);
        $stmt->bindParam(":address", $this->address);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function readOne() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE client_id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->client_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->client_name = $row['client_name'];
        $this->contact_person = $row['contact_person'];
        $this->contact_email = $row['contact_email'];
        $this->contact_phone = $row['contact_phone'];
        $this->address = $row['address'];
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . "
                SET
                    client_name = :client_name,
                    contact_person = :contact_person,
                    contact_email = :contact_email,
                    contact_phone = :contact_phone,
                    address = :address
                WHERE
                    client_id = :client_id";

        $stmt = $this->conn->prepare($query);

        $this->client_name = htmlspecialchars(strip_tags($this->client_name));
        $this->contact_person = htmlspecialchars(strip_tags($this->contact_person));
        $this->contact_email = htmlspecialchars(strip_tags($this->contact_email));
        $this->contact_phone = htmlspecialchars(strip_tags($this->contact_phone));
        $this->address = htmlspecialchars(strip_tags($this->address));
        $this->client_id = htmlspecialchars(strip_tags($this->client_id));

        $stmt->bindParam(":client_name", $this->client_name);
        $stmt->bindParam(":contact_person", $this->contact_person);
        $stmt->bindParam(":contact_email", $this->contact_email);
        $stmt->bindParam(":contact_phone", $this->contact_phone);
        $stmt->bindParam(":address", $this->address);
        $stmt->bindParam(":client_id", $this->client_id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE client_id = ?";
        $stmt = $this->conn->prepare($query);
        $this->client_id = htmlspecialchars(strip_tags($this->client_id));
        $stmt->bindParam(1, $this->client_id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function search($keyword) {
        $query = 'SELECT * FROM ' . $this->table_name . '
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

    public function getHardwareDistribution() {
        $query = 'SELECT c.client_id, c.client_name, COUNT(h.hardware_id) as total
                  FROM ' . $this->table_name . ' c
                  LEFT JOIN hardware h ON c.client_id = h.client_id
                  GROUP BY c.client_id
                  ORDER BY total DESC';
                  
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt;
    }
    
    public function nameExists($name, $exclude_id = null) {
        $query = 'SELECT client_id FROM ' . $this->table_name . ' WHERE client_name = :name';
        
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
?> 