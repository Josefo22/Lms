<?php
// Título de la página
$page_title = "Editar Equipo";

// Incluir el controlador
require_once __DIR__ . '/../controllers/InventoryController.php';
$inventoryController = new InventoryController();

// Obtener ID del hardware desde la URL
$hardware_id = isset($_GET['id']) ? $_GET['id'] : null;

// Redireccionar si no hay ID
if (!$hardware_id) {
    header("Location: ?page=inventory");
    exit;
}

// Obtener detalles del hardware
$hardwareDetails = $inventoryController->getHardwareDetails($hardware_id);

// Verificar si el hardware existe
if (!$hardwareDetails['hardware']) {
    echo '<div class="alert alert-danger">Equipo no encontrado</div>';
    echo '<p><a href="?page=inventory" class="btn btn-primary">Volver al inventario</a></p>';
    exit;
}

$hardware = $hardwareDetails['hardware'];

// Obtener opciones para formularios
$formOptions = $inventoryController->getFormOptions();
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <div class="row align-items-center">
                        <div class="col">
                            <h5 class="mb-0">
                                <i class="bi bi-pencil-square me-2"></i> 
                                Editar Equipo: <?php echo htmlspecialchars($hardware['model_name']); ?> 
                                <?php if($hardware['asset_tag']): ?>
                                    (<?php echo htmlspecialchars($hardware['asset_tag']); ?>)
                                <?php endif; ?>
                            </h5>
                        </div>
                        <div class="col text-end">
                            <a href="?page=inventory&action=view&id=<?php echo $hardware_id; ?>" class="btn btn-sm btn-light me-2">
                                <i class="bi bi-arrow-left me-1"></i> Cancelar
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <form action="?page=inventory&action=update" method="POST" id="editHardwareForm">
                        <input type="hidden" name="hardware_id" value="<?php echo $hardware_id; ?>">
                        
                        <div class="row">
                            <!-- Información básica -->
                            <div class="col-md-6">
                                <div class="card mb-4">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0">Información Básica</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label for="serial_number" class="form-label">Número de Serie *</label>
                                                <input type="text" class="form-control" id="serial_number" name="serial_number" value="<?php echo htmlspecialchars($hardware['serial_number']); ?>" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="asset_tag" class="form-label">Asset Tag</label>
                                                <input type="text" class="form-control" id="asset_tag" name="asset_tag" value="<?php echo htmlspecialchars($hardware['asset_tag'] ?? ''); ?>">
                                            </div>
                                            <div class="col-md-6">
                                                <label for="model_id" class="form-label">Modelo *</label>
                                                <select class="form-select" id="model_id" name="model_id" required>
                                                    <?php foreach($formOptions['models'] as $model): ?>
                                                    <option value="<?php echo $model['model_id']; ?>" <?php echo ($model['model_id'] == $hardware['model_id']) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($model['model_name']); ?> (<?php echo htmlspecialchars($model['brand_name']); ?>)
                                                    </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="purchase_date" class="form-label">Fecha de Compra *</label>
                                                <input type="date" class="form-control" id="purchase_date" name="purchase_date" value="<?php echo date('Y-m-d', strtotime($hardware['purchase_date'])); ?>" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="warranty_expiry_date" class="form-label">Fecha de Vencimiento de Garantía</label>
                                                <input type="date" class="form-control" id="warranty_expiry_date" name="warranty_expiry_date" value="<?php echo $hardware['warranty_expiry_date'] ? date('Y-m-d', strtotime($hardware['warranty_expiry_date'])) : ''; ?>">
                                            </div>
                                            <div class="col-md-6">
                                                <label for="status" class="form-label">Estado *</label>
                                                <select class="form-select" id="status" name="status" required>
                                                    <option value="In Stock" <?php echo $hardware['status'] == 'In Stock' ? 'selected' : ''; ?>>Disponible</option>
                                                    <option value="In Use" <?php echo $hardware['status'] == 'In Use' ? 'selected' : ''; ?>>En Uso</option>
                                                    <option value="In Repair" <?php echo $hardware['status'] == 'In Repair' ? 'selected' : ''; ?>>En Reparación</option>
                                                    <option value="In Transit" <?php echo $hardware['status'] == 'In Transit' ? 'selected' : ''; ?>>En Tránsito</option>
                                                    <option value="Retired" <?php echo $hardware['status'] == 'Retired' ? 'selected' : ''; ?>>Retirado</option>
                                                    <option value="Lost" <?php echo $hardware['status'] == 'Lost' ? 'selected' : ''; ?>>Perdido</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="condition_status" class="form-label">Condición *</label>
                                                <select class="form-select" id="condition_status" name="condition_status" required>
                                                    <option value="New" <?php echo $hardware['condition_status'] == 'New' ? 'selected' : ''; ?>>Nuevo</option>
                                                    <option value="Good" <?php echo $hardware['condition_status'] == 'Good' ? 'selected' : ''; ?>>Bueno</option>
                                                    <option value="Fair" <?php echo $hardware['condition_status'] == 'Fair' ? 'selected' : ''; ?>>Regular</option>
                                                    <option value="Poor" <?php echo $hardware['condition_status'] == 'Poor' ? 'selected' : ''; ?>>Malo</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Asignación -->
                            <div class="col-md-6">
                                <div class="card mb-4">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0">Asignación</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label for="client_id" class="form-label">Cliente</label>
                                                <select class="form-select" id="client_id" name="client_id">
                                                    <option value="">Sin asignar</option>
                                                    <?php foreach($formOptions['clients'] as $client): ?>
                                                    <option value="<?php echo $client['client_id']; ?>" <?php echo ($client['client_id'] == $hardware['client_id']) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($client['client_name']); ?>
                                                    </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="location_id" class="form-label">Ubicación</label>
                                                <select class="form-select" id="location_id" name="location_id">
                                                    <option value="">Sin asignar</option>
                                                    <?php foreach($formOptions['locations'] as $location): ?>
                                                    <option value="<?php echo $location['location_id']; ?>" 
                                                        data-client="<?php echo $location['client_id']; ?>" 
                                                        <?php echo ($location['location_id'] == $hardware['location_id']) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($location['location_name']); ?>
                                                        <?php if($location['client_name']): ?>
                                                        (<?php echo htmlspecialchars($location['client_name']); ?>)
                                                        <?php endif; ?>
                                                    </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-12">
                                                <label for="user_id" class="form-label">Usuario Asignado</label>
                                                <select class="form-select" id="user_id" name="user_id">
                                                    <option value="">Sin asignar</option>
                                                    <?php foreach($formOptions['users'] as $user): ?>
                                                    <option value="<?php echo $user['user_id']; ?>" <?php echo ($user['user_id'] == $hardware['user_id']) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($user['name']); ?>
                                                    </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="assignment_date" class="form-label">Fecha de Asignación</label>
                                                <input type="date" class="form-control" id="assignment_date" name="assignment_date" 
                                                       value="<?php echo date('Y-m-d'); ?>" 
                                                       <?php echo (!$hardware['client_id'] && !$hardware['user_id']) ? '' : 'disabled'; ?>>
                                                <div class="form-text text-muted">Solo aplica para nuevas asignaciones</div>
                                            </div>
                                            <div class="col-12 mt-3">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" id="register_assignment" name="register_assignment" value="1" 
                                                           <?php echo (!$hardware['client_id'] && !$hardware['user_id']) ? 'checked' : ''; ?>>
                                                    <label class="form-check-label" for="register_assignment">
                                                        Registrar como nueva asignación en el historial
                                                    </label>
                                                </div>
                                                <div class="form-text text-muted">
                                                    Active esta opción si está asignando este equipo a un nuevo cliente o usuario.
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Notas -->
                            <div class="col-md-12">
                                <div class="card mb-4">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0">Notas</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="notes" class="form-label">Notas sobre el equipo</label>
                                            <textarea class="form-control" id="notes" name="notes" rows="4"><?php echo htmlspecialchars($hardware['notes'] ?? ''); ?></textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label for="assignment_notes" class="form-label">Notas de asignación</label>
                                            <textarea class="form-control" id="assignment_notes" name="assignment_notes" rows="3"></textarea>
                                            <div class="form-text text-muted">
                                                Estas notas solo se guardarán en el historial de asignación si registra una nueva asignación.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Botones de acción -->
                            <div class="col-md-12 text-end">
                                <a href="?page=inventory&action=view&id=<?php echo $hardware_id; ?>" class="btn btn-secondary me-2">
                                    <i class="bi bi-x-circle me-1"></i> Cancelar
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle me-1"></i> Guardar Cambios
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Manejar la visibilidad del campo de fecha de asignación
    const registerAssignmentCheckbox = document.getElementById('register_assignment');
    const assignmentDateField = document.getElementById('assignment_date');
    const assignmentNotesField = document.getElementById('assignment_notes');
    const clientSelect = document.getElementById('client_id');
    const userSelect = document.getElementById('user_id');
    const statusSelect = document.getElementById('status');
    
    function updateAssignmentFields() {
        const isNewAssignment = registerAssignmentCheckbox.checked;
        assignmentDateField.disabled = !isNewAssignment;
        
        // Si se está registrando una nueva asignación y se selecciona un cliente o usuario
        if (isNewAssignment && (clientSelect.value || userSelect.value)) {
            statusSelect.value = 'In Use';
        }
    }
    
    if (registerAssignmentCheckbox && assignmentDateField) {
        registerAssignmentCheckbox.addEventListener('change', updateAssignmentFields);
        
        // También actualizar cuando cambia el cliente o usuario
        if (clientSelect) clientSelect.addEventListener('change', function() {
            if (this.value && registerAssignmentCheckbox.checked) {
                statusSelect.value = 'In Use';
            }
        });
        
        if (userSelect) userSelect.addEventListener('change', function() {
            if (this.value && registerAssignmentCheckbox.checked) {
                statusSelect.value = 'In Use';
            }
        });
    }
    
    // Filtrar ubicaciones según el cliente seleccionado
    if (clientSelect && document.getElementById('location_id')) {
        const locationSelect = document.getElementById('location_id');
        
        clientSelect.addEventListener('change', function() {
            const clientId = this.value;
            const locationOptions = locationSelect.querySelectorAll('option');
            
            // Mostrar opción "Sin asignar" siempre
            locationSelect.value = '';
            
            // Recorrer opciones y mostrar/ocultar según el cliente
            locationOptions.forEach(function(option) {
                if (option.value === '') {
                    option.style.display = 'block'; // Mostrar opción "Sin asignar"
                } else {
                    const optionClientId = option.getAttribute('data-client');
                    if (clientId === '' || optionClientId === clientId) {
                        option.style.display = 'block';
                    } else {
                        option.style.display = 'none';
                    }
                }
            });
        });
    }
});
</script> 