<?php
require_once 'config/config.php';
require_once 'app/controllers/AuthController.php';

$auth = new AuthController();
$auth->logout();

redirect('login.php');
?> 