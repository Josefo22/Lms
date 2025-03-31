<?php
// Inicializar controlador
require_once 'app/controllers/SupportController.php';
$supportController = new SupportController();

// Obtener filtros
$status = isset($_GET['status']) ? $_GET['status'] : null;
$priority = isset($_GET['priority']) ? $_GET['priority'] : null;
$request_type = isset($_GET['request_type']) ? $_GET['request_type'] : null;

// Obtener datos para la vista
$tickets = $supportController->getTickets([
    'status' => $status,
    'priority' => $priority,
    'request_type' => $request_type
]);

// Obtener estadísticas
$ticketStats = [
    'total' => count($tickets),
    'new' => count(array_filter($tickets, function($ticket) { return $ticket['status'] == 'New'; })),
    'in_progress' => count(array_filter($tickets, function($ticket) { return $ticket['status'] == 'In Progress'; })),
    'resolved' => count(array_filter($tickets, function($ticket) { return $ticket['status'] == 'Resolved'; }))
];
?>

<div id="content" class="p-4 p-md-5 pt-5">
    <div class="container-fluid">
        <!-- Encabezado principal y botón de acción -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Gestión de Soporte Técnico</h2>
            <a href="?page=support&action=create" class="btn btn-primary">
                <i class="bi bi-plus-circle me-2"></i> Nueva Solicitud
            </a>
        </div>

        <!-- Tarjetas de estadísticas -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card h-100 border-0 shadow-sm rounded">
                    <div class="card-body bg-primary text-white rounded">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-subtitle mb-2">Total Solicitudes</h6>
                                <h2 class="card-title mb-0"><?php echo $ticketStats['total']; ?></h2>
                            </div>
                            <i class="bi bi-ticket-detailed fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-3">
                <div class="card h-100 border-0 shadow-sm rounded">
                    <div class="card-body bg-warning text-dark rounded">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-subtitle mb-2">Nuevas</h6>
                                <h2 class="card-title mb-0"><?php echo $ticketStats['new']; ?></h2>
                            </div>
                            <i class="bi bi-hourglass-split fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-3">
                <div class="card h-100 border-0 shadow-sm rounded">
                    <div class="card-body bg-info text-white rounded">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-subtitle mb-2">En Proceso</h6>
                                <h2 class="card-title mb-0"><?php echo $ticketStats['in_progress']; ?></h2>
                            </div>
                            <i class="bi bi-gear-wide-connected fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-3">
                <div class="card h-100 border-0 shadow-sm rounded">
                    <div class="card-body bg-success text-white rounded">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-subtitle mb-2">Resueltas</h6>
                                <h2 class="card-title mb-0"><?php echo $ticketStats['resolved']; ?></h2>
                            </div>
                            <i class="bi bi-check-circle fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card border-0 shadow-sm rounded">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label for="statusFilter" class="form-label">Estado</label>
                                <select id="statusFilter" class="form-select">
                                    <option value="">Todos</option>
                                    <?php foreach($supportController->getStatusOptions() as $value => $label): ?>
                                        <option value="<?php echo $value; ?>" <?php echo $status == $value ? 'selected' : ''; ?>>
                                            <?php echo $label; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <label for="priorityFilter" class="form-label">Prioridad</label>
                                <select id="priorityFilter" class="form-select">
                                    <option value="">Todas</option>
                                    <?php foreach($supportController->getPriorityOptions() as $value => $label): ?>
                                        <option value="<?php echo $value; ?>" <?php echo $priority == $value ? 'selected' : ''; ?>>
                                            <?php echo $label; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <label for="requestTypeFilter" class="form-label">Tipo de Solicitud</label>
                                <select id="requestTypeFilter" class="form-select">
                                    <option value="">Todos</option>
                                    <?php foreach($supportController->getRequestTypeOptions() as $value => $label): ?>
                                        <option value="<?php echo $value; ?>" <?php echo $request_type == $value ? 'selected' : ''; ?>>
                                            <?php echo $label; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-3 mb-3 d-flex align-items-end">
                                <div class="d-flex gap-2 w-100">
                                    <button id="filterBtn" class="btn btn-primary flex-grow-1">
                                        <i class="bi bi-filter me-1"></i> Filtrar
                                    </button>
                                    <button id="clearBtn" class="btn btn-outline-secondary">
                                        <i class="bi bi-x-circle me-1"></i> Limpiar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de solicitudes -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Tipo</th>
                                <th>Hardware</th>
                                <th>Descripción</th>
                                <th>Solicitante</th>
                                <th>Prioridad</th>
                                <th>Estado</th>
                                <th>Fecha</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($tickets)): ?>
                            <tr>
                                <td colspan="9" class="text-center p-4">No se encontraron solicitudes de soporte</td>
                            </tr>
                            <?php else: ?>
                                <?php foreach($tickets as $ticket): ?>
                                <tr>
                                    <td><?php echo $ticket['request_id']; ?></td>
                                    <td><?php echo htmlspecialchars($supportController->getRequestTypeOptions()[$ticket['request_type']] ?? $ticket['request_type']); ?></td>
                                    <td><?php echo htmlspecialchars($ticket['hardware_name'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($ticket['description']); ?></td>
                                    <td><?php echo htmlspecialchars($ticket['requester_name']); ?></td>
                                    <td>
                                        <?php 
                                        $priorityClass = 'bg-secondary';
                                        $priorityText = 'Desconocida';
                                        
                                        switch($ticket['priority']) {
                                            case 'High':
                                                $priorityClass = 'bg-danger';
                                                $priorityText = 'Alta';
                                                break;
                                            case 'Medium':
                                                $priorityClass = 'bg-warning text-dark';
                                                $priorityText = 'Media';
                                                break;
                                            case 'Low':
                                                $priorityClass = 'bg-info';
                                                $priorityText = 'Baja';
                                                break;
                                            case 'Urgent':
                                                $priorityClass = 'bg-danger';
                                                $priorityText = 'Urgente';
                                                break;
                                        }
                                        ?>
                                        <span class="badge <?php echo $priorityClass; ?>"><?php echo $priorityText; ?></span>
                                    </td>
                                    <td>
                                        <?php 
                                        $statusClass = 'bg-secondary';
                                        $statusText = 'Desconocido';
                                        
                                        switch($ticket['status']) {
                                            case 'New':
                                                $statusClass = 'bg-warning text-dark';
                                                $statusText = 'Nuevo';
                                                break;
                                            case 'Assigned':
                                                $statusClass = 'bg-info';
                                                $statusText = 'Asignado';
                                                break;
                                            case 'In Progress':
                                                $statusClass = 'bg-primary';
                                                $statusText = 'En Proceso';
                                                break;
                                            case 'Resolved':
                                                $statusClass = 'bg-success';
                                                $statusText = 'Resuelto';
                                                break;
                                            case 'Closed':
                                                $statusClass = 'bg-secondary';
                                                $statusText = 'Cerrado';
                                                break;
                                            case 'Cancelled':
                                                $statusClass = 'bg-danger';
                                                $statusText = 'Cancelado';
                                                break;
                                        }
                                        ?>
                                        <span class="badge <?php echo $statusClass; ?>"><?php echo $statusText; ?></span>
                                    </td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($ticket['created_at'])); ?></td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="?page=support&action=view&id=<?php echo $ticket['request_id']; ?>" class="btn btn-sm btn-info text-white">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="?page=support&action=edit&id=<?php echo $ticket['request_id']; ?>" class="btn btn-sm btn-primary">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <?php if($ticket['status'] !== 'Closed' && $ticket['status'] !== 'Cancelled'): ?>
                                            <button type="button" class="btn btn-sm btn-success" onclick="resolveTicket(<?php echo $ticket['request_id']; ?>)">
                                                <i class="bi bi-check-circle"></i>
                                            </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Eventos para filtros
    document.getElementById('filterBtn').addEventListener('click', function() {
        applyFilters();
    });
    
    document.getElementById('clearBtn').addEventListener('click', function() {
        clearFilters();
    });
    
    // Función para aplicar filtros
    function applyFilters() {
        const status = document.getElementById('statusFilter').value;
        const priority = document.getElementById('priorityFilter').value;
        const requestType = document.getElementById('requestTypeFilter').value;
        
        let url = 'index.php?page=support';
        
        if(status) url += '&status=' + status;
        if(priority) url += '&priority=' + priority;
        if(requestType) url += '&request_type=' + requestType;
        
        window.location.href = url;
    }
    
    // Función para limpiar filtros
    function clearFilters() {
        window.location.href = 'index.php?page=support';
    }
});

// Función para resolver ticket
function resolveTicket(ticketId) {
    if(confirm('¿Estás seguro de que deseas marcar esta solicitud como resuelta?')) {
        window.location.href = 'index.php?page=support&action=resolve&id=' + ticketId;
    }
}
</script> 