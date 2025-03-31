<?php
// Título de la página
$page_title = "Agregar Equipo";

// Incluir el controlador
require_once __DIR__ . '/../controllers/InventoryController.php';
$inventoryController = new InventoryController();

// Obtener opciones para formularios
$formOptions = $inventoryController->getFormOptions();

// Procesar formulario si se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $result = $inventoryController->createHardware($_POST);
        $_SESSION['message'] = $result['message'];
        $_SESSION['message_type'] = $result['success'] ? 'success' : 'danger';
        
        if ($result['success']) {
            header('Location: index.php?page=inventory&action=view&id=' . $result['hardware_id']);
            exit;
        }
    } catch (Exception $e) {
        $_SESSION['message'] = 'Error al crear el equipo: ' . $e->getMessage();
        $_SESSION['message_type'] = 'danger';
        error_log('Error en add_hardware.php: ' . $e->getMessage());
    }
}
?>

<div id="content" class="p-4 p-md-5 pt-5">
    <div class="container-fluid">
        <!-- Encabezado principal y botón de acción -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Agregar Nuevo Equipo</h2>
            <a href="?page=inventory" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Volver al Inventario
            </a>
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
        
        <?php if(empty($formOptions['models'])): ?>
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <strong>¡Atención!</strong> No hay modelos disponibles en el sistema. Puede crear un nuevo modelo usando el botón "Nuevo Modelo" en el formulario.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>
        
        <!-- Para depuración de usuarios -->
        <?php if(empty($formOptions['users']) || !is_array($formOptions['users'])): ?>
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <strong>¡Atención!</strong> No hay usuarios disponibles en el sistema. 
            <?php 
            echo "Tipo de datos de users: " . gettype($formOptions['users']);
            if(isset($formOptions['users'])) {
                if(is_array($formOptions['users'])) {
                    echo " - Cantidad: " . count($formOptions['users']);
                }
            } else {
                echo " - No está definido";
            }
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php else: ?>
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <strong>Información:</strong> Hay <?php echo count($formOptions['users']); ?> usuarios disponibles.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>
        
        <form action="?page=add_hardware" method="POST" id="addHardwareForm">
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
                                    <input type="text" class="form-control" id="serial_number" name="serial_number" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="asset_tag" class="form-label">Asset Tag</label>
                                    <input type="text" class="form-control" id="asset_tag" name="asset_tag">
                                </div>
                                <div class="col-md-12">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <label for="model_id" class="form-label">Modelo *</label>
                                        <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#newModelModal">
                                            <i class="bi bi-plus-circle"></i> Nuevo Modelo
                                        </button>
                                    </div>
                                    <br>
                                    <select class="form-select" id="model_id" name="model_id" required>
                                        <option value="">Seleccione un modelo</option>
                                        <?php if(empty($formOptions['models'])): ?>
                                            <option value="" disabled>No hay modelos disponibles - Cree uno nuevo</option>
                                        <?php else: ?>
                                            <?php foreach($formOptions['models'] as $model): ?>
                                            <option value="<?php echo $model['model_id']; ?>">
                                                <?php echo htmlspecialchars($model['model_name']); ?> (<?php echo htmlspecialchars($model['brand_name']); ?>)
                                            </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                    <div class="form-text text-muted">
                                        Si no encuentra el modelo deseado, puede crear uno nuevo con el botón "Nuevo Modelo".
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="purchase_date" class="form-label">Fecha de Compra *</label>
                                    <input type="date" class="form-control" id="purchase_date" name="purchase_date" value="<?php echo date('Y-m-d'); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="warranty_expiry_date" class="form-label">Vencimiento Garantía</label>
                                    <input type="date" class="form-control" id="warranty_expiry_date" name="warranty_expiry_date">
                                </div>
                                <div class="col-md-6">
                                    <label for="status" class="form-label">Estado *</label>
                                    <select class="form-select" id="status" name="status" required>
                                        <option value="In Stock" selected>Disponible</option>
                                        <option value="In Use">En Uso</option>
                                        <option value="In Repair">En Reparación</option>
                                        <option value="In Transit">En Tránsito</option>
                                        <option value="Retired">Retirado</option>
                                        <option value="Lost">Perdido</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="condition_status" class="form-label">Condición *</label>
                                    <select class="form-select" id="condition_status" name="condition_status" required>
                                        <option value="New" selected>Nuevo</option>
                                        <option value="Good">Bueno</option>
                                        <option value="Fair">Regular</option>
                                        <option value="Poor">Malo</option>
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
                                <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Asignación y otras opciones -->
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm rounded mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Asignación</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label for="user_id" class="form-label"><i class="bi bi-person me-1"></i> Usuario Asignado</label>
                                    <select class="form-select" id="user_id" name="user_id">
                                        <option value="">Sin asignar</option>
                                        <?php if(is_array($formOptions['users']) && !empty($formOptions['users'])): ?>
                                            <?php foreach($formOptions['users'] as $user): ?>
                                                <?php if(isset($user['user_id']) && isset($user['name'])): ?>
                                                <option value="<?php echo $user['user_id']; ?>">
                                                    <?php echo htmlspecialchars($user['name'] ?? '(Sin nombre)'); ?>
                                                </option>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                    <div class="form-text text-muted">
                                        Seleccione el usuario que va a utilizar este equipo.
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="client_id" class="form-label"><i class="bi bi-building me-1"></i> Cliente (Opcional)</label>
                                    <select class="form-select" id="client_id" name="client_id">
                                        <option value="">Sin asignar</option>
                                        <?php foreach($formOptions['clients'] as $client): ?>
                                        <option value="<?php echo $client['client_id']; ?>">
                                            <?php echo htmlspecialchars($client['client_name']); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="location_id" class="form-label"><i class="bi bi-geo-alt me-1"></i> Ubicación</label>
                                    <select class="form-select" id="location_id" name="location_id">
                                        <option value="">Sin asignar</option>
                                        <?php foreach($formOptions['locations'] as $location): ?>
                                        <option value="<?php echo $location['location_id']; ?>" 
                                            data-client="<?php echo $location['client_id']; ?>">
                                            <?php echo htmlspecialchars($location['location_name']); ?>
                                            <?php if($location['client_name']): ?>
                                            (<?php echo htmlspecialchars($location['client_name']); ?>)
                                            <?php endif; ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-12 mt-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="register_assignment" name="register_assignment" value="1" checked>
                                        <label class="form-check-label" for="register_assignment">
                                            Registrar asignación en el historial
                                        </label>
                                    </div>
                                    <div class="form-text text-muted">
                                        Active esta opción si está asignando este equipo a un usuario.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card border-0 shadow-sm rounded mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Notas de Asignación</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="assignment_notes" class="form-label">Notas de asignación</label>
                                <textarea class="form-control" id="assignment_notes" name="assignment_notes" rows="3"></textarea>
                                <div class="form-text text-muted">
                                    Estas notas solo se guardarán en el historial de asignación si registra una asignación.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Botones de acción -->
                <div class="col-12 text-end mt-2">
                    <a href="?page=inventory" class="btn btn-secondary me-2">
                        <i class="bi bi-x-circle me-1"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-1"></i> Guardar Equipo
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Manejar la visibilidad del campo de fecha de asignación
    const registerAssignmentCheckbox = document.getElementById('register_assignment');
    const assignmentNotesField = document.getElementById('assignment_notes');
    const clientSelect = document.getElementById('client_id');
    const userSelect = document.getElementById('user_id');
    const statusSelect = document.getElementById('status');
    
    function updateAssignmentFields() {
        const isNewAssignment = registerAssignmentCheckbox.checked;
        
        // Si se está registrando una asignación y se selecciona un usuario
        if (isNewAssignment && userSelect.value) {
            statusSelect.value = 'In Use';
        }
    }
    
    if (registerAssignmentCheckbox) {
        registerAssignmentCheckbox.addEventListener('change', updateAssignmentFields);
        
        // También actualizar cuando cambia el usuario
        if (userSelect) userSelect.addEventListener('change', function() {
            if (this.value && registerAssignmentCheckbox.checked) {
                statusSelect.value = 'In Use';
            }
        });
        
        // El cliente es secundario en la asignación
        if (clientSelect) clientSelect.addEventListener('change', function() {
            // No cambiamos el estado basado en cliente, sólo en usuario
        });
    }
    
    // Validación del formulario antes de enviar
    const form = document.getElementById('addHardwareForm');
    
    form.addEventListener('submit', function(event) {
        event.preventDefault(); // Prevenir envío por defecto
        
        let isValid = true;
        
        // Validar serial number
        const serialNumber = document.getElementById('serial_number').value.trim();
        if (!serialNumber) {
            isValid = false;
            showAlert('El número de serie es obligatorio', 'error');
            return;
        }
        
        // Validar modelo
        const modelId = document.getElementById('model_id').value;
        if (!modelId) {
            isValid = false;
            showAlert('Debe seleccionar un modelo', 'error');
            return;
        }
        
        // Validación adicional para clientes y usuarios - asegurar que valores vacíos sean manejados como null
        const clientSelect = document.getElementById('client_id');
        if (clientSelect && clientSelect.value === "") {
            clientSelect.value = "";  // Asegurar que valor vacío sea realmente ""
        }
        
        const userSelect = document.getElementById('user_id');
        if (userSelect && userSelect.value === "") {
            userSelect.value = "";  // Asegurar que valor vacío sea realmente ""
        }
        
        const locationSelect = document.getElementById('location_id');
        if (locationSelect && locationSelect.value === "") {
            locationSelect.value = "";  // Asegurar que valor vacío sea realmente ""
        }
        
        // Si todo está validado, enviar el formulario
        if (isValid) {
            form.submit();
        }
    });
    
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

// Funcionalidad para crear y guardar nuevos modelos, marcas y categorías
document.addEventListener('DOMContentLoaded', function() {
    // Referencias a elementos del DOM
    const saveModelBtn = document.getElementById('saveModelBtn');
    const saveBrandBtn = document.getElementById('saveBrandBtn');
    const saveCategoryBtn = document.getElementById('saveCategoryBtn');
    const modelSelect = document.getElementById('model_id');
    
    // Función para mostrar alertas (más visible y detallada)
    function showAlert(message, type = 'error') {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show`;
        alertDiv.setAttribute('role', 'alert');
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        
        // Insertar al principio del contenedor
        const container = document.querySelector('.container-fluid');
        container.insertBefore(alertDiv, container.firstChild);
        
        // Auto-cerrar después de 5 segundos
        setTimeout(() => {
            alertDiv.remove();
        }, 5000);
    }
    
    // Guardar nueva marca
    if (saveBrandBtn) {
        saveBrandBtn.addEventListener('click', function() {
            const brandName = document.getElementById('new_brand_name').value.trim();
            
            if (!brandName) {
                showAlert('Por favor, ingrese el nombre de la marca');
                return;
            }
            
            // Mostrar indicador de carga
            saveBrandBtn.disabled = true;
            saveBrandBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Guardando...';
            
            // Enviar petición AJAX para crear marca
            const formData = new FormData();
            formData.append('action', 'create_brand');
            formData.append('brand_name', brandName);
            
            fetch('ajax_handler.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`Error de red: ${response.status} ${response.statusText}`);
                }
                return response.text().then(text => {
                    try {
                        // Intentar parsear la respuesta como JSON
                        if (!text) {
                            throw new Error('Respuesta vacía del servidor');
                        }
                        return JSON.parse(text);
                    } catch (e) {
                        console.error('Error al analizar JSON:', e);
                        console.error('Respuesta del servidor:', text);
                        throw new Error(`Respuesta no válida del servidor: ${e.message}`);
                    }
                });
            })
            .then(data => {
                // Restaurar botón
                saveBrandBtn.disabled = false;
                saveBrandBtn.innerHTML = 'Guardar Marca';
                
                if (data.success) {
                    // Actualizar el selector de marcas en el modal de modelo
                    const brandSelect = document.getElementById('new_brand_id');
                    const option = document.createElement('option');
                    option.value = data.brand_id;
                    option.textContent = brandName;
                    brandSelect.appendChild(option);
                    brandSelect.value = data.brand_id;
                    
                    // Cerrar el modal
                    const brandModal = bootstrap.Modal.getInstance(document.getElementById('newBrandModal'));
                    brandModal.hide();
                    
                    // Mostrar mensaje de éxito
                    showAlert('Marca creada correctamente', 'success');
                } else {
                    showAlert(`Error al crear la marca: ${data.message}`);
                }
            })
            .catch(error => {
                // Restaurar botón
                saveBrandBtn.disabled = false;
                saveBrandBtn.innerHTML = 'Guardar Marca';
                
                console.error('Error:', error);
                showAlert(`Error al procesar la solicitud: ${error.message}`);
            });
        });
    }
    
    // Guardar nueva categoría
    if (saveCategoryBtn) {
        saveCategoryBtn.addEventListener('click', function() {
            const categoryName = document.getElementById('new_category_name').value.trim();
            const categoryDescription = document.getElementById('new_category_description').value.trim();
            
            if (!categoryName) {
                showAlert('Por favor, ingrese el nombre de la categoría');
                return;
            }
            
            // Mostrar indicador de carga
            saveCategoryBtn.disabled = true;
            saveCategoryBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Guardando...';
            
            // Enviar petición AJAX para crear categoría
            const formData = new FormData();
            formData.append('action', 'create_category');
            formData.append('category_name', categoryName);
            formData.append('description', categoryDescription);
            
            fetch('ajax_handler.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`Error de red: ${response.status} ${response.statusText}`);
                }
                return response.text().then(text => {
                    try {
                        // Intentar parsear la respuesta como JSON
                        if (!text) {
                            throw new Error('Respuesta vacía del servidor');
                        }
                        return JSON.parse(text);
                    } catch (e) {
                        console.error('Error al analizar JSON:', e);
                        console.error('Respuesta del servidor:', text);
                        throw new Error(`Respuesta no válida del servidor: ${e.message}`);
                    }
                });
            })
            .then(data => {
                // Restaurar botón
                saveCategoryBtn.disabled = false;
                saveCategoryBtn.innerHTML = 'Guardar Categoría';
                
                if (data.success) {
                    // Actualizar el selector de categorías en el modal de modelo
                    const categorySelect = document.getElementById('new_category_id');
                    const option = document.createElement('option');
                    option.value = data.category_id;
                    option.textContent = categoryName;
                    categorySelect.appendChild(option);
                    categorySelect.value = data.category_id;
                    
                    // Cerrar el modal
                    const categoryModal = bootstrap.Modal.getInstance(document.getElementById('newCategoryModal'));
                    categoryModal.hide();
                    
                    // Mostrar mensaje de éxito
                    showAlert('Categoría creada correctamente', 'success');
                } else {
                    showAlert(`Error al crear la categoría: ${data.message}`);
                }
            })
            .catch(error => {
                // Restaurar botón
                saveCategoryBtn.disabled = false;
                saveCategoryBtn.innerHTML = 'Guardar Categoría';
                
                console.error('Error:', error);
                showAlert(`Error al procesar la solicitud: ${error.message}`);
            });
        });
    }
    
    // Guardar nuevo modelo
    if (saveModelBtn) {
        saveModelBtn.addEventListener('click', function() {
            const modelName = document.getElementById('new_model_name').value.trim();
            const brandId = document.getElementById('new_brand_id').value;
            const categoryId = document.getElementById('new_category_id').value;
            const specifications = document.getElementById('new_specifications').value.trim();
            
            if (!modelName) {
                showAlert('Por favor, ingrese el nombre del modelo');
                return;
            }
            
            if (!brandId) {
                showAlert('Por favor, seleccione una marca');
                return;
            }
            
            if (!categoryId) {
                showAlert('Por favor, seleccione una categoría');
                return;
            }
            
            // Mostrar indicador de carga
            saveModelBtn.disabled = true;
            saveModelBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Guardando...';
            
            // Enviar petición AJAX para crear modelo
            const formData = new FormData();
            formData.append('action', 'create_model');
            formData.append('model_name', modelName);
            formData.append('brand_id', brandId);
            formData.append('category_id', categoryId);
            formData.append('specifications', specifications);
            
            fetch('ajax_handler.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`Error de red: ${response.status} ${response.statusText}`);
                }
                return response.text().then(text => {
                    try {
                        // Intentar parsear la respuesta como JSON
                        if (!text) {
                            throw new Error('Respuesta vacía del servidor');
                        }
                        return JSON.parse(text);
                    } catch (e) {
                        console.error('Error al analizar JSON:', e);
                        console.error('Respuesta del servidor:', text);
                        throw new Error(`Respuesta no válida del servidor: ${e.message}`);
                    }
                });
            })
            .then(data => {
                // Restaurar botón
                saveModelBtn.disabled = false;
                saveModelBtn.innerHTML = 'Guardar Modelo';
                
                if (data.success) {
                    // Actualizar el selector de modelos en el formulario principal
                    const option = document.createElement('option');
                    option.value = data.model_id;
                    
                    // Obtener el nombre de la marca
                    const brandSelect = document.getElementById('new_brand_id');
                    const brandName = brandSelect.options[brandSelect.selectedIndex].text;
                    
                    option.textContent = modelName + ' (' + brandName + ')';
                    modelSelect.appendChild(option);
                    modelSelect.value = data.model_id;
                    
                    // Cerrar el modal
                    const modelModal = bootstrap.Modal.getInstance(document.getElementById('newModelModal'));
                    modelModal.hide();
                    
                    // Mostrar mensaje de éxito
                    showAlert('Modelo creado correctamente', 'success');
                } else {
                    showAlert(`Error al crear el modelo: ${data.message}`);
                }
            })
            .catch(error => {
                // Restaurar botón
                saveModelBtn.disabled = false;
                saveModelBtn.innerHTML = 'Guardar Modelo';
                
                console.error('Error:', error);
                showAlert(`Error al procesar la solicitud: ${error.message}`);
            });
        });
    }
    
    // Limpiar formularios al cerrar modales
    document.querySelectorAll('.modal').forEach(function(modal) {
        modal.addEventListener('hidden.bs.modal', function() {
            const forms = this.querySelectorAll('form');
            forms.forEach(form => form.reset());
        });
    });
});
</script>

<!-- Modal para crear nuevo modelo -->
<div class="modal fade" id="newModelModal" tabindex="-1" aria-labelledby="newModelModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="newModelModalLabel">Crear Nuevo Modelo</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="newModelForm" action="javascript:void(0);">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="new_model_name" class="form-label">Nombre del Modelo *</label>
                            <input type="text" class="form-control" id="new_model_name" required>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between align-items-center">
                                <label for="new_brand_id" class="form-label">Marca *</label>
                                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#newBrandModal">
                                    <i class="bi bi-plus-circle"></i> Nueva Marca
                                </button>
                            </div>
                            <select class="form-select" id="new_brand_id" required>
                                <option value="">Seleccione una marca</option>
                                <?php if(empty($formOptions['brands'])): ?>
                                    <option value="" disabled>No hay marcas disponibles - Cree una nueva</option>
                                <?php else: ?>
                                    <?php foreach($formOptions['brands'] as $brand): ?>
                                    <option value="<?php echo $brand['brand_id']; ?>">
                                        <?php echo htmlspecialchars($brand['brand_name']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <div class="d-flex justify-content-between align-items-center">
                                <label for="new_category_id" class="form-label">Categoría *</label>
                                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#newCategoryModal">
                                    <i class="bi bi-plus-circle"></i> Nueva Categoría
                                </button>
                            </div>
                            <select class="form-select" id="new_category_id" required>
                                <option value="">Seleccione una categoría</option>
                                <?php if(empty($formOptions['categories'])): ?>
                                    <option value="" disabled>No hay categorías disponibles - Cree una nueva</option>
                                <?php else: ?>
                                    <?php foreach($formOptions['categories'] as $category): ?>
                                    <option value="<?php echo $category['category_id']; ?>">
                                        <?php echo htmlspecialchars($category['category_name']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label for="new_specifications" class="form-label">Especificaciones</label>
                            <textarea class="form-control" id="new_specifications" rows="3"></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="saveModelBtn">Guardar Modelo</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para crear nueva marca -->
<div class="modal fade" id="newBrandModal" tabindex="-1" aria-labelledby="newBrandModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="newBrandModalLabel">Crear Nueva Marca</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="newBrandForm" action="javascript:void(0);">
                    <div class="mb-3">
                        <label for="new_brand_name" class="form-label">Nombre de la Marca *</label>
                        <input type="text" class="form-control" id="new_brand_name" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="saveBrandBtn">Guardar Marca</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para crear nueva categoría -->
<div class="modal fade" id="newCategoryModal" tabindex="-1" aria-labelledby="newCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="newCategoryModalLabel">Crear Nueva Categoría</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="newCategoryForm" action="javascript:void(0);">
                    <div class="mb-3">
                        <label for="new_category_name" class="form-label">Nombre de la Categoría *</label>
                        <input type="text" class="form-control" id="new_category_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="new_category_description" class="form-label">Descripción</label>
                        <textarea class="form-control" id="new_category_description" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="saveCategoryBtn">Guardar Categoría</button>
            </div>
        </div>
    </div>
</div> 