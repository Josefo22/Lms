<?php
class InventoryController {
    private $db;
    private $hardware;
    private $client;
    private $model;
    private $brand;
    private $category;
    private $location;
    private $assignment;

    public function __construct() {
        // Conectar a la base de datos
        require_once __DIR__ . '/../../config/database.php';
        $database = new Database();
        $this->db = $database->connect();
        
        // Inicializar modelos
        require_once __DIR__ . '/../models/Hardware.php';
        require_once __DIR__ . '/../models/Client.php';
        require_once __DIR__ . '/../models/Model.php';
        require_once __DIR__ . '/../models/Brand.php';
        require_once __DIR__ . '/../models/HardwareCategory.php';
        require_once __DIR__ . '/../models/Location.php';
        require_once __DIR__ . '/../models/AssignmentHistory.php';
        
        $this->hardware = new Hardware($this->db);
        $this->client = new Client($this->db);
        $this->model = new Model($this->db);
        $this->brand = new Brand($this->db);
        $this->category = new HardwareCategory($this->db);
        $this->location = new Location($this->db);
        $this->assignment = new AssignmentHistory($this->db);
    }
    
    // Obtener inventario con estadísticas
    public function getInventory($status = null, $category = null, $client = null) {
        $result = [];
        
        // Obtener estadísticas por estado
        $result['statistics'] = [
            'total' => $this->hardware->countByStatus(),
            'available' => $this->hardware->countByStatus('available'),
            'assigned' => $this->hardware->countByStatus('assigned'),
            'repair' => $this->hardware->countByStatus('repair'),
            'decommissioned' => $this->hardware->countByStatus('decommissioned')
        ];
        
        // Obtener categorías para filtros
        $categoryData = $this->category->read();
        $result['categories'] = [];
        while($row = $categoryData->fetch(PDO::FETCH_ASSOC)) {
            $result['categories'][] = $row;
        }
        
        // Obtener clientes para filtros
        $clientData = $this->client->read();
        $result['clients'] = [];
        while($row = $clientData->fetch(PDO::FETCH_ASSOC)) {
            $result['clients'][] = $row;
        }
        
        // Obtener hardware con filtros aplicados
        $hardwareData = $this->hardware->read($status, $category, $client);
        $result['hardware'] = [];
        while($row = $hardwareData->fetch(PDO::FETCH_ASSOC)) {
            $result['hardware'][] = $row;
        }
        
        // Obtener distribución por categoría
        $result['distribution'] = $this->hardware->getDistributionByCategory();
        
        return $result;
    }
    
    // Obtener detalles de un hardware específico
    public function getHardwareDetails($id) {
        $this->hardware->hardware_id = $id;
        
        if($this->hardware->read_single()) {
            // Obtener historial de asignaciones
            $history = $this->assignment->getHardwareHistory($id);
            $historyData = [];
            while($row = $history->fetch(PDO::FETCH_ASSOC)) {
                $historyData[] = $row;
            }
            
            return [
                'hardware' => [
                    'hardware_id' => $this->hardware->hardware_id,
                    'serial_number' => $this->hardware->serial_number,
                    'asset_tag' => $this->hardware->asset_tag,
                    'model_id' => $this->hardware->model_id,
                    'model_name' => $this->hardware->model_name,
                    'brand_name' => $this->hardware->brand_name,
                    'category_name' => $this->hardware->category_name,
                    'purchase_date' => $this->hardware->purchase_date,
                    'warranty_expiry_date' => $this->hardware->warranty_expiry_date,
                    'status' => $this->hardware->status,
                    'condition_status' => $this->hardware->condition_status,
                    'notes' => $this->hardware->notes,
                    'client_id' => $this->hardware->client_id,
                    'client_name' => $this->hardware->client_name,
                    'location_id' => $this->hardware->location_id,
                    'location_name' => $this->hardware->location_name,
                    'current_user_id' => $this->hardware->current_user_id,
                    'user_name' => $this->hardware->user_name,
                    'created_at' => $this->hardware->created_at,
                    'updated_at' => $this->hardware->updated_at
                ],
                'history' => $historyData
            ];
        } else {
            return ['error' => 'Hardware no encontrado'];
        }
    }
    
    // Crear nuevo hardware
    public function createHardware($data) {
        // Asignar datos al modelo
        $this->hardware->serial_number = $data['serial_number'];
        $this->hardware->asset_tag = $data['asset_tag'];
        $this->hardware->model_id = $data['model_id'];
        $this->hardware->purchase_date = $data['purchase_date'];
        $this->hardware->warranty_expiry_date = $data['warranty_expiry_date'] ?? null;
        $this->hardware->status = $data['status'];
        $this->hardware->condition_status = $data['condition_status'];
        $this->hardware->notes = $data['notes'] ?? null;
        $this->hardware->client_id = $data['client_id'] ?? null;
        $this->hardware->location_id = $data['location_id'] ?? null;
        
        // Crear el hardware
        if($this->hardware->create()) {
            // Si se asigna a un cliente, crear registro en historial
            if($data['client_id']) {
                $this->assignment->hardware_id = $this->hardware->hardware_id;
                $this->assignment->client_id = $data['client_id'];
                $this->assignment->assignment_date = date('Y-m-d H:i:s');
                $this->assignment->notes = "Asignación inicial al registrar el equipo";
                $this->assignment->create();
            }
            
            return [
                'success' => true,
                'message' => 'Hardware creado correctamente',
                'hardware_id' => $this->hardware->hardware_id
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Error al crear el hardware'
            ];
        }
    }
    
    // Actualizar hardware existente
    public function updateHardware($id, $data) {
        // Verificar que el hardware existe
        $this->hardware->hardware_id = $id;
        if(!$this->hardware->read_single()) {
            return [
                'success' => false,
                'message' => 'Hardware no encontrado'
            ];
        }
        
        // Guardar cliente actual para comparar si cambia
        $current_client_id = $this->hardware->client_id;
        
        // Asignar datos al modelo
        $this->hardware->serial_number = $data['serial_number'];
        $this->hardware->asset_tag = $data['asset_tag'];
        $this->hardware->model_id = $data['model_id'];
        $this->hardware->purchase_date = $data['purchase_date'];
        $this->hardware->warranty_expiry_date = $data['warranty_expiry_date'] ?? null;
        $this->hardware->status = $data['status'];
        $this->hardware->condition_status = $data['condition_status'];
        $this->hardware->notes = $data['notes'] ?? null;
        $this->hardware->client_id = $data['client_id'] ?? null;
        $this->hardware->location_id = $data['location_id'] ?? null;
        $this->hardware->current_user_id = $data['current_user_id'] ?? null;
        
        // Actualizar el hardware
        if($this->hardware->update()) {
            // Si cambió el cliente, crear registro en historial
            if($data['client_id'] != $current_client_id) {
                $this->assignment->hardware_id = $id;
                $this->assignment->client_id = $data['client_id'];
                $this->assignment->assignment_date = date('Y-m-d H:i:s');
                $this->assignment->notes = "Actualización de asignación";
                $this->assignment->create();
            }
            
            return [
                'success' => true,
                'message' => 'Hardware actualizado correctamente'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Error al actualizar el hardware'
            ];
        }
    }
    
    // Eliminar hardware
    public function deleteHardware($id) {
        $this->hardware->hardware_id = $id;
        
        // Intentar eliminar
        if($this->hardware->delete()) {
            return [
                'success' => true,
                'message' => 'Hardware eliminado correctamente'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'No se puede eliminar el hardware porque tiene registros asociados'
            ];
        }
    }
    
    // Obtener equipos asignados a un usuario específico
    public function getHardwareByUser($user_id) {
        $result = $this->hardware->getByCurrentUser($user_id);
        $hardware = [];
        
        while($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $hardware[] = $row;
        }
        
        return $hardware;
    }
    
    // Obtener opciones para formularios
    public function getFormOptions() {
        $result = [];
        
        // Obtener modelos
        $modelData = $this->model->read();
        $result['models'] = [];
        while($row = $modelData->fetch(PDO::FETCH_ASSOC)) {
            $result['models'][] = $row;
        }
        
        // Obtener marcas
        $brandData = $this->brand->read();
        $result['brands'] = [];
        while($row = $brandData->fetch(PDO::FETCH_ASSOC)) {
            $result['brands'][] = $row;
        }
        
        // Obtener categorías
        $categoryData = $this->category->read();
        $result['categories'] = [];
        while($row = $categoryData->fetch(PDO::FETCH_ASSOC)) {
            $result['categories'][] = $row;
        }
        
        // Obtener clientes
        $clientData = $this->client->read();
        $result['clients'] = [];
        while($row = $clientData->fetch(PDO::FETCH_ASSOC)) {
            $result['clients'][] = $row;
        }
        
        // Obtener ubicaciones
        $locationData = $this->location->read();
        $result['locations'] = [];
        while($row = $locationData->fetch(PDO::FETCH_ASSOC)) {
            $result['locations'][] = $row;
        }
        
        return $result;
    }
    
    // Obtener opciones de ubicaciones por cliente
    public function getLocationsByClient($client_id) {
        $locationData = $this->location->readByClient($client_id);
        $result = [];
        while($row = $locationData->fetch(PDO::FETCH_ASSOC)) {
            $result[] = $row;
        }
        return $result;
    }
    
    // Crear nuevo modelo
    public function createModel($data) {
        $this->model->model_name = $data['model_name'];
        $this->model->brand_id = $data['brand_id'];
        $this->model->category_id = $data['category_id'];
        $this->model->specifications = $data['specifications'] ?? null;
        
        if($this->model->create()) {
            return [
                'success' => true,
                'message' => 'Modelo creado correctamente',
                'model_id' => $this->model->model_id
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Error al crear el modelo'
            ];
        }
    }
    
    // Crear nueva marca
    public function createBrand($data) {
        $this->brand->brand_name = $data['brand_name'];
        
        if($this->brand->create()) {
            return [
                'success' => true,
                'message' => 'Marca creada correctamente',
                'brand_id' => $this->brand->brand_id
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Error al crear la marca'
            ];
        }
    }
    
    // Crear nueva categoría
    public function createCategory($data) {
        $this->category->category_name = $data['category_name'];
        $this->category->description = $data['description'] ?? null;
        
        if($this->category->create()) {
            return [
                'success' => true,
                'message' => 'Categoría creada correctamente',
                'category_id' => $this->category->category_id
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Error al crear la categoría'
            ];
        }
    }
}
?> 