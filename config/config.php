<?php
// Asegurarse de que las extensiones necesarias estén disponibles
if (!extension_loaded('date')) {
    die('La extensión date es requerida');
}
if (!extension_loaded('session')) {
    die('La extensión session es requerida');
}

// Definición de constantes para el proyecto
define('ROOT_URL', 'http://localhost/LMS_IT_Inventory/');
define('APP_ROOT', dirname(dirname(__FILE__)));
define('SITE_NAME', 'IT Inventory Management System');

// Configuración de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Configuración de zona horaria
date_default_timezone_set('UTC');

// Sesión
session_start();

// Funciones de ayuda globales
function redirect($page) {
    header('Location: ' . ROOT_URL . $page);
    exit;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if(!isLoggedIn()) {
        redirect('login.php');
    }
}
?>