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
        $this->hardware->asset_tag = $data['asset_tag'] ?? '';
        $this->hardware->model_id = $data['model_id'];
        $this->hardware->purchase_date = $data['purchase_date'] ?? null;
        $this->hardware->warranty_expiry_date = $data['warranty_expiry_date'] ?? null;
        $this->hardware->status = $data['status'];
        $this->hardware->condition_status = $data['condition_status'] ?? 'Good';
        $this->hardware->notes = $data['notes'] ?? '';
        
        // Manejo correcto de valores nulos o vacíos para claves foráneas
        $this->hardware->client_id = (!empty($data['client_id'])) ? $data['client_id'] : null;
        $this->hardware->location_id = (!empty($data['location_id'])) ? $data['location_id'] : null;
        $this->hardware->current_user_id = (!empty($data['current_user_id'])) ? $data['current_user_id'] : null;
        
        // Actualizar el hardware
        if($this->hardware->update()) {
            // Si cambió el cliente, crear registro en historial
            if($this->hardware->client_id != $current_client_id) {
                $this->assignment->hardware_id = $id;
                $this->assignment->client_id = $this->hardware->client_id;
                $this->assignment->assignment_date = date('Y-m-d H:i:s');
                $this->assignment->notes = "Actualización de asignación";
                
                // Solo crear el registro si hay un cliente asignado
                if($this->hardware->client_id) {
                    $this->assignment->create();
                }
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
    public function getCategories() {
        try {
            $result = $this->category->read();
            $categories = [];
            
            while($row = $result->fetch(PDO::FETCH_ASSOC)) {
                $categories[] = $row;
            }
            
            return $categories;
        } catch (PDOException $e) {
            // Manejar el error, posiblemente registrándolo
            error_log("Error al obtener categorías: " . $e->getMessage());
            return [];
        }
    }
    public function getLocations($client_id = null) {
        try {
            if ($client_id) {
                $query = "SELECT l.location_id, l.location_name, l.client_id, c.client_name 
                          FROM locations l 
                          LEFT JOIN clients c ON l.client_id = c.client_id 
                          WHERE l.client_id = :client_id 
                          ORDER BY l.location_name ASC";
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':client_id', $client_id, PDO::PARAM_INT);
            } else {
                $query = "SELECT l.location_id, l.location_name, l.client_id, c.client_name 
                          FROM locations l 
                          LEFT JOIN clients c ON l.client_id = c.client_id 
                          ORDER BY l.location_name ASC";
                $stmt = $this->db->prepare($query);
            }
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Manejar el error, posiblemente registrándolo
            error_log("Error al obtener ubicaciones: " . $e->getMessage());
            return [];
        }
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
        
        // Obtener usuarios
        $result['users'] = $this->getUsers();
        
        return $result;
    }
    
    // Obtener ubicaciones por cliente
    public function getLocationsByClient($client_id) {
        try {
            $result = $this->location->readByClient($client_id);
            $locations = [];
            
            while($row = $result->fetch(PDO::FETCH_ASSOC)) {
                $locations[] = $row;
            }
            
            return $locations;
        } catch (PDOException $e) {
            error_log("Error al obtener ubicaciones por cliente: " . $e->getMessage());
            return [];
        }
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
    
    // Crear una nueva marca
    public function createBrand($data) {
        try {
            // Asegurarse de que la clase Brand existe
            if (!class_exists('Brand')) {
                throw new Exception('La clase Brand no está definida');
            }
            
            // Asignar datos al modelo
            $this->brand->brand_name = $data['brand_name'];
            
            // Crear la marca
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
        } catch (Exception $e) {
            error_log("Error al crear marca: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error al crear la marca: ' . $e->getMessage()
            ];
        }
    }
    
    // Crear una nueva categoría
    public function createCategory($data) {
        try {
            // Asegurarse de que la clase HardwareCategory existe
            if (!class_exists('HardwareCategory')) {
                throw new Exception('La clase HardwareCategory no está definida');
            }
            
            // Asignar datos al modelo
            $this->category->category_name = $data['category_name'];
            $this->category->description = $data['description'] ?? '';
            
            // Crear la categoría
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
        } catch (Exception $e) {
            error_log("Error al crear categoría: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error al crear la categoría: ' . $e->getMessage()
            ];
        }
    }
    
    // Obtener todo el hardware con información relacionada
    public function getAllHardware() {
        $query = "SELECT 
            h.*,
            m.model_name,
            b.brand_name,
            c.client_name,
            l.location_name,
            CONCAT(u.first_name, ' ', u.last_name) as user_name
        FROM hardware h
        LEFT JOIN models m ON h.model_id = m.model_id
        LEFT JOIN brands b ON m.brand_id = b.brand_id
        LEFT JOIN clients c ON h.client_id = c.client_id
        LEFT JOIN locations l ON h.location_id = l.location_id
        LEFT JOIN users u ON h.current_user_id = u.user_id
        ORDER BY h.created_at DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener estadísticas del inventario
    public function getInventoryStats() {
        $stats = [
            'total' => 0,
            'in_stock' => 0,
            'in_use' => 0,
            'in_repair' => 0
        ];
        
        $query = "SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status = 'In Stock' THEN 1 ELSE 0 END) as in_stock,
            SUM(CASE WHEN status = 'In Use' THEN 1 ELSE 0 END) as in_use,
            SUM(CASE WHEN status = 'In Repair' THEN 1 ELSE 0 END) as in_repair
        FROM hardware";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            $stats = [
                'total' => (int)$result['total'],
                'in_stock' => (int)$result['in_stock'],
                'in_use' => (int)$result['in_use'],
                'in_repair' => (int)$result['in_repair']
            ];
        }
        
        return $stats;
    }

    // Obtener clientes para el selector
    public function getClients() {
        $query = "SELECT client_id, client_name FROM clients ORDER BY client_name";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener usuarios para el formulario
    public function getUsers() {
        $query = "SELECT user_id, CONCAT(first_name, ' ', last_name) AS name, 
                 job_title, client_id FROM users ORDER BY first_name, last_name";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener todas las marcas
    public function getBrands() {
        try {
            $result = $this->brand->read();
            $brands = [];
            
            while($row = $result->fetch(PDO::FETCH_ASSOC)) {
                $brands[] = $row;
            }
            
            return $brands;
        } catch (PDOException $e) {
            error_log("Error al obtener marcas: " . $e->getMessage());
            return [];
        }
    }
    
    // Obtener modelos por marca
    public function getModelsByBrand($brand_id) {
        try {
            $result = $this->model->getByBrand($brand_id);
            $models = [];
            
            while($row = $result->fetch(PDO::FETCH_ASSOC)) {
                $models[] = $row;
            }
            
            return $models;
        } catch (PDOException $e) {
            error_log("Error al obtener modelos por marca: " . $e->getMessage());
            return [];
        }
    }

    // Actualizar una categoría existente
    public function updateCategory($id, $data) {
        try {
            // Validar ID
            if (!$id) {
                return [
                    'success' => false,
                    'message' => 'ID de categoría no proporcionado'
                ];
            }

            // Asignar datos al modelo
            $this->category->category_id = $id;
            
            // Verificar que la categoría existe
            if (!$this->category->read_single()) {
                return [
                    'success' => false,
                    'message' => 'Categoría no encontrada'
                ];
            }
            
            $this->category->category_name = $data['category_name'];
            $this->category->description = $data['description'] ?? '';
            
            // Actualizar la categoría
            if ($this->category->update()) {
                return [
                    'success' => true,
                    'message' => 'Categoría actualizada correctamente'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error al actualizar la categoría'
                ];
            }
        } catch (Exception $e) {
            error_log("Error al actualizar categoría: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error al actualizar la categoría: ' . $e->getMessage()
            ];
        }
    }
    
    // Eliminar una categoría
    public function deleteCategory($id) {
        try {
            // Validar ID
            if (!$id) {
                return [
                    'success' => false,
                    'message' => 'ID de categoría no proporcionado'
                ];
            }
            
            // Asignar ID al modelo
            $this->category->category_id = $id;
            
            // Verificar que la categoría existe
            if (!$this->category->read_single()) {
                return [
                    'success' => false,
                    'message' => 'Categoría no encontrada'
                ];
            }
            
            // Intentar eliminar la categoría
            if ($this->category->delete()) {
                return [
                    'success' => true,
                    'message' => 'Categoría eliminada correctamente'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'No se puede eliminar la categoría porque tiene modelos asociados'
                ];
            }
        } catch (Exception $e) {
            error_log("Error al eliminar categoría: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error al eliminar la categoría: ' . $e->getMessage()
            ];
        }
    }

    // Actualizar una marca existente
    public function updateBrand($id, $data) {
        try {
            // Validar ID
            if (!$id) {
                return [
                    'success' => false,
                    'message' => 'ID de marca no proporcionado'
                ];
            }

            // Asignar datos al modelo
            $this->brand->brand_id = $id;
            
            // Verificar que la marca existe
            if (!$this->brand->read_single()) {
                return [
                    'success' => false,
                    'message' => 'Marca no encontrada'
                ];
            }
            
            $this->brand->brand_name = $data['brand_name'];
            
            // Actualizar la marca
            if ($this->brand->update()) {
                return [
                    'success' => true,
                    'message' => 'Marca actualizada correctamente'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error al actualizar la marca'
                ];
            }
        } catch (Exception $e) {
            error_log("Error al actualizar marca: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error al actualizar la marca: ' . $e->getMessage()
            ];
        }
    }
    
    // Eliminar una marca
    public function deleteBrand($id) {
        try {
            // Validar ID
            if (!$id) {
                return [
                    'success' => false,
                    'message' => 'ID de marca no proporcionado'
                ];
            }
            
            // Asignar ID al modelo
            $this->brand->brand_id = $id;
            
            // Verificar que la marca existe
            if (!$this->brand->read_single()) {
                return [
                    'success' => false,
                    'message' => 'Marca no encontrada'
                ];
            }
            
            // Intentar eliminar la marca
            if ($this->brand->delete()) {
                return [
                    'success' => true,
                    'message' => 'Marca eliminada correctamente'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'No se puede eliminar la marca porque tiene modelos asociados'
                ];
            }
        } catch (Exception $e) {
            error_log("Error al eliminar marca: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error al eliminar la marca: ' . $e->getMessage()
            ];
        }
    }

    // Obtener todos los modelos
    public function getModels() {
        try {
            $query = "SELECT m.model_id, m.model_name, m.brand_id, m.category_id, m.specifications, 
                          b.brand_name, c.category_name
                      FROM models m
                      JOIN brands b ON m.brand_id = b.brand_id
                      JOIN hardwarecategories c ON m.category_id = c.category_id
                      ORDER BY m.model_name";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error al obtener modelos: " . $e->getMessage());
            return [];
        }
    }
    
    // Actualizar un modelo existente
    public function updateModel($id, $data) {
        try {
            // Validar ID
            if (!$id) {
                return [
                    'success' => false,
                    'message' => 'ID de modelo no proporcionado'
                ];
            }
            
            // Asignar datos al modelo
            $this->model->model_id = $id;
            
            // Verificar que el modelo existe
            if (!$this->model->read_single()) {
                return [
                    'success' => false,
                    'message' => 'Modelo no encontrado'
                ];
            }
            
            $this->model->model_name = $data['model_name'];
            $this->model->brand_id = $data['brand_id'];
            $this->model->category_id = $data['category_id'];
            $this->model->specifications = $data['specifications'] ?? '';
            
            // Actualizar el modelo
            if ($this->model->update()) {
                return [
                    'success' => true,
                    'message' => 'Modelo actualizado correctamente'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error al actualizar el modelo'
                ];
            }
        } catch (Exception $e) {
            error_log("Error al actualizar modelo: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error al actualizar el modelo: ' . $e->getMessage()
            ];
        }
    }
    
    // Eliminar un modelo
    public function deleteModel($id) {
        try {
            // Validar ID
            if (!$id) {
                return [
                    'success' => false,
                    'message' => 'ID de modelo no proporcionado'
                ];
            }
            
            // Asignar ID al modelo
            $this->model->model_id = $id;
            
            // Verificar que el modelo existe
            if (!$this->model->read_single()) {
                return [
                    'success' => false,
                    'message' => 'Modelo no encontrado'
                ];
            }
            
            // Verificar si hay hardware asociado
            $query = "SELECT COUNT(*) as count FROM hardware WHERE model_id = :model_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':model_id', $id);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($row['count'] > 0) {
                return [
                    'success' => false,
                    'message' => 'No se puede eliminar el modelo porque tiene equipos asociados'
                ];
            }
            
            // Intentar eliminar el modelo
            if ($this->model->delete()) {
                return [
                    'success' => true,
                    'message' => 'Modelo eliminado correctamente'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error al eliminar el modelo'
                ];
            }
        } catch (Exception $e) {
            error_log("Error al eliminar modelo: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error al eliminar el modelo: ' . $e->getMessage()
            ];
        }
    }
}
?> 