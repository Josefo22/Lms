<?php
class Brand {
    private $conn;
    private $table = 'brands';

    // Propiedades
    public $brand_id;
    public $brand_name;
    public $created_at;
    public $updated_at;

    // Constructor
    public function __construct($db) {
        $this->conn = $db;
    }

    // Obtener todas las marcas
    public function read() {
        $query = 'SELECT * FROM ' . $this->table . ' ORDER BY brand_name';
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt;
    }

    // Obtener una marca específica
    public function read_single() {
        $query = 'SELECT * FROM ' . $this->table . ' WHERE brand_id = :id';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->brand_id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row) {
            $this->brand_name = $row['brand_name'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            
            return true;
        }
        
        return false;
    }

    // Crear una nueva marca
    public function create() {
        $query = 'INSERT INTO ' . $this->table . ' (brand_name) VALUES (:brand_name)';
        $stmt = $this->conn->prepare($query);
        
        // Limpiar datos
        $this->brand_name = htmlspecialchars(strip_tags($this->brand_name));
        
        // Bind param
        $stmt->bindParam(':brand_name', $this->brand_name);
        
        // Ejecutar
        if($stmt->execute()) {
            $this->brand_id = $this->conn->lastInsertId();
            return true;
        }
        
        return false;
    }

    // Actualizar una marca existente
    public function update() {
        $query = 'UPDATE ' . $this->table . ' SET brand_name = :brand_name WHERE brand_id = :brand_id';
        $stmt = $this->conn->prepare($query);
        
        // Limpiar datos
        $this->brand_id = htmlspecialchars(strip_tags($this->brand_id));
        $this->brand_name = htmlspecialchars(strip_tags($this->brand_name));
        
        // Bind params
        $stmt->bindParam(':brand_id', $this->brand_id);
        $stmt->bindParam(':brand_name', $this->brand_name);
        
        // Ejecutar
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }

    // Eliminar una marca
    public function delete() {
        // Verificar si hay modelos asociados
        $query = 'SELECT COUNT(*) as count FROM models WHERE brand_id = :brand_id';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':brand_id', $this->brand_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row['count'] > 0) {
            return false; // No se puede eliminar si hay modelos asociados
        }
        
        // Proceder con eliminación
        $query = 'DELETE FROM ' . $this->table . ' WHERE brand_id = :brand_id';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':brand_id', $this->brand_id);
        
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    // Contar equipos por marca
    public function countHardwareByBrand() {
        $query = 'SELECT b.brand_id, b.brand_name, COUNT(h.hardware_id) as total
                  FROM ' . $this->table . ' b
                  LEFT JOIN models m ON b.brand_id = m.brand_id
                  LEFT JOIN hardware h ON m.model_id = h.model_id
                  GROUP BY b.brand_id
                  ORDER BY total DESC';
                  
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt;
    }
}
?> 