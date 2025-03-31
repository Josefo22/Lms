<?php
// Obtener el ID del hardware a editar
$hardware_id = $_GET['id'] ?? null;

if (!$hardware_id) {
    $_SESSION['message'] = 'ID de hardware no especificado';
    $_SESSION['message_type'] = 'danger';
    header('Location: ?page=inventory');
    exit;
}

// Obtener detalles del hardware
$hardwareDetails = $inventoryController->getHardwareDetails($hardware_id);
$formOptions = $inventoryController->getFormOptions();

// Procesar formulario si se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $result = $inventoryController->updateHardware($hardware_id, $_POST);
        $_SESSION['message'] = $result['message'];
        $_SESSION['message_type'] = $result['success'] ? 'success' : 'danger';
        
        if ($result['success']) {
            header('Location: ?page=inventory&action=view&id=' . $hardware_id);
            exit;
        }
    } catch (Exception $e) {
        $_SESSION['message'] = 'Error al actualizar el equipo: ' . $e->getMessage();
        $_SESSION['message_type'] = 'danger';
    }
}
?>

<div id="content" class="p-4 p-md-5 pt-5">
    <div class="container-fluid">
        <!-- Encabezado principal y botón de acción -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Editar Equipo</h2>
            <div>
                <a href="?page=inventory&action=view&id=<?php echo $hardware_id; ?>" class="btn btn-outline-primary me-2">
                    <i class="bi bi-eye me-1"></i> Ver Detalles
                </a>
                <a href="?page=inventory" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Volver al Inventario
                </a>
            </div>
        </div>

        <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-<?php echo $_SESSION['message_type']; ?> alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['message']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php 
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
        endif; 
        ?>

        <form action="?page=inventory&action=edit&id=<?php echo $hardware_id; ?>" method="POST" id="editHardwareForm">
            <div class="row">
                <!-- Información básica -->
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm rounded mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Información Básica</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="serial_number" class="form-label">Número de Serie *</label>
                                    <input type="text" class="form-control" id="serial_number" name="serial_number" 
                                           value="<?php echo htmlspecialchars($hardwareDetails['hardware']['serial_number']); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="asset_tag" class="form-label">Asset Tag</label>
                                    <input type="text" class="form-control" id="asset_tag" name="asset_tag"
                                           value="<?php echo htmlspecialchars($hardwareDetails['hardware']['asset_tag'] ?? ''); ?>">
                                </div>
                                <div class="col-md-12">
                                    <label for="model_id" class="form-label">Modelo *</label>
                                    <select class="form-select" id="model_id" name="model_id" required>
                                        <option value="">Seleccione un modelo</option>
                                        <?php foreach($formOptions['models'] as $model): ?>
                                        <option value="<?php echo $model['model_id']; ?>" 
                                                <?php echo ($model['model_id'] == $hardwareDetails['hardware']['model_id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($model['model_name']); ?> 
                                            (<?php echo htmlspecialchars($model['brand_name']); ?>)
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="status" class="form-label">Estado *</label>
                                    <select class="form-select" id="status" name="status" required>
                                        <?php
                                        $estados = [
                                            'In Stock' => 'Disponible',
                                            'In Use' => 'En Uso',
                                            'In Repair' => 'En Reparación',
                                            'In Transit' => 'En Tránsito',
                                            'Retired' => 'Retirado',
                                            'Lost' => 'Perdido'
                                        ];
                                        foreach($estados as $value => $label):
                                        ?>
                                        <option value="<?php echo $value; ?>" 
                                                <?php echo ($value == $hardwareDetails['hardware']['status']) ? 'selected' : ''; ?>>
                                            <?php echo $label; ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="condition_status" class="form-label">Condición *</label>
                                    <select class="form-select" id="condition_status" name="condition_status" required>
                                        <?php
                                        $condiciones = [
                                            'New' => 'Nuevo',
                                            'Good' => 'Bueno',
                                            'Fair' => 'Regular',
                                            'Poor' => 'Malo'
                                        ];
                                        foreach($condiciones as $value => $label):
                                        ?>
                                        <option value="<?php echo $value; ?>" 
                                                <?php echo ($value == $hardwareDetails['hardware']['condition_status']) ? 'selected' : ''; ?>>
                                            <?php echo $label; ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Fechas -->
                    <div class="card border-0 shadow-sm rounded mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Fechas</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="purchase_date" class="form-label">Fecha de Compra *</label>
                                    <input type="date" class="form-control" id="purchase_date" name="purchase_date" 
                                           value="<?php echo $hardwareDetails['hardware']['purchase_date']; ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="warranty_expiry_date" class="form-label">Vencimiento Garantía</label>
                                    <input type="date" class="form-control" id="warranty_expiry_date" name="warranty_expiry_date"
                                           value="<?php echo $hardwareDetails['hardware']['warranty_expiry_date'] ?? ''; ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Asignación y Ubicación -->
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm rounded mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Asignación y Ubicación</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label for="client_id" class="form-label">Cliente</label>
                                    <select class="form-select" id="client_id" name="client_id">
                                        <option value="">Sin asignar</option>
                                        <?php foreach($formOptions['clients'] as $client): ?>
                                        <option value="<?php echo $client['client_id']; ?>"
                                                <?php echo ($client['client_id'] == $hardwareDetails['hardware']['client_id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($client['client_name']); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-12">
                                    <label for="location_id" class="form-label">Ubicación</label>
                                    <select class="form-select" id="location_id" name="location_id">
                                        <option value="">Sin asignar</option>
                                        <?php foreach($formOptions['locations'] as $location): ?>
                                        <option value="<?php echo $location['location_id']; ?>"
                                                data-client="<?php echo $location['client_id']; ?>"
                                                <?php echo ($location['location_id'] == $hardwareDetails['hardware']['location_id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($location['location_name']); ?>
                                            <?php if($location['client_name']): ?>
                                            (<?php echo htmlspecialchars($location['client_name']); ?>)
                                            <?php endif; ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-12">
                                    <label for="current_user_id" class="form-label">Usuario Asignado</label>
                                    <select class="form-select" id="current_user_id" name="current_user_id">
                                        <option value="">Sin asignar</option>
                                        <?php foreach($formOptions['users'] as $user): ?>
                                        <option value="<?php echo $user['user_id']; ?>"
                                                <?php echo ($user['user_id'] == $hardwareDetails['hardware']['current_user_id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($user['name']); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Notas -->
                    <div class="card border-0 shadow-sm rounded mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Notas</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="notes" class="form-label">Notas sobre el equipo</label>
                                <textarea class="form-control" id="notes" name="notes" rows="3"><?php 
                                    echo htmlspecialchars($hardwareDetails['hardware']['notes'] ?? ''); 
                                ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Botones de acción -->
                <div class="col-12 text-end mt-3">
                    <a href="?page=inventory" class="btn btn-secondary me-2">
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Filtrar ubicaciones según el cliente seleccionado
    const clientSelect = document.getElementById('client_id');
    const locationSelect = document.getElementById('location_id');
    
    if (clientSelect && locationSelect) {
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