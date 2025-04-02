<?php
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
            <h2 class="mb-0">Gestión de Categorías</h2>
            <div>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                    <i class="fas fa-plus"></i> Nueva Categoría
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

        <!-- Tabla de categorías -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Descripción</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(count($categories) > 0): ?>
                                <?php foreach($categories as $category): ?>
                                    <tr>
                                        <td><?php echo $category['category_id']; ?></td>
                                        <td><?php echo htmlspecialchars($category['category_name']); ?></td>
                                        <td><?php echo htmlspecialchars($category['description'] ?? ''); ?></td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-info edit-category" 
                                                    data-id="<?php echo $category['category_id']; ?>"
                                                    data-name="<?php echo htmlspecialchars($category['category_name']); ?>"
                                                    data-description="<?php echo htmlspecialchars($category['description'] ?? ''); ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger delete-category"
                                                    data-id="<?php echo $category['category_id']; ?>"
                                                    data-name="<?php echo htmlspecialchars($category['category_name']); ?>">
                                                <i class="fas fa-trash"></i>
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

<!-- Modal para Añadir Categoría -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addCategoryModalLabel">Agregar Nueva Categoría</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addCategoryForm" action="index.php?page=manage_categories&action=create" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="category_name" class="form-label">Nombre de la Categoría *</label>
                        <input type="text" class="form-control" id="category_name" name="category_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Descripción</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
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

<!-- Modal para Editar Categoría -->
<div class="modal fade" id="editCategoryModal" tabindex="-1" aria-labelledby="editCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editCategoryModalLabel">Editar Categoría</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editCategoryForm" action="index.php?page=manage_categories&action=update" method="POST">
                <input type="hidden" id="edit_category_id" name="category_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_category_name" class="form-label">Nombre de la Categoría *</label>
                        <input type="text" class="form-control" id="edit_category_name" name="category_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_description" class="form-label">Descripción</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
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

<!-- Modal para Eliminar Categoría -->
<div class="modal fade" id="deleteCategoryModal" tabindex="-1" aria-labelledby="deleteCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteCategoryModalLabel">Confirmar Eliminación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro de que desea eliminar la categoría "<span id="delete_category_name"></span>"?</p>
                <p class="text-danger">¡Esta acción no se puede deshacer! Si existen modelos asociados a esta categoría, no podrá ser eliminada.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form id="deleteCategoryForm" action="index.php?page=manage_categories&action=delete" method="POST">
                    <input type="hidden" id="delete_category_id" name="category_id">
                    <button type="submit" class="btn btn-danger">Eliminar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Script para editar categoría
    document.querySelectorAll('.edit-category').forEach(button => {
        button.addEventListener('click', function() {
            const categoryId = this.getAttribute('data-id');
            const categoryName = this.getAttribute('data-name');
            const description = this.getAttribute('data-description');
            
            document.getElementById('edit_category_id').value = categoryId;
            document.getElementById('edit_category_name').value = categoryName;
            document.getElementById('edit_description').value = description;
            
            const editModal = new bootstrap.Modal(document.getElementById('editCategoryModal'));
            editModal.show();
        });
    });
    
    // Script para eliminar categoría
    document.querySelectorAll('.delete-category').forEach(button => {
        button.addEventListener('click', function() {
            const categoryId = this.getAttribute('data-id');
            const categoryName = this.getAttribute('data-name');
            
            document.getElementById('delete_category_id').value = categoryId;
            document.getElementById('delete_category_name').textContent = categoryName;
            
            const deleteModal = new bootstrap.Modal(document.getElementById('deleteCategoryModal'));
            deleteModal.show();
        });
    });
</script> 