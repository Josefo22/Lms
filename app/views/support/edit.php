<?php
// Verificar si hay un ID de ticket
if(!isset($_GET['id'])) {
    $_SESSION['message'] = 'ID de solicitud no especificado';
    $_SESSION['message_type'] = 'danger';
    header('Location: index.php?page=support');
    exit;
}

$request_id = $_GET['id'];

// Inicializar controlador
require_once 'app/controllers/SupportController.php';
$supportController = new SupportController();
$ticket = $supportController->getTicketDetails($request_id);

// Verificar si se encontró el ticket
if(!$ticket) {
    $_SESSION['message'] = 'Solicitud no encontrada';
    $_SESSION['message_type'] = 'danger';
    header('Location: index.php?page=support');
    exit;
}

// Obtener listas para los selectores
$users = $supportController->getUserOptions();
$statuses = $supportController->getStatusOptions();
$priorities = $supportController->getPriorityOptions();
$requestTypeOptions = $supportController->getRequestTypeOptions();
$hardwareOptions = $supportController->getHardwareOptions();
$clients = $supportController->getClientOptions();
?>

<div id="content" class="p-4 p-md-5 pt-5">
    <div class="container-fluid">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-primary text-white">
                <div class="row align-items-center">
                    <div class="col">
                        <h5 class="mb-0">
                            <i class="bi bi-pencil-square me-2"></i>
                            Editar Solicitud #<?php echo $ticket['request_id']; ?>
                        </h5>
                    </div>
                    <div class="col text-end">
                        <a href="?page=support&action=view&id=<?php echo $ticket['request_id']; ?>" class="btn btn-sm btn-light">
                            <i class="bi bi-x-lg me-1"></i> Cancelar
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <form action="index.php?page=support&action=update" method="POST" id="editTicketForm" class="needs-validation" novalidate>
                    <input type="hidden" name="request_id" value="<?php echo $ticket['request_id']; ?>">
                    
                    <div class="row g-4">
                        <!-- Información Principal -->
                        <div class="col-md-8">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">Detalles de la Solicitud</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label for="request_type">Tipo de Solicitud</label>
                                                <select class="form-control" id="request_type" name="request_type" required>
                                                    <option value="">Seleccione un tipo</option>
                                                    <?php foreach ($requestTypeOptions as $value => $label): ?>
                                                        <option value="<?php echo $value; ?>" <?php echo $ticket['request_type'] == $value ? 'selected' : ''; ?>>
                                                            <?php echo htmlspecialchars($label); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label for="priority">Prioridad</label>
                                                <select class="form-control" id="priority" name="priority" required>
                                                    <option value="">Seleccione una prioridad</option>
                                                    <?php foreach ($priorities as $value => $label): ?>
                                                        <option value="<?php echo $value; ?>" <?php echo $ticket['priority'] == $value ? 'selected' : ''; ?>>
                                                            <?php echo htmlspecialchars($label); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label for="hardware_id">Hardware</label>
                                                <select class="form-control" id="hardware_id" name="hardware_id">
                                                    <option value="">Seleccione un hardware</option>
                                                    <?php foreach ($hardwareOptions as $hardware): ?>
                                                        <option value="<?php echo $hardware['id']; ?>" <?php echo $ticket['hardware_id'] == $hardware['id'] ? 'selected' : ''; ?>>
                                                            <?php echo htmlspecialchars($hardware['name']); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label for="status">Estado</label>
                                                <select class="form-control" id="status" name="status" required>
                                                    <option value="">Seleccione un estado</option>
                                                    <?php foreach ($statuses as $value => $label): ?>
                                                        <option value="<?php echo $value; ?>" <?php echo $ticket['status'] == $value ? 'selected' : ''; ?>>
                                                            <?php echo htmlspecialchars($label); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group mb-3">
                                        <label for="description">Descripción</label>
                                        <textarea class="form-control" id="description" name="description" rows="4" required><?php echo htmlspecialchars($ticket['description']); ?></textarea>
                                    </div>

                                    <div class="form-group mb-3">
                                        <label for="resolution_notes">Notas de Resolución</label>
                                        <textarea class="form-control" id="resolution_notes" name="resolution_notes" rows="3"><?php echo htmlspecialchars($ticket['resolution_notes'] ?? ''); ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Información Adicional -->
                        <div class="col-md-4">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">Información de la Solicitud</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label for="client_id" class="form-label fw-bold">Cliente</label>
                                        <select class="form-select" id="client_id" name="client_id">
                                            <option value="">Sin Asignar</option>
                                            <?php foreach($clients as $client): ?>
                                                <option value="<?php echo $client['id']; ?>" 
                                                        <?php echo $ticket['client_id'] == $client['id'] ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($client['name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="assigned_to" class="form-label fw-bold">Asignar a</label>
                                        <select class="form-select" id="assigned_to" name="assigned_to">
                                            <option value="">Sin Asignar</option>
                                            <?php foreach($users as $user): ?>
                                                <option value="<?php echo $user['id']; ?>" 
                                                        data-client="<?php echo $user['client_id'] ?? ''; ?>"
                                                        <?php echo $ticket['assigned_to'] == $user['id'] ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($user['name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    
                                    <div class="mb-0">
                                        <label class="form-label fw-bold">Fechas</label>
                                        <div class="text-muted small">
                                            <div>Creado: <?php echo date('d/m/Y H:i', strtotime($ticket['created_at'])); ?></div>
                                            <?php if($ticket['updated_at'] !== $ticket['created_at']): ?>
                                                <div>Actualizado: <?php echo date('d/m/Y H:i', strtotime($ticket['updated_at'])); ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Botones de acción -->
                    <div class="mt-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i> Guardar Cambios
                        </button>
                        <a href="?page=support&action=view&id=<?php echo $ticket['request_id']; ?>" class="btn btn-secondary">
                            <i class="bi bi-x-lg me-1"></i> Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Validación del formulario
(function() {
    'use strict';
    
    var forms = document.querySelectorAll('.needs-validation');
    
    Array.prototype.slice.call(forms).forEach(function(form) {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
})();

document.getElementById('editTicketForm').addEventListener('submit', function(e) {
    const requiredFields = ['request_type', 'priority', 'status', 'description'];
    let missingFields = [];

    requiredFields.forEach(field => {
        if (!document.getElementById(field).value.trim()) {
            missingFields.push(field);
        }
    });

    if (missingFields.length > 0) {
        e.preventDefault();
        alert('Por favor complete los siguientes campos requeridos:\n' + missingFields.join('\n'));
    }
});

// Filtrar usuarios por cliente seleccionado
document.addEventListener('DOMContentLoaded', function() {
    const clientSelect = document.getElementById('client_id');
    const userSelect = document.getElementById('assigned_to');
    
    if (clientSelect && userSelect) {
        clientSelect.addEventListener('change', function() {
            const clientId = this.value;
            const userOptions = userSelect.querySelectorAll('option');
            
            userOptions.forEach(option => {
                if (option.value === '') {
                    option.style.display = 'block'; // Siempre mostrar la opción "Sin Asignar"
                } else {
                    const optionClientId = option.getAttribute('data-client');
                    if (clientId === '' || optionClientId === clientId) {
                        option.style.display = 'block';
                    } else {
                        option.style.display = 'none';
                    }
                }
            });
            
            // Resetear la selección si la opción actual no está visible
            if (userSelect.selectedOptions[0] && userSelect.selectedOptions[0].style.display === 'none') {
                userSelect.value = '';
            }
        });
        
        // Inicializar los filtros al cargar la página
        clientSelect.dispatchEvent(new Event('change'));
    }
});
</script> 