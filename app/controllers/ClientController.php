<?php
require_once 'config/database.php';
require_once 'app/models/Client.php';
require_once 'app/models/Hardware.php';
require_once 'app/models/User.php';

class ClientController {
    private $db;
    private $client;
    private $hardware;
    private $user;
    private $current_user;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
        
        $this->client = new Client($this->db);
        $this->hardware = new Hardware($this->db);
        $this->user = new User($this->db);
        $this->current_user = $_SESSION['user_id'] ?? null;
    }

    /**
     * Obtiene la lista de clientes con filtros opcionales
     */
    public function getClients($filters = []) {
        try {
            $sql = "SELECT c.*, 
                    COUNT(DISTINCT h.hardware_id) as hardware_count,
                    COUNT(DISTINCT u.user_id) as user_count
                    FROM clients c
                    LEFT JOIN hardware h ON c.client_id = h.client_id
                    LEFT JOIN users u ON c.client_id = u.client_id
                    WHERE 1=1";

            $params = [];

            if (!empty($filters['search'])) {
                $sql .= " AND (c.client_name LIKE ? OR c.contact_person LIKE ? OR c.contact_email LIKE ?)";
                $searchTerm = "%{$filters['search']}%";
                $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
            }

            $sql .= " GROUP BY c.client_id ORDER BY c.client_name";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Obtiene los detalles de un cliente específico
     */
    public function getClientDetails($client_id) {
        try {
            $sql = "SELECT c.*, 
                    COUNT(DISTINCT h.hardware_id) as hardware_count,
                    COUNT(DISTINCT u.user_id) as user_count
                    FROM clients c
                    LEFT JOIN hardware h ON c.client_id = h.client_id
                    LEFT JOIN users u ON c.client_id = u.client_id
                    WHERE c.client_id = ?
                    GROUP BY c.client_id";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([$client_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return null;
        }
    }

    /**
     * Crea un nuevo cliente
     */
    public function createClient($data) {
        try {
            $this->db->beginTransaction();

            $sql = "INSERT INTO clients (client_name, contact_person, contact_email, contact_phone, address) 
                    VALUES (?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $data['client_name'],
                $data['contact_person'],
                $data['contact_email'],
                $data['contact_phone'],
                $data['address']
            ]);

            $client_id = $this->db->lastInsertId();
            $this->logActivity($client_id, 'create', 'Cliente creado');
            $this->db->commit();

            return [
                'success' => true,
                'message' => 'Cliente creado exitosamente',
                'client_id' => $client_id
            ];
        } catch (PDOException $e) {
            $this->db->rollBack();
            return [
                'success' => false,
                'message' => 'Error al crear el cliente: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Actualiza un cliente existente
     */
    public function updateClient($client_id, $data) {
        try {
            $this->db->beginTransaction();

            $sql = "UPDATE clients SET 
                    client_name = ?,
                    contact_person = ?,
                    contact_email = ?,
                    contact_phone = ?,
                    address = ?
                    WHERE client_id = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $data['client_name'],
                $data['contact_person'],
                $data['contact_email'],
                $data['contact_phone'],
                $data['address'],
                $client_id
            ]);

            $this->logActivity($client_id, 'update', 'Cliente actualizado');
            $this->db->commit();

            return [
                'success' => true,
                'message' => 'Cliente actualizado exitosamente'
            ];
        } catch (PDOException $e) {
            $this->db->rollBack();
            return [
                'success' => false,
                'message' => 'Error al actualizar el cliente: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Elimina un cliente
     */
    public function deleteClient($client_id) {
        try {
            $this->db->beginTransaction();

            // Verificar si el cliente tiene hardware asociado
            $sql = "SELECT COUNT(*) FROM hardware WHERE client_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$client_id]);
            $hardware_count = $stmt->fetchColumn();

            if ($hardware_count > 0) {
                return [
                    'success' => false,
                    'message' => 'No se puede eliminar el cliente porque tiene hardware asociado'
                ];
            }

            $sql = "DELETE FROM clients WHERE client_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$client_id]);

            $this->logActivity($client_id, 'delete', 'Cliente eliminado');
            $this->db->commit();

            return [
                'success' => true,
                'message' => 'Cliente eliminado exitosamente'
            ];
        } catch (PDOException $e) {
            $this->db->rollBack();
            return [
                'success' => false,
                'message' => 'Error al eliminar el cliente: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obtiene las estadísticas de los clientes
     */
    public function getClientStats() {
        try {
            // Consulta para obtener clientes con más hardware
            $query = "SELECT c.client_id, c.client_name, 
                    COUNT(DISTINCT h.hardware_id) as hardware_count,
                    COUNT(DISTINCT u.user_id) as user_count
                    FROM clients c
                    LEFT JOIN hardware h ON c.client_id = h.client_id
                    LEFT JOIN users u ON c.client_id = u.client_id
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
     * Registra una actividad relacionada con clientes
     */
    private function logActivity($client_id, $action, $details) {
        try {
            $sql = "INSERT INTO activity_log (user_id, action, details, reference_id, reference_type) 
                    VALUES (?, ?, ?, ?, 'client')";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$this->current_user, $action, $details, $client_id]);
        } catch (PDOException $e) {
            // Silenciosamente fallar el logging
        }
    }
} 