<?php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'app/controllers/AuthController.php';
require_once 'app/controllers/DashboardController.php';
require_once 'app/controllers/InventoryController.php';
require_once 'app/controllers/SupportController.php';

// Verificar si el usuario está autenticado
if (!isset($_SESSION['user_id']) && !in_array($_GET['page'] ?? '', ['login', 'register'])) {
    header('Location: index.php?page=login');
    exit;
}

// Manejar la acción de logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    $auth = new AuthController();
    $auth->logout();
    header('Location: index.php?page=login');
    exit;
}

// Obtener la página solicitada
$page = $_GET['page'] ?? 'dashboard';

// Definir páginas válidas
$valid_pages = [
    'dashboard',
    'inventory',
    'add_hardware',
    'clients',
    'users',
    'reports',
    'settings',
    'notifications',
    'login',
    'api',
    'support',
    '404'
];

// Verificar si la página solicitada es válida
if(!in_array($page, $valid_pages)) {
    $page = '404';
}

// Manejar acciones para cada página
$action = $_GET['action'] ?? null;

// Cargar componentes comunes
include_once 'app/includes/header.php';
include_once 'app/includes/navbar.php';
include_once 'app/includes/sidebar.php';

// Requerir controllers según la página
if($page == 'inventory' || $page == 'add_hardware') {
    require_once 'app/controllers/InventoryController.php';
    $inventoryController = new InventoryController();
    
    if($page == 'add_hardware') {
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $result = $inventoryController->createHardware($_POST);
            if($result['success']) {
                $_SESSION['message'] = 'Equipo agregado exitosamente';
                $_SESSION['message_type'] = 'success';
                header('Location: index.php?page=inventory');
                exit;
            } else {
                $_SESSION['message'] = $result['message'];
                $_SESSION['message_type'] = 'danger';
            }
        }
        include 'app/views/inventory/add.php';
        exit;
    }
    
    // Resto del manejo de inventario...
    if($action) {
        switch($action) {
            case 'view':
                if(isset($_GET['id'])) {
                    include 'app/views/inventory/view.php';
                }
                break;
            case 'edit':
                if(isset($_GET['id'])) {
                    if($_SERVER['REQUEST_METHOD'] === 'POST') {
                        $result = $inventoryController->updateHardware($_GET['id'], $_POST);
                        $_SESSION['message'] = $result['message'];
                        $_SESSION['message_type'] = $result['success'] ? 'success' : 'danger';
                        header('Location: index.php?page=inventory&action=view&id=' . $_GET['id']);
                        exit;
                    }
                    include 'app/views/inventory/edit.php';
                }
                break;
            default:
                include 'app/views/inventory/index.php';
        }
    } else {
        include 'app/views/inventory/index.php';
    }
    exit;
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

// Manejar soporte
if($page == 'support') {
    require_once 'app/controllers/SupportController.php';
    $supportController = new SupportController();
    
    // Procesar acciones de soporte
    if($action) {
        switch($action) {
            case 'create':
                if($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $result = $supportController->createTicket($_POST);
                    if ($result['success']) {
                        $_SESSION['message'] = 'Solicitud creada exitosamente';
                        $_SESSION['message_type'] = 'success';
                        header('Location: index.php?page=support');
                        exit;
                    } else {
                        $_SESSION['message'] = $result['message'];
                        $_SESSION['message_type'] = 'danger';
                    }
                }
                include 'app/views/support/create.php';
                break;
                
            case 'edit':
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $result = $supportController->updateTicket($_POST['request_id'], $_POST);
                    if ($result['success']) {
                        $_SESSION['message'] = 'Solicitud actualizada exitosamente';
                        $_SESSION['message_type'] = 'success';
                        header('Location: index.php?page=support&action=view&id=' . $_POST['request_id']);
                        exit;
                    } else {
                        $_SESSION['message'] = $result['message'];
                        $_SESSION['message_type'] = 'danger';
                    }
                }
                include 'app/views/support/edit.php';
                break;
                
            case 'view':
                include 'app/views/support/view.php';
                break;
                
            case 'resolve':
                if (isset($_GET['id'])) {
                    $result = $supportController->changeStatus($_GET['id'], 'Resolved');
                    if ($result['success']) {
                        $_SESSION['message'] = 'Solicitud marcada como resuelta';
                        $_SESSION['message_type'] = 'success';
                    } else {
                        $_SESSION['message'] = $result['message'];
                        $_SESSION['message_type'] = 'danger';
                    }
                    header('Location: index.php?page=support&action=view&id=' . $_GET['id']);
                    exit;
                }
                break;
                
            case 'close':
                if (isset($_GET['id'])) {
                    $result = $supportController->changeStatus($_GET['id'], 'Closed');
                    if ($result['success']) {
                        $_SESSION['message'] = 'Solicitud cerrada exitosamente';
                        $_SESSION['message_type'] = 'success';
                    } else {
                        $_SESSION['message'] = $result['message'];
                        $_SESSION['message_type'] = 'danger';
                    }
                    header('Location: index.php?page=support&action=view&id=' . $_GET['id']);
                    exit;
                }
                break;
                
            case 'reopen':
                if (isset($_GET['id'])) {
                    $result = $supportController->changeStatus($_GET['id'], 'New');
                    if ($result['success']) {
                        $_SESSION['message'] = 'Solicitud reabierta exitosamente';
                        $_SESSION['message_type'] = 'success';
                    } else {
                        $_SESSION['message'] = $result['message'];
                        $_SESSION['message_type'] = 'danger';
                    }
                    header('Location: index.php?page=support&action=view&id=' . $_GET['id']);
                    exit;
                }
                break;
                
            default:
                include 'app/views/support/index.php';
        }
    }
}

// API para obtener datos
if($page == 'api') {
    // Asegurarse de que la respuesta sea JSON
    header('Content-Type: application/json');
    
    switch($action) {
        case 'models':
            if(isset($_GET['brand_id'])) {
                require_once 'app/controllers/InventoryController.php';
                $inventoryController = new InventoryController();
                $models = $inventoryController->getModelsByBrand($_GET['brand_id']);
                echo json_encode($models);
            } else {
                echo json_encode(['error' => 'Marca no especificada']);
            }
            exit;
            
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

// Manejar clientes
if($page == 'clients') {
    require_once 'app/controllers/ClientController.php';
    $clientController = new ClientController();
    
    // Procesar acciones de clientes
    if($action) {
        switch($action) {
            case 'create':
                if($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $result = $clientController->createClient($_POST);
                    if ($result['success']) {
                        $_SESSION['message'] = 'Cliente creado exitosamente';
                        $_SESSION['message_type'] = 'success';
                        header('Location: index.php?page=clients');
                        exit;
                    } else {
                        $_SESSION['message'] = $result['message'];
                        $_SESSION['message_type'] = 'danger';
                    }
                }
                include 'app/views/clients/create.php';
                break;
                
            case 'edit':
                if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['client_id'])) {
                    $result = $clientController->updateClient($_POST['client_id'], $_POST);
                    if ($result['success']) {
                        $_SESSION['message'] = 'Cliente actualizado exitosamente';
                        $_SESSION['message_type'] = 'success';
                        header('Location: index.php?page=clients&action=view&id=' . $_POST['client_id']);
                        exit;
                    } else {
                        $_SESSION['message'] = $result['message'];
                        $_SESSION['message_type'] = 'danger';
                    }
                }
                include 'app/views/clients/edit.php';
                break;
                
            case 'view':
                include 'app/views/clients/view.php';
                break;
                
            case 'delete':
                if(isset($_GET['id'])) {
                    $result = $clientController->deleteClient($_GET['id']);
                    if ($result['success']) {
                        $_SESSION['message'] = 'Cliente eliminado exitosamente';
                        $_SESSION['message_type'] = 'success';
                    } else {
                        $_SESSION['message'] = $result['message'];
                        $_SESSION['message_type'] = 'danger';
                    }
                    header('Location: index.php?page=clients');
                    exit;
                }
                break;
                
            default:
                include 'app/views/clients/index.php';
        }
    } else {
        include 'app/views/clients/index.php';
    }
    exit; // Importante: evitar que se procese el resto del archivo
}

// Manejar reportes
if($page == 'reports') {
    require_once 'app/controllers/ReportController.php';
    
    $type = $_GET['type'] ?? '';
    $format = $_GET['format'] ?? '';
    
    $reportController = new ReportController();

    // Manejar exportaciones
    if ($format) {
        switch ($type) {
            case 'hardware_inventory':
                $data = $reportController->getHardwareInventory();
                if ($format === 'csv') {
                    $reportController->exportToCSV($data, 'inventario_hardware.csv');
                } elseif ($format === 'pdf') {
                    $reportController->exportToPDF($data, 'hardware_inventory', 'inventario_hardware.pdf');
                }
                exit;
        }
    }

    // Incluir header
    include_once 'app/views/partials/header.php';

    // Manejar vistas de reportes
    switch ($type) {
        case 'hardware_inventory':
            include 'app/views/reports/hardware_inventory.php';
            break;
        case 'hardware_status':
            include 'app/views/reports/hardware_status.php';
            break;
        case 'hardware_assignments':
            include 'app/views/reports/hardware_assignments.php';
            break;
        case 'client_resources':
            include 'app/views/reports/client_resources.php';
            break;
        case 'client_users':
            include 'app/views/reports/client_users.php';
            break;
        case 'client_support':
            include 'app/views/reports/client_support.php';
            break;
        case 'support_summary':
            include 'app/views/reports/support_summary.php';
            break;
        case 'support_performance':
            include 'app/views/reports/support_performance.php';
            break;
        case 'support_trends':
            include 'app/views/reports/support_trends.php';
            break;
        case 'audit_results':
            include 'app/views/reports/audit_results.php';
            break;
        case 'audit_discrepancies':
            include 'app/views/reports/audit_discrepancies.php';
            break;
        case 'audit_schedule':
            include 'app/views/reports/audit_schedule.php';
            break;
        case 'shipment_status':
            include 'app/views/reports/shipment_status.php';
            break;
        case 'shipment_history':
            include 'app/views/reports/shipment_history.php';
            break;
        case 'shipment_pending':
            include 'app/views/reports/shipment_pending.php';
            break;
        case 'custom_new':
            include 'app/views/reports/custom_new.php';
            break;
        case 'custom_saved':
            include 'app/views/reports/custom_saved.php';
            break;
        case 'custom_schedule':
            include 'app/views/reports/custom_schedule.php';
            break;
        default:
            include 'app/views/reports/index.php';
            break;
    }

    // Incluir footer
    include_once 'app/views/partials/footer.php';
} 
// Manejar configuración
elseif($page == 'settings') {
    require_once 'app/controllers/SettingsController.php';
    $settingsController = new SettingsController();
    
    // Incluir header y componentes comunes
    include_once 'app/views/partials/header.php';
    
    // Procesar acciones de configuración
    if($action) {
        switch($action) {
            case 'general':
                if($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $result = $settingsController->updateSettings($_POST);
                    $_SESSION['message'] = $result['message'];
                    $_SESSION['message_type'] = $result['success'] ? 'success' : 'danger';
                    header('Location: index.php?page=settings&action=general');
                    exit;
                }
                include 'app/views/settings/general.php';
                break;
                
            case 'notifications':
                if($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $result = $settingsController->updateSettings($_POST);
                    $_SESSION['message'] = $result['message'];
                    $_SESSION['message_type'] = $result['success'] ? 'success' : 'danger';
                    header('Location: index.php?page=settings&action=notifications');
                    exit;
                }
                include 'app/views/settings/notifications.php';
                break;
                
            case 'roles':
                if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['role_id'])) {
                    $result = $settingsController->updateRolePermissions($_POST['role_id'], $_POST['permissions'] ?? []);
                    $_SESSION['message'] = $result['message'];
                    $_SESSION['message_type'] = $result['success'] ? 'success' : 'danger';
                    header('Location: index.php?page=settings&action=roles');
                    exit;
                }
                include 'app/views/settings/roles.php';
                break;
                
            case 'email':
                if($_SERVER['REQUEST_METHOD'] === 'POST') {
                    if(isset($_POST['test'])) {
                        $result = $settingsController->testEmailSettings($_POST);
                    } else {
                        $result = $settingsController->updateSettings($_POST);
                    }
                    $_SESSION['message'] = $result['message'];
                    $_SESSION['message_type'] = $result['success'] ? 'success' : 'danger';
                    header('Location: index.php?page=settings&action=email');
                    exit;
                }
                include 'app/views/settings/email.php';
                break;
                
            case 'backup':
                if($_SERVER['REQUEST_METHOD'] === 'POST') {
                    if(isset($_POST['create_backup'])) {
                        $result = $settingsController->createBackup();
                    } else {
                        $result = $settingsController->updateSettings($_POST);
                    }
                    $_SESSION['message'] = $result['message'];
                    $_SESSION['message_type'] = $result['success'] ? 'success' : 'danger';
                    header('Location: index.php?page=settings&action=backup');
                    exit;
                }
                include 'app/views/settings/backup.php';
                break;
                
            default:
                include 'app/views/settings/index.php';
        }
    } else {
        include 'app/views/settings/index.php';
    }
    
    // Incluir footer
    include_once 'app/views/partials/footer.php';
    exit;
} 
else {
    // Para todas las demás páginas, verificar autenticación
    // ... existing code ...
    
    // Incluir el header y la vista correspondiente
    include_once 'app/views/partials/header.php';
    
    // Cargar vista específica según acción
    if($page == 'inventory' && $action == 'view' && isset($_GET['id'])) {
        include_once 'app/views/inventory_view.php';
    } elseif($page == 'inventory' && $action == 'edit' && isset($_GET['id'])) {
        include_once 'app/views/inventory_edit.php';
    } elseif($page == 'users' && $action == 'view' && isset($_GET['id'])) {
        include_once 'app/views/user_view.php';
    } elseif($page == 'users' && $action == 'edit' && isset($_GET['id'])) {
        include_once 'app/views/user_edit.php';
    } elseif($page == 'support' && $action == 'view' && isset($_GET['id'])) {
        include_once 'app/views/support/view.php';
    } elseif($page == 'support' && $action == 'edit' && isset($_GET['id'])) {
        include_once 'app/views/support/edit.php';
    } elseif($page == 'support' && $action == 'create') {
        include_once 'app/views/support/create.php';
    } elseif($page == 'clients' && $action == 'view' && isset($_GET['id'])) {
        include_once 'app/views/clients/view.php';
    } elseif($page == 'clients' && $action == 'edit' && isset($_GET['id'])) {
        include_once 'app/views/clients/edit.php';
    } elseif($page == 'clients' && $action == 'create') {
        include_once 'app/views/clients/create.php';
    } elseif($page == 'clients') {
        include_once 'app/views/clients/index.php';
    } else {
        // Cargar vista principal por defecto
        if(file_exists('app/views/' . $page . '.php')) {
            include_once 'app/views/' . $page . '.php';
        } elseif($page == 'support') {
            include_once 'app/views/support/index.php';
        } else {
            include_once 'app/views/404.php';
        }
    }
    
    // Incluir footer
    include_once 'app/views/partials/footer.php';
}

// Manejar páginas generales
switch($page) {
    case 'dashboard':
        // ... existing code ...
        break;
    case 'notifications':
        // ... existing code ...
        break;
    case 'login':
        // ... existing code ...
        break;
    case 'register':
        // ... existing code ...
        break;
    default:
        // ... existing code ...
        break;
}
?>