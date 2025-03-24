<?php
// Título de la página
$page_title = "Gestión de Catálogos";

// Incluir el controlador
require_once __DIR__ . '/../controllers/InventoryController.php';
$inventoryController = new InventoryController();

// Obtener opciones para formularios
$formOptions = $inventoryController->getFormOptions();
?>

<div id="content" class="p-4 p-md-5 pt-5">
    <div class="container-fluid">
        <!-- Encabezado principal -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Gestión de Catálogos</h2>
            <a href="?page=inventory" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Volver al Inventario
            </a>
        </div>
        
        <!-- Alertas para mensajes -->
        <div id="alertContainer"></div>
        
        <div class="row">
            <!-- Gestión de Marcas -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded mb-4">
                    <div class="card-header bg-primary text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="bi bi-tag me-2"></i> Marcas</h5>
                            <button type="button" class="btn btn-sm btn-light" data-bs-toggle="modal" data-bs-target="#newBrandModal">
                                <i class="bi bi-plus-circle me-1"></i> Nueva Marca
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped" id="brandTable">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre</th>
                                        <th>Fecha Creación</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($formOptions['brands'])): ?>
                                        <?php foreach ($formOptions['brands'] as $brand): ?>
                                            <tr>
                                                <td><?php echo $brand['brand_id']; ?></td>
                                                <td><?php echo htmlspecialchars($brand['brand_name']); ?></td>
                                                <td><?php echo date('d/m/Y', strtotime($brand['created_at'])); ?></td>
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-danger delete-brand" data-id="<?php echo $brand['brand_id']; ?>" data-name="<?php echo htmlspecialchars($brand['brand_name']); ?>">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="text-center">No hay marcas disponibles</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Gestión de Categorías -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded mb-4">
                    <div class="card-header bg-success text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="bi bi-folder me-2"></i> Categorías</h5>
                            <button type="button" class="btn btn-sm btn-light" data-bs-toggle="modal" data-bs-target="#newCategoryModal">
                                <i class="bi bi-plus-circle me-1"></i> Nueva Categoría
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped" id="categoryTable">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre</th>
                                        <th>Descripción</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($formOptions['categories'])): ?>
                                        <?php foreach ($formOptions['categories'] as $category): ?>
                                            <tr>
                                                <td><?php echo $category['category_id']; ?></td>
                                                <td><?php echo htmlspecialchars($category['category_name']); ?></td>
                                                <td><?php echo htmlspecialchars($category['description'] ?? ''); ?></td>
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-danger delete-category" data-id="<?php echo $category['category_id']; ?>" data-name="<?php echo htmlspecialchars($category['category_name']); ?>">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="text-center">No hay categorías disponibles</td>
                                        </tr>
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
            <div class="modal-header bg-success text-white">
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
                <button type="button" class="btn btn-success" id="saveCategoryBtn">Guardar Categoría</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmación para eliminar -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteConfirmModalLabel">Confirmar Eliminación</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="deleteConfirmText">¿Está seguro que desea eliminar este elemento?</p>
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-2"></i> Esta acción no se puede deshacer. Solo se pueden eliminar elementos que no tengan relaciones con otros registros.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Eliminar</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Función para mostrar alertas
    function showAlert(message, type = 'error') {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show`;
        alertDiv.setAttribute('role', 'alert');
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        
        // Insertar al inicio del contenedor
        const container = document.getElementById('alertContainer');
        container.innerHTML = '';
        container.appendChild(alertDiv);
        
        // Auto-cerrar después de 5 segundos
        setTimeout(() => {
            alertDiv.remove();
        }, 5000);
    }
    
    // Guardar nueva marca
    const saveBrandBtn = document.getElementById('saveBrandBtn');
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
                        // Intentar parsear como JSON
                        if (!text) throw new Error('Respuesta vacía del servidor');
                        return JSON.parse(text);
                    } catch (e) {
                        console.error('Error al analizar JSON:', e);
                        console.error('Respuesta del servidor:', text);
                        throw new Error(`Error al procesar la respuesta: ${e.message}`);
                    }
                });
            })
            .then(data => {
                // Restaurar botón
                saveBrandBtn.disabled = false;
                saveBrandBtn.innerHTML = 'Guardar Marca';
                
                if (data.success) {
                    // Cerrar el modal
                    const brandModal = bootstrap.Modal.getInstance(document.getElementById('newBrandModal'));
                    brandModal.hide();
                    
                    // Mostrar mensaje de éxito
                    showAlert('Marca creada correctamente. La página se recargará para mostrar los cambios.', 'success');
                    
                    // Recargar página después de un breve retraso
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
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
    const saveCategoryBtn = document.getElementById('saveCategoryBtn');
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
                        // Intentar parsear como JSON
                        if (!text) throw new Error('Respuesta vacía del servidor');
                        return JSON.parse(text);
                    } catch (e) {
                        console.error('Error al analizar JSON:', e);
                        console.error('Respuesta del servidor:', text);
                        throw new Error(`Error al procesar la respuesta: ${e.message}`);
                    }
                });
            })
            .then(data => {
                // Restaurar botón
                saveCategoryBtn.disabled = false;
                saveCategoryBtn.innerHTML = 'Guardar Categoría';
                
                if (data.success) {
                    // Cerrar el modal
                    const categoryModal = bootstrap.Modal.getInstance(document.getElementById('newCategoryModal'));
                    categoryModal.hide();
                    
                    // Mostrar mensaje de éxito
                    showAlert('Categoría creada correctamente. La página se recargará para mostrar los cambios.', 'success');
                    
                    // Recargar página después de un breve retraso
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
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
    
    // Variables para almacenar información del elemento a eliminar
    let deleteType = '';
    let deleteId = 0;
    let deleteName = '';
    
    // Configurar modal de confirmación para eliminar marcas
    const deleteBrandButtons = document.querySelectorAll('.delete-brand');
    deleteBrandButtons.forEach(button => {
        button.addEventListener('click', function() {
            deleteType = 'brand';
            deleteId = this.getAttribute('data-id');
            deleteName = this.getAttribute('data-name');
            
            document.getElementById('deleteConfirmText').innerHTML = `¿Está seguro que desea eliminar la marca <strong>${deleteName}</strong>?`;
            
            const deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
            deleteModal.show();
        });
    });
    
    // Configurar modal de confirmación para eliminar categorías
    const deleteCategoryButtons = document.querySelectorAll('.delete-category');
    deleteCategoryButtons.forEach(button => {
        button.addEventListener('click', function() {
            deleteType = 'category';
            deleteId = this.getAttribute('data-id');
            deleteName = this.getAttribute('data-name');
            
            document.getElementById('deleteConfirmText').innerHTML = `¿Está seguro que desea eliminar la categoría <strong>${deleteName}</strong>?`;
            
            const deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
            deleteModal.show();
        });
    });
    
    // Manejar confirmación de eliminación
    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
    if (confirmDeleteBtn) {
        confirmDeleteBtn.addEventListener('click', function() {
            // Mostrar indicador de carga
            confirmDeleteBtn.disabled = true;
            confirmDeleteBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Eliminando...';
            
            // Preparar datos y acción según el tipo
            const formData = new FormData();
            if (deleteType === 'brand') {
                formData.append('action', 'delete_brand');
                formData.append('brand_id', deleteId);
            } else if (deleteType === 'category') {
                formData.append('action', 'delete_category');
                formData.append('category_id', deleteId);
            }
            
            // Enviar petición AJAX
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
                        // Intentar parsear como JSON
                        if (!text) throw new Error('Respuesta vacía del servidor');
                        return JSON.parse(text);
                    } catch (e) {
                        console.error('Error al analizar JSON:', e);
                        console.error('Respuesta del servidor:', text);
                        throw new Error(`Error al procesar la respuesta: ${e.message}`);
                    }
                });
            })
            .then(data => {
                // Cerrar el modal de confirmación
                const deleteModal = bootstrap.Modal.getInstance(document.getElementById('deleteConfirmModal'));
                deleteModal.hide();
                
                // Restaurar botón
                confirmDeleteBtn.disabled = false;
                confirmDeleteBtn.innerHTML = 'Eliminar';
                
                if (data.success) {
                    // Mostrar mensaje de éxito
                    showAlert(`${deleteType === 'brand' ? 'Marca' : 'Categoría'} eliminada correctamente. La página se recargará para mostrar los cambios.`, 'success');
                    
                    // Eliminar la fila de la tabla
                    if (deleteType === 'brand') {
                        const row = document.querySelector(`#brandTable tr[data-id="${deleteId}"]`);
                        if (row) row.remove();
                    } else if (deleteType === 'category') {
                        const row = document.querySelector(`#categoryTable tr[data-id="${deleteId}"]`);
                        if (row) row.remove();
                    }
                    
                    // Recargar página después de un breve retraso
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    showAlert(`Error al eliminar: ${data.message}`);
                }
            })
            .catch(error => {
                // Cerrar el modal de confirmación
                const deleteModal = bootstrap.Modal.getInstance(document.getElementById('deleteConfirmModal'));
                deleteModal.hide();
                
                // Restaurar botón
                confirmDeleteBtn.disabled = false;
                confirmDeleteBtn.innerHTML = 'Eliminar';
                
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