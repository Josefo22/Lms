<?php
class ReportController {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    // Reportes de Hardware
    public function getHardwareInventory($filters = []) {
        $query = "SELECT h.*, m.model_name, b.brand_name, c.client_name, l.location_name, u.first_name, u.last_name 
                 FROM hardware h
                 LEFT JOIN models m ON h.model_id = m.model_id
                 LEFT JOIN brands b ON m.brand_id = b.brand_id
                 LEFT JOIN clients c ON h.client_id = c.client_id
                 LEFT JOIN locations l ON h.location_id = l.location_id
                 LEFT JOIN users u ON h.current_user_id = u.user_id";
        
        return $this->db->query($query)->fetchAll();
    }

    public function getHardwareStatus() {
        $query = "SELECT status, COUNT(*) as count 
                 FROM hardware 
                 GROUP BY status";
        
        return $this->db->query($query)->fetchAll();
    }

    public function getHardwareAssignments() {
        $query = "SELECT ah.*, h.serial_number, h.asset_tag, 
                        u.first_name, u.last_name, u.employee_id,
                        c.client_name
                 FROM assignmenthistory ah
                 JOIN hardware h ON ah.hardware_id = h.hardware_id
                 JOIN users u ON ah.user_id = u.user_id
                 LEFT JOIN clients c ON u.client_id = c.client_id
                 ORDER BY ah.assigned_date DESC";
        
        return $this->db->query($query)->fetchAll();
    }

    // Reportes de Clientes
    public function getClientResources($clientId = null) {
        $query = "SELECT c.client_name,
                        COUNT(DISTINCT h.hardware_id) as total_hardware,
                        COUNT(DISTINCT u.user_id) as total_users,
                        COUNT(DISTINCT sr.request_id) as total_tickets
                 FROM clients c
                 LEFT JOIN hardware h ON c.client_id = h.client_id
                 LEFT JOIN users u ON c.client_id = u.client_id
                 LEFT JOIN supportrequests sr ON u.user_id = sr.user_id";
        
        if ($clientId) {
            $query .= " WHERE c.client_id = ?";
            return $this->db->query($query, [$clientId])->fetch();
        }
        
        $query .= " GROUP BY c.client_id";
        return $this->db->query($query)->fetchAll();
    }

    // Reportes de Soporte
    public function getSupportSummary($filters = []) {
        $query = "SELECT sr.status, sr.priority, COUNT(*) as count,
                        AVG(TIMESTAMPDIFF(HOUR, sr.created_at, sr.updated_at)) as avg_resolution_time
                 FROM supportrequests sr
                 GROUP BY sr.status, sr.priority";
        
        return $this->db->query($query)->fetchAll();
    }

    public function getSupportPerformance($staffId = null) {
        $query = "SELECT u.first_name, u.last_name,
                        COUNT(sr.request_id) as total_tickets,
                        COUNT(CASE WHEN sr.status = 'Resolved' THEN 1 END) as resolved_tickets,
                        AVG(TIMESTAMPDIFF(HOUR, sr.created_at, sr.updated_at)) as avg_resolution_time
                 FROM users u
                 JOIN itstaff i ON u.user_id = i.user_id
                 LEFT JOIN supportrequests sr ON i.user_id = sr.assigned_to";
        
        if ($staffId) {
            $query .= " WHERE i.staff_id = ?";
            return $this->db->query($query, [$staffId])->fetch();
        }
        
        $query .= " GROUP BY u.user_id";
        return $this->db->query($query)->fetchAll();
    }

    // Reportes de Auditoría
    public function getAuditResults($filters = []) {
        $query = "SELECT ia.*, 
                        c.client_name,
                        l.location_name,
                        u.first_name as auditor_name,
                        COUNT(ad.audit_detail_id) as total_items,
                        SUM(ad.is_discrepancy) as discrepancies
                 FROM inventoryaudits ia
                 LEFT JOIN clients c ON ia.client_id = c.client_id
                 LEFT JOIN locations l ON ia.location_id = l.location_id
                 LEFT JOIN users u ON ia.audited_by = u.user_id
                 LEFT JOIN auditdetails ad ON ia.audit_id = ad.audit_id
                 GROUP BY ia.audit_id
                 ORDER BY ia.audit_date DESC";
        
        return $this->db->query($query)->fetchAll();
    }

    // Reportes de Envíos
    public function getShipmentStatus($filters = []) {
        $query = "SELECT s.*, 
                        ol.location_name as origin_name,
                        dl.location_name as destination_name,
                        u.first_name as recipient_name,
                        COUNT(sd.shipment_detail_id) as total_items
                 FROM shipments s
                 LEFT JOIN locations ol ON s.origin_location_id = ol.location_id
                 LEFT JOIN locations dl ON s.destination_location_id = dl.location_id
                 LEFT JOIN users u ON s.recipient_user_id = u.user_id
                 LEFT JOIN shipmentdetails sd ON s.shipment_id = sd.shipment_id
                 GROUP BY s.shipment_id
                 ORDER BY s.shipping_date DESC";
        
        return $this->db->query($query)->fetchAll();
    }

    // Reportes Personalizados
    public function generateCustomReport($params) {
        // Implementar lógica para reportes personalizados basados en parámetros
        $query = $this->buildCustomQuery($params);
        return $this->db->query($query)->fetchAll();
    }

    private function buildCustomQuery($params) {
        // Implementar constructor de consultas dinámico
        return "";
    }

    // Utilidades
    public function exportToCSV($data, $filename) {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        
        // Escribir encabezados
        if (!empty($data)) {
            fputcsv($output, array_keys($data[0]));
        }
        
        // Escribir datos
        foreach ($data as $row) {
            fputcsv($output, $row);
        }
        
        fclose($output);
    }

    public function exportToPDF($data, $template, $filename) {
        // Implementar exportación a PDF usando una biblioteca como TCPDF o FPDF
    }
} 