<?php
class SupportRequest {
    private $conn;
    private $table = 'supportrequests';

    // Propiedades
    public $request_id;
    public $user_id;
    public $request_type;
    public $hardware_id;
    public $description;
    public $priority;
    public $status;
    public $assigned_to;
    public $resolution_notes;
    public $client_id;
    public $created_at;
    public $updated_at;
    
    // Propiedades adicionales para relaciones
    public $user_name;
    public $hardware_details;
    public $assigned_to_name;
    public $client_name;

    // Constructor
    public function __construct($db) {
        $this->conn = $db;
    }

    // Obtener las solicitudes de soporte pendientes
    public function getPendingRequests($limit = 5) {
        $query = 'SELECT sr.*, 
                  CONCAT(u.first_name, " ", u.last_name) as user_name,
                  CONCAT(a.first_name, " ", a.last_name) as assigned_to_name,
                  h.asset_tag, m.model_name, b.brand_name
                  FROM ' . $this->table . ' sr
                  JOIN users u ON sr.user_id = u.user_id
                  LEFT JOIN users a ON sr.assigned_to = a.user_id
                  LEFT JOIN hardware h ON sr.hardware_id = h.hardware_id
                  LEFT JOIN models m ON h.model_id = m.model_id
                  LEFT JOIN brands b ON m.brand_id = b.brand_id
                  WHERE sr.status IN ("New", "Assigned", "In Progress")
                  ORDER BY 
                    CASE sr.priority
                        WHEN "Urgent" THEN 1
                        WHEN "High" THEN 2
                        WHEN "Medium" THEN 3
                        WHEN "Low" THEN 4
                    END,
                    sr.created_at ASC
                  LIMIT :limit';
                  
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt;
    }

    // Contar solicitudes por estado
    public function countByStatus($status = null) {
        $query = 'SELECT COUNT(*) as total FROM ' . $this->table;
        
        if($status) {
            $query .= ' WHERE status = :status';
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':status', $status);
        } else {
            $stmt = $this->conn->prepare($query);
        }
        
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $row['total'];
    }

    // Crear una nueva solicitud de soporte
    public function create() {
        $query = 'INSERT INTO ' . $this->table . ' 
                  (user_id, request_type, hardware_id, description, priority, status, client_id) 
                  VALUES (:user_id, :request_type, :hardware_id, :description, :priority, :status, :client_id)';
                  
        $stmt = $this->conn->prepare($query);
        
        // Limpiar datos
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        $this->request_type = htmlspecialchars(strip_tags($this->request_type));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->priority = htmlspecialchars(strip_tags($this->priority));
        
        // Establecer valores predeterminados
        if(!$this->status) {
            $this->status = 'New';
        }
        
        // Bind params
        $stmt->bindParam(':user_id', $this->user_id);
        $stmt->bindParam(':request_type', $this->request_type);
        $stmt->bindParam(':hardware_id', $this->hardware_id);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':priority', $this->priority);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':client_id', $this->client_id);
        
        // Ejecutar
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }

    // Actualizar estado de una solicitud
    public function updateStatus() {
        $query = 'UPDATE ' . $this->table . ' SET 
                  status = :status, 
                  assigned_to = :assigned_to,
                  resolution_notes = :resolution_notes,
                  client_id = :client_id,
                  updated_at = NOW() 
                  WHERE request_id = :request_id';
                  
        $stmt = $this->conn->prepare($query);
        
        // Limpiar datos
        $this->request_id = htmlspecialchars(strip_tags($this->request_id));
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->resolution_notes = htmlspecialchars(strip_tags($this->resolution_notes));
        
        // Bind params
        $stmt->bindParam(':request_id', $this->request_id);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':assigned_to', $this->assigned_to);
        $stmt->bindParam(':resolution_notes', $this->resolution_notes);
        $stmt->bindParam(':client_id', $this->client_id);
        
        // Ejecutar
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }
}
?> 