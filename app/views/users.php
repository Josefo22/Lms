<?php
// Título de la página
$page_title = "Usuarios";

// Verificar si hay un mensaje de éxito o error
$message = $_SESSION['message'] ?? null;
$message_type = $_SESSION['message_type'] ?? 'info';
// Limpiar mensaje de sesión
unset($_SESSION['message']);
unset($_SESSION['message_type']);

// Obtener filtros
$client_filter = $_GET['client_id'] ?? null;
$search = $_GET['search'] ?? null;

// Inicializar controlador de usuarios
require_once 'app/controllers/UserController.php';
$userController = new UserController();
$data = $userController->getUsers($client_filter, $search);
$formOptions = $userController->getFormOptions();

// Contar usuarios por estado
$statusCounts = [
    'total' => count($data['users']),
    'Active' => count(array_filter($data['users'], function($user) { return $user['status'] == 'Active'; })),
    'Inactive' => count(array_filter($data['users'], function($user) { return $user['status'] == 'Inactive'; })),
    'On Leave' => count(array_filter($data['users'], function($user) { return $user['status'] == 'On Leave'; }))
];
?>

<div id="content" class="p-4 p-md-5 pt-5">
    <div class="container-fluid">
        <!-- Encabezado principal y botón de acción -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Gestión de Usuarios</h2>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                <i class="bi bi-person-plus me-2"></i>Nuevo Usuario
            </button>
        </div>

        <!-- Alertas y mensajes -->
        <?php if($message): ?>
        <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
            <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>

        <!-- Tarjetas de estadísticas -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card h-100 border-0 shadow-sm rounded">
                    <div class="card-body bg-primary text-white rounded">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-subtitle mb-2">Total Usuarios</h6>
                                <h2 class="card-title mb-0"><?php echo $statusCounts['total']; ?></h2>
                            </div>
                            <i class="bi bi-people fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-3">
                <div class="card h-100 border-0 shadow-sm rounded">
                    <div class="card-body bg-success text-white rounded">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-subtitle mb-2">Activos</h6>
                                <h2 class="card-title mb-0"><?php echo $statusCounts['Active']; ?></h2>
                            </div>
                            <i class="bi bi-person-check fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-3">
                <div class="card h-100 border-0 shadow-sm rounded">
                    <div class="card-body bg-warning text-dark rounded">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-subtitle mb-2">De Permiso</h6>
                                <h2 class="card-title mb-0"><?php echo $statusCounts['On Leave']; ?></h2>
                            </div>
                            <i class="bi bi-person-dash fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-3">
                <div class="card h-100 border-0 shadow-sm rounded">
                    <div class="card-body bg-danger text-white rounded">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-subtitle mb-2">Inactivos</h6>
                                <h2 class="card-title mb-0"><?php echo $statusCounts['Inactive']; ?></h2>
                            </div>
                            <i class="bi bi-person-x fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card border-0 shadow-sm rounded">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <label for="searchInput" class="form-label">Buscar</label>
                                <input type="text" class="form-control" id="searchInput" placeholder="Buscar por nombre, email o ID..." value="<?php echo htmlspecialchars($search ?? ''); ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="clientFilter" class="form-label">Cliente</label>
                                <select class="form-select" id="clientFilter">
                                    <option value="">Todos los clientes</option>
                                    <?php foreach($data['clients'] as $client): ?>
                                        <?php $selected = ($client_filter == $client['client_id']) ? 'selected' : ''; ?>
                                        <option value="<?php echo $client['client_id']; ?>" <?php echo $selected; ?>>
                                            <?php echo htmlspecialchars($client['client_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-end mt-3">
                            <button id="clearBtn" class="btn btn-outline-secondary me-2">
                                <i class="bi bi-x-circle me-1"></i> Limpiar
                            </button>
                            <button id="filterBtn" class="btn btn-primary">
                                <i class="bi bi-filter me-1"></i> Filtrar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de usuarios -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Nombre</th>
                                <th>Email</th>
                                <th>Cliente</th>
                                <th>Cargo</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($data['users'])): ?>
                                <tr>
                                    <td colspan="7" class="text-center p-4">No se encontraron usuarios con los filtros seleccionados</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach($data['users'] as $user): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($user['user_id']); ?></td>
                                        <td><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td><?php echo htmlspecialchars($user['client_name'] ?? 'No asignado'); ?></td>
                                        <td><?php echo htmlspecialchars($user['job_title'] ?? 'No especificado'); ?></td>
                                        <td>
                                            <?php 
                                            $badgeClass = '';
                                            $statusText = '';
                                            switch($user['status']) {
                                                case 'Active':
                                                    $badgeClass = 'bg-success';
                                                    $statusText = 'Activo';
                                                    break;
                                                case 'Inactive':
                                                    $badgeClass = 'bg-danger';
                                                    $statusText = 'Inactivo';
                                                    break;
                                                case 'On Leave':
                                                    $badgeClass = 'bg-warning';
                                                    $statusText = 'De Permiso';
                                                    break;
                                                default:
                                                    $badgeClass = 'bg-secondary';
                                                    $statusText = $user['status'];
                                            }
                                            ?>
                                            <span class="badge <?php echo $badgeClass; ?>"><?php echo $statusText; ?></span>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="?page=users&action=view&id=<?php echo $user['user_id']; ?>" 
                                                   class="btn btn-sm btn-info" title="Ver detalles">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="?page=users&action=edit&id=<?php echo $user['user_id']; ?>" 
                                                   class="btn btn-sm btn-primary" title="Editar">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-danger" title="Eliminar"
                                                        onclick="confirmDelete(<?php echo $user['user_id']; ?>, '<?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>')">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Agregar Usuario -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addUserModalLabel">Agregar Nuevo Usuario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="?page=users&action=add" method="POST">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="first_name" class="form-label">Nombre *</label>
                            <input type="text" class="form-control" id="first_name" name="first_name" required>
                        </div>
                        <div class="col-md-6">
                            <label for="last_name" class="form-label">Apellido *</label>
                            <input type="text" class="form-control" id="last_name" name="last_name" required>
                        </div>
                        <div class="col-md-6">
                            <label for="employee_id" class="form-label">ID de Empleado *</label>
                            <input type="text" class="form-control" id="employee_id" name="employee_id" required>
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email *</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="col-md-6">
                            <label for="password" class="form-label">Contraseña *</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="col-md-6">
                            <label for="phone" class="form-label">Teléfono</label>
                            <input type="tel" class="form-control" id="phone" name="phone">
                        </div>
                        <div class="col-md-6">
                            <label for="job_title" class="form-label">Cargo</label>
                            <input type="text" class="form-control" id="job_title" name="job_title">
                        </div>
                        <div class="col-md-6">
                            <label for="department" class="form-label">Departamento</label>
                            <input type="text" class="form-control" id="department" name="department">
                        </div>
                        <div class="col-md-6">
                            <label for="status" class="form-label">Estado *</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="Active">Activo</option>
                                <option value="Inactive">Inactivo</option>
                                <option value="On Leave">De permiso</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Usuario</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Eventos para filtros
    document.getElementById('filterBtn').addEventListener('click', function() {
        applyFilters();
    });
    
    document.getElementById('clearBtn').addEventListener('click', function() {
        clearFilters();
    });
    
    // Función para aplicar filtros
    function applyFilters() {
        const search = document.getElementById('searchInput').value;
        const client = document.getElementById('clientFilter').value;
        
        let url = 'index.php?page=users';
        
        if(search) url += '&search=' + encodeURIComponent(search);
        if(client) url += '&client_id=' + client;
        
        window.location.href = url;
    }
    
    // Función para limpiar filtros
    function clearFilters() {
        window.location.href = 'index.php?page=users';
    }
    
    // Función para confirmar eliminación
    window.confirmDelete = function(userId, userName) {
        if (confirm('¿Estás seguro de que deseas eliminar al usuario ' + userName + '?')) {
            window.location.href = '?page=users&action=delete&id=' + userId;
        }
    }
});
</script> 