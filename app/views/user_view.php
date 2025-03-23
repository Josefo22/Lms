<?php
// Verificar si hay un ID de usuario
if(!isset($_GET['id'])) {
    $_SESSION['message'] = 'ID de usuario no especificado';
    $_SESSION['message_type'] = 'danger';
    header('Location: index.php?page=users');
    exit;
}

$user_id = $_GET['id'];

// Inicializar controlador
require_once 'app/controllers/UserController.php';
$userController = new UserController();
$data = $userController->getUserDetails($user_id);

// Verificar si se encontró el usuario
if(isset($data['error'])) {
    $_SESSION['message'] = $data['error'];
    $_SESSION['message_type'] = 'danger';
    header('Location: index.php?page=users');
    exit;
}

$user = $data['user'];
?>

<div class="container-fluid pt-4 px-4">
    <div class="row g-4">
        <div class="col-12">
            <div class="bg-light rounded h-100 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="mb-0">Detalles del Usuario</h5>
                    <div>
                        <a href="?page=users" class="btn btn-secondary">
                            <i class="bi bi-arrow-left me-2"></i>Volver
                        </a>
                        <a href="?page=users&action=edit&id=<?php echo $user['user_id']; ?>" class="btn btn-primary">
                            <i class="bi bi-pencil me-2"></i>Editar
                        </a>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="mb-0">Información Personal</h6>
                            </div>
                            <div class="card-body">
                                <table class="table table-borderless">
                                    <tr>
                                        <th style="width: 40%">Nombre completo:</th>
                                        <td><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                                    </tr>
                                    <tr>
                                        <th>ID de Empleado:</th>
                                        <td><?php echo htmlspecialchars($user['employee_id']); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Email:</th>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Teléfono:</th>
                                        <td><?php echo htmlspecialchars($user['phone'] ?? 'No especificado'); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Cargo:</th>
                                        <td><?php echo htmlspecialchars($user['job_title'] ?? 'No especificado'); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Departamento:</th>
                                        <td><?php echo htmlspecialchars($user['department'] ?? 'No especificado'); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Rol en sistema:</th>
                                        <td>
                                            <?php 
                                            $role_labels = [
                                                'admin' => '<span class="badge bg-danger">Administrador</span>',
                                                'manager' => '<span class="badge bg-primary">Gerente</span>',
                                                'support' => '<span class="badge bg-info">Soporte Técnico</span>',
                                                'user' => '<span class="badge bg-secondary">Usuario</span>'
                                            ];
                                            echo $role_labels[$user['role']] ?? $user['role']; 
                                            ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Estado:</th>
                                        <td>
                                            <?php 
                                            $status_labels = [
                                                'active' => '<span class="badge bg-success">Activo</span>',
                                                'inactive' => '<span class="badge bg-secondary">Inactivo</span>',
                                                'suspended' => '<span class="badge bg-warning">Suspendido</span>'
                                            ];
                                            echo $status_labels[$user['status']] ?? $user['status']; 
                                            ?>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="mb-0">Información de Trabajo</h6>
                            </div>
                            <div class="card-body">
                                <table class="table table-borderless">
                                    <tr>
                                        <th style="width: 40%">Cliente:</th>
                                        <td>
                                            <?php 
                                            if (!empty($user['client_id'])) {
                                                echo '<a href="?page=clients&action=view&id=' . $user['client_id'] . '">';
                                                echo htmlspecialchars($user['client_name'] ?? 'Cliente #' . $user['client_id']);
                                                echo '</a>';
                                            } else {
                                                echo 'No asignado';
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Ubicación:</th>
                                        <td>
                                            <?php 
                                            if (!empty($user['location_id'])) {
                                                echo htmlspecialchars($user['location_name'] ?? 'Ubicación #' . $user['location_id']);
                                            } else {
                                                echo 'No especificada';
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Trabajo Remoto:</th>
                                        <td><?php echo $user['is_remote'] ? 'Sí' : 'No'; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Fecha de Registro:</th>
                                        <td><?php echo date('d/m/Y H:i', strtotime($user['created_at'])); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Última Actualización:</th>
                                        <td><?php echo date('d/m/Y H:i', strtotime($user['updated_at'])); ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        
                        <!-- Equipos asignados -->
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">Equipos Asignados</h6>
                                <span id="equipmentCount" class="badge bg-primary"></span>
                            </div>
                            <div class="card-body">
                                <div id="userEquipment" class="table-responsive">
                                    <p class="text-center">Cargando equipos asignados...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Opciones adicionales -->
                <div class="mt-4">
                    <button type="button" class="btn btn-warning me-2" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                        <i class="bi bi-key me-2"></i>Cambiar Contraseña
                    </button>
                    <button type="button" class="btn btn-danger" onclick="confirmDelete(<?php echo $user['user_id']; ?>, '<?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>')">
                        <i class="bi bi-trash me-2"></i>Eliminar Usuario
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Cambiar Contraseña -->
<div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="changePasswordModalLabel">Cambiar Contraseña</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="?page=users&action=change_password" method="POST">
                <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Contraseña Actual</label>
                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                    </div>
                    <div class="mb-3">
                        <label for="new_password" class="form-label">Nueva Contraseña</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" required>
                    </div>
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirmar Nueva Contraseña</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Cargar equipos asignados al usuario
document.addEventListener('DOMContentLoaded', function() {
    const userId = <?php echo $user['user_id']; ?>;
    const userEquipmentContainer = document.getElementById('userEquipment');
    const equipmentCountBadge = document.getElementById('equipmentCount');
    
    fetch('index.php?page=api&action=user_equipment&user_id=' + userId)
        .then(response => response.json())
        .then(data => {
            equipmentCountBadge.textContent = data.length;
            
            if (data.length === 0) {
                userEquipmentContainer.innerHTML = '<p class="text-center">No hay equipos asignados a este usuario</p>';
                return;
            }
            
            let html = '<table class="table table-sm table-hover">';
            html += '<thead><tr><th>Asset Tag</th><th>Tipo</th><th>Marca / Modelo</th><th>Acciones</th></tr></thead>';
            html += '<tbody>';
            
            data.forEach(item => {
                html += '<tr>';
                html += '<td>' + (item.asset_tag || 'N/A') + '</td>';
                html += '<td>' + (item.category_name || 'N/A') + '</td>';
                html += '<td>' + (item.brand_name || 'N/A') + ' ' + (item.model_name || 'N/A') + '</td>';
                html += '<td><a href="?page=inventory&action=view&id=' + item.hardware_id + '" class="btn btn-sm btn-info"><i class="bi bi-eye"></i></a></td>';
                html += '</tr>';
            });
            
            html += '</tbody></table>';
            userEquipmentContainer.innerHTML = html;
        })
        .catch(error => {
            console.error('Error cargando equipos:', error);
            userEquipmentContainer.innerHTML = '<p class="text-center text-danger">Error al cargar equipos</p>';
        });
});

// Confirmación de eliminación de usuario
function confirmDelete(userId, userName) {
    if (confirm('¿Estás seguro de que deseas eliminar al usuario ' + userName + '?')) {
        window.location.href = '?page=users&action=delete&id=' + userId;
    }
}

// Validación de contraseñas
document.addEventListener('DOMContentLoaded', function() {
    const passwordForm = document.querySelector('#changePasswordModal form');
    const newPassword = document.getElementById('new_password');
    const confirmPassword = document.getElementById('confirm_password');
    
    passwordForm.addEventListener('submit', function(e) {
        if (newPassword.value !== confirmPassword.value) {
            e.preventDefault();
            alert('Las contraseñas no coinciden');
            confirmPassword.focus();
        }
    });
});
</script> 