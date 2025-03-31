<?php
require_once 'config/database.php';
require_once 'app/models/Hardware.php';
require_once 'app/models/SupportRequest.php';
require_once 'app/models/AssignmentHistory.php';

class NotificationController {
    private $db;
    private $hardware;
    private $supportRequest;
    private $assignment;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
        
        $this->hardware = new Hardware($this->db);
        $this->supportRequest = new SupportRequest($this->db);
        $this->assignment = new AssignmentHistory($this->db);
    }
    
    /**
     * Obtiene las notificaciones para mostrar en la barra de navegación
     */
    public function getNotifications() {
        $notifications = [
            'total' => 0,
            'items' => []
        ];
        
        // 1. Equipos con garantía a punto de expirar
        $warrantyAlerts = $this->getWarrantyAlerts();
        if (count($warrantyAlerts) > 0) {
            $notifications['items'][] = [
                'type' => 'warranty',
                'icon' => 'exclamation-circle',
                'icon_class' => 'text-warning',
                'bg_class' => 'bg-warning-subtle',
                'title' => 'Garantías por vencer',
                'count' => count($warrantyAlerts),
                'details' => count($warrantyAlerts) . ' equipos con garantía a punto de expirar',
                'link' => '?page=inventory&filter=warranty_expiring'
            ];
            $notifications['total'] += 1;
        }
        
        // 2. Equipos en reparación
        $repairCount = $this->getRepairCount();
        if ($repairCount > 0) {
            $notifications['items'][] = [
                'type' => 'repair',
                'icon' => 'tools',
                'icon_class' => 'text-primary',
                'bg_class' => 'bg-primary-subtle',
                'title' => 'Equipos en reparación',
                'count' => $repairCount,
                'details' => $repairCount . ' equipos en proceso de reparación',
                'link' => '?page=inventory&status=In+Repair'
            ];
            $notifications['total'] += 1;
        }
        
        // 3. Solicitudes de soporte pendientes
        $supportCount = $this->getSupportRequestsCount();
        if ($supportCount > 0) {
            $notifications['items'][] = [
                'type' => 'support',
                'icon' => 'headset',
                'icon_class' => 'text-danger',
                'bg_class' => 'bg-danger-subtle',
                'title' => 'Solicitudes pendientes',
                'count' => $supportCount,
                'details' => $supportCount . ' solicitudes de soporte pendientes',
                'link' => '?page=support'
            ];
            $notifications['total'] += 1;
        }
        
        // 4. Asignaciones recientes (últimas 24 horas)
        $recentAssignments = $this->getRecentAssignments();
        if (count($recentAssignments) > 0) {
            $notifications['items'][] = [
                'type' => 'assignment',
                'icon' => 'check-circle',
                'icon_class' => 'text-success',
                'bg_class' => 'bg-success-subtle',
                'title' => 'Asignaciones recientes',
                'count' => count($recentAssignments),
                'details' => count($recentAssignments) . ' equipos asignados recientemente',
                'link' => '?page=assignments'
            ];
            $notifications['total'] += 1;
        }
        
        return $notifications;
    }
    
    /**
     * Obtiene equipos con garantía a punto de expirar (próximos 30 días)
     */
    public function getWarrantyAlerts() {
        try {
            $query = "SELECT h.hardware_id, h.asset_tag, m.model_name, b.brand_name,
                        h.warranty_expiry_date, DATEDIFF(h.warranty_expiry_date, CURRENT_DATE()) as days_left
                      FROM hardware h
                      JOIN models m ON h.model_id = m.model_id
                      JOIN brands b ON m.brand_id = b.brand_id
                      WHERE h.status != 'Retired'
                      AND h.warranty_expiry_date IS NOT NULL
                      AND h.warranty_expiry_date BETWEEN CURRENT_DATE() AND DATE_ADD(CURRENT_DATE(), INTERVAL 30 DAY)
                      ORDER BY h.warranty_expiry_date
                      LIMIT 10";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
    
    /**
     * Obtiene el número de equipos en reparación
     */
    private function getRepairCount() {
        try {
            $query = "SELECT COUNT(*) as count FROM hardware WHERE status = 'In Repair'";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'] ?? 0;
        } catch (PDOException $e) {
            return 0;
        }
    }
    
    /**
     * Obtiene equipos en estado de reparación
     */
    public function getRepairEquipment() {
        try {
            $query = "SELECT h.hardware_id, h.asset_tag, h.serial_number, h.notes, h.updated_at,
                      m.model_name, b.brand_name
                      FROM hardware h
                      JOIN models m ON h.model_id = m.model_id
                      JOIN brands b ON m.brand_id = b.brand_id
                      WHERE h.status = 'In Repair'
                      ORDER BY h.updated_at DESC";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
    
    /**
     * Obtiene el número de solicitudes de soporte pendientes
     */
    private function getSupportRequestsCount() {
        try {
            $query = "SELECT COUNT(*) as count FROM supportrequests 
                      WHERE status IN ('New', 'Assigned', 'In Progress')";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'] ?? 0;
        } catch (PDOException $e) {
            return 0;
        }
    }
    
    /**
     * Obtiene las solicitudes de soporte pendientes
     */
    public function getPendingSupportRequests() {
        try {
            $query = "SELECT sr.*, 
                      CONCAT(u.first_name, ' ', u.last_name) as user_name,
                      CONCAT(a.first_name, ' ', a.last_name) as assigned_to_name
                      FROM supportrequests sr
                      JOIN users u ON sr.user_id = u.user_id
                      LEFT JOIN users a ON sr.assigned_to = a.user_id
                      WHERE sr.status IN ('New', 'Assigned', 'In Progress')
                      ORDER BY 
                        CASE sr.priority
                            WHEN 'High' THEN 1
                            WHEN 'Medium' THEN 2
                            WHEN 'Low' THEN 3
                        END,
                        sr.created_at ASC";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
    
    /**
     * Obtiene las asignaciones de hardware realizadas en las últimas 24 horas
     */
    public function getRecentAssignments() {
        try {
            $query = "SELECT ah.assignment_id, ah.hardware_id, ah.user_id, ah.assigned_date,
                      CONCAT(u.first_name, ' ', u.last_name) as user_name,
                      h.asset_tag, m.model_name
                      FROM assignmenthistory ah
                      JOIN users u ON ah.user_id = u.user_id
                      JOIN hardware h ON ah.hardware_id = h.hardware_id
                      JOIN models m ON h.model_id = m.model_id
                      WHERE ah.assigned_date >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                      AND ah.status = 'Active'
                      ORDER BY ah.assigned_date DESC
                      LIMIT 5";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
}
?> 