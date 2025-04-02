<?php
// Inicializar controlador
require_once 'app/controllers/SupportController.php';
$supportController = new SupportController();

// Obtener listas para los selectores
$users = $supportController->getUserOptions();
$statuses = $supportController->getStatusOptions();
$priorities = $supportController->getPriorityOptions();
$clients = $supportController->getClientOptions();
?>

<div id="content" class="p-4 p-md-5 pt-5">
    <div class="container-fluid">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-primary text-white">
                <div class="row align-items-center">
                    <div class="col">
                        <h5 class="mb-0">
                            <i class="bi bi-plus-circle me-2"></i>
                            Nuevo Ticket de Soporte
                        </h5>
                    </div>
                    <div class="col text-end">
                        <a href="?page=support" class="btn btn-sm btn-light">
                            <i class="bi bi-x-lg me-1"></i> Cancelar
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <form action="index.php?page=support&action=store" method="POST" class="needs-validation" novalidate>
                    <div class="row g-4">
                        <!-- Información Principal -->
                        <div class="col-md-8">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">Detalles de la Solicitud</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-4">
                                        <label for="subject" class="form-label fw-bold">Asunto</label>
                                        <input type="text" class="form-control" id="subject" name="subject" required>
                                        <div class="invalid-feedback">Por favor ingrese un asunto</div>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label for="request_type" class="form-label fw-bold">Tipo de Solicitud</label>
                                        <select class="form-select" id="request_type" name="request_type" required>
                                            <option value="">Seleccione un tipo</option>
                                            <?php foreach($supportController->getRequestTypeOptions() as $value => $label): ?>
                                                <option value="<?php echo $value; ?>"><?php echo htmlspecialchars($label); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="invalid-feedback">Por favor seleccione un tipo de solicitud</div>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label for="hardware_id" class="form-label fw-bold">Hardware</label>
                                        <select class="form-select" id="hardware_id" name="hardware_id">
                                            <option value="">Seleccione un hardware (opcional)</option>
                                            <?php foreach($supportController->getHardwareOptions() as $hardware): ?>
                                                <option value="<?php echo $hardware['id']; ?>">
                                                    <?php echo htmlspecialchars($hardware['name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label for="description" class="form-label fw-bold">Descripción</label>
                                        <textarea class="form-control" id="description" name="description" rows="4" required></textarea>
                                        <div class="invalid-feedback">Por favor ingrese una descripción</div>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label for="solution" class="form-label fw-bold">Solución</label>
                                        <textarea class="form-control" id="solution" name="resolution_notes" rows="4"></textarea>
                                    </div>
                                    
                                    <div class="mb-0">
                                        <label for="notes" class="form-label fw-bold">Notas Adicionales</label>
                                        <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Información Adicional -->
                        <div class="col-md-4">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">Información del Ticket</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label for="status" class="form-label fw-bold">Estado</label>
                                        <select class="form-select" id="status" name="status" required>
                                            <?php foreach($statuses as $value => $label): ?>
                                                <option value="<?php echo $value; ?>"><?php echo htmlspecialchars($label); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="invalid-feedback">Por favor seleccione un estado</div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="priority" class="form-label fw-bold">Prioridad</label>
                                        <select class="form-select" id="priority" name="priority" required>
                                            <?php foreach($priorities as $value => $label): ?>
                                                <option value="<?php echo $value; ?>"><?php echo htmlspecialchars($label); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="invalid-feedback">Por favor seleccione una prioridad</div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="client_id" class="form-label fw-bold">Cliente</label>
                                        <select class="form-select" id="client_id" name="client_id">
                                            <option value="">Seleccionar Cliente</option>
                                            <?php foreach($clients as $client): ?>
                                                <option value="<?php echo $client['id']; ?>">
                                                    <?php echo htmlspecialchars($client['name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    
                                    <div class="mb-0">
                                        <label for="assigned_to" class="form-label fw-bold">Asignar a</label>
                                        <select class="form-select" id="assigned_to" name="assigned_to">
                                            <option value="">Sin Asignar</option>
                                            <?php foreach($users as $user): ?>
                                                <option value="<?php echo $user['id']; ?>" 
                                                        data-client="<?php echo $user['client_id'] ?? ''; ?>">
                                                    <?php echo htmlspecialchars($user['name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Botones de acción -->
                    <div class="mt-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i> Crear Ticket
                        </button>
                        <a href="?page=support" class="btn btn-secondary">
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
    }
});
</script> 