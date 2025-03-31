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
        
        if($this->user->read_single()) {
            return [
                'user' => [
                    'user_id' => $this->user->user_id,
                    'username' => $this->user->username,
                    'employee_id' => $this->user->employee_id,
                    'first_name' => $this->user->first_name,
                    'last_name' => $this->user->last_name,
                    'email' => $this->user->email,
                    'phone' => $this->user->phone,
                    'job_title' => $this->user->job_title,
                    'department' => $this->user->department,
                    'client_id' => $this->user->client_id,
                    'location_id' => $this->user->location_id,
                    'is_remote' => $this->user->is_remote,
                    'status' => $this->user->status,
                    'role' => $this->user->role,
                    'created_at' => $this->user->created_at,
                    'updated_at' => $this->user->updated_at
                ]
            ];
        } else {
            return ['error' => 'Usuario no encontrado'];
        }
    }
    
    // Crear nuevo usuario
    public function createUser($data) {
        // Verificar que el email no esté en uso
        if($this->user->emailExists($data['email'])) {
            return [
                'success' => false,
                'message' => 'El email ya está en uso'
            ];
        }
        
        // Asignar datos al modelo
        $this->user->username = $data['username'] ?? null;
        $this->user->password = $data['password'];
        $this->user->employee_id = $data['employee_id'];
        $this->user->first_name = $data['first_name'];
        $this->user->last_name = $data['last_name'];
        $this->user->email = $data['email'];
        $this->user->phone = $data['phone'] ?? null;
        $this->user->job_title = $data['job_title'] ?? null;
        $this->user->department = $data['department'] ?? null;
        $this->user->client_id = $data['client_id'] ?? null;
        $this->user->location_id = $data['location_id'] ?? null;
        $this->user->is_remote = $data['is_remote'] ?? 0;
        $this->user->status = $data['status'] ?? 'active';
        $this->user->role = $data['role'] ?? 'user';
        
        // Crear el usuario
        if($this->user->create()) {
            return [
                'success' => true,
                'message' => 'Usuario creado correctamente',
                'user_id' => $this->user->user_id
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Error al crear el usuario'
            ];
        }
    }
    
    // Actualizar usuario existente
    public function updateUser($id, $data) {
        // Verificar que el usuario existe
        $this->user->user_id = $id;
        if(!$this->user->read_single()) {
            return [
                'success' => false,
                'message' => 'Usuario no encontrado'
            ];
        }
        
        // Verificar que el email no esté en uso por otro usuario
        if(isset($data['email']) && $data['email'] !== $this->user->email && $this->user->emailExists($data['email'], $id)) {
            return [
                'success' => false,
                'message' => 'El email ya está en uso por otro usuario'
            ];
        }
        
        // Asignar datos al modelo
        $this->user->username = $data['username'] ?? $this->user->username;
        if(isset($data['password']) && !empty($data['password'])) {
            $this->user->password = $data['password'];
        }
        $this->user->employee_id = $data['employee_id'] ?? $this->user->employee_id;
        $this->user->first_name = $data['first_name'] ?? $this->user->first_name;
        $this->user->last_name = $data['last_name'] ?? $this->user->last_name;
        $this->user->email = $data['email'] ?? $this->user->email;
        $this->user->phone = $data['phone'] ?? $this->user->phone;
        $this->user->job_title = $data['job_title'] ?? $this->user->job_title;
        $this->user->department = $data['department'] ?? $this->user->department;
        $this->user->client_id = $data['client_id'] ?? $this->user->client_id;
        $this->user->location_id = $data['location_id'] ?? $this->user->location_id;
        $this->user->is_remote = $data['is_remote'] ?? $this->user->is_remote;
        $this->user->status = $data['status'] ?? $this->user->status;
        $this->user->role = $data['role'] ?? $this->user->role;
        
        // Actualizar el usuario
        if($this->user->update()) {
            return [
                'success' => true,
                'message' => 'Usuario actualizado correctamente'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Error al actualizar el usuario'
            ];
        }
    }
    
    // Eliminar usuario
    public function deleteUser($id) {
        $this->user->user_id = $id;
        
        // Intentar eliminar
        if($this->user->delete()) {
            return [
                'success' => true,
                'message' => 'Usuario eliminado correctamente'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'No se puede eliminar el usuario porque tiene equipos asignados'
            ];
        }
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
    
    // Obtener usuarios por cliente para asignación de hardware
    public function getUsersByClient($client_id) {
        $userData = $this->user->getByClient($client_id);
        $result = [];
        while($row = $userData->fetch(PDO::FETCH_ASSOC)) {
            $result[] = [
                'user_id' => $row['user_id'],
                'name' => $row['first_name'] . ' ' . $row['last_name'],
                'job_title' => $row['job_title']
            ];
        }
        return $result;
    }
    
    // Cambiar contraseña de usuario
    public function changePassword($id, $current_password, $new_password) {
        $this->user->user_id = $id;
        
        if(!$this->user->read_single()) {
            return [
                'success' => false,
                'message' => 'Usuario no encontrado'
            ];
        }
        
        // Verificar contraseña actual
        $result = $this->user->login($this->user->email, $current_password);
        if(!$result) {
            return [
                'success' => false,
                'message' => 'Contraseña actual incorrecta'
            ];
        }
        
        // Actualizar contraseña
        $this->user->password = $new_password;
        
        if($this->user->update()) {
            return [
                'success' => true,
                'message' => 'Contraseña actualizada correctamente'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Error al actualizar la contraseña'
            ];
        }
    }
}
?> 