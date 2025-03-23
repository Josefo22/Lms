<?php
class HardwareCategory {
    private $conn;
    private $table = 'hardwarecategories';

    // Propiedades
    public $category_id;
    public $category_name;
    public $description;
    public $created_at;
    public $updated_at;

    // Constructor
    public function __construct($db) {
        $this->conn = $db;
    }

    // Obtener todas las categorías
    public function read() {
        $query = 'SELECT * FROM ' . $this->table . ' ORDER BY category_name';
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt;
    }

    // Obtener una categoría específica
    public function read_single() {
        $query = 'SELECT * FROM ' . $this->table . ' WHERE category_id = :id';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->category_id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row) {
            $this->category_name = $row['category_name'];
            $this->description = $row['description'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            
            return true;
        }
        
        return false;
    }

    // Crear una nueva categoría
    public function create() {
        $query = 'INSERT INTO ' . $this->table . ' 
                  (category_name, description) 
                  VALUES (:category_name, :description)';
        $stmt = $this->conn->prepare($query);
        
        // Limpiar datos
        $this->category_name = htmlspecialchars(strip_tags($this->category_name));
        $this->description = htmlspecialchars(strip_tags($this->description));
        
        // Bind params
        $stmt->bindParam(':category_name', $this->category_name);
        $stmt->bindParam(':description', $this->description);
        
        // Ejecutar
        if($stmt->execute()) {
            $this->category_id = $this->conn->lastInsertId();
            return true;
        }
        
        return false;
    }

    // Actualizar una categoría existente
    public function update() {
        $query = 'UPDATE ' . $this->table . ' SET 
                  category_name = :category_name, 
                  description = :description 
                  WHERE category_id = :category_id';
        $stmt = $this->conn->prepare($query);
        
        // Limpiar datos
        $this->category_id = htmlspecialchars(strip_tags($this->category_id));
        $this->category_name = htmlspecialchars(strip_tags($this->category_name));
        $this->description = htmlspecialchars(strip_tags($this->description));
        
        // Bind params
        $stmt->bindParam(':category_id', $this->category_id);
        $stmt->bindParam(':category_name', $this->category_name);
        $stmt->bindParam(':description', $this->description);
        
        // Ejecutar
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }

    // Eliminar una categoría
    public function delete() {
        // Verificar si hay modelos asociados
        $query = 'SELECT COUNT(*) as count FROM models WHERE category_id = :category_id';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':category_id', $this->category_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row['count'] > 0) {
            return false; // No se puede eliminar si hay modelos asociados
        }
        
        // Proceder con eliminación
        $query = 'DELETE FROM ' . $this->table . ' WHERE category_id = :category_id';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':category_id', $this->category_id);
        
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    // Contar equipos por categoría para reportes
    public function countHardwareByCategory() {
        $query = 'SELECT c.category_id, c.category_name, COUNT(h.hardware_id) as total
                  FROM ' . $this->table . ' c
                  LEFT JOIN models m ON c.category_id = m.category_id
                  LEFT JOIN hardware h ON m.model_id = h.model_id
                  GROUP BY c.category_id
                  ORDER BY total DESC';
                  
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt;
    }
}
?> 