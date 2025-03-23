<?php
require_once 'config/database.php';
require_once 'app/models/Hardware.php';
require_once 'app/models/SupportRequest.php';
require_once 'app/models/User.php';
require_once 'app/models/Client.php';
require_once 'app/models/Location.php';
require_once 'app/models/AssignmentHistory.php';

class DashboardController {
    private $db;
    private $hardware;
    private $supportRequest;
    private $user;
    private $client;
    private $location;
    private $assignment;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
        
        $this->hardware = new Hardware($this->db);
        $this->supportRequest = new SupportRequest($this->db);
        $this->user = new User($this->db);
        $this->client = new Client($this->db);
        $this->location = new Location($this->db);
        $this->assignment = new AssignmentHistory($this->db);
    }
    
    /**
     * Obtiene las estadísticas para el dashboard
     * (Compatible con la vista existente)
     */
    public function getStats() {
        return $this->getHardwareStats();
    }
    
    /**
     * Obtiene las estadísticas generales de hardware
     */
    public function getHardwareStats() {
        try {
            // Consulta para obtener el conteo de hardware por estado
            $query = "SELECT 
                        COUNT(*) as total_count,
                        SUM(CASE WHEN status = 'In Stock' THEN 1 ELSE 0 END) as available_count,
                        SUM(CASE WHEN status = 'In Use' THEN 1 ELSE 0 END) as in_use_count,
                        SUM(CASE WHEN status = 'In Repair' THEN 1 ELSE 0 END) as repair_count,
                        SUM(CASE WHEN status = 'Retired' THEN 1 ELSE 0 END) as retired_count
                      FROM hardware";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return [
                'hardware_count' => $result['total_count'] ?? 0,
                'available_count' => $result['available_count'] ?? 0,
                'in_use_count' => $result['in_use_count'] ?? 0,
                'repair_count' => $result['repair_count'] ?? 0,
                'retired_count' => $result['retired_count'] ?? 0
            ];
        } catch (PDOException $e) {
            return [
                'hardware_count' => 0,
                'available_count' => 0,
                'in_use_count' => 0,
                'repair_count' => 0,
                'retired_count' => 0
            ];
        }
    }
    
    /**
     * Obtiene las estadísticas de solicitudes de soporte
     */
    public function getSupportStats() {
        try {
            // Consulta para obtener el conteo de solicitudes por estado
            $query = "SELECT 
                        COUNT(*) as total_count,
                        SUM(CASE WHEN status = 'New' THEN 1 ELSE 0 END) as new_count,
                        SUM(CASE WHEN status = 'In Progress' THEN 1 ELSE 0 END) as in_progress_count,
                        SUM(CASE WHEN status = 'Resolved' THEN 1 ELSE 0 END) as resolved_count
                      FROM supportrequests";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Obtener solicitudes pendientes
            $pending_requests = [];
            $pending = $this->supportRequest->getPendingRequests(5);
            while($row = $pending->fetch(PDO::FETCH_ASSOC)) {
                $pending_requests[] = [
                    'request_id' => $row['request_id'],
                    'type' => $row['request_type'],
                    'user_name' => $row['user_name'],
                    'priority' => $row['priority'],
                    'asset_tag' => $row['asset_tag'] ?? '',
                    'model' => $row['model_name'] ?? ''
                ];
            }
            
            return [
                'total' => $result['total_count'] ?? 0,
                'new' => $result['new_count'] ?? 0,
                'in_progress' => $result['in_progress_count'] ?? 0,
                'resolved' => $result['resolved_count'] ?? 0,
                'pending_requests' => $pending_requests
            ];
        } catch (PDOException $e) {
            return [
                'total' => 0,
                'new' => 0,
                'in_progress' => 0,
                'resolved' => 0,
                'pending_requests' => []
            ];
        }
    }
    
    /**
     * Obtiene las estadísticas de los clientes
     */
    public function getClientStats() {
        try {
            // Consulta para obtener clientes con más hardware
            $query = "SELECT c.client_id, c.client_name, COUNT(h.hardware_id) as hardware_count
                      FROM clients c
                      LEFT JOIN hardware h ON c.client_id = h.client_id
                      GROUP BY c.client_id
                      ORDER BY hardware_count DESC
                      LIMIT 5";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $client_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Consulta para obtener el total de clientes
            $query = "SELECT COUNT(*) as total FROM clients";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $total_clients = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
            
            return [
                'total_clients' => $total_clients,
                'top_clients' => $client_data
            ];
        } catch (PDOException $e) {
            return [
                'total_clients' => 0,
                'top_clients' => []
            ];
        }
    }
    
    /**
     * Obtiene las estadísticas de los usuarios
     */
    public function getUserStats() {
        try {
            // Consulta para obtener el conteo de usuarios por estado
            $query = "SELECT 
                        COUNT(*) as total_count,
                        SUM(CASE WHEN status = 'Active' THEN 1 ELSE 0 END) as active_count,
                        SUM(CASE WHEN status = 'Inactive' THEN 1 ELSE 0 END) as inactive_count
                      FROM users";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Consulta para obtener los usuarios con más equipos asignados
            $query = "SELECT u.user_id, CONCAT(u.first_name, ' ', u.last_name) as user_name,
                        COUNT(h.hardware_id) as hardware_count
                      FROM users u
                      LEFT JOIN hardware h ON u.user_id = h.current_user_id
                      GROUP BY u.user_id
                      ORDER BY hardware_count DESC
                      LIMIT 5";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $top_users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'total_users' => $result['total_count'] ?? 0,
                'active_users' => $result['active_count'] ?? 0,
                'inactive_users' => $result['inactive_count'] ?? 0,
                'top_users' => $top_users
            ];
        } catch (PDOException $e) {
            return [
                'total_users' => 0,
                'active_users' => 0,
                'inactive_users' => 0,
                'top_users' => []
            ];
        }
    }
    
    /**
     * Obtiene las actividades recientes del sistema
     */
    public function getRecentActivities() {
        try {
            $query = "SELECT 
                        a.activity_id,
                        a.action,
                        a.details,
                        a.timestamp,
                        CONCAT(u.first_name, ' ', u.last_name) as user_name
                      FROM activities a
                      LEFT JOIN users u ON a.user_id = u.user_id
                      ORDER BY a.timestamp DESC
                      LIMIT 10";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
    
    /**
     * Obtiene los datos de hardware agrupados por categoría
     */
    public function getHardwareByCategory() {
        try {
            $query = "SELECT hc.category_name, COUNT(h.hardware_id) as count
                      FROM hardwarecategories hc
                      LEFT JOIN models m ON hc.category_id = m.category_id
                      LEFT JOIN hardware h ON m.model_id = h.model_id
                      GROUP BY hc.category_id
                      ORDER BY count DESC";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
    
    /**
     * Obtiene hardware que necesita atención (garantía a punto de expirar, etc.)
     */
    public function getHardwareAlerts() {
        try {
            // Hardware con garantía a punto de expirar (próximos 30 días)
            $query = "SELECT h.hardware_id, h.asset_tag, m.model_name, b.brand_name,
                        h.warranty_expiry_date, DATEDIFF(h.warranty_expiry_date, CURRENT_DATE()) as days_left
                      FROM hardware h
                      JOIN models m ON h.model_id = m.model_id
                      JOIN brands b ON m.brand_id = b.brand_id
                      WHERE h.status != 'Retired'
                      AND h.warranty_expiry_date IS NOT NULL
                      AND h.warranty_expiry_date BETWEEN CURRENT_DATE() AND DATE_ADD(CURRENT_DATE(), INTERVAL 30 DAY)
                      ORDER BY h.warranty_expiry_date
                      LIMIT 5";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
    
    /**
     * Obtiene todos los datos necesarios para el dashboard
     */
    public function getDashboardData() {
        return [
            'hardware_stats' => $this->getHardwareStats(),
            'support_stats' => $this->getSupportStats(),
            'client_stats' => $this->getClientStats(),
            'user_stats' => $this->getUserStats(),
            'recent_activities' => $this->getRecentActivities(),
            'hardware_by_category' => $this->getHardwareByCategory(),
            'hardware_alerts' => $this->getHardwareAlerts()
        ];
    }
}
?> 