<?php
class Hardware {
    private $conn;
    private $table = 'hardware';

    // Propiedades
    public $hardware_id;
    public $serial_number;
    public $asset_tag;
    public $model_id;
    public $purchase_date;
    public $warranty_expiry_date;
    public $status;
    public $condition_status;
    public $notes;
    public $client_id;
    public $location_id;
    public $current_user_id;
    public $created_at;
    public $updated_at;
    
    // Propiedades adicionales de relaciones
    public $model_name;
    public $brand_name;
    public $category_name;
    public $client_name;
    public $location_name;
    public $user_name;

    // Constructor
    public function __construct($db) {
        $this->conn = $db;
    }

    // Obtener lista de hardware con filtros opcionales
    public function read($status = null, $category = null, $client = null) {
        $query = 'SELECT h.*, 
                  m.model_name, b.brand_name, c.category_name,
                  cl.client_name, l.location_name,
                  CONCAT(u.first_name, " ", u.last_name) as user_name
                  FROM ' . $this->table . ' h
                  JOIN models m ON h.model_id = m.model_id
                  JOIN brands b ON m.brand_id = b.brand_id
                  JOIN hardwarecategories c ON m.category_id = c.category_id
                  LEFT JOIN clients cl ON h.client_id = cl.client_id
                  LEFT JOIN locations l ON h.location_id = l.location_id
                  LEFT JOIN users u ON h.current_user_id = u.user_id
                  WHERE 1=1';
        
        $params = [];
        
        // Agregar filtros si existen
        if($status) {
            $query .= ' AND h.status = :status';
            $params[':status'] = $status;
        }
        
        if($category) {
            $query .= ' AND c.category_id = :category';
            $params[':category'] = $category;
        }
        
        if($client) {
            $query .= ' AND h.client_id = :client';
            $params[':client'] = $client;
        }
        
        $query .= ' ORDER BY h.updated_at DESC';
        
        $stmt = $this->conn->prepare($query);
        
        // Bind params si existen
        foreach($params as $param => $value) {
            $stmt->bindValue($param, $value);
        }
        
        $stmt->execute();
        
        return $stmt;
    }

    // Contar hardware por estado
    public function countByStatus($status = null) {
        $query = 'SELECT COUNT(*) as total FROM ' . $this->table;
        
        if($status) {
            $query .= ' WHERE status = :status';
        }
        
        $stmt = $this->conn->prepare($query);
        
        if($status) {
            $stmt->bindParam(':status', $status);
        }
        
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $row['total'];
    }

    // Obtener adquisiciones por mes para el año actual
    public function getMonthlyAcquisitions($year = null) {
        if(!$year) {
            $year = date('Y');
        }
        
        $query = 'SELECT MONTH(purchase_date) as month, COUNT(*) as total 
                  FROM ' . $this->table . ' 
                  WHERE YEAR(purchase_date) = :year 
                  GROUP BY MONTH(purchase_date)
                  ORDER BY month';
                  
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':year', $year);
        $stmt->execute();
        
        $months = array_fill(1, 12, 0); // Inicializar todos los meses en 0
        
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $months[(int)$row['month']] = (int)$row['total'];
        }
        
        return $months;
    }

    // Obtener distribución por categoría de hardware
    public function getDistributionByCategory() {
        $query = 'SELECT c.category_name, COUNT(*) as count 
                 FROM ' . $this->table . ' h
                 JOIN models m ON h.model_id = m.model_id
                 JOIN hardwarecategories c ON m.category_id = c.category_id
                 GROUP BY c.category_id
                 ORDER BY count DESC';
                 
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        $result = [];
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[] = $row;
        }
        
        return $result;
    }

    // Obtener los últimos equipos adquiridos
    public function getRecentAcquisitions($limit = 5) {
        $query = 'SELECT h.hardware_id, h.asset_tag, h.serial_number, h.purchase_date, 
                         h.status, m.model_name, b.brand_name, hc.category_name
                  FROM ' . $this->table . ' h
                  JOIN models m ON h.model_id = m.model_id
                  JOIN brands b ON m.brand_id = b.brand_id
                  JOIN hardwarecategories hc ON m.category_id = hc.category_id
                  ORDER BY h.purchase_date DESC
                  LIMIT :limit';
                  
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt;
    }

    // Leer un solo dispositivo de hardware
    public function read_single() {
        $query = 'SELECT h.*, m.model_name, b.brand_name, hc.category_name,
                  cl.client_name, l.location_name,
                  CONCAT(u.first_name, " ", u.last_name) as user_name
                  FROM ' . $this->table . ' h
                  JOIN models m ON h.model_id = m.model_id
                  JOIN brands b ON m.brand_id = b.brand_id
                  JOIN hardwarecategories hc ON m.category_id = hc.category_id
                  LEFT JOIN clients cl ON h.client_id = cl.client_id
                  LEFT JOIN locations l ON h.location_id = l.location_id
                  LEFT JOIN users u ON h.current_user_id = u.user_id
                  WHERE h.hardware_id = :id';
                  
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->hardware_id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row) {
            $this->serial_number = $row['serial_number'];
            $this->asset_tag = $row['asset_tag'];
            $this->model_id = $row['model_id'];
            $this->purchase_date = $row['purchase_date'];
            $this->warranty_expiry_date = $row['warranty_expiry_date'];
            $this->status = $row['status'];
            $this->condition_status = $row['condition_status'];
            $this->notes = $row['notes'];
            $this->client_id = $row['client_id'];
            $this->location_id = $row['location_id'];
            $this->current_user_id = $row['current_user_id'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            
            // Datos adicionales
            $this->model_name = $row['model_name'];
            $this->brand_name = $row['brand_name'];
            $this->category_name = $row['category_name'];
            $this->client_name = $row['client_name'];
            $this->location_name = $row['location_name'];
            $this->user_name = $row['user_name'];
            
            return true;
        }
        
        return false;
    }
    
    // Crear un nuevo hardware
    public function create() {
        $query = 'INSERT INTO ' . $this->table . ' 
                  (serial_number, asset_tag, model_id, purchase_date, warranty_expiry_date, 
                   status, condition_status, notes, client_id, location_id) 
                  VALUES 
                  (:serial_number, :asset_tag, :model_id, :purchase_date, :warranty_expiry_date, 
                   :status, :condition_status, :notes, :client_id, :location_id)';
                  
        $stmt = $this->conn->prepare($query);
        
        // Limpiar datos
        $this->serial_number = htmlspecialchars(strip_tags($this->serial_number));
        $this->asset_tag = htmlspecialchars(strip_tags($this->asset_tag));
        $this->model_id = htmlspecialchars(strip_tags($this->model_id));
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->condition_status = htmlspecialchars(strip_tags($this->condition_status));
        $this->notes = htmlspecialchars(strip_tags($this->notes));
        
        // Bind params
        $stmt->bindParam(':serial_number', $this->serial_number);
        $stmt->bindParam(':asset_tag', $this->asset_tag);
        $stmt->bindParam(':model_id', $this->model_id);
        $stmt->bindParam(':purchase_date', $this->purchase_date);
        $stmt->bindParam(':warranty_expiry_date', $this->warranty_expiry_date);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':condition_status', $this->condition_status);
        $stmt->bindParam(':notes', $this->notes);
        $stmt->bindParam(':client_id', $this->client_id);
        $stmt->bindParam(':location_id', $this->location_id);
        
        // Ejecutar
        if($stmt->execute()) {
            $this->hardware_id = $this->conn->lastInsertId();
            return true;
        }
        
        return false;
    }
    
    // Actualizar hardware existente
    public function update() {
        $query = 'UPDATE ' . $this->table . ' SET 
                  serial_number = :serial_number, 
                  asset_tag = :asset_tag, 
                  model_id = :model_id, 
                  purchase_date = :purchase_date, 
                  warranty_expiry_date = :warranty_expiry_date, 
                  status = :status, 
                  condition_status = :condition_status, 
                  notes = :notes, 
                  client_id = :client_id, 
                  location_id = :location_id, 
                  current_user_id = :current_user_id 
                  WHERE hardware_id = :hardware_id';
                  
        $stmt = $this->conn->prepare($query);
        
        // Limpiar datos
        $this->hardware_id = htmlspecialchars(strip_tags($this->hardware_id));
        $this->serial_number = htmlspecialchars(strip_tags($this->serial_number));
        $this->asset_tag = htmlspecialchars(strip_tags($this->asset_tag));
        $this->model_id = htmlspecialchars(strip_tags($this->model_id));
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->condition_status = htmlspecialchars(strip_tags($this->condition_status));
        
        if ($this->notes !== null) {
            $this->notes = htmlspecialchars(strip_tags($this->notes));
        }
        
        // Bind params
        $stmt->bindParam(':hardware_id', $this->hardware_id);
        $stmt->bindParam(':serial_number', $this->serial_number);
        $stmt->bindParam(':asset_tag', $this->asset_tag);
        $stmt->bindParam(':model_id', $this->model_id);
        
        // Bind de las fechas
        if ($this->purchase_date === null) {
            $stmt->bindValue(':purchase_date', null, PDO::PARAM_NULL);
        } else {
            $stmt->bindParam(':purchase_date', $this->purchase_date);
        }
        
        if ($this->warranty_expiry_date === null) {
            $stmt->bindValue(':warranty_expiry_date', null, PDO::PARAM_NULL);
        } else {
            $stmt->bindParam(':warranty_expiry_date', $this->warranty_expiry_date);
        }
        
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':condition_status', $this->condition_status);
        $stmt->bindParam(':notes', $this->notes);
        
        // Bind de las claves foráneas con manejo de NULL
        if ($this->client_id === null) {
            $stmt->bindValue(':client_id', null, PDO::PARAM_NULL);
        } else {
            $stmt->bindParam(':client_id', $this->client_id);
        }
        
        if ($this->location_id === null) {
            $stmt->bindValue(':location_id', null, PDO::PARAM_NULL);
        } else {
            $stmt->bindParam(':location_id', $this->location_id);
        }
        
        if ($this->current_user_id === null) {
            $stmt->bindValue(':current_user_id', null, PDO::PARAM_NULL);
        } else {
            $stmt->bindParam(':current_user_id', $this->current_user_id);
        }
        
        // Ejecutar
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    // Eliminar hardware
    public function delete() {
        // Verificar si hay registros en tablas relacionadas
        $query = 'SELECT COUNT(*) as count FROM assignmenthistory WHERE hardware_id = :hardware_id';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':hardware_id', $this->hardware_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row['count'] > 0) {
            return false; // No se puede eliminar si hay registros relacionados
        }
        
        // Proceder con eliminación
        $query = 'DELETE FROM ' . $this->table . ' WHERE hardware_id = :hardware_id';
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':hardware_id', $this->hardware_id);
        
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }

    // Obtener hardware asignado a un usuario específico
    public function getByCurrentUser($user_id) {
        $query = 'SELECT h.*, m.model_name, b.brand_name, c.category_name 
                 FROM ' . $this->table . ' h
                 LEFT JOIN models m ON h.model_id = m.model_id
                 LEFT JOIN brands b ON m.brand_id = b.brand_id
                 LEFT JOIN hardwarecategories c ON m.category_id = c.category_id
                 WHERE h.current_user_id = :user_id
                 ORDER BY h.hardware_id';
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        
        return $stmt;
    }
}
?> 