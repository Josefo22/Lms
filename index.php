<?php
require_once 'config/config.php';
require_once 'config/database.php';

// Determinar la página a mostrar
$page = $_GET['page'] ?? 'dashboard';

// Definir páginas válidas
$valid_pages = [
    'dashboard',
    'inventory',
    'clients',
    'users',
    'reports',
    'settings',
    'notifications',
    'login',
    'api',
    'support',
    'add_hardware',
    '404',
    'management'
];

// Verificar si la página solicitada es válida
if(!in_array($page, $valid_pages)) {
    $page = '404';
}

// Manejar acciones para cada página
$action = $_GET['action'] ?? null;

// Verificar si el usuario está autenticado
requireLogin();

// Cargar componentes comunes
include_once 'app/includes/header.php';
include_once 'app/includes/navbar.php';
include_once 'app/includes/sidebar.php';

// Requerir controllers según la página
if($page == 'inventory') {
    require_once 'app/controllers/InventoryController.php';
    $inventoryController = new InventoryController();
}

// Manejar usuarios
if($page == 'users') {
    require_once 'app/controllers/UserController.php';
    $userController = new UserController();
    
    // Procesar acciones de usuarios
    if($action) {
        switch($action) {
            case 'add':
                if($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $result = $userController->createUser($_POST);
                    $_SESSION['message'] = $result['message'];
                    $_SESSION['message_type'] = $result['success'] ? 'success' : 'danger';
                    header('Location: index.php?page=users');
                    exit;
                }
                break;
                
            case 'update':
                if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'])) {
                    $result = $userController->updateUser($_POST['user_id'], $_POST);
                    $_SESSION['message'] = $result['message'];
                    $_SESSION['message_type'] = $result['success'] ? 'success' : 'danger';
                    header('Location: index.php?page=users&action=view&id=' . $_POST['user_id']);
                    exit;
                }
                break;
                
            case 'delete':
                if(isset($_GET['id'])) {
                    $result = $userController->deleteUser($_GET['id']);
                    $_SESSION['message'] = $result['message'];
                    $_SESSION['message_type'] = $result['success'] ? 'success' : 'danger';
                    header('Location: index.php?page=users');
                    exit;
                }
                break;
                
            case 'change_password':
                if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'])) {
                    $user_id = $_POST['user_id'];
                    $current_password = $_POST['current_password'];
                    $new_password = $_POST['new_password'];
                    
                    // Verificar que las contraseñas coincidan
                    if($_POST['new_password'] !== $_POST['confirm_password']) {
                        $_SESSION['message'] = 'Las contraseñas no coinciden';
                        $_SESSION['message_type'] = 'danger';
                    } else {
                        $result = $userController->changePassword($user_id, $current_password, $new_password);
                        $_SESSION['message'] = $result['message'];
                        $_SESSION['message_type'] = $result['success'] ? 'success' : 'danger';
                    }
                    
                    header('Location: index.php?page=users&action=view&id=' . $user_id);
                    exit;
                }
                break;
        }
    }
}

// API para obtener datos
if($page == 'api') {
    // Asegurarse de que la respuesta sea JSON
    header('Content-Type: application/json');
    
    switch($action) {
        case 'locations':
            if(isset($_GET['client_id'])) {
                require_once 'app/controllers/InventoryController.php';
                $inventoryController = new InventoryController();
                $locations = $inventoryController->getLocationsByClient($_GET['client_id']);
                echo json_encode($locations);
            } else {
                echo json_encode(['error' => 'Cliente no especificado']);
            }
            exit;
            
        case 'user_equipment':
            if(isset($_GET['user_id'])) {
                require_once 'app/controllers/InventoryController.php';
                $inventoryController = new InventoryController();
                $hardware = $inventoryController->getHardwareByUser($_GET['user_id']);
                echo json_encode($hardware);
            } else {
                echo json_encode(['error' => 'Usuario no especificado']);
            }
            exit;
            
        case 'users_by_client':
            if(isset($_GET['client_id'])) {
                require_once 'app/controllers/UserController.php';
                $userController = new UserController();
                $users = $userController->getUsersByClient($_GET['client_id']);
                echo json_encode($users);
            } else {
                echo json_encode(['error' => 'Cliente no especificado']);
            }
            exit;
    }
}

// Cargar la vista apropiada
if($page == 'api') {
    // Ya se ha manejado en el bloque anterior
    exit;
} elseif($page == 'login') {
    require_once 'app/views/login.php';
} else {
    // Para todas las demás páginas, verificar autenticación
    // ... existing code ...
    
    // Incluir el header y la vista correspondiente
    include_once 'app/views/partials/header.php';
    
    // Cargar vista específica según acción
    if($page == 'inventory' && $action == 'view' && isset($_GET['id'])) {
        include_once 'app/views/inventory_view.php';
    } elseif($page == 'inventory' && $action == 'edit' && isset($_GET['id'])) {
        include_once 'app/views/inventory_edit.php';
    } elseif($page == 'add_hardware') {
        include_once 'app/views/add_hardware.php';
    } elseif($page == 'users' && $action == 'view' && isset($_GET['id'])) {
        include_once 'app/views/user_view.php';
    } elseif($page == 'users' && $action == 'edit' && isset($_GET['id'])) {
        include_once 'app/views/user_edit.php';
    } else {
        // Cargar vista principal por defecto
        if(file_exists('app/views/' . $page . '.php')) {
            include_once 'app/views/' . $page . '.php';
        } else {
            include_once 'app/views/404.php';
        }
    }
    
    // Incluir footer
    include_once 'app/views/partials/footer.php';
}
?>