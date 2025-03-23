<?php
class AssignmentHistory {
    private $conn;
    private $table = 'assignmenthistory';

    // Propiedades
    public $assignment_id;
    public $hardware_id;
    public $user_id;
    public $assigned_date;
    public $return_date;
    public $assigned_by;
    public $return_received_by;
    public $assignment_notes;
    public $return_notes;
    public $status;
    public $created_at;
    public $updated_at;
    
    // Propiedades adicionales para relaciones
    public $hardware_details;
    public $user_name;
    public $assigned_by_name;
    public $return_received_by_name;
    public $hardware_serial;
    public $hardware_asset_tag;
    public $client_id;
    public $assignment_date;
    public $notes;

    // Constructor
    public function __construct($db) {
        $this->conn = $db;
    }

    // Obtener los últimos movimientos
    public function getRecentMovements($limit = 5) {
        $query = 'SELECT ah.assignment_id, ah.hardware_id, ah.user_id, ah.assigned_date, 
                  ah.return_date, ah.status, ah.assigned_by,
                  CONCAT(u.first_name, " ", u.last_name) as user_name,
                  CONCAT(a.first_name, " ", a.last_name) as assigned_by_name,
                  h.asset_tag, m.model_name, b.brand_name
                  FROM ' . $this->table . ' ah
                  JOIN users u ON ah.user_id = u.user_id
                  JOIN users a ON ah.assigned_by = a.user_id
                  JOIN hardware h ON ah.hardware_id = h.hardware_id
                  JOIN models m ON h.model_id = m.model_id
                  JOIN brands b ON m.brand_id = b.brand_id
                  ORDER BY ah.assigned_date DESC
                  LIMIT :limit';
                  
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt;
    }

    // Obtener historial de un hardware específico
    public function getHardwareHistory($hardware_id) {
        $query = 'SELECT a.*, 
                  CONCAT(u.first_name, " ", u.last_name) as user_name,
                  CONCAT(ab.first_name, " ", ab.last_name) as assigned_by_name,
                  CONCAT(rb.first_name, " ", rb.last_name) as return_received_by_name,
                  h.serial_number as hardware_serial, h.asset_tag as hardware_asset_tag
                  FROM ' . $this->table . ' a
                  LEFT JOIN users u ON a.user_id = u.user_id
                  LEFT JOIN users ab ON a.assigned_by = ab.user_id
                  LEFT JOIN users rb ON a.return_received_by = rb.user_id
                  LEFT JOIN hardware h ON a.hardware_id = h.hardware_id
                  WHERE a.hardware_id = :hardware_id
                  ORDER BY a.assigned_date DESC';
                  
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':hardware_id', $hardware_id);
        $stmt->execute();
        
        return $stmt;
    }

    // Crear un nuevo registro de asignación
    public function create() {
        $query = 'INSERT INTO ' . $this->table . ' 
                  (hardware_id, user_id, assigned_date, assigned_by, assignment_notes, status) 
                  VALUES 
                  (:hardware_id, :user_id, :assigned_date, :assigned_by, :assignment_notes, :status)';
                  
        $stmt = $this->conn->prepare($query);
        
        // Limpiar datos
        $this->hardware_id = htmlspecialchars(strip_tags($this->hardware_id));
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        $this->assigned_by = htmlspecialchars(strip_tags($this->assigned_by));
        $this->assignment_notes = htmlspecialchars(strip_tags($this->assignment_notes));
        $this->status = 'Assigned';
        
        // Si no se proporciona fecha, usar la actual
        if(!$this->assigned_date) {
            $this->assigned_date = date('Y-m-d H:i:s');
        }
        
        // Bind params
        $stmt->bindParam(':hardware_id', $this->hardware_id);
        $stmt->bindParam(':user_id', $this->user_id);
        $stmt->bindParam(':assigned_date', $this->assigned_date);
        $stmt->bindParam(':assigned_by', $this->assigned_by);
        $stmt->bindParam(':assignment_notes', $this->assignment_notes);
        $stmt->bindParam(':status', $this->status);
        
        // Ejecutar
        if($stmt->execute()) {
            $this->assignment_id = $this->conn->lastInsertId();
            // Actualizar el current_user_id en la tabla hardware
            $query = 'UPDATE hardware SET current_user_id = :user_id, status = "In Use", updated_at = NOW() 
                      WHERE hardware_id = :hardware_id';
            $updateStmt = $this->conn->prepare($query);
            $updateStmt->bindParam(':user_id', $this->user_id);
            $updateStmt->bindParam(':hardware_id', $this->hardware_id);
            $updateStmt->execute();
            
            return true;
        }
        
        return false;
    }

    // Registrar devolución
    public function registerReturn() {
        $query = 'UPDATE ' . $this->table . ' SET 
                  return_date = :return_date, 
                  return_received_by = :return_received_by, 
                  return_notes = :return_notes, 
                  status = "Returned", 
                  updated_at = NOW() 
                  WHERE assignment_id = :assignment_id';
                  
        $stmt = $this->conn->prepare($query);
        
        // Limpiar datos
        $this->assignment_id = htmlspecialchars(strip_tags($this->assignment_id));
        $this->return_received_by = htmlspecialchars(strip_tags($this->return_received_by));
        $this->return_notes = htmlspecialchars(strip_tags($this->return_notes));
        
        // Si no se proporciona fecha, usar la actual
        if(!$this->return_date) {
            $this->return_date = date('Y-m-d H:i:s');
        }
        
        // Bind params
        $stmt->bindParam(':assignment_id', $this->assignment_id);
        $stmt->bindParam(':return_date', $this->return_date);
        $stmt->bindParam(':return_received_by', $this->return_received_by);
        $stmt->bindParam(':return_notes', $this->return_notes);
        
        // Ejecutar
        if($stmt->execute()) {
            // Obtener el hardware_id para la actualización
            $query = 'SELECT hardware_id FROM ' . $this->table . ' WHERE assignment_id = :assignment_id';
            $getStmt = $this->conn->prepare($query);
            $getStmt->bindParam(':assignment_id', $this->assignment_id);
            $getStmt->execute();
            $row = $getStmt->fetch(PDO::FETCH_ASSOC);
            
            if($row) {
                // Actualizar el estado del hardware a "In Stock" y eliminar current_user_id
                $query = 'UPDATE hardware SET current_user_id = NULL, status = "In Stock", updated_at = NOW() 
                          WHERE hardware_id = :hardware_id';
                $updateStmt = $this->conn->prepare($query);
                $updateStmt->bindParam(':hardware_id', $row['hardware_id']);
                $updateStmt->execute();
            }
            
            return true;
        }
        
        return false;
    }

    // Registrar devolución de equipo
    public function recordReturn($assignment_id, $return_date, $return_received_by, $return_notes) {
        $query = 'UPDATE ' . $this->table . ' SET 
                  return_date = :return_date,
                  return_received_by = :return_received_by,
                  return_notes = :return_notes,
                  status = "Returned"
                  WHERE assignment_id = :assignment_id';
                  
        $stmt = $this->conn->prepare($query);
        
        // Bind params
        $stmt->bindParam(':assignment_id', $assignment_id);
        $stmt->bindParam(':return_date', $return_date);
        $stmt->bindParam(':return_received_by', $return_received_by);
        $stmt->bindParam(':return_notes', $return_notes);
        
        // Ejecutar
        if($stmt->execute()) {
            return true;
        }
        
        return false;
    }

    // Obtener asignaciones activas (sin devolución)
    public function getActiveAssignments() {
        $query = 'SELECT a.*, 
                  CONCAT(u.first_name, " ", u.last_name) as user_name,
                  CONCAT(ab.first_name, " ", ab.last_name) as assigned_by_name,
                  h.serial_number as hardware_serial, h.asset_tag as hardware_asset_tag,
                  m.model_name, b.brand_name
                  FROM ' . $this->table . ' a
                  LEFT JOIN users u ON a.user_id = u.user_id
                  LEFT JOIN users ab ON a.assigned_by = ab.user_id
                  LEFT JOIN hardware h ON a.hardware_id = h.hardware_id
                  LEFT JOIN models m ON h.model_id = m.model_id
                  LEFT JOIN brands b ON m.brand_id = b.brand_id
                  WHERE a.status = "Assigned"
                  ORDER BY a.assigned_date DESC';
                  
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt;
    }

    // Obtener asignaciones por usuario
    public function getAssignmentsByUser($user_id) {
        $query = 'SELECT a.*, 
                  h.serial_number as hardware_serial, h.asset_tag as hardware_asset_tag,
                  m.model_name, b.brand_name
                  FROM ' . $this->table . ' a
                  LEFT JOIN hardware h ON a.hardware_id = h.hardware_id
                  LEFT JOIN models m ON h.model_id = m.model_id
                  LEFT JOIN brands b ON m.brand_id = b.brand_id
                  WHERE a.user_id = :user_id
                  ORDER BY a.assigned_date DESC';
                  
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        
        return $stmt;
    }
}
?> 