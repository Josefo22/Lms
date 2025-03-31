<?php
$brands = $inventoryController->getBrands();
$categories = $inventoryController->getCategories();
$clients = $inventoryController->getClients();
$locations = $inventoryController->getLocations();
?>

<div id="content" class="p-4 p-md-5 pt-5">
    <div class="container-fluid">
        <!-- Encabezado principal y botón de acción -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Agregar Nuevo Equipo</h2>
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
                            <select class="form-select" id="brand_id" name="brand_id" required>
                                <option value="">Seleccionar Marca</option>
                                <?php foreach($brands as $brand): ?>
                                    <option value="<?php echo $brand['brand_id']; ?>"><?php echo htmlspecialchars($brand['brand_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
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
                            <select class="form-select" id="category_id" name="category_id" required>
                                <option value="">Seleccionar Categoría</option>
                                <?php foreach($categories as $category): ?>
                                    <option value="<?php echo $category['category_id']; ?>"><?php echo htmlspecialchars($category['category_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
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
                                    <option value="<?php echo $location['location_id']; ?>"><?php echo htmlspecialchars($location['location_name']); ?></option>
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
                    locationSelect.appendChild(option);
                });
            });
    }
});
</script> 