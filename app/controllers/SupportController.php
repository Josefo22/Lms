<?php
require_once 'config/database.php';
require_once 'app/models/Hardware.php';
require_once 'app/models/SupportRequest.php';
require_once 'app/models/User.php';

class SupportController {
    private $db;
    private $hardware;
    private $supportRequest;
    private $user;

    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
        
        $this->hardware = new Hardware($this->db);
        $this->supportRequest = new SupportRequest($this->db);
        $this->user = new User($this->db);
    }

    // Obtener todos los tickets con filtros
    public function getTickets($filters = []) {
        $sql = "SELECT sr.*, 
                       CONCAT(u1.first_name, ' ', u1.last_name) as requester_name,
                       CONCAT(u2.first_name, ' ', u2.last_name) as assigned_name,
                       CONCAT(m.model_name, ' (', h.asset_tag, ')') as hardware_name
                FROM supportrequests sr
                LEFT JOIN users u1 ON sr.user_id = u1.user_id
                LEFT JOIN users u2 ON sr.assigned_to = u2.user_id
                LEFT JOIN hardware h ON sr.hardware_id = h.hardware_id
                LEFT JOIN models m ON h.model_id = m.model_id
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($filters['status'])) {
            $sql .= " AND sr.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['priority'])) {
            $sql .= " AND sr.priority = ?";
            $params[] = $filters['priority'];
        }
        
        if (!empty($filters['request_type'])) {
            $sql .= " AND sr.request_type = ?";
            $params[] = $filters['request_type'];
        }
        
        if (!empty($filters['search'])) {
            $search = "%{$filters['search']}%";
            $sql .= " AND (sr.description LIKE ? OR m.model_name LIKE ? OR h.asset_tag LIKE ?)";
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
        }
        
        $sql .= " ORDER BY sr.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener detalles de un ticket específico
    public function getTicketDetails($id) {
        $sql = "SELECT sr.*, 
                       CONCAT(u1.first_name, ' ', u1.last_name) as requester_name,
                       CONCAT(u2.first_name, ' ', u2.last_name) as assigned_name,
                       CONCAT(m.model_name, ' (', h.asset_tag, ')') as hardware_name
                FROM supportrequests sr
                LEFT JOIN users u1 ON sr.user_id = u1.user_id
                LEFT JOIN users u2 ON sr.assigned_to = u2.user_id
                LEFT JOIN hardware h ON sr.hardware_id = h.hardware_id
                LEFT JOIN models m ON h.model_id = m.model_id
                WHERE sr.request_id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Crear un nuevo ticket
    public function createTicket($data) {
        try {
            $this->db->beginTransaction();
            
            $sql = "INSERT INTO supportrequests (
                        user_id, request_type, hardware_id, description,
                        priority, status, assigned_to, resolution_notes,
                        created_at, updated_at
                    ) VALUES (
                        ?, ?, ?, ?,
                        ?, ?, ?, ?,
                        NOW(), NOW()
                    )";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $_SESSION['user_id'],
                $data['request_type'],
                $data['hardware_id'] ?: null,
                $data['description'],
                $data['priority'],
                $data['status'],
                $data['assigned_to'] ?: null,
                $data['resolution_notes'] ?? null
            ]);
            
            $ticketId = $this->db->lastInsertId();
            
            // Registrar actividad
            $this->logActivity('create', 'Nueva solicitud de soporte creada', $ticketId);
            
            $this->db->commit();
            return [
                'success' => true,
                'message' => 'Solicitud creada correctamente',
                'ticket_id' => $ticketId
            ];
            
        } catch (Exception $e) {
            $this->db->rollBack();
            return [
                'success' => false,
                'message' => 'Error al crear la solicitud: ' . $e->getMessage()
            ];
        }
    }

    // Actualizar un ticket existente
    public function updateTicket($id, $data) {
        try {
            $this->db->beginTransaction();
            
            $sql = "UPDATE supportrequests SET 
                    request_type = ?,
                    hardware_id = ?,
                    description = ?,
                    priority = ?,
                    status = ?,
                    assigned_to = ?,
                    resolution_notes = ?,
                    updated_at = NOW()
                    WHERE request_id = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $data['request_type'],
                $data['hardware_id'] ?: null,
                $data['description'],
                $data['priority'],
                $data['status'],
                $data['assigned_to'] ?: null,
                $data['resolution_notes'] ?? null,
                $id
            ]);
            
            // Registrar actividad
            $this->logActivity('update', 'Solicitud de soporte actualizada', $id);
            
            $this->db->commit();
            return [
                'success' => true,
                'message' => 'Solicitud actualizada correctamente'
            ];
            
        } catch (Exception $e) {
            $this->db->rollBack();
            return [
                'success' => false,
                'message' => 'Error al actualizar la solicitud: ' . $e->getMessage()
            ];
        }
    }

    // Cambiar el estado de un ticket
    public function changeStatus($id, $status) {
        try {
            $this->db->beginTransaction();
            
            $sql = "UPDATE supportrequests SET 
                    status = ?,
                    updated_at = NOW()
                    WHERE request_id = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$status, $id]);
            
            // Registrar actividad
            $this->logActivity('status_change', "Estado cambiado a: {$status}", $id);
            
            $this->db->commit();
            return [
                'success' => true,
                'message' => 'Estado actualizado correctamente'
            ];
            
        } catch (Exception $e) {
            $this->db->rollBack();
            return [
                'success' => false,
                'message' => 'Error al actualizar el estado: ' . $e->getMessage()
            ];
        }
    }

    // Registrar actividad
    private function logActivity($action, $details, $ticket_id) {
        $sql = "INSERT INTO activities (user_id, action, details, related_id, type, timestamp)
                VALUES (?, ?, ?, ?, 'support', NOW())";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$_SESSION['user_id'], $action, $details, $ticket_id]);
    }

    // Obtener opciones para los selectores
    public function getRequestTypeOptions() {
        return [
            'Hardware Issue' => 'Problema de Hardware',
            'Peripheral Request' => 'Solicitud de Periférico',
            'Replacement' => 'Reemplazo',
            'Return' => 'Devolución',
            'Other' => 'Otro'
        ];
    }

    public function getStatusOptions() {
        return [
            'New' => 'Nuevo',
            'Assigned' => 'Asignado',
            'In Progress' => 'En Proceso',
            'Resolved' => 'Resuelto',
            'Closed' => 'Cerrado',
            'Cancelled' => 'Cancelado'
        ];
    }

    public function getPriorityOptions() {
        return [
            'Low' => 'Baja',
            'Medium' => 'Media',
            'High' => 'Alta',
            'Urgent' => 'Urgente'
        ];
    }

    public function getUserOptions() {
        $sql = "SELECT user_id as id, CONCAT(first_name, ' ', last_name) as name 
                FROM users 
                WHERE status = 'active' 
                ORDER BY first_name, last_name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getHardwareOptions() {
        $sql = "SELECT hardware_id as id, asset_tag as name 
                FROM hardware 
                WHERE status = 'active' 
                ORDER BY asset_tag";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
