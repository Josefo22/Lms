<?php
$brands = $inventoryController->getBrands();
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
            <h2 class="mb-0">Gestión de Marcas</h2>
            <div>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBrandModal">
                    <i class="fas fa-plus"></i> Nueva Marca
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

        <!-- Tabla de marcas -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(count($brands) > 0): ?>
                                <?php foreach($brands as $brand): ?>
                                    <tr>
                                        <td><?php echo $brand['brand_id']; ?></td>
                                        <td><?php echo htmlspecialchars($brand['brand_name']); ?></td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-info edit-brand" 
                                                    data-id="<?php echo $brand['brand_id']; ?>"
                                                    data-name="<?php echo htmlspecialchars($brand['brand_name']); ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger delete-brand"
                                                    data-id="<?php echo $brand['brand_id']; ?>"
                                                    data-name="<?php echo htmlspecialchars($brand['brand_name']); ?>">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="text-center">No hay marcas disponibles</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Añadir Marca -->
<div class="modal fade" id="addBrandModal" tabindex="-1" aria-labelledby="addBrandModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addBrandModalLabel">Agregar Nueva Marca</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addBrandForm" action="index.php?page=manage_brands&action=create" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="brand_name" class="form-label">Nombre de la Marca *</label>
                        <input type="text" class="form-control" id="brand_name" name="brand_name" required>
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

<!-- Modal para Editar Marca -->
<div class="modal fade" id="editBrandModal" tabindex="-1" aria-labelledby="editBrandModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editBrandModalLabel">Editar Marca</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editBrandForm" action="index.php?page=manage_brands&action=update" method="POST">
                <input type="hidden" id="edit_brand_id" name="brand_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_brand_name" class="form-label">Nombre de la Marca *</label>
                        <input type="text" class="form-control" id="edit_brand_name" name="brand_name" required>
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

<!-- Modal para Eliminar Marca -->
<div class="modal fade" id="deleteBrandModal" tabindex="-1" aria-labelledby="deleteBrandModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteBrandModalLabel">Confirmar Eliminación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro de que desea eliminar la marca "<span id="delete_brand_name"></span>"?</p>
                <p class="text-danger">¡Esta acción no se puede deshacer! Si existen modelos asociados a esta marca, no podrá ser eliminada.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form id="deleteBrandForm" action="index.php?page=manage_brands&action=delete" method="POST">
                    <input type="hidden" id="delete_brand_id" name="brand_id">
                    <button type="submit" class="btn btn-danger">Eliminar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Script para editar marca
    document.querySelectorAll('.edit-brand').forEach(button => {
        button.addEventListener('click', function() {
            const brandId = this.getAttribute('data-id');
            const brandName = this.getAttribute('data-name');
            
            document.getElementById('edit_brand_id').value = brandId;
            document.getElementById('edit_brand_name').value = brandName;
            
            const editModal = new bootstrap.Modal(document.getElementById('editBrandModal'));
            editModal.show();
        });
    });
    
    // Script para eliminar marca
    document.querySelectorAll('.delete-brand').forEach(button => {
        button.addEventListener('click', function() {
            const brandId = this.getAttribute('data-id');
            const brandName = this.getAttribute('data-name');
            
            document.getElementById('delete_brand_id').value = brandId;
            document.getElementById('delete_brand_name').textContent = brandName;
            
            const deleteModal = new bootstrap.Modal(document.getElementById('deleteBrandModal'));
            deleteModal.show();
        });
    });
</script> 