<?php
// Este archivo es un puente entre index.php?page=support y el módulo de soporte

// Incluir los archivos necesarios
require_once 'config/database.php';
require_once 'app/models/SupportRequest.php';
require_once 'app/models/User.php';
require_once 'app/models/Hardware.php';

// Verificar si el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Determinar la acción a realizar
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$id = isset($_GET['id']) ? $_GET['id'] : null;

// Redirigir según la acción
if ($action == 'view' && $id) {
    // Mostrar detalles de una solicitud específica
    include_once 'app/views/support/view.php';
} elseif ($action == 'edit' && $id) {
    // Editar una solicitud específica
    include_once 'app/views/support/edit.php';
} elseif ($action == 'new') {
    // Crear nueva solicitud
    include_once 'app/views/support/new.php';
} else {
    // Mostrar la lista de solicitudes por defecto
    include_once 'app/views/support/index.php';
}
?> 