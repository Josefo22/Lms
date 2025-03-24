<?php
class UserController {
    private $db;
    private $user;
    private $client;
    private $location;

    public function __construct() {
        // Conectar a la base de datos
        require_once __DIR__ . '/../../config/database.php';
        $database = new Database();
        $this->db = $database->connect();
        
        // Inicializar modelos
        require_once __DIR__ . '/../models/User.php';
        require_once __DIR__ . '/../models/Client.php';
        require_once __DIR__ . '/../models/Location.php';
        
        $this->user = new User($this->db);
        $this->client = new Client($this->db);
        $this->location = new Location($this->db);
    }
    
    // Obtener lista de usuarios con filtros opcionales
    public function getUsers($client_id = null, $search = null) {
        $result = [];
        
        // Obtener clientes para filtros
        $clientData = $this->client->read();
        $result['clients'] = [];
        while($row = $clientData->fetch(PDO::FETCH_ASSOC)) {
            $result['clients'][] = $row;
        }
        
        // Obtener usuarios según filtros
        if($search) {
            $userData = $this->user->search($search, $client_id);
        } else {
            $userData = $this->user->read($client_id);
        }
        
        $result['users'] = [];
        while($row = $userData->fetch(PDO::FETCH_ASSOC)) {
            $result['users'][] = $row;
        }
        
        return $result;
    }
    
    // Obtener detalles de un usuario específico
    public function getUserDetails($id) {
        $this->user->user_id = $id;
        
        if($this->user->readOne()) {
            return [
                'user' => [
                    'user_id' => $this->user->user_id,
                    'employee_id' => $this->user->employee_id,
                    'first_name' => $this->user->first_name,
                    'last_name' => $this->user->last_name,
                    'email' => $this->user->email,
                    'phone' => $this->user->phone,
                    'job_title' => $this->user->job_title,
                    'department' => $this->user->department,
                    'client_id' => $this->user->client_id,
                    'client_name' => $this->user->client_name,
                    'contact_person' => $this->user->contact_person,
                    'contact_email' => $this->user->contact_email,
                    'contact_phone' => $this->user->contact_phone,
                    'location_id' => $this->user->location_id,
                    'location_name' => $this->user->location_name,
                    'location_address' => $this->user->location_address,
                    'location_city' => $this->user->location_city,
                    'location_state' => $this->user->location_state,
                    'location_country' => $this->user->location_country,
                    'is_remote' => $this->user->is_remote,
                    'status' => $this->user->status,
                    'created_at' => $this->user->created_at,
                    'updated_at' => $this->user->updated_at
                ]
            ];
        }
        
        return ['error' => 'Usuario no encontrado'];
    }
    
    // Crear un nuevo usuario
    public function createUser($data) {
        // Validar email único
        $this->user->email = $data['email'];
        $stmt = $this->user->search($this->user->email);
        
        if($stmt->rowCount() > 0) {
            return [
                'success' => false,
                'message' => 'El email ya está registrado.'
            ];
        }
        
        // Asignar valores
        $this->user->employee_id = $data['employee_id'];
        $this->user->first_name = $data['first_name'];
        $this->user->last_name = $data['last_name'];
        $this->user->email = $data['email'];
        $this->user->password = password_hash($data['password'], PASSWORD_DEFAULT);
        $this->user->phone = $data['phone'];
        $this->user->job_title = $data['job_title'];
        $this->user->department = $data['department'];
        $this->user->client_id = $data['client_id'] ? $data['client_id'] : null;
        $this->user->location_id = $data['location_id'] ? $data['location_id'] : null;
        $this->user->is_remote = isset($data['is_remote']) ? 1 : 0;
        $this->user->status = $data['status'];
        
        // Crear usuario
        if($this->user->create()) {
            return [
                'success' => true,
                'message' => 'Usuario creado correctamente.'
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Error al crear el usuario.'
        ];
    }
    
    // Actualizar usuario existente
    public function updateUser($id, $data) {
        // Cargar usuario actual
        $this->user->user_id = $id;
        if(!$this->user->readOne()) {
            return [
                'success' => false,
                'message' => 'Usuario no encontrado.'
            ];
        }
        
        // Verificar email único si cambia
        if($this->user->email != $data['email']) {
            $this->user->email = $data['email'];
            $stmt = $this->user->search($this->user->email);
            
            if($stmt->rowCount() > 0) {
                return [
                    'success' => false,
                    'message' => 'El email ya está registrado.'
                ];
            }
        }
        
        // Asignar valores
        $this->user->employee_id = $data['employee_id'];
        $this->user->first_name = $data['first_name'];
        $this->user->last_name = $data['last_name'];
        $this->user->email = $data['email'];
        $this->user->phone = $data['phone'];
        $this->user->job_title = $data['job_title'];
        $this->user->department = $data['department'];
        $this->user->client_id = $data['client_id'] ? $data['client_id'] : null;
        $this->user->location_id = $data['location_id'] ? $data['location_id'] : null;
        $this->user->is_remote = isset($data['is_remote']) ? 1 : 0;
        $this->user->status = $data['status'];
        
        // Actualizar usuario
        if($this->user->update()) {
            return [
                'success' => true,
                'message' => 'Usuario actualizado correctamente.'
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Error al actualizar el usuario.'
        ];
    }
    
    // Eliminar usuario
    public function deleteUser($id) {
        $this->user->user_id = $id;
        
        if($this->user->delete()) {
            return [
                'success' => true,
                'message' => 'Usuario eliminado correctamente.'
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Error al eliminar el usuario.'
        ];
    }
    
    // Obtener opciones para formularios de usuario
    public function getFormOptions() {
        $result = [];
        
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
        
        // Definir roles disponibles
        $result['roles'] = [
            ['id' => 'admin', 'name' => 'Administrador'],
            ['id' => 'manager', 'name' => 'Gerente'],
            ['id' => 'support', 'name' => 'Soporte Técnico'],
            ['id' => 'user', 'name' => 'Usuario']
        ];
        
        // Definir estados disponibles
        $result['statuses'] = [
            ['id' => 'active', 'name' => 'Activo'],
            ['id' => 'inactive', 'name' => 'Inactivo'],
            ['id' => 'suspended', 'name' => 'Suspendido']
        ];
        
        // Definir departamentos comunes
        $result['departments'] = [
            'IT', 'RRHH', 'Contabilidad', 'Ventas', 'Marketing', 
            'Operaciones', 'Administración', 'Finanzas', 'Legal', 'Producción'
        ];
        
        return $result;
    }
    
    // Obtener usuarios por cliente
    public function getUsersByClient($client_id) {
        $stmt = $this->user->readByClient($client_id);
        $users = [];
        
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $users[] = $row;
        }
        
        return $users;
    }
    
    // Cambiar contraseña de usuario
    public function changePassword($id, $current_password, $new_password) {
        // Verificar que el usuario existe
        $this->user->user_id = $id;
        if(!$this->user->readOne()) {
            return [
                'success' => false,
                'message' => 'Usuario no encontrado'
            ];
        }
        
        // Verificar contraseña actual
        // Implementar verificación adecuada aquí
        
        // Actualizar contraseña
        $this->user->password = password_hash($new_password, PASSWORD_DEFAULT);
        if($this->user->updatePassword()) {
            return [
                'success' => true,
                'message' => 'Contraseña actualizada correctamente'
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Error al actualizar la contraseña'
        ];
    }
}
?> 