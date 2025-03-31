<?php
class Model {
    private $conn;
    private $table = 'models';

    // Propiedades
    public $model_id;
    public $brand_id;
    public $model_name;
    public $category_id;
    public $specifications;
    public $created_at;
    public $updated_at;
    
    // Propiedades adicionales
    public $brand_name;
    public $category_name;

    // Constructor
    public function __construct($db) {
        $this->conn = $db;
    }

    // Obtener todos los modelos
    public function read() {
        $query = 'SELECT m.model_id, m.model_name, m.brand_id, m.category_id, m.specifications, 
                    b.brand_name, c.category_name
                  FROM ' . $this->table . ' m
                  JOIN brands b ON m.brand_id = b.brand_id
                  JOIN hardwarecategories c ON m.category_id = c.category_id
                  ORDER BY m.model_name';
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt;
    }

    // Obtener un modelo específico
    public function read_single() {
        $query = 'SELECT m.model_id, m.model_name, m.brand_id, m.category_id, m.specifications, 
                    m.created_at, m.updated_at, b.brand_name, c.category_name
                  FROM ' . $this->table . ' m
                  JOIN brands b ON m.brand_id = b.brand_id
                  JOIN hardwarecategories c ON m.category_id = c.category_id
                  WHERE m.model_id = :id';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->model_id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row) {
            $this->model_name = $row['model_name'];
            $this->brand_id = $row['brand_id'];
            $this->category_id = $row['category_id'];
            $this->specifications = $row['specifications'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            $this->brand_name = $row['brand_name'];
            $this->category_name = $row['category_name'];
            
            return true;
        }
        
        return false;
    }

    // Crear un nuevo modelo
    public function create() {
        $query = 'INSERT INTO ' . $this->table . ' 
                  (brand_id, model_name, category_id, specifications) 
                  VALUES (:brand_id, :model_name, :category_id, :specifications)';
                  
        $stmt = $this->conn->prepare($query);
        
        // Limpiar datos
        $this->brand_id = htmlspecialchars(strip_tags($this->brand_id));
        $this->model_name = htmlspecialchars(strip_tags($this->model_name));
        $this->category_id = htmlspecialchars(strip_tags($this->category_id));
        $this->specifications = htmlspecialchars(strip_tags($this->specifications));
        
        // Bind params
        $stmt->bindParam(':brand_id', $this->brand_id);
        $stmt->bindParam(':model_name', $this->model_name);
        $stmt->bindParam(':category_id', $this->category_id);
        $stmt->bindParam(':specifications', $this->specifications);
        
        // Ejecutar
        if($stmt->execute()) {
            $this->model_id = $this->conn->lastInsertId();
            return true;
        }
        
        return false;
    }

    // Actualizar un modelo existente
    public function update() {
        $query = 'UPDATE ' . $this->table . ' SET 
                  brand_id = :brand_id, 
                  model_name = :model_name, 
                  category_id = :category_id, 
                  specifications = :specifications 
                  WHERE model_id = :model_id';
                  
        $stmt = $this->conn->prepare($query);
        
        // Limpiar datos
        $this->model_id = htmlspecialchars(strip_tags($this->model_id));
        $this->brand_id = htmlspecialchars(strip_tags($this->brand_id));
        $this->model_name = htmlspecialchars(strip_tags($this->model_name));
        $this->category_id = htmlspecialchars(strip_tags($this->category_id));
        $this->specifications = htmlspecialchars(strip_tags($this->specifications));
        
        // Bind params
        $stmt->bindParam(':model_id', $this->model_id);
        $stmt->bindParam(':brand_id', $this->brand_id);
        $stmt->bindParam(':model_name', $this->model_name);
        $stmt->bindParam(':category_id', $this->category_id);
        $stmt->bindParam(':specifications', $this->specifications);
        
        // Ejecutar
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }

    // Eliminar un modelo
    public function delete() {
        // Verificar si hay hardware asociado
        $query = 'SELECT COUNT(*) as count FROM hardware WHERE model_id = :model_id';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':model_id', $this->model_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row['count'] > 0) {
            return false; // No se puede eliminar si hay hardware asociado
        }
        
        // Proceder con eliminación
        $query = 'DELETE FROM ' . $this->table . ' WHERE model_id = :model_id';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':model_id', $this->model_id);
        
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }

    // Obtener modelos por marca
    public function getByBrand($brand_id) {
        $query = 'SELECT m.model_id, m.model_name, m.brand_id, m.category_id, m.specifications, 
                    b.brand_name, c.category_name
                  FROM ' . $this->table . ' m
                  JOIN brands b ON m.brand_id = b.brand_id
                  JOIN hardwarecategories c ON m.category_id = c.category_id
                  WHERE m.brand_id = :brand_id
                  ORDER BY m.model_name';
                  
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':brand_id', $brand_id);
        $stmt->execute();
        
        return $stmt;
    }
}
?> 