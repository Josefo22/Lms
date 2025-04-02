<?php
$models = $inventoryController->getModels();
$brands = $inventoryController->getBrands();
$categories = $inventoryController->getCategories();
?>
<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- FontAwesome -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
<!-- Bootstrap JS Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<div id="content" class="p-4 p-md-5 pt-5">
    <div class="container-fluid">
        <!-- Encabezado principal y botones de acción -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Gestión de Modelos</h2>
            <div>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModelModal">
                    <i class="fas fa-plus"></i> Nuevo Modelo
                </button>
                <a href="index.php?page=add_hardware" class="btn btn-secondary ms-2">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </div>
        </div>

        <!-- Mensajes de alerta -->
        <?php if(isset($_SESSION['message'])): ?>
            <div class="alert alert-<?php echo $_SESSION['message_type']; ?> alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['message']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['message']); unset($_SESSION['message_type']); ?>
        <?php endif; ?>

        <!-- Tabla de modelos -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Marca</th>
                                <th>Categoría</th>
                                <th>Especificaciones</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(count($models) > 0): ?>
                                <?php foreach($models as $model): ?>
                                    <tr>
                                        <td><?php echo $model['model_id']; ?></td>
                                        <td><?php echo htmlspecialchars($model['model_name']); ?></td>
                                        <td><?php echo htmlspecialchars($model['brand_name']); ?></td>
                                        <td><?php echo htmlspecialchars($model['category_name']); ?></td>
                                        <td><?php echo htmlspecialchars($model['specifications'] ?? ''); ?></td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-info edit-model" 
                                                    data-id="<?php echo $model['model_id']; ?>"
                                                    data-name="<?php echo htmlspecialchars($model['model_name']); ?>"
                                                    data-brand="<?php echo $model['brand_id']; ?>"
                                                    data-category="<?php echo $model['category_id']; ?>"
                                                    data-specs="<?php echo htmlspecialchars($model['specifications'] ?? ''); ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger delete-model"
                                                    data-id="<?php echo $model['model_id']; ?>"
                                                    data-name="<?php echo htmlspecialchars($model['model_name']); ?>">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center">No hay modelos disponibles</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Añadir Modelo -->
<div class="modal fade" id="addModelModal" tabindex="-1" aria-labelledby="addModelModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addModelModalLabel">Agregar Nuevo Modelo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addModelForm" action="index.php?page=manage_models&action=create" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="model_name" class="form-label">Nombre del Modelo *</label>
                        <input type="text" class="form-control" id="model_name" name="model_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="brand_id" class="form-label">Marca *</label>
                        <select class="form-select" id="brand_id" name="brand_id" required>
                            <option value="">Seleccionar Marca</option>
                            <?php foreach($brands as $brand): ?>
                                <option value="<?php echo $brand['brand_id']; ?>"><?php echo htmlspecialchars($brand['brand_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="category_id" class="form-label">Categoría *</label>
                        <select class="form-select" id="category_id" name="category_id" required>
                            <option value="">Seleccionar Categoría</option>
                            <?php foreach($categories as $category): ?>
                                <option value="<?php echo $category['category_id']; ?>"><?php echo htmlspecialchars($category['category_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="specifications" class="form-label">Especificaciones</label>
                        <textarea class="form-control" id="specifications" name="specifications" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para Editar Modelo -->
<div class="modal fade" id="editModelModal" tabindex="-1" aria-labelledby="editModelModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModelModalLabel">Editar Modelo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editModelForm" action="index.php?page=manage_models&action=update" method="POST">
                <input type="hidden" id="edit_model_id" name="model_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_model_name" class="form-label">Nombre del Modelo *</label>
                        <input type="text" class="form-control" id="edit_model_name" name="model_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_brand_id" class="form-label">Marca *</label>
                        <select class="form-select" id="edit_brand_id" name="brand_id" required>
                            <option value="">Seleccionar Marca</option>
                            <?php foreach($brands as $brand): ?>
                                <option value="<?php echo $brand['brand_id']; ?>"><?php echo htmlspecialchars($brand['brand_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_category_id" class="form-label">Categoría *</label>
                        <select class="form-select" id="edit_category_id" name="category_id" required>
                            <option value="">Seleccionar Categoría</option>
                            <?php foreach($categories as $category): ?>
                                <option value="<?php echo $category['category_id']; ?>"><?php echo htmlspecialchars($category['category_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_specifications" class="form-label">Especificaciones</label>
                        <textarea class="form-control" id="edit_specifications" name="specifications" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Actualizar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para Eliminar Modelo -->
<div class="modal fade" id="deleteModelModal" tabindex="-1" aria-labelledby="deleteModelModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModelModalLabel">Confirmar Eliminación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro de que desea eliminar el modelo "<span id="delete_model_name"></span>"?</p>
                <p class="text-danger">¡Esta acción no se puede deshacer! Si existen equipos asociados a este modelo, no podrá ser eliminado.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form id="deleteModelForm" action="index.php?page=manage_models&action=delete" method="POST">
                    <input type="hidden" id="delete_model_id" name="model_id">
                    <button type="submit" class="btn btn-danger">Eliminar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Script para editar modelo
    document.querySelectorAll('.edit-model').forEach(button => {
        button.addEventListener('click', function() {
            const modelId = this.getAttribute('data-id');
            const modelName = this.getAttribute('data-name');
            const brandId = this.getAttribute('data-brand');
            const categoryId = this.getAttribute('data-category');
            const specifications = this.getAttribute('data-specs');
            
            document.getElementById('edit_model_id').value = modelId;
            document.getElementById('edit_model_name').value = modelName;
            document.getElementById('edit_brand_id').value = brandId;
            document.getElementById('edit_category_id').value = categoryId;
            document.getElementById('edit_specifications').value = specifications;
            
            const editModal = new bootstrap.Modal(document.getElementById('editModelModal'));
            editModal.show();
        });
    });
    
    // Script para eliminar modelo
    document.querySelectorAll('.delete-model').forEach(button => {
        button.addEventListener('click', function() {
            const modelId = this.getAttribute('data-id');
            const modelName = this.getAttribute('data-name');
            
            document.getElementById('delete_model_id').value = modelId;
            document.getElementById('delete_model_name').textContent = modelName;
            
            const deleteModal = new bootstrap.Modal(document.getElementById('deleteModelModal'));
            deleteModal.show();
        });
    });
</script> 