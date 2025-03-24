<?php
// Controlador AJAX para manejar solicitudes de creación de modelos, marcas y categorías

// Iniciar buffer de salida para capturar cualquier error o advertencia
ob_start();

// Iniciar sesión
session_start();

// Habilitar visualización de errores para depuración
ini_set('display_errors', 0); // Desactivamos la salida directa de errores
error_reporting(E_ALL);

try {
    // Cargar las dependencias necesarias
    require_once __DIR__ . '/config/database.php';
    require_once __DIR__ . '/app/controllers/InventoryController.php';

    // Verificar que sea una solicitud POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método no permitido. Solo se aceptan solicitudes POST.');
    }

    // Inicializar controlador
    $inventoryController = new InventoryController();

    // Procesar la acción solicitada
    $action = isset($_POST['action']) ? $_POST['action'] : '';

    if (empty($action)) {
        throw new Exception('No se especificó ninguna acción.');
    }

    // Respuesta por defecto
    $response = ['success' => false, 'message' => 'Acción desconocida'];

    switch ($action) {
        case 'create_model':
            // Validar datos obligatorios
            if (empty($_POST['model_name'])) {
                throw new Exception('El nombre del modelo es obligatorio.');
            }
            
            if (empty($_POST['brand_id'])) {
                throw new Exception('La marca es obligatoria.');
            }
            
            if (empty($_POST['category_id'])) {
                throw new Exception('La categoría es obligatoria.');
            }
            
            // Preparar datos para crear el modelo
            $modelData = [
                'model_name' => $_POST['model_name'],
                'brand_id' => $_POST['brand_id'],
                'category_id' => $_POST['category_id'],
                'specifications' => $_POST['specifications'] ?? null
            ];
            
            // Intentar crear el modelo
            $result = $inventoryController->createModel($modelData);
            
            if (!isset($result['success'])) {
                throw new Exception('Respuesta no válida del controlador.');
            }
            
            $response = $result;
            break;
        
        case 'create_brand':
            // Validar nombre de marca
            if (empty($_POST['brand_name'])) {
                throw new Exception('El nombre de la marca es obligatorio.');
            }
            
            // Preparar datos para crear la marca
            $brandData = [
                'brand_name' => $_POST['brand_name']
            ];
            
            // Intentar crear la marca
            $result = $inventoryController->createBrand($brandData);
            
            if (!isset($result['success'])) {
                throw new Exception('Respuesta no válida del controlador.');
            }
            
            $response = $result;
            break;
        
        case 'create_category':
            // Validar nombre de categoría
            if (empty($_POST['category_name'])) {
                throw new Exception('El nombre de la categoría es obligatorio.');
            }
            
            // Preparar datos para crear la categoría
            $categoryData = [
                'category_name' => $_POST['category_name'],
                'description' => $_POST['description'] ?? null
            ];
            
            // Intentar crear la categoría
            $result = $inventoryController->createCategory($categoryData);
            
            if (!isset($result['success'])) {
                throw new Exception('Respuesta no válida del controlador.');
            }
            
            $response = $result;
            break;
        
        case 'delete_brand':
            $brand_id = isset($_POST['brand_id']) ? (int)$_POST['brand_id'] : 0;
            $result = $inventoryController->deleteBrand($brand_id);
            break;
        
        case 'delete_category':
            $category_id = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 0;
            $result = $inventoryController->deleteCategory($category_id);
            break;
            
        default:
            throw new Exception("Acción '$action' no reconocida.");
    }

    // Limpiar cualquier salida anterior
    ob_clean();
    
    // Enviar respuesta como JSON
    header('Content-Type: application/json');
    echo json_encode($response);
    
} catch (Exception $e) {
    // Log del error
    error_log('Error en ajax_handler.php: ' . $e->getMessage());
    
    // Capturar cualquier salida no deseada
    ob_clean();
    
    // Responder con mensaje de error
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false, 
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
// Asegurarse de que todo el buffer se ha enviado y cerrado
ob_end_flush();
?> 