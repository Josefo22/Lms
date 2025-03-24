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
$formOptions = $userController->getFormOptions();

// Verificar si se encontró el usuario
if(isset($data['error'])) {
    $_SESSION['message'] = $data['error'];
    $_SESSION['message_type'] = 'danger';
    header('Location: index.php?page=users');
    exit;
}

$user = $data['user'];
?>

<div id="content">
    <div class="container-fluid pt-4 px-4 content-with-sidebar">
        <div class="row g-4">
            <div class="col-12">
                <div class="bg-light rounded h-100 p-4 user-card">
                    <div class="d-flex justify-content-between align-items-center mb-4 user-header">
                        <h5 class="mb-0"><i class="bi bi-pencil-square me-2"></i>Editar Usuario</h5>
                        <a href="?page=users" class="btn btn-secondary">
                                <i class="bi bi-arrow-left me-2"></i>Volver
                            </a>
                    </div>
                    
                    <!-- Formulario de edición -->
                    <form action="?page=users&action=update" method="POST">
                        <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                        
                        <div class="row g-3">
                            <!-- Información Personal -->
                            <div class="col-12">
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h6 class="mb-0">Información Personal</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label for="first_name" class="form-label">Nombre *</label>
                                                <input type="text" class="form-control" id="first_name" name="first_name" 
                                                       value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="last_name" class="form-label">Apellido *</label>
                                                <input type="text" class="form-control" id="last_name" name="last_name" 
                                                       value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="employee_id" class="form-label">ID de Empleado *</label>
                                                <input type="text" class="form-control" id="employee_id" name="employee_id" 
                                                       value="<?php echo htmlspecialchars($user['employee_id']); ?>" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="email" class="form-label">Email *</label>
                                                <input type="email" class="form-control" id="email" name="email" 
                                                       value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="phone" class="form-label">Teléfono</label>
                                                <input type="text" class="form-control" id="phone" name="phone" 
                                                       value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                                            </div>
                                            <div class="col-md-6">
                                                <label for="username" class="form-label">Nombre de Usuario</label>
                                                <input type="text" class="form-control" id="username" name="username" 
                                                       value="<?php echo htmlspecialchars($user['username'] ?? ''); ?>">
                                                <div class="form-text">Opcional. Si no se especifica, se usará el email.</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Información Laboral -->
                            <div class="col-md-6">
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h6 class="mb-0">Información Laboral</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="job_title" class="form-label">Cargo</label>
                                            <input type="text" class="form-control" id="job_title" name="job_title" 
                                                   value="<?php echo htmlspecialchars($user['job_title'] ?? ''); ?>">
                                        </div>
                                        <div class="mb-3">
                                            <label for="department" class="form-label">Departamento</label>
                                            <select class="form-select" id="department" name="department">
                                                <option value="">Seleccionar departamento</option>
                                                <?php foreach($formOptions['departments'] as $dept): ?>
                                                <option value="<?php echo $dept; ?>" <?php echo ($user['department'] ?? '') === $dept ? 'selected' : ''; ?>>
                                                    <?php echo $dept; ?>
                                                </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="client_id" class="form-label">Cliente</label>
                                            <select class="form-select" id="client_id" name="client_id" onchange="loadLocations(this.value)">
                                                <option value="">Sin asignar</option>
                                                <?php foreach($formOptions['clients'] as $client): ?>
                                                <option value="<?php echo $client['client_id']; ?>" <?php echo ($user['client_id'] ?? '') == $client['client_id'] ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($client['client_name']); ?>
                                                </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="location_id" class="form-label">Ubicación</label>
                                            <select class="form-select" id="location_id" name="location_id" <?php echo empty($user['client_id']) ? 'disabled' : ''; ?>>
                                                <option value="">Seleccione ubicación</option>
                                                <?php if (!empty($user['client_id']) && !empty($formOptions['locations'])): ?>
                                                    <?php foreach($formOptions['locations'] as $location): ?>
                                                        <?php if ($location['client_id'] == $user['client_id']): ?>
                                                        <option value="<?php echo $location['location_id']; ?>" <?php echo ($user['location_id'] ?? '') == $location['location_id'] ? 'selected' : ''; ?>>
                                                            <?php echo htmlspecialchars($location['location_name']); ?>
                                                        </option>
                                                        <?php endif; ?>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </select>
                                        </div>
                                        <div class="form-check mb-3">
                                            <input class="form-check-input" type="checkbox" id="is_remote" name="is_remote" value="1" 
                                                   <?php echo ($user['is_remote'] ?? 0) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="is_remote">
                                                Trabaja remotamente
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Configuración del Sistema -->
                            <div class="col-md-6">
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h6 class="mb-0">Configuración del Sistema</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="role" class="form-label">Rol *</label>
                                            <select class="form-select" id="role" name="role" required>
                                                <?php foreach($formOptions['roles'] as $role): ?>
                                                <option value="<?php echo $role['id']; ?>" <?php echo ($user['role'] ?? 'user') === $role['id'] ? 'selected' : ''; ?>>
                                                    <?php echo $role['name']; ?>
                                                </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="status" class="form-label">Estado *</label>
                                            <select class="form-select" id="status" name="status" required>
                                                <?php foreach($formOptions['statuses'] as $status): ?>
                                                <option value="<?php echo $status['id']; ?>" <?php echo ($user['status'] ?? 'active') === $status['id'] ? 'selected' : ''; ?>>
                                                    <?php echo $status['name']; ?>
                                                </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="password" class="form-label">Nueva Contraseña</label>
                                            <input type="password" class="form-control" id="password" name="password">
                                            <div class="form-text">Dejar en blanco para mantener la contraseña actual.</div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="confirm_password" class="form-label">Confirmar Nueva Contraseña</label>
                                            <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Botones de acción -->
                            <div class="col-12 mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save me-2"></i>Guardar Cambios
                                </button>
                                <a href="?page=users&action=view&id=<?php echo $user['user_id']; ?>" class="btn btn-secondary ms-2">
                                    Cancelar
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Cargar ubicaciones cuando cambia el cliente
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
                
                // Seleccionar la ubicación actual si coincide
                if (location.location_id == <?php echo json_encode($user['location_id'] ?? ''); ?>) {
                    option.selected = true;
                }
                
                locationSelect.appendChild(option);
            });
        })
        .catch(error => {
            console.error('Error cargando ubicaciones:', error);
            locationSelect.innerHTML = '<option value="">Error al cargar ubicaciones</option>';
        });
}

// Validación de contraseñas
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirm_password');
    
    form.addEventListener('submit', function(e) {
        if (password.value !== '' && password.value !== confirmPassword.value) {
            e.preventDefault();
            alert('Las contraseñas no coinciden');
            confirmPassword.focus();
        }
    });
});

// Inicializar el selector de ubicaciones si es necesario
document.addEventListener('DOMContentLoaded', function() {
    const clientId = document.getElementById('client_id').value;
    if (clientId) {
        // Si ya hay un cliente seleccionado, asegurarse de que las ubicaciones estén cargadas
        const locationSelect = document.getElementById('location_id');
        if (locationSelect.options.length <= 1) {
            loadLocations(clientId);
        }
    }
});
</script>
</div> <!-- Fin del div id="content" --> 