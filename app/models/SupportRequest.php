<?php
class SupportRequest {
    // Conexión a la base de datos y tabla
    private $conn;
    private $table_name = "supportrequests";

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
    public $created_at;
    public $updated_at;
    
    // Propiedades adicionales para relaciones
    public $user_name;
    public $user_email;
    public $hardware_details;
    public $assigned_to_name;
    public $assigned_name;
    public $serial_number;
    public $asset_tag;
    public $model_id;

    // Constructor con conexión a BD
    public function __construct($db) {
        $this->conn = $db;
    }

    // Leer todas las solicitudes de soporte
    public function read() {
        // Query para seleccionar todos los registros
        $query = "SELECT sr.*, 
                  CONCAT(u.first_name, ' ', u.last_name) as user_name,
                  CONCAT(a.first_name, ' ', a.last_name) as assigned_name,
                  h.serial_number, h.asset_tag
                  FROM " . $this->table_name . " sr
                  LEFT JOIN users u ON sr.user_id = u.user_id
                  LEFT JOIN users a ON sr.assigned_to = a.user_id
                  LEFT JOIN hardware h ON sr.hardware_id = h.hardware_id
                  ORDER BY sr.created_at DESC";

        // Preparar statement
        $stmt = $this->conn->prepare($query);

        // Ejecutar query
        $stmt->execute();

        return $stmt;
    }

    // Crear solicitud de soporte
    public function create() {
        // Query
        $query = "INSERT INTO " . $this->table_name . "
                  SET user_id=:user_id, 
                      request_type=:request_type, 
                      hardware_id=:hardware_id, 
                      description=:description, 
                      priority=:priority, 
                      status=:status,
                      assigned_to=:assigned_to";

        // Preparar statement
        $stmt = $this->conn->prepare($query);

        // Sanear datos
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        $this->request_type = htmlspecialchars(strip_tags($this->request_type));
        $this->hardware_id = $this->hardware_id ? htmlspecialchars(strip_tags($this->hardware_id)) : null;
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->priority = htmlspecialchars(strip_tags($this->priority));
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->assigned_to = $this->assigned_to ? htmlspecialchars(strip_tags($this->assigned_to)) : null;

        // Vincular valores
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":request_type", $this->request_type);
        $stmt->bindParam(":hardware_id", $this->hardware_id);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":priority", $this->priority);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":assigned_to", $this->assigned_to);

        // Ejecutar query
        if($stmt->execute()) {
            return true;
        }

        return false;
    }

    // Leer una solicitud de soporte
    public function readOne() {
        // Query para leer un solo registro
        $query = "SELECT sr.*, 
                  CONCAT(u.first_name, ' ', u.last_name) as user_name,
                  u.email as user_email,
                  CONCAT(a.first_name, ' ', a.last_name) as assigned_name,
                  h.serial_number, h.asset_tag, h.model_id
                  FROM " . $this->table_name . " sr
                  LEFT JOIN users u ON sr.user_id = u.user_id
                  LEFT JOIN users a ON sr.assigned_to = a.user_id
                  LEFT JOIN hardware h ON sr.hardware_id = h.hardware_id
                  WHERE sr.request_id = ?
                  LIMIT 0,1";

        // Preparar statement
        $stmt = $this->conn->prepare($query);

        // Vincular ID
        $stmt->bindParam(1, $this->request_id);

        // Ejecutar query
        $stmt->execute();

        // Obtener fila
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        // Si hay resultados
        if($row) {
            // Asignar valores a propiedades
            $this->request_id = $row['request_id'];
            $this->user_id = $row['user_id'];
            $this->user_name = $row['user_name'];
            $this->user_email = $row['user_email'];
            $this->request_type = $row['request_type'];
            $this->hardware_id = $row['hardware_id'];
            $this->serial_number = $row['serial_number'];
            $this->asset_tag = $row['asset_tag'];
            $this->model_id = $row['model_id'];
            $this->description = $row['description'];
            $this->priority = $row['priority'];
            $this->status = $row['status'];
            $this->assigned_to = $row['assigned_to'];
            $this->assigned_name = $row['assigned_name'];
            $this->resolution_notes = $row['resolution_notes'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            
            return true;
        }

        return false;
    }

    // Actualizar solicitud de soporte
    public function update() {
        // Query
        $query = "UPDATE " . $this->table_name . "
                  SET request_type=:request_type,
                      hardware_id=:hardware_id,
                      description=:description,
                      priority=:priority,
                      status=:status,
                      assigned_to=:assigned_to,
                      resolution_notes=:resolution_notes
                  WHERE request_id=:request_id";

        // Preparar statement
        $stmt = $this->conn->prepare($query);

        // Sanear datos
        $this->request_id = htmlspecialchars(strip_tags($this->request_id));
        $this->request_type = htmlspecialchars(strip_tags($this->request_type));
        $this->hardware_id = $this->hardware_id ? htmlspecialchars(strip_tags($this->hardware_id)) : null;
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->priority = htmlspecialchars(strip_tags($this->priority));
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->assigned_to = $this->assigned_to ? htmlspecialchars(strip_tags($this->assigned_to)) : null;
        $this->resolution_notes = htmlspecialchars(strip_tags($this->resolution_notes));

        // Vincular valores
        $stmt->bindParam(":request_id", $this->request_id);
        $stmt->bindParam(":request_type", $this->request_type);
        $stmt->bindParam(":hardware_id", $this->hardware_id);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":priority", $this->priority);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":assigned_to", $this->assigned_to);
        $stmt->bindParam(":resolution_notes", $this->resolution_notes);

        // Ejecutar query
        if($stmt->execute()) {
            return true;
        }

        return false;
    }

    // Eliminar solicitud de soporte
    public function delete() {
        // Query
        $query = "DELETE FROM " . $this->table_name . " WHERE request_id = ?";

        // Preparar statement
        $stmt = $this->conn->prepare($query);

        // Sanear request_id
        $this->request_id = htmlspecialchars(strip_tags($this->request_id));

        // Vincular ID
        $stmt->bindParam(1, $this->request_id);

        // Ejecutar query
        if($stmt->execute()) {
            return true;
        }

        return false;
    }

    // Obtener solicitudes por usuario
    public function readByUser($user_id) {
        // Query
        $query = "SELECT sr.*, 
                  CONCAT(a.first_name, ' ', a.last_name) as assigned_name,
                  h.serial_number, h.asset_tag
                  FROM " . $this->table_name . " sr
                  LEFT JOIN users a ON sr.assigned_to = a.user_id
                  LEFT JOIN hardware h ON sr.hardware_id = h.hardware_id
                  WHERE sr.user_id = ?
                  ORDER BY sr.created_at DESC";

        // Preparar statement
        $stmt = $this->conn->prepare($query);

        // Vincular ID
        $stmt->bindParam(1, $user_id);

        // Ejecutar query
        $stmt->execute();

        return $stmt;
    }

    // Obtener solicitudes por estado
    public function readByStatus($status) {
        // Query
        $query = "SELECT sr.*, 
                  CONCAT(u.first_name, ' ', u.last_name) as user_name,
                  CONCAT(a.first_name, ' ', a.last_name) as assigned_name,
                  h.serial_number, h.asset_tag
                  FROM " . $this->table_name . " sr
                  LEFT JOIN users u ON sr.user_id = u.user_id
                  LEFT JOIN users a ON sr.assigned_to = a.user_id
                  LEFT JOIN hardware h ON sr.hardware_id = h.hardware_id
                  WHERE sr.status = ?
                  ORDER BY sr.created_at DESC";

        // Preparar statement
        $stmt = $this->conn->prepare($query);

        // Vincular estado
        $stmt->bindParam(1, $status);

        // Ejecutar query
        $stmt->execute();

        return $stmt;
    }

    // Obtener solicitudes asignadas a un técnico
    public function readByAssigned($assigned_to) {
        // Query
        $query = "SELECT sr.*, 
                  CONCAT(u.first_name, ' ', u.last_name) as user_name,
                  h.serial_number, h.asset_tag
                  FROM " . $this->table_name . " sr
                  LEFT JOIN users u ON sr.user_id = u.user_id
                  LEFT JOIN hardware h ON sr.hardware_id = h.hardware_id
                  WHERE sr.assigned_to = ? AND sr.status != 'Closed' AND sr.status != 'Cancelled'
                  ORDER BY sr.priority DESC, sr.created_at ASC";

        // Preparar statement
        $stmt = $this->conn->prepare($query);

        // Vincular ID del técnico
        $stmt->bindParam(1, $assigned_to);

        // Ejecutar query
        $stmt->execute();

        return $stmt;
    }

    // Búsqueda de solicitudes
    public function search($keywords) {
        // Query
        $query = "SELECT sr.*, 
                  CONCAT(u.first_name, ' ', u.last_name) as user_name,
                  CONCAT(a.first_name, ' ', a.last_name) as assigned_name,
                  h.serial_number, h.asset_tag
                  FROM " . $this->table_name . " sr
                  LEFT JOIN users u ON sr.user_id = u.user_id
                  LEFT JOIN users a ON sr.assigned_to = a.user_id
                  LEFT JOIN hardware h ON sr.hardware_id = h.hardware_id
                  WHERE sr.description LIKE ? OR 
                        sr.resolution_notes LIKE ? OR
                        u.first_name LIKE ? OR 
                        u.last_name LIKE ? OR
                        h.serial_number LIKE ? OR
                        h.asset_tag LIKE ?
                  ORDER BY sr.created_at DESC";

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

        // Ejecutar query
        $stmt->execute();

        return $stmt;
    }

    // Obtener conteo por estado
    public function getStatusCounts() {
        $query = "SELECT status, COUNT(*) as count FROM " . $this->table_name . " GROUP BY status";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
    
    // Obtener solicitudes pendientes
    public function getPendingRequests($limit = 5) {
        $query = "SELECT sr.*, 
                  CONCAT(u.first_name, ' ', u.last_name) as user_name,
                  h.asset_tag, m.model_name
                  FROM " . $this->table_name . " sr
                  LEFT JOIN users u ON sr.user_id = u.user_id
                  LEFT JOIN hardware h ON sr.hardware_id = h.hardware_id
                  LEFT JOIN models m ON h.model_id = m.model_id
                  WHERE sr.status IN ('New', 'Assigned', 'In Progress')
                  ORDER BY FIELD(sr.priority, 'Urgent', 'High', 'Medium', 'Low'), sr.created_at ASC
                  LIMIT ?";
                  
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt;
    }
}
?> 