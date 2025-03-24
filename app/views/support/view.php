<?php
// No iniciar sesión aquí, ya está iniciada en index.php principal
//session_start();

// Usar rutas absolutas desde la raíz del proyecto
require_once 'config/database.php';
require_once 'app/models/SupportRequest.php';
require_once 'app/models/User.php';
require_once 'app/models/Hardware.php';

// Verificar si el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Verificar si se proporcionó un ID
if (!isset($_GET['id'])) {
    header('Location: index.php?page=support');
    exit();
}

$database = new Database();
$db = $database->connect();
$support = new SupportRequest($db);
$user = new User($db);
$hardware = new Hardware($db);

// Obtener detalles de la solicitud
$support->request_id = $_GET['id'];
if (!$support->readOne()) {
    // Si no se encuentra la solicitud, redirigir
    header('Location: index.php?page=support');
    exit();
}

// Verificar si el usuario actual es del personal de IT o el propietario de la solicitud
$is_it_staff = isset($_SESSION['is_it_staff']) && $_SESSION['is_it_staff'];
$is_owner = ($_SESSION['user_id'] == $support->user_id);
$can_edit = $is_it_staff || $is_owner;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalles de Solicitud - LMS IT Inventory</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Contenido principal -->
    <div id="content" class="p-4 p-md-5 pt-5">
        <div class="container-fluid">
            <!-- Encabezado principal y botones de acción -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0"><i class="bi bi-headset me-2"></i>Solicitud #<?php echo $support->request_id; ?></h2>
                <div>
                    <a href="index.php?page=support" class="btn btn-secondary me-2">
                        <i class="bi bi-arrow-left me-1"></i> Volver
                    </a>
                    <?php if ($can_edit): ?>
                    <a href="index.php?page=support&action=edit&id=<?php echo $support->request_id; ?>" class="btn btn-primary">
                        <i class="bi bi-pencil me-1"></i> Editar
                    </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Información de la solicitud -->
            <div class="row">
                <div class="col-md-8">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-bottom py-3">
                            <h5 class="card-title mb-0">Información de la Solicitud</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="row mb-3">
                                <div class="col-md-3 fw-bold text-muted">
                                    Estado:
                                </div>
                                <div class="col-md-9">
                                    <?php
                                    $status_class = '';
                                    switch($support->status) {
                                        case 'New':
                                            $status_class = 'bg-info';
                                            break;
                                        case 'Assigned':
                                            $status_class = 'bg-primary';
                                            break;
                                        case 'In Progress':
                                            $status_class = 'bg-warning';
                                            break;
                                        case 'Resolved':
                                            $status_class = 'bg-success';
                                            break;
                                        case 'Closed':
                                            $status_class = 'bg-secondary';
                                            break;
                                        case 'Cancelled':
                                            $status_class = 'bg-danger';
                                            break;
                                    }
                                    ?>
                                    <span class="badge rounded-pill <?php echo $status_class; ?> px-3 py-2"><?php echo $support->status; ?></span>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-3 fw-bold text-muted">
                                    Prioridad:
                                </div>
                                <div class="col-md-9">
                                    <?php
                                    $priority_class = '';
                                    switch($support->priority) {
                                        case 'Low':
                                            $priority_class = 'bg-info';
                                            break;
                                        case 'Medium':
                                            $priority_class = 'bg-primary';
                                            break;
                                        case 'High':
                                            $priority_class = 'bg-warning';
                                            break;
                                        case 'Urgent':
                                            $priority_class = 'bg-danger';
                                            break;
                                    }
                                    ?>
                                    <span class="badge rounded-pill <?php echo $priority_class; ?> px-3 py-2"><?php echo $support->priority; ?></span>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-3 fw-bold text-muted">
                                    Tipo:
                                </div>
                                <div class="col-md-9">
                                    <?php echo $support->request_type; ?>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-3 fw-bold text-muted">
                                    Descripción:
                                </div>
                                <div class="col-md-9">
                                    <p class="mb-0"><?php echo nl2br(htmlspecialchars($support->description)); ?></p>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-3 fw-bold text-muted">
                                    Fecha Creación:
                                </div>
                                <div class="col-md-9">
                                    <?php 
                                    $created_date = new DateTime($support->created_at);
                                    echo $created_date->format('d/m/Y H:i'); 
                                    ?>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-3 fw-bold text-muted">
                                    Última Actualización:
                                </div>
                                <div class="col-md-9">
                                    <?php 
                                    if ($support->updated_at) {
                                        $updated_date = new DateTime($support->updated_at);
                                        echo $updated_date->format('d/m/Y H:i');
                                    } else {
                                        echo 'Sin actualizaciones';
                                    }
                                    ?>
                                </div>
                            </div>
                            
                            <?php if (!empty($support->resolution_notes)): ?>
                            <div class="row mb-3">
                                <div class="col-md-3 fw-bold text-muted">
                                    Notas de Resolución:
                                </div>
                                <div class="col-md-9">
                                    <p class="mb-0"><?php echo nl2br(htmlspecialchars($support->resolution_notes)); ?></p>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Hardware Relacionado (si existe) -->
                    <?php if ($support->hardware_id): ?>
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-bottom py-3">
                            <h5 class="card-title mb-0">Hardware Relacionado</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="row mb-3">
                                <div class="col-md-3 fw-bold text-muted">
                                    Serial:
                                </div>
                                <div class="col-md-9">
                                    <?php echo $support->serial_number; ?>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-3 fw-bold text-muted">
                                    Asset Tag:
                                </div>
                                <div class="col-md-9">
                                    <?php echo $support->asset_tag; ?>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <a href="../hardware/view.php?id=<?php echo $support->hardware_id; ?>" class="btn btn-outline-primary btn-sm">
                                        <i class="bi bi-pc-display me-1"></i> Ver Detalles del Hardware
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Acciones disponibles -->
                    <?php if ($is_it_staff): ?>
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-bottom py-3">
                            <h5 class="card-title mb-0">Acciones Rápidas</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="d-flex gap-2">
                                <?php if ($support->status == 'New'): ?>
                                <button class="btn btn-primary" onclick="updateRequestStatus('Assigned')">
                                    <i class="bi bi-person-check me-1"></i> Asignar
                                </button>
                                <?php endif; ?>
                                
                                <?php if ($support->status == 'Assigned'): ?>
                                <button class="btn btn-warning" onclick="updateRequestStatus('In Progress')">
                                    <i class="bi bi-play-fill me-1"></i> Iniciar Trabajo
                                </button>
                                <?php endif; ?>
                                
                                <?php if ($support->status == 'In Progress'): ?>
                                <button class="btn btn-success" onclick="updateRequestStatus('Resolved')">
                                    <i class="bi bi-check-circle me-1"></i> Marcar como Resuelto
                                </button>
                                <?php endif; ?>
                                
                                <?php if ($support->status == 'Resolved'): ?>
                                <button class="btn btn-secondary" onclick="updateRequestStatus('Closed')">
                                    <i class="bi bi-file-earmark-check me-1"></i> Cerrar Ticket
                                </button>
                                <?php endif; ?>
                                
                                <?php if ($support->status != 'Cancelled' && $support->status != 'Closed'): ?>
                                <button class="btn btn-danger" onclick="updateRequestStatus('Cancelled')">
                                    <i class="bi bi-x-circle me-1"></i> Cancelar
                                </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                </div>
                <div class="col-md-4">
                    <!-- Información del Usuario -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-bottom py-3">
                            <h5 class="card-title mb-0">Información del Solicitante</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="text-center mb-3">
                                <i class="bi bi-person-circle" style="font-size: 4rem;"></i>
                            </div>
                            <div class="row mb-2">
                                <div class="col-md-4 fw-bold text-muted">
                                    Nombre:
                                </div>
                                <div class="col-md-8">
                                    <?php echo $support->user_name; ?>
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-md-4 fw-bold text-muted">
                                    Email:
                                </div>
                                <div class="col-md-8">
                                    <?php echo $support->user_email; ?>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12 mt-3">
                                    <a href="../users/view.php?id=<?php echo $support->user_id; ?>" class="btn btn-outline-primary btn-sm w-100">
                                        <i class="bi bi-person me-1"></i> Ver Perfil del Usuario
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Información del Técnico Asignado -->
                    <?php if ($support->assigned_to): ?>
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-bottom py-3">
                            <h5 class="card-title mb-0">Técnico Asignado</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="text-center mb-3">
                                <i class="bi bi-person-badge" style="font-size: 4rem;"></i>
                            </div>
                            <div class="row mb-2">
                                <div class="col-md-4 fw-bold text-muted">
                                    Nombre:
                                </div>
                                <div class="col-md-8">
                                    <?php echo $support->assigned_name; ?>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12 mt-3">
                                    <a href="../users/view.php?id=<?php echo $support->assigned_to; ?>" class="btn btn-outline-primary btn-sm w-100">
                                        <i class="bi bi-person me-1"></i> Ver Perfil del Técnico
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Historial de actividad -->
                    <div class="card">
                        <div class="card-header bg-white border-bottom py-3">
                            <h5 class="card-title mb-0">Historial de Actividad</h5>
                        </div>
                        <div class="card-body p-0">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1">Ticket creado</h6>
                                        <small><?php echo (new DateTime($support->created_at))->format('d/m/Y H:i'); ?></small>
                                    </div>
                                    <p class="mb-1">El usuario <?php echo $support->user_name; ?> ha creado este ticket.</p>
                                </li>
                                <?php if ($support->assigned_to): ?>
                                <li class="list-group-item">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1">Ticket asignado</h6>
                                        <small><?php echo (new DateTime($support->updated_at))->format('d/m/Y H:i'); ?></small>
                                    </div>
                                    <p class="mb-1">El ticket ha sido asignado a <?php echo $support->assigned_name; ?>.</p>
                                </li>
                                <?php endif; ?>
                                <?php if ($support->status == 'Resolved' || $support->status == 'Closed'): ?>
                                <li class="list-group-item">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1">Ticket resuelto</h6>
                                        <small><?php echo (new DateTime($support->updated_at))->format('d/m/Y H:i'); ?></small>
                                    </div>
                                    <p class="mb-1">El problema ha sido resuelto.</p>
                                </li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Toggle Sidebar
            $('#sidebarCollapse').on('click', function() {
                $('#sidebar').toggleClass('active');
            });
        });

        // Actualizar estado de solicitud
        function updateRequestStatus(status) {
            if (!confirm(`¿Está seguro de cambiar el estado a "${status}"?`)) {
                return;
            }

            const data = {
                request_id: <?php echo $support->request_id; ?>,
                request_type: "<?php echo $support->request_type; ?>",
                hardware_id: <?php echo $support->hardware_id ? $support->hardware_id : 'null'; ?>,
                description: "<?php echo addslashes($support->description); ?>",
                priority: "<?php echo $support->priority; ?>",
                status: status,
                assigned_to: <?php echo $support->assigned_to ? $support->assigned_to : ($_SESSION['user_id'] && $is_it_staff ? $_SESSION['user_id'] : 'null'); ?>,
                resolution_notes: "<?php echo addslashes($support->resolution_notes ?? ''); ?>"
            };

            if (status === 'Resolved' || status === 'Closed') {
                const notes = prompt("Por favor, agregue notas de resolución:", "<?php echo addslashes($support->resolution_notes ?? ''); ?>");
                if (notes === null) {
                    return; // El usuario canceló
                }
                data.resolution_notes = notes;
            }

            fetch('../../app/controllers/SupportController.php', {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                if (data.message) {
                    alert(data.message);
                    if (data.message.includes('actualizada')) {
                        location.reload();
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al actualizar la solicitud.');
            });
        }
    </script>
</body>
</html> 