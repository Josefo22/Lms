<?php
// Verificar si hay un ID de ticket
if(!isset($_GET['id'])) {
    $_SESSION['message'] = 'ID de ticket no especificado';
    $_SESSION['message_type'] = 'danger';
    header('Location: index.php?page=support');
    exit;
}

$ticket_id = $_GET['id'];

// Inicializar controlador
require_once 'app/controllers/SupportController.php';
$supportController = new SupportController();
$ticket = $supportController->getTicketDetails($ticket_id);

// Verificar si se encontró el ticket
if(!$ticket) {
    $_SESSION['message'] = 'Ticket no encontrado';
    $_SESSION['message_type'] = 'danger';
    header('Location: index.php?page=support');
    exit;
}

$statusOptions = $supportController->getStatusOptions();
$priorityOptions = $supportController->getPriorityOptions();
$requestTypeOptions = $supportController->getRequestTypeOptions();

function getStatusBadgeClass($status) {
    switch($status) {
        case 'New':
            return 'warning';
        case 'Assigned':
            return 'info';
        case 'In Progress':
            return 'primary';
        case 'Resolved':
            return 'success';
        case 'Closed':
            return 'secondary';
        case 'Cancelled':
            return 'danger';
        default:
            return 'secondary';
    }
}

function getPriorityBadgeClass($priority) {
    switch($priority) {
        case 'Urgent':
            return 'danger';
        case 'High':
            return 'warning';
        case 'Medium':
            return 'info';
        case 'Low':
            return 'success';
        default:
            return 'secondary';
    }
}
?>

<div id="content" class="p-4 p-md-5 pt-5">
    <div class="container-fluid">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-primary text-white">
                <div class="row align-items-center">
                    <div class="col">
                        <h5 class="mb-0">
                            <i class="bi bi-ticket-detailed me-2"></i>
                            Solicitud de Soporte #<?php echo htmlspecialchars($ticket['request_id']); ?>
                            <span class="badge badge-<?php echo getStatusBadgeClass($ticket['status']); ?>">
                                <?php echo htmlspecialchars($statusOptions[$ticket['status']] ?? $ticket['status']); ?>
                            </span>
                        </h5>
                    </div>
                    <div class="col text-end">
                        <a href="?page=support" class="btn btn-sm btn-light me-2">
                            <i class="bi bi-arrow-left me-1"></i> Volver
                        </a>
                        <a href="?page=support&action=edit&id=<?php echo $ticket['request_id']; ?>" class="btn btn-sm btn-warning">
                            <i class="bi bi-pencil me-1"></i> Editar
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <!-- Información del Ticket -->
                    <div class="col-md-8">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">Detalles de la Solicitud</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Tipo de Solicitud:</strong> <?php echo htmlspecialchars($requestTypeOptions[$ticket['request_type']] ?? $ticket['request_type']); ?></p>
                                        <p><strong>Prioridad:</strong> <span class="badge badge-<?php echo getPriorityBadgeClass($ticket['priority']); ?>"><?php echo htmlspecialchars($priorityOptions[$ticket['priority']] ?? $ticket['priority']); ?></span></p>
                                        <p><strong>Hardware:</strong> <?php echo htmlspecialchars($ticket['hardware_name'] ?? 'No especificado'); ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Solicitante:</strong> <?php echo htmlspecialchars($ticket['requester_name']); ?></p>
                                        <p><strong>Asignado a:</strong> <?php echo htmlspecialchars($ticket['assigned_name'] ?? 'Sin asignar'); ?></p>
                                        <p><strong>Fecha de Creación:</strong> <?php echo date('d/m/Y H:i', strtotime($ticket['created_at'])); ?></p>
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-12">
                                        <h5>Descripción</h5>
                                        <p><?php echo nl2br(htmlspecialchars($ticket['description'])); ?></p>
                                    </div>
                                </div>
                                <?php if (!empty($ticket['resolution_notes'])): ?>
                                <div class="row mt-3">
                                    <div class="col-12">
                                        <h5>Notas de Resolución</h5>
                                        <p><?php echo nl2br(htmlspecialchars($ticket['resolution_notes'])); ?></p>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Información Adicional -->
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">Acciones</h6>
                            </div>
                            <div class="card-body">
                                <div class="btn-group-vertical w-100">
                                    <a href="index.php?page=support&action=edit&id=<?php echo $ticket['request_id']; ?>" class="btn btn-warning mb-2">
                                        <i class="fas fa-edit"></i> Editar Solicitud
                                    </a>
                                    <?php if ($ticket['status'] !== 'Resolved' && $ticket['status'] !== 'Closed'): ?>
                                    <button type="button" class="btn btn-success mb-2" onclick="resolveTicket(<?php echo $ticket['request_id']; ?>)">
                                        <i class="fas fa-check"></i> Marcar como Resuelto
                                    </button>
                                    <?php endif; ?>
                                    <?php if ($ticket['status'] === 'Resolved'): ?>
                                    <button type="button" class="btn btn-primary mb-2" onclick="closeTicket(<?php echo $ticket['request_id']; ?>)">
                                        <i class="fas fa-times"></i> Cerrar Solicitud
                                    </button>
                                    <?php endif; ?>
                                    <?php if ($ticket['status'] === 'Closed'): ?>
                                    <button type="button" class="btn btn-info mb-2" onclick="reopenTicket(<?php echo $ticket['request_id']; ?>)">
                                        <i class="fas fa-redo"></i> Reabrir Solicitud
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Historial de Actualizaciones -->
                    <div class="col-md-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">Historial de Actualizaciones</h6>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Fecha</th>
                                                <th>Usuario</th>
                                                <th>Acción</th>
                                                <th>Detalles</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if(empty($ticket['history'])): ?>
                                            <tr>
                                                <td colspan="4" class="text-center p-4">No hay actualizaciones registradas</td>
                                            </tr>
                                            <?php else: ?>
                                                <?php foreach($ticket['history'] as $history): ?>
                                                <tr>
                                                    <td><?php echo date('d/m/Y H:i', strtotime($history['created_at'])); ?></td>
                                                    <td>
                                                        <a href="?page=users&action=view&id=<?php echo $history['user_id']; ?>">
                                                            <?php echo htmlspecialchars($history['user_name']); ?>
                                                        </a>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-primary"><?php echo htmlspecialchars($history['action']); ?></span>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($history['details']); ?></td>
                                                </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Función para resolver ticket
function resolveTicket(id) {
    if (confirm('¿Está seguro de que desea marcar esta solicitud como resuelta?')) {
        window.location.href = 'index.php?page=support&action=resolve&id=' + id;
    }
}

// Función para cerrar ticket
function closeTicket(id) {
    if (confirm('¿Está seguro de que desea cerrar esta solicitud?')) {
        window.location.href = 'index.php?page=support&action=close&id=' + id;
    }
}

// Función para reabrir ticket
function reopenTicket(id) {
    if (confirm('¿Está seguro de que desea reabrir esta solicitud?')) {
        window.location.href = 'index.php?page=support&action=reopen&id=' + id;
    }
}
</script> 