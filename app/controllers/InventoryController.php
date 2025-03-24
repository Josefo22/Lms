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
    private $database;

    public function __construct() {
        // Conectar a la base de datos
        require_once __DIR__ . '/../../config/database.php';
        $database = new Database();
        $this->db = $database->connect();
        $this->database = $database->connect();
        
        // Inicializar modelos
        require_once __DIR__ . '/../models/Hardware.php';
        require_once __DIR__ . '/../models/Client.php';
        require_once __DIR__ . '/../models/Model.php';
        require_once __DIR__ . '/../models/Brand.php';
        require_once __DIR__ . '/../models/HardwareCategory.php';
        require_once __DIR__ . '/../models/Location.php';
        require_once __DIR__ . '/../models/AssignmentHistory.php';
        require_once __DIR__ . '/../models/User.php';
        
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
        $current_user_id = $this->hardware->current_user_id;
        $current_client_id = $this->hardware->client_id;
        
        // Asignar datos al modelo
        $this->hardware->serial_number = $data['serial_number'];
        $this->hardware->asset_tag = !empty($data['asset_tag']) ? $data['asset_tag'] : null;
        $this->hardware->model_id = $data['model_id'];
        $this->hardware->purchase_date = $data['purchase_date'];
        $this->hardware->warranty_expiry_date = !empty($data['warranty_expiry_date']) ? $data['warranty_expiry_date'] : null;
        $this->hardware->status = $data['status'];
        $this->hardware->condition_status = $data['condition_status'];
        $this->hardware->notes = !empty($data['notes']) ? $data['notes'] : null;
        
        // Manejar valores nulos para claves foráneas - priorizar usuario sobre cliente
        $this->hardware->current_user_id = !empty($data['user_id']) ? $data['user_id'] : null;
        $this->hardware->client_id = !empty($data['client_id']) ? $data['client_id'] : null;
        $this->hardware->location_id = !empty($data['location_id']) ? $data['location_id'] : null;
        
        // Actualizar el hardware
        if($this->hardware->update()) {
            // Si cambió el usuario y se registra como nueva asignación
            if (isset($data['register_assignment']) && $data['register_assignment'] == '1' && 
                (!empty($data['user_id']) && $data['user_id'] != $current_user_id)) {
                
                $this->assignment->hardware_id = $id;
                $this->assignment->client_id = !empty($data['client_id']) ? $data['client_id'] : null;
                $this->assignment->user_id = !empty($data['user_id']) ? $data['user_id'] : null;
                $this->assignment->assigned_date = !empty($data['assignment_date']) ? $data['assignment_date'] : date('Y-m-d H:i:s');
                $this->assignment->assigned_by = $_SESSION['user_id'] ?? 1; // ID del usuario autenticado
                $this->assignment->assignment_notes = !empty($data['assignment_notes']) ? $data['assignment_notes'] : "Actualización de asignación";
                $this->assignment->create();
            }
            
            return [
                'success' => true,
                'message' => 'Hardware actualizado correctamente'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Error al actualizar el hardware: ' . ($this->hardware->error_message ?? 'Error desconocido')
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
    
    // Obtener opciones para formularios (modelos, categorías, marcas, etc.)
    public function getFormOptions() {
        $users = [];
        
        try {
            // Primero intentamos utilizar el método alternativo que hemos mejorado
            $users = $this->getUsersAlternative();
            
            // Solo si esto falla, intentamos el UserController
            if (empty($users)) {
                require_once __DIR__ . '/UserController.php';
                if (class_exists('UserController')) {
                    $userController = new UserController();
                    if (method_exists($userController, 'getUsers')) {
                        $data = $userController->getUsers();
                        if (isset($data['users']) && is_array($data['users'])) {
                            $users = $data['users'];
                        }
                    }
                }
            }
        } catch (Exception $e) {
            error_log('Error al obtener usuarios: ' . $e->getMessage());
            // En caso de error, dejamos $users como array vacío
        }
        
        // Asegurar que users sea un array
        if (!is_array($users)) {
            $users = [];
        }
        
        // Intentar leer los demás datos
        try {
            $models = $this->model->read();
            $models = is_object($models) && method_exists($models, 'fetchAll') ? $models->fetchAll(PDO::FETCH_ASSOC) : [];
        } catch (Exception $e) {
            error_log('Error al obtener modelos: ' . $e->getMessage());
            $models = [];
        }
        
        try {
            $brands = $this->brand->read();
            $brands = is_object($brands) && method_exists($brands, 'fetchAll') ? $brands->fetchAll(PDO::FETCH_ASSOC) : [];
        } catch (Exception $e) {
            error_log('Error al obtener marcas: ' . $e->getMessage());
            $brands = [];
        }
        
        try {
            $categories = $this->category->read();
            $categories = is_object($categories) && method_exists($categories, 'fetchAll') ? $categories->fetchAll(PDO::FETCH_ASSOC) : [];
        } catch (Exception $e) {
            error_log('Error al obtener categorías: ' . $e->getMessage());
            $categories = [];
        }
        
        try {
            $clients = $this->client->read();
            $clients = is_object($clients) && method_exists($clients, 'fetchAll') ? $clients->fetchAll(PDO::FETCH_ASSOC) : [];
        } catch (Exception $e) {
            error_log('Error al obtener clientes: ' . $e->getMessage());
            $clients = [];
        }
        
        try {
            $locations = $this->location->read();
            $locations = is_object($locations) && method_exists($locations, 'fetchAll') ? $locations->fetchAll(PDO::FETCH_ASSOC) : [];
        } catch (Exception $e) {
            error_log('Error al obtener ubicaciones: ' . $e->getMessage());
            $locations = [];
        }
        
        return [
            'models' => $models,
            'brands' => $brands,
            'categories' => $categories,
            'clients' => $clients,
            'locations' => $locations,
            'users' => $users
        ];
    }
    
    // Método alternativo para obtener usuarios directamente de la base de datos
    private function getUsersAlternative() {
        try {
            // Consulta para obtener usuarios con manejo adecuado para PHP 8.2+
            // Usamos COALESCE para garantizar que siempre haya un valor en name
            $query = "SELECT 
                        user_id, 
                        COALESCE(
                            CONCAT(first_name, ' ', last_name),
                            first_name,
                            last_name,
                            CONCAT('Usuario #', user_id)
                        ) as name 
                     FROM 
                        users 
                     ORDER BY 
                        first_name, last_name";
            
            $stmt = $this->database->prepare($query);
            $stmt->execute();
            
            // Obtener resultados como array asociativo
            $users = [];
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                // Garantizar que siempre haya un valor no nulo para name
                $userId = $row['user_id'] ?? 0;
                if (!isset($row['name']) || trim($row['name']) === '') {
                    $row['name'] = 'Usuario #' . $userId;
                }
                if (isset($row['user_id'])) {
                    $users[] = $row;
                }
            }
            
            return $users;
        } catch (Exception $e) {
            error_log('Error en getUsersAlternative: ' . $e->getMessage());
            // Datos mínimos para evitar errores en la interfaz
            return [
                ['user_id' => '0', 'name' => 'Error al cargar usuarios']
            ];
        }
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
    
    // Crear una nueva marca
    public function createBrand($data) {
        // Verificar si la marca ya existe
        $query = "SELECT * FROM brands WHERE brand_name = :brand_name";
        $stmt = $this->database->prepare($query);
        $brandName = htmlspecialchars(strip_tags($data['brand_name']));
        $stmt->bindParam(':brand_name', $brandName);
        $stmt->execute();
        
        if($stmt->rowCount() > 0) {
            return [
                'success' => false,
                'message' => 'Ya existe una marca con ese nombre'
            ];
        }
        
        // Asignar nombre de marca
        $this->brand->brand_name = $data['brand_name'];
        
        // Crear marca
        try {
            if ($this->brand->create()) {
                return [
                    'success' => true,
                    'message' => 'Marca creada correctamente',
                    'brand_id' => $this->brand->brand_id
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error al crear la marca: ' . ($this->brand->error_message ?? 'Error desconocido')
                ];
            }
        } catch (Exception $e) {
            error_log('Error en createBrand: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }
    
    // Crear una nueva categoría
    public function createCategory($data) {
        // Verificar si la categoría ya existe
        $query = "SELECT * FROM hardwarecategories WHERE category_name = :category_name";
        $stmt = $this->database->prepare($query);
        $categoryName = htmlspecialchars(strip_tags($data['category_name']));
        $stmt->bindParam(':category_name', $categoryName);
        $stmt->execute();
        
        if($stmt->rowCount() > 0) {
            return [
                'success' => false,
                'message' => 'Ya existe una categoría con ese nombre'
            ];
        }
        
        // Asignar datos a la categoría
        $this->category->category_name = $data['category_name'];
        $this->category->description = $data['description'] ?? null;
        
        // Crear categoría
        try {
            if ($this->category->create()) {
                return [
                    'success' => true,
                    'message' => 'Categoría creada correctamente',
                    'category_id' => $this->category->category_id
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error al crear la categoría: ' . ($this->category->error_message ?? 'Error desconocido')
                ];
            }
        } catch (Exception $e) {
            error_log('Error en createCategory: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }
    
    // Crear un nuevo modelo
    public function createModel($data) {
        // Verificar que existan la marca y categoría
        $brandExists = $this->verifyBrandExists($data['brand_id']);
        if (!$brandExists) {
            return [
                'success' => false,
                'message' => 'La marca seleccionada no existe'
            ];
        }
        
        $categoryExists = $this->verifyCategoryExists($data['category_id']);
        if (!$categoryExists) {
            return [
                'success' => false,
                'message' => 'La categoría seleccionada no existe'
            ];
        }
        
        // Verificar si ya existe un modelo con ese nombre para esa marca
        $query = "SELECT * FROM models WHERE model_name = :model_name AND brand_id = :brand_id";
        $stmt = $this->database->prepare($query);
        $modelName = htmlspecialchars(strip_tags($data['model_name']));
        $brandId = $data['brand_id'];
        $stmt->bindParam(':model_name', $modelName);
        $stmt->bindParam(':brand_id', $brandId);
        $stmt->execute();
        
        if($stmt->rowCount() > 0) {
            return [
                'success' => false,
                'message' => 'Ya existe un modelo con ese nombre para esta marca'
            ];
        }
        
        // Asignar datos al modelo
        $this->model->model_name = $data['model_name'];
        $this->model->brand_id = $data['brand_id'];
        $this->model->category_id = $data['category_id'];
        $this->model->specifications = $data['specifications'] ?? null;
        
        // Crear modelo
        try {
            if ($this->model->create()) {
                return [
                    'success' => true,
                    'message' => 'Modelo creado correctamente',
                    'model_id' => $this->model->model_id
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error al crear el modelo: ' . ($this->model->error_message ?? 'Error desconocido')
                ];
            }
        } catch (Exception $e) {
            error_log('Error en createModel: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }
    
    // Verificar si existe una marca
    private function verifyBrandExists($brandId) {
        $query = "SELECT * FROM brands WHERE brand_id = :brand_id";
        $stmt = $this->database->prepare($query);
        $stmt->bindParam(':brand_id', $brandId);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }
    
    // Verificar si existe una categoría
    private function verifyCategoryExists($categoryId) {
        $query = "SELECT * FROM hardwarecategories WHERE category_id = :category_id";
        $stmt = $this->database->prepare($query);
        $stmt->bindParam(':category_id', $categoryId);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }
    
    // Eliminar una marca
    public function deleteBrand($brand_id) {
        $this->brand->brand_id = $brand_id;
        
        // Verificar si existe la marca
        if (!$this->brand->read_single()) {
            return [
                'success' => false,
                'message' => 'La marca no existe'
            ];
        }
        
        // Intentar eliminar
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
    }
    
    // Eliminar una categoría
    public function deleteCategory($category_id) {
        $this->category->category_id = $category_id;
        
        // Verificar si existe la categoría
        if (!$this->category->read_single()) {
            return [
                'success' => false,
                'message' => 'La categoría no existe'
            ];
        }
        
        // Intentar eliminar
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
    }
    
    // Agregar nuevo hardware desde la vista add_hardware
    public function addHardware($data) {
        // Asignar datos al modelo
        $this->hardware->serial_number = $data['serial_number'];
        $this->hardware->asset_tag = !empty($data['asset_tag']) ? $data['asset_tag'] : null;
        $this->hardware->model_id = $data['model_id'];
        $this->hardware->purchase_date = $data['purchase_date'];
        $this->hardware->warranty_expiry_date = !empty($data['warranty_expiry_date']) ? $data['warranty_expiry_date'] : null;
        $this->hardware->status = $data['status'];
        $this->hardware->condition_status = $data['condition_status'];
        $this->hardware->notes = !empty($data['notes']) ? $data['notes'] : null;
        
        // Manejar valores nulos para claves foráneas
        // Priorizar usuario sobre cliente en la asignación
        $this->hardware->current_user_id = !empty($data['user_id']) ? $data['user_id'] : null;
        $this->hardware->client_id = !empty($data['client_id']) ? $data['client_id'] : null;
        $this->hardware->location_id = !empty($data['location_id']) ? $data['location_id'] : null;
        
        // Verificar si es necesario cambiar el estado basado en asignación
        if (!empty($data['user_id']) && $data['status'] == 'In Stock') {
            $this->hardware->status = 'In Use';
        }
        
        // Crear el hardware
        if($this->hardware->create()) {
            // Si hay asignación a usuario y se marcó la opción de registrar en historial
            if(isset($data['register_assignment']) && $data['register_assignment'] == '1' && 
              (!empty($data['user_id']))) {
                $this->assignment->hardware_id = $this->hardware->hardware_id;
                $this->assignment->client_id = !empty($data['client_id']) ? $data['client_id'] : null;
                $this->assignment->user_id = !empty($data['user_id']) ? $data['user_id'] : null;
                $this->assignment->assigned_date = date('Y-m-d H:i:s');
                $this->assignment->assigned_by = $_SESSION['user_id'] ?? 1; // ID del usuario autenticado
                $this->assignment->assignment_notes = !empty($data['assignment_notes']) ? $data['assignment_notes'] : "Asignación inicial al registrar el equipo";
                $this->assignment->create();
            }
            
            return [
                'success' => true,
                'message' => 'Equipo creado correctamente',
                'hardware_id' => $this->hardware->hardware_id
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Error al crear el equipo: ' . $this->hardware->error_message
            ];
        }
    }
}
?> 