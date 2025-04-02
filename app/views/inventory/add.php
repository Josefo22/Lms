<?php
$brands = $inventoryController->getBrands();
$categories = $inventoryController->getCategories();
$clients = $inventoryController->getClients();
$locations = $inventoryController->getLocations();
$users = $inventoryController->getUsers();
?>

<div id="content" class="p-4 p-md-5 pt-5">
    <div class="container-fluid">
        <!-- Encabezado principal y botones de acción -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Agregar Nuevo Equipo</h2>
            <div>
                <a href="index.php?page=manage_categories" class="btn btn-outline-primary me-2">
                    <i class="fas fa-list-ul"></i> Gestionar Categorías
                </a>
                <a href="index.php?page=manage_brands" class="btn btn-outline-primary me-2">
                    <i class="fas fa-tags"></i> Gestionar Marcas
                </a>
                <a href="index.php?page=manage_models" class="btn btn-outline-primary">
                    <i class="fas fa-laptop"></i> Gestionar Modelos
                </a>
            </div>
        </div>
    <div class="card mb-4">
        <div class="card-body">
            <form action="index.php?page=add_hardware" method="POST">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label for="serial_number" class="form-label">Número de Serie *</label>
                            <input type="text" class="form-control" id="serial_number" name="serial_number" required>
                        </div>
                        
                        <div class="form-group mb-3">
                            <label for="asset_tag" class="form-label">Etiqueta de Activo</label>
                            <input type="text" class="form-control" id="asset_tag" name="asset_tag">
                        </div>
                        
                        <div class="form-group mb-3">
                            <label for="brand_id" class="form-label">Marca *</label>
                            <div class="input-group">
                                <select class="form-select" id="brand_id" name="brand_id" required>
                                    <option value="">Seleccionar Marca</option>
                                    <?php foreach($brands as $brand): ?>
                                        <option value="<?php echo $brand['brand_id']; ?>"><?php echo htmlspecialchars($brand['brand_name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button class="btn btn-outline-secondary" type="button" data-bs-toggle="modal" data-bs-target="#addBrandModal">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="form-group mb-3">
                            <label for="model_id" class="form-label">Modelo *</label>
                            <select class="form-select" id="model_id" name="model_id" required>
                                <option value="">Seleccionar Modelo</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label for="category_id" class="form-label">Categoría *</label>
                            <div class="input-group">
                                <select class="form-select" id="category_id" name="category_id" required>
                                    <option value="">Seleccionar Categoría</option>
                                    <?php foreach($categories as $category): ?>
                                        <option value="<?php echo $category['category_id']; ?>"><?php echo htmlspecialchars($category['category_name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button class="btn btn-outline-secondary" type="button" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="form-group mb-3">
                            <label for="status" class="form-label">Estado *</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="In Stock">En Stock</option>
                                <option value="In Use">En Uso</option>
                                <option value="In Transit">En Tránsito</option>
                                <option value="In Repair">En Reparación</option>
                                <option value="Retired">Retirado</option>
                                <option value="Lost">Perdido</option>
                            </select>
                        </div>
                        
                        <div class="form-group mb-3">
                            <label for="client_id" class="form-label">Cliente</label>
                            <select class="form-select" id="client_id" name="client_id">
                                <option value="">Seleccionar Cliente</option>
                                <?php foreach($clients as $client): ?>
                                    <option value="<?php echo $client['client_id']; ?>"><?php echo htmlspecialchars($client['client_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group mb-3">
                            <label for="location_id" class="form-label">Ubicación</label>
                            <select class="form-select" id="location_id" name="location_id">
                                <option value="">Seleccionar Ubicación</option>
                                <?php foreach($locations as $location): ?>
                                    <option value="<?php echo $location['location_id']; ?>" 
                                           data-client="<?php echo $location['client_id']; ?>">
                                        <?php echo htmlspecialchars($location['location_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group mb-3">
                            <label for="current_user_id" class="form-label">Usuario Asignado</label>
                            <select class="form-select" id="current_user_id" name="current_user_id">
                                <option value="">Sin asignar</option>
                                <?php foreach($users as $user): ?>
                                <option value="<?php echo $user['user_id']; ?>"
                                        data-client="<?php echo $user['client_id']; ?>">
                                    <?php echo htmlspecialchars($user['name']); ?>
                                    <?php if($user['job_title']): ?> (<?php echo htmlspecialchars($user['job_title']); ?>)<?php endif; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="notes" class="form-label">Notas</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">Guardar Equipo</button>
                        <a href="index.php?page=inventory" class="btn btn-secondary">Cancelar</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para Agregar Nueva Marca -->
<div class="modal fade" id="addBrandModal" tabindex="-1" aria-labelledby="addBrandModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addBrandModalLabel">Agregar Nueva Marca</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addBrandForm">
                    <div class="mb-3">
                        <label for="brand_name" class="form-label">Nombre de la Marca *</label>
                        <input type="text" class="form-control" id="brand_name" name="brand_name" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="saveBrandBtn">Guardar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Agregar Nueva Categoría -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addCategoryModalLabel">Agregar Nueva Categoría</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addCategoryForm">
                    <div class="mb-3">
                        <label for="category_name" class="form-label">Nombre de la Categoría *</label>
                        <input type="text" class="form-control" id="category_name" name="category_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="category_description" class="form-label">Descripción</label>
                        <textarea class="form-control" id="category_description" name="description" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="saveCategoryBtn">Guardar</button>
            </div>
        </div>
    </div>
</div>

<script>
// Actualizar modelos cuando cambie la marca
document.getElementById('brand_id').addEventListener('change', function() {
    const brandId = this.value;
    const modelSelect = document.getElementById('model_id');
    
    // Limpiar opciones actuales
    modelSelect.innerHTML = '<option value="">Seleccionar Modelo</option>';
    
    if(brandId) {
        // Obtener modelos para la marca seleccionada
        fetch(`index.php?page=api&action=models&brand_id=${brandId}`)
            .then(response => response.json())
            .then(models => {
                models.forEach(model => {
                    const option = document.createElement('option');
                    option.value = model.model_id;
                    option.textContent = model.model_name;
                    modelSelect.appendChild(option);
                });
            });
    }
});

// Actualizar ubicaciones cuando cambie el cliente
document.getElementById('client_id').addEventListener('change', function() {
    const clientId = this.value;
    const locationSelect = document.getElementById('location_id');
    const userSelect = document.getElementById('current_user_id');
    
    // Actualizar ubicaciones
    // Limpiar opciones actuales
    locationSelect.innerHTML = '<option value="">Seleccionar Ubicación</option>';
    
    if(clientId) {
        // Obtener ubicaciones para el cliente seleccionado
        fetch(`index.php?page=api&action=locations&client_id=${clientId}`)
            .then(response => response.json())
            .then(locations => {
                locations.forEach(location => {
                    const option = document.createElement('option');
                    option.value = location.location_id;
                    option.textContent = location.location_name;
                    option.setAttribute('data-client', clientId);
                    locationSelect.appendChild(option);
                });
            });
    }
    
    // Filtrar usuarios por cliente
    if (userSelect) {
        const userOptions = userSelect.querySelectorAll('option');
        
        userOptions.forEach(option => {
            if (option.value === '') {
                option.style.display = 'block'; // Siempre mostrar la opción "Sin asignar"
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
    }
});

// Funcionalidad para agregar marca
document.getElementById('saveBrandBtn').addEventListener('click', function() {
    const brandName = document.getElementById('brand_name').value.trim();
    
    if (!brandName) {
        alert('Por favor ingrese el nombre de la marca');
        return;
    }
    
    const formData = new URLSearchParams();
    formData.append('brand_name', brandName);
    
    fetch('index.php?page=api&action=create_brand', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            console.error('Error de servidor:', response.status, response.statusText);
            throw new Error('Error del servidor: ' + response.status);
        }
        console.log('Respuesta completa:', response);
        return response.text();
    })
    .then(text => {
        console.log('Respuesta en texto:', text);
        try {
            const data = JSON.parse(text);
            if (data.success) {
                // Crear nueva opción para el select
                const brandSelect = document.getElementById('brand_id');
                const option = document.createElement('option');
                option.value = data.brand_id;
                option.textContent = brandName;
                brandSelect.appendChild(option);
                
                // Seleccionar la nueva marca
                brandSelect.value = data.brand_id;
                
                // Cerrar modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('addBrandModal'));
                modal.hide();
                
                // Limpiar formulario
                document.getElementById('brand_name').value = '';
                
                // Mostrar mensaje de éxito
                alert('Marca creada correctamente');
            } else {
                alert('Error al crear la marca: ' + (data.message || 'Error desconocido'));
            }
        } catch (e) {
            console.error('Error al procesar la respuesta JSON:', e);
            alert('Error al procesar la respuesta del servidor');
        }
    })
    .catch(error => {
        console.error('Error completo:', error);
        alert('Error al crear la marca: ' + error.message);
    });
});

// Funcionalidad para agregar categoría
document.getElementById('saveCategoryBtn').addEventListener('click', function() {
    const categoryName = document.getElementById('category_name').value.trim();
    const categoryDescription = document.getElementById('category_description').value.trim();
    
    if (!categoryName) {
        alert('Por favor ingrese el nombre de la categoría');
        return;
    }
    
    const formData = new URLSearchParams();
    formData.append('category_name', categoryName);
    formData.append('description', categoryDescription);
    
    fetch('index.php?page=api&action=create_category', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            console.error('Error de servidor:', response.status, response.statusText);
            throw new Error('Error del servidor: ' + response.status);
        }
        console.log('Respuesta completa:', response);
        return response.text();
    })
    .then(text => {
        console.log('Respuesta en texto:', text);
        try {
            const data = JSON.parse(text);
            if (data.success) {
                // Crear nueva opción para el select
                const categorySelect = document.getElementById('category_id');
                const option = document.createElement('option');
                option.value = data.category_id;
                option.textContent = categoryName;
                categorySelect.appendChild(option);
                
                // Seleccionar la nueva categoría
                categorySelect.value = data.category_id;
                
                // Cerrar modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('addCategoryModal'));
                modal.hide();
                
                // Limpiar formulario
                document.getElementById('category_name').value = '';
                document.getElementById('category_description').value = '';
                
                // Mostrar mensaje de éxito
                alert('Categoría creada correctamente');
            } else {
                alert('Error al crear la categoría: ' + (data.message || 'Error desconocido'));
            }
        } catch (e) {
            console.error('Error al procesar la respuesta JSON:', e);
            alert('Error al procesar la respuesta del servidor');
        }
    })
    .catch(error => {
        console.error('Error completo:', error);
        alert('Error al crear la categoría: ' + error.message);
    });
});
</script> 