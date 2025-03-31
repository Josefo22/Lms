<?php
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

// Obtener estadísticas de usuarios
$userStats = [
    'total' => count($data['users']),
    'active' => count(array_filter($data['users'], function($user) { return $user['status'] === 'active'; })),
    'inactive' => count(array_filter($data['users'], function($user) { return $user['status'] === 'inactive'; })),
    'suspended' => count(array_filter($data['users'], function($user) { return $user['status'] === 'suspended'; }))
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
                                <h2 class="card-title mb-0"><?php echo $userStats['total']; ?></h2>
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
                                <h6 class="card-subtitle mb-2">Usuarios Activos</h6>
                                <h2 class="card-title mb-0"><?php echo $userStats['active']; ?></h2>
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
                                <h6 class="card-subtitle mb-2">Usuarios Suspendidos</h6>
                                <h2 class="card-title mb-0"><?php echo $userStats['suspended']; ?></h2>
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
                                <h6 class="card-subtitle mb-2">Usuarios Inactivos</h6>
                                <h2 class="card-title mb-0"><?php echo $userStats['inactive']; ?></h2>
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
                                <form action="" method="GET" class="d-flex">
                                    <input type="hidden" name="page" value="users">
                                    <input type="text" class="form-control me-2" placeholder="Buscar por nombre, email o ID..." name="search" value="<?php echo htmlspecialchars($search ?? ''); ?>">
                                    <button class="btn btn-primary" type="submit">
                                        <i class="bi bi-search me-1"></i> Buscar
                                    </button>
                                    <?php if($search || $client_filter): ?>
                                    <a href="?page=users" class="btn btn-outline-secondary ms-2">
                                        <i class="bi bi-x-circle me-1"></i> Limpiar
                                    </a>
                                    <?php endif; ?>
                                </form>
                            </div>
                            <div class="col-md-4">
                                <form action="" method="GET">
                                    <input type="hidden" name="page" value="users">
                                    <select class="form-select" name="client_id" onchange="this.form.submit()">
                                        <option value="">Todos los clientes</option>
                                        <?php foreach($data['clients'] as $client): ?>
                                        <option value="<?php echo $client['client_id']; ?>" <?php echo $client_filter == $client['client_id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($client['client_name']); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de usuarios -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Nombre</th>
                                <th scope="col">Email</th>
                                <th scope="col">Cliente</th>
                                <th scope="col">Cargo</th>
                                <th scope="col">Estado</th>
                                <th scope="col">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($data['users'])): ?>
                            <tr>
                                <td colspan="7" class="text-center p-4">No se encontraron usuarios</td>
                            </tr>
                            <?php else: ?>
                                <?php foreach($data['users'] as $user): ?>
                                <tr>
                                    <td><?php echo $user['employee_id']; ?></td>
                                    <td><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td><?php echo htmlspecialchars($user['client_name'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($user['job_title'] ?? 'N/A'); ?></td>
                                    <td>
                                        <?php 
                                        $badgeClass = 'bg-secondary';
                                        $statusText = 'Desconocido';
                                        
                                        switch($user['status']) {
                                            case 'active':
                                                $badgeClass = 'bg-success';
                                                $statusText = 'Activo';
                                                break;
                                            case 'inactive':
                                                $badgeClass = 'bg-danger';
                                                $statusText = 'Inactivo';
                                                break;
                                            case 'suspended':
                                                $badgeClass = 'bg-warning';
                                                $statusText = 'Suspendido';
                                                break;
                                        }
                                        ?>
                                        <span class="badge <?php echo $badgeClass; ?>"><?php echo $statusText; ?></span>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="?page=users&action=view&id=<?php echo $user['user_id']; ?>" class="btn btn-sm btn-info text-white">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="?page=users&action=edit&id=<?php echo $user['user_id']; ?>" class="btn btn-sm btn-primary">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-danger" 
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
                            <input type="text" class="form-control" id="phone" name="phone">
                        </div>
                        <div class="col-md-6">
                            <label for="client_id" class="form-label">Cliente</label>
                            <select class="form-select" id="client_id" name="client_id" onchange="loadLocations(this.value)">
                                <option value="">Sin asignar</option>
                                <?php foreach($formOptions['clients'] as $client): ?>
                                <option value="<?php echo $client['client_id']; ?>">
                                    <?php echo htmlspecialchars($client['client_name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="location_id" class="form-label">Ubicación</label>
                            <select class="form-select" id="location_id" name="location_id" disabled>
                                <option value="">Seleccione un cliente primero</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="job_title" class="form-label">Cargo</label>
                            <input type="text" class="form-control" id="job_title" name="job_title">
                        </div>
                        <div class="col-md-6">
                            <label for="department" class="form-label">Departamento</label>
                            <select class="form-select" id="department" name="department">
                                <option value="">Seleccionar departamento</option>
                                <?php foreach($formOptions['departments'] as $dept): ?>
                                <option value="<?php echo $dept; ?>"><?php echo $dept; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="role" class="form-label">Rol *</label>
                            <select class="form-select" id="role" name="role" required>
                                <?php foreach($formOptions['roles'] as $role): ?>
                                <option value="<?php echo $role['id']; ?>" <?php echo $role['id'] === 'user' ? 'selected' : ''; ?>>
                                    <?php echo $role['name']; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="status" class="form-label">Estado *</label>
                            <select class="form-select" id="status" name="status" required>
                                <?php foreach($formOptions['statuses'] as $status): ?>
                                <option value="<?php echo $status['id']; ?>" <?php echo $status['id'] === 'active' ? 'selected' : ''; ?>>
                                    <?php echo $status['name']; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_remote" name="is_remote" value="1">
                                <label class="form-check-label" for="is_remote">
                                    Trabaja remotamente
                                </label>
                            </div>
                        </div>
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

<!-- Script para confirmación de eliminación -->
<script>
function confirmDelete(userId, userName) {
    if (confirm('¿Estás seguro de que deseas eliminar al usuario ' + userName + '?')) {
        window.location.href = '?page=users&action=delete&id=' + userId;
    }
}

function loadLocations(clientId) {
    const locationSelect = document.getElementById('location_id');
    
    // Desactivar si no hay cliente seleccionado
    if (!clientId) {
        locationSelect.disabled = true;
        locationSelect.innerHTML = '<option value="">Seleccione un cliente primero</option>';
        return;
    }
    
    // Activar y cargar ubicaciones
    locationSelect.disabled = false;
    locationSelect.innerHTML = '<option value="">Cargando...</option>';
    
    // Hacer petición AJAX para obtener ubicaciones
    fetch('index.php?page=api&action=locations&client_id=' + clientId)
        .then(response => response.json())
        .then(data => {
            locationSelect.innerHTML = '<option value="">Seleccione ubicación</option>';
            data.forEach(location => {
                const option = document.createElement('option');
                option.value = location.location_id;
                option.textContent = location.location_name;
                locationSelect.appendChild(option);
            });
        })
        .catch(error => {
            console.error('Error cargando ubicaciones:', error);
            locationSelect.innerHTML = '<option value="">Error al cargar ubicaciones</option>';
        });
}
</script> 