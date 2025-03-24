<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../config/database.php';
include_once '../models/Client.php';

$database = new Database();
$db = $database->connect();
$client = new Client($db);

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        if(isset($_GET['id'])) {
            // Obtener un cliente específico
            $client->client_id = $_GET['id'];
            $client->readOne();
            
            if($client->client_name) {
                http_response_code(200);
                echo json_encode(array(
                    "success" => true,
                    "data" => array(
                        "client_id" => $client->client_id,
                        "client_name" => $client->client_name,
                        "contact_person" => $client->contact_person,
                        "contact_email" => $client->contact_email,
                        "contact_phone" => $client->contact_phone,
                        "address" => $client->address
                    )
                ));
            } else {
                http_response_code(404);
                echo json_encode(array(
                    "success" => false,
                    "message" => "Cliente no encontrado."
                ));
            }
        } elseif(isset($_GET['client_id']) && isset($_GET['type'])) {
            // Obtener hardware o usuarios asociados al cliente
            $client_id = $_GET['client_id'];
            $type = $_GET['type'];
            
            if($type === 'hardware') {
                $query = "SELECT h.hardware_id, h.serial_number, m.model_name, h.status, l.location_name 
                         FROM hardware h 
                         LEFT JOIN models m ON h.model_id = m.model_id 
                         LEFT JOIN locations l ON h.location_id = l.location_id 
                         WHERE h.client_id = ?";
                $stmt = $db->prepare($query);
                $stmt->bindParam(1, $client_id);
                $stmt->execute();
                
                $hardware = array();
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    array_push($hardware, $row);
                }
                
                echo json_encode(array(
                    "success" => true,
                    "hardware" => $hardware
                ));
            } elseif($type === 'users') {
                $query = "SELECT user_id, first_name, last_name, email, department, status 
                         FROM users 
                         WHERE client_id = ?";
                $stmt = $db->prepare($query);
                $stmt->bindParam(1, $client_id);
                $stmt->execute();
                
                $users = array();
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    array_push($users, $row);
                }
                
                echo json_encode(array(
                    "success" => true,
                    "users" => $users
                ));
            } else {
                http_response_code(400);
                echo json_encode(array(
                    "success" => false,
                    "message" => "Tipo de consulta no válido."
                ));
            }
        } else {
            // Obtener todos los clientes
            $stmt = $client->read();
            $num = $stmt->rowCount();
            
            if($num > 0) {
                $clients_arr = array();
                $clients_arr["records"] = array();
                
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    extract($row);
                    $client_item = array(
                        "client_id" => $client_id,
                        "client_name" => $client_name,
                        "contact_person" => $contact_person,
                        "contact_email" => $contact_email,
                        "contact_phone" => $contact_phone,
                        "address" => $address
                    );
                    array_push($clients_arr["records"], $client_item);
                }
                
                http_response_code(200);
                echo json_encode($clients_arr);
            } else {
                http_response_code(404);
                echo json_encode(array(
                    "success" => false,
                    "message" => "No se encontraron clientes."
                ));
            }
        }
        break;
        
    case 'POST':
        // Crear nuevo cliente
        $data = json_decode(file_get_contents("php://input"));
        
        if(
            !empty($data->client_name) &&
            !empty($data->contact_person) &&
            !empty($data->contact_email) &&
            !empty($data->contact_phone)
        ) {
            $client->client_name = $data->client_name;
            $client->contact_person = $data->contact_person;
            $client->contact_email = $data->contact_email;
            $client->contact_phone = $data->contact_phone;
            $client->address = $data->address;
            
            if($client->create()) {
                http_response_code(201);
                echo json_encode(array(
                    "success" => true,
                    "message" => "Cliente creado exitosamente."
                ));
            } else {
                http_response_code(503);
                echo json_encode(array(
                    "success" => false,
                    "message" => "No se pudo crear el cliente."
                ));
            }
        } else {
            http_response_code(400);
            echo json_encode(array(
                "success" => false,
                "message" => "Datos incompletos."
            ));
        }
        break;
        
    case 'PUT':
        // Actualizar cliente
        $data = json_decode(file_get_contents("php://input"));
        
        if(
            !empty($data->client_id) &&
            !empty($data->client_name) &&
            !empty($data->contact_person) &&
            !empty($data->contact_email) &&
            !empty($data->contact_phone)
        ) {
            $client->client_id = $data->client_id;
            $client->client_name = $data->client_name;
            $client->contact_person = $data->contact_person;
            $client->contact_email = $data->contact_email;
            $client->contact_phone = $data->contact_phone;
            $client->address = $data->address;
            
            if($client->update()) {
                http_response_code(200);
                echo json_encode(array(
                    "success" => true,
                    "message" => "Cliente actualizado exitosamente."
                ));
            } else {
                http_response_code(503);
                echo json_encode(array(
                    "success" => false,
                    "message" => "No se pudo actualizar el cliente."
                ));
            }
        } else {
            http_response_code(400);
            echo json_encode(array(
                "success" => false,
                "message" => "Datos incompletos."
            ));
        }
        break;
        
    case 'DELETE':
        // Eliminar cliente
        $data = json_decode(file_get_contents("php://input"));
        
        if(!empty($data->client_id)) {
            $client->client_id = $data->client_id;
            
            if($client->delete()) {
                http_response_code(200);
                echo json_encode(array(
                    "success" => true,
                    "message" => "Cliente eliminado exitosamente."
                ));
            } else {
                http_response_code(503);
                echo json_encode(array(
                    "success" => false,
                    "message" => "No se pudo eliminar el cliente."
                ));
            }
        } else {
            http_response_code(400);
            echo json_encode(array(
                "success" => false,
                "message" => "ID de cliente no proporcionado."
            ));
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(array(
            "success" => false,
            "message" => "Método no permitido."
        ));
        break;
}
?> 