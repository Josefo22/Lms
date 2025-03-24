<?php
// Headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Verificar si el archivo está siendo incluido directamente o a través de index.php
$base_path = '';
if (strpos($_SERVER['SCRIPT_FILENAME'], 'index.php') !== false) {
    // Si se incluye a través de index.php, usar ruta absoluta
    $base_path = '';
} else {
    // Si se accede directamente al controlador
    $base_path = '../../';
}

// Incluir base de datos y modelo
include_once $base_path . 'config/database.php';
include_once $base_path . 'app/models/SupportRequest.php';
include_once $base_path . 'app/models/User.php';
include_once $base_path . 'app/models/Hardware.php';

// Obtener conexión a la base de datos
$database = new Database();
$db = $database->connect();

// Instanciar objeto SupportRequest
$support = new SupportRequest($db);
$user = new User($db);
$hardware = new Hardware($db);

// Obtener método de la petición
$method = $_SERVER['REQUEST_METHOD'];

// Manejar según el método HTTP
switch($method) {
    case 'GET':
        if(isset($_GET['id'])) {
            // Si se proporciona un ID, obtener una solicitud específica
            $support->request_id = $_GET['id'];
            
            if($support->readOne()) {
                // Crear array
                $request_arr = array(
                    "request_id" => $support->request_id,
                    "user_id" => $support->user_id,
                    "user_name" => $support->user_name,
                    "user_email" => $support->user_email,
                    "request_type" => $support->request_type,
                    "hardware_id" => $support->hardware_id,
                    "serial_number" => $support->serial_number,
                    "asset_tag" => $support->asset_tag,
                    "description" => $support->description,
                    "priority" => $support->priority,
                    "status" => $support->status,
                    "assigned_to" => $support->assigned_to,
                    "assigned_name" => $support->assigned_name,
                    "resolution_notes" => $support->resolution_notes,
                    "created_at" => $support->created_at,
                    "updated_at" => $support->updated_at
                );
            
                // Establecer código de respuesta - 200 OK
                http_response_code(200);
            
                // Mostrar en formato json
                echo json_encode($request_arr);
            } else {
                // No se encontró la solicitud
                http_response_code(404);
            
                // Informar al usuario
                echo json_encode(array("message" => "La solicitud de soporte no existe."));
            }
        } elseif(isset($_GET['user_id'])) {
            // Obtener solicitudes por usuario
            $stmt = $support->readByUser($_GET['user_id']);
            $num = $stmt->rowCount();
            
            if($num > 0) {
                $requests_arr = array();
                $requests_arr["records"] = array();
            
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    extract($row);
                    
                    $request_item = array(
                        "request_id" => $request_id,
                        "request_type" => $request_type,
                        "hardware_id" => $hardware_id,
                        "serial_number" => $serial_number,
                        "asset_tag" => $asset_tag,
                        "description" => $description,
                        "priority" => $priority,
                        "status" => $status,
                        "assigned_to" => $assigned_to,
                        "assigned_name" => $assigned_name,
                        "created_at" => $created_at
                    );
                    
                    array_push($requests_arr["records"], $request_item);
                }
            
                // Establecer código de respuesta - 200 OK
                http_response_code(200);
            
                // Mostrar en formato json
                echo json_encode($requests_arr);
            } else {
                // No se encontraron solicitudes
                http_response_code(404);
            
                // Informar al usuario
                echo json_encode(array("message" => "No se encontraron solicitudes de soporte."));
            }
        } elseif(isset($_GET['status'])) {
            // Obtener solicitudes por estado
            $stmt = $support->readByStatus($_GET['status']);
            $num = $stmt->rowCount();
            
            if($num > 0) {
                $requests_arr = array();
                $requests_arr["records"] = array();
            
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    extract($row);
                    
                    $request_item = array(
                        "request_id" => $request_id,
                        "user_id" => $user_id,
                        "user_name" => $user_name,
                        "request_type" => $request_type,
                        "hardware_id" => $hardware_id,
                        "serial_number" => $serial_number,
                        "asset_tag" => $asset_tag,
                        "description" => $description,
                        "priority" => $priority,
                        "status" => $status,
                        "assigned_to" => $assigned_to,
                        "assigned_name" => $assigned_name,
                        "created_at" => $created_at
                    );
                    
                    array_push($requests_arr["records"], $request_item);
                }
            
                // Establecer código de respuesta - 200 OK
                http_response_code(200);
            
                // Mostrar en formato json
                echo json_encode($requests_arr);
            } else {
                // No se encontraron solicitudes
                http_response_code(404);
            
                // Informar al usuario
                echo json_encode(array("message" => "No se encontraron solicitudes con ese estado."));
            }
        } elseif(isset($_GET['assigned_to'])) {
            // Obtener solicitudes asignadas a un técnico
            $stmt = $support->readByAssigned($_GET['assigned_to']);
            $num = $stmt->rowCount();
            
            if($num > 0) {
                $requests_arr = array();
                $requests_arr["records"] = array();
            
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    extract($row);
                    
                    $request_item = array(
                        "request_id" => $request_id,
                        "user_id" => $user_id,
                        "user_name" => $user_name,
                        "request_type" => $request_type,
                        "hardware_id" => $hardware_id,
                        "serial_number" => $serial_number,
                        "asset_tag" => $asset_tag,
                        "description" => $description,
                        "priority" => $priority,
                        "status" => $status,
                        "created_at" => $created_at
                    );
                    
                    array_push($requests_arr["records"], $request_item);
                }
            
                // Establecer código de respuesta - 200 OK
                http_response_code(200);
            
                // Mostrar en formato json
                echo json_encode($requests_arr);
            } else {
                // No se encontraron solicitudes
                http_response_code(404);
            
                // Informar al usuario
                echo json_encode(array("message" => "No se encontraron solicitudes asignadas a este técnico."));
            }
        } elseif(isset($_GET['search'])) {
            // Buscar solicitudes
            $stmt = $support->search($_GET['search']);
            $num = $stmt->rowCount();
            
            if($num > 0) {
                $requests_arr = array();
                $requests_arr["records"] = array();
            
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    extract($row);
                    
                    $request_item = array(
                        "request_id" => $request_id,
                        "user_id" => $user_id,
                        "user_name" => $user_name,
                        "request_type" => $request_type,
                        "hardware_id" => $hardware_id,
                        "serial_number" => $serial_number,
                        "asset_tag" => $asset_tag,
                        "description" => $description,
                        "priority" => $priority,
                        "status" => $status,
                        "assigned_to" => $assigned_to,
                        "assigned_name" => $assigned_name,
                        "created_at" => $created_at
                    );
                    
                    array_push($requests_arr["records"], $request_item);
                }
            
                // Establecer código de respuesta - 200 OK
                http_response_code(200);
            
                // Mostrar en formato json
                echo json_encode($requests_arr);
            } else {
                // No se encontraron solicitudes
                http_response_code(404);
            
                // Informar al usuario
                echo json_encode(array("message" => "No se encontraron solicitudes que coincidan con la búsqueda."));
            }
        } else {
            // Obtener todas las solicitudes
            $stmt = $support->read();
            $num = $stmt->rowCount();
            
            if($num > 0) {
                $requests_arr = array();
                $requests_arr["records"] = array();
            
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    extract($row);
                    
                    $request_item = array(
                        "request_id" => $request_id,
                        "user_id" => $user_id,
                        "user_name" => $user_name,
                        "request_type" => $request_type,
                        "hardware_id" => $hardware_id,
                        "serial_number" => $serial_number,
                        "asset_tag" => $asset_tag,
                        "description" => $description,
                        "priority" => $priority,
                        "status" => $status,
                        "assigned_to" => $assigned_to,
                        "assigned_name" => $assigned_name,
                        "created_at" => $created_at
                    );
                    
                    array_push($requests_arr["records"], $request_item);
                }
            
                // Establecer código de respuesta - 200 OK
                http_response_code(200);
            
                // Mostrar en formato json
                echo json_encode($requests_arr);
            } else {
                // No se encontraron solicitudes
                http_response_code(404);
            
                // Informar al usuario
                echo json_encode(array("message" => "No se encontraron solicitudes de soporte."));
            }
        }
        break;
        
    case 'POST':
        // Obtener los datos enviados
        $data = json_decode(file_get_contents("php://input"));
        
        // Asegurar que los datos no estén vacíos
        if(
            !empty($data->user_id) &&
            !empty($data->request_type) &&
            !empty($data->description) &&
            !empty($data->priority)
        ) {
            // Asignar valores
            $support->user_id = $data->user_id;
            $support->request_type = $data->request_type;
            $support->hardware_id = isset($data->hardware_id) ? $data->hardware_id : null;
            $support->description = $data->description;
            $support->priority = $data->priority;
            $support->status = isset($data->status) ? $data->status : "New";
            $support->assigned_to = isset($data->assigned_to) ? $data->assigned_to : null;
            
            // Crear la solicitud
            if($support->create()) {
                // Establecer código de respuesta - 201 creado
                http_response_code(201);
            
                // Informar al usuario
                echo json_encode(array("message" => "La solicitud de soporte fue creada."));
            } else {
                // Establecer código de respuesta - 503 servicio no disponible
                http_response_code(503);
            
                // Informar al usuario
                echo json_encode(array("message" => "No se pudo crear la solicitud de soporte."));
            }
        } else {
            // Establecer código de respuesta - 400 solicitud incorrecta
            http_response_code(400);
        
            // Informar al usuario
            echo json_encode(array("message" => "No se pudo crear la solicitud. Los datos están incompletos."));
        }
        break;
        
    case 'PUT':
        // Actualizar solicitud
        // Obtener datos enviados
        $data = json_decode(file_get_contents("php://input"));
        
        // Verificar que hay datos recibidos
        if (!$data || !isset($data->request_id)) {
            http_response_code(400);
            echo json_encode(array("message" => "Error: Datos incompletos o incorrectos."));
            break;
        }
        
        // Registrar los datos recibidos para depuración
        error_log('Datos recibidos en PUT: ' . json_encode($data));

        // Configurar valores en el objeto
        $support->request_id = $data->request_id;
        $support->request_type = $data->request_type;
        $support->hardware_id = $data->hardware_id;
        $support->description = $data->description;
        $support->priority = $data->priority;
        $support->status = $data->status;
        $support->assigned_to = $data->assigned_to;
        $support->resolution_notes = isset($data->resolution_notes) ? $data->resolution_notes : "";
        
        // Actualizar la solicitud
        if($support->update()) {
            // Establecer código de respuesta - 200 OK
            http_response_code(200);
            
            // Informar al usuario
            echo json_encode(array("message" => "Solicitud de soporte actualizada correctamente."));
        } else {
            // Si falló, informar al usuario
            http_response_code(500);
            
            // Informar al usuario
            echo json_encode(array("message" => "No se pudo actualizar la solicitud de soporte. Verifique los datos ingresados."));
        }
        break;
        
    case 'DELETE':
        // Obtener los datos enviados
        $data = json_decode(file_get_contents("php://input"));
        
        // Asegurar que el ID no esté vacío
        if(!empty($data->request_id)) {
            // Asignar valores
            $support->request_id = $data->request_id;
            
            // Eliminar la solicitud
            if($support->delete()) {
                // Establecer código de respuesta - 200 ok
                http_response_code(200);
            
                // Informar al usuario
                echo json_encode(array("message" => "La solicitud de soporte fue eliminada."));
            } else {
                // Establecer código de respuesta - 503 servicio no disponible
                http_response_code(503);
            
                // Informar al usuario
                echo json_encode(array("message" => "No se pudo eliminar la solicitud de soporte."));
            }
        } else {
            // Establecer código de respuesta - 400 solicitud incorrecta
            http_response_code(400);
        
            // Informar al usuario
            echo json_encode(array("message" => "No se pudo eliminar la solicitud. Falta el ID."));
        }
        break;
        
    default:
        // Método no permitido
        http_response_code(405);
        echo json_encode(array("message" => "Método no permitido"));
        break;
}
?> 