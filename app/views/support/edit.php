<?php
// No iniciar sesión aquí, ya está iniciada en index.php principal
//session_start();

// Usar rutas absolutas desde la raíz del proyecto
require_once 'config/database.php';
require_once 'app/models/SupportRequest.php';
require_once 'app/models/User.php';
require_once 'app/models/Hardware.php';

// Verificar si el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Verificar si se proporcionó un ID
if (!isset($_GET['id'])) {
    header('Location: index.php?page=support');
    exit();
}

$database = new Database();
$db = $database->connect();
$support = new SupportRequest($db);
$user = new User($db);
$hardware = new Hardware($db);

// Obtener detalles de la solicitud
$support->request_id = $_GET['id'];
if (!$support->readOne()) {
    // Si no se encuentra la solicitud, redirigir
    header('Location: index.php?page=support');
    exit();
}

// Verificar si el usuario actual es del personal de IT o el propietario de la solicitud
$is_it_staff = isset($_SESSION['is_it_staff']) && $_SESSION['is_it_staff'];
$is_owner = ($_SESSION['user_id'] == $support->user_id);
$can_edit = $is_it_staff || $is_owner;

// Si no tiene permiso para editar, redirigir
if (!$can_edit) {
    header('Location: index.php?page=support&action=view&id=' . $support->request_id);
    exit();
}

// Obtener lista de técnicos de IT para asignación
$it_staff = [];
if ($is_it_staff) {
    $stmt = $user->readITStaff();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $it_staff[] = $row;
    }
}

// Obtener lista de hardware
$hardware_list = [];
$stmt = $hardware->read();
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $hardware_list[] = $row;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Solicitud - LMS IT Inventory</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Contenido principal -->
    <div id="content" class="p-4 p-md-5 pt-5">
        <div class="container-fluid">
            <!-- Encabezado principal y botones de acción -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0"><i class="bi bi-pencil-square me-2"></i>Editar Solicitud #<?php echo $support->request_id; ?></h2>
                <div>
                    <a href="index.php?page=support&action=view&id=<?php echo $support->request_id; ?>" class="btn btn-secondary me-2">
                        <i class="bi bi-arrow-left me-1"></i> Cancelar
                    </a>
                    <button type="button" class="btn btn-primary" id="saveChanges">
                        <i class="bi bi-save me-1"></i> Guardar Cambios
                    </button>
                </div>
            </div>

            <!-- Formulario de edición -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="card-title mb-0">Información de la Solicitud</h5>
                </div>
                <div class="card-body p-4">
                    <form id="editTicketForm">
                        <input type="hidden" id="request_id" value="<?php echo $support->request_id; ?>">
                        
                        <div class="row mb-4">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <label for="request_type" class="form-label fw-bold">Tipo de Solicitud</label>
                                <select class="form-select form-select-lg" id="request_type" name="request_type" required>
                                    <option value="Hardware Issue" <?php echo $support->request_type == 'Hardware Issue' ? 'selected' : ''; ?>>Problema de Hardware</option>
                                    <option value="Peripheral Request" <?php echo $support->request_type == 'Peripheral Request' ? 'selected' : ''; ?>>Solicitud de Periférico</option>
                                    <option value="Replacement" <?php echo $support->request_type == 'Replacement' ? 'selected' : ''; ?>>Reemplazo</option>
                                    <option value="Return" <?php echo $support->request_type == 'Return' ? 'selected' : ''; ?>>Devolución</option>
                                    <option value="Other" <?php echo $support->request_type == 'Other' ? 'selected' : ''; ?>>Otro</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="priority" class="form-label fw-bold">Prioridad</label>
                                <select class="form-select form-select-lg" id="priority" name="priority" required>
                                    <option value="Low" <?php echo $support->priority == 'Low' ? 'selected' : ''; ?>>Baja</option>
                                    <option value="Medium" <?php echo $support->priority == 'Medium' ? 'selected' : ''; ?>>Media</option>
                                    <option value="High" <?php echo $support->priority == 'High' ? 'selected' : ''; ?>>Alta</option>
                                    <option value="Urgent" <?php echo $support->priority == 'Urgent' ? 'selected' : ''; ?>>Urgente</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="hardware_id" class="form-label fw-bold">Hardware Relacionado</label>
                            <select class="form-select" id="hardware_id" name="hardware_id">
                                <option value="">Ninguno</option>
                                <?php
                                foreach ($hardware_list as $item) {
                                    $selected = ($item['hardware_id'] == $support->hardware_id) ? 'selected' : '';
                                    echo "<option value='{$item['hardware_id']}' {$selected}>{$item['asset_tag']} - {$item['model_name']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        
                        <div class="mb-4">
                            <label for="description" class="form-label fw-bold">Descripción del Problema</label>
                            <textarea class="form-control" id="description" name="description" rows="4" required><?php echo htmlspecialchars($support->description); ?></textarea>
                        </div>
                        
                        <?php if ($is_it_staff): ?>
                        <div class="row mb-4">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <label for="status" class="form-label fw-bold">Estado</label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="New" <?php echo $support->status == 'New' ? 'selected' : ''; ?>>Nuevo</option>
                                    <option value="Assigned" <?php echo $support->status == 'Assigned' ? 'selected' : ''; ?>>Asignado</option>
                                    <option value="In Progress" <?php echo $support->status == 'In Progress' ? 'selected' : ''; ?>>En Progreso</option>
                                    <option value="Resolved" <?php echo $support->status == 'Resolved' ? 'selected' : ''; ?>>Resuelto</option>
                                    <option value="Closed" <?php echo $support->status == 'Closed' ? 'selected' : ''; ?>>Cerrado</option>
                                    <option value="Cancelled" <?php echo $support->status == 'Cancelled' ? 'selected' : ''; ?>>Cancelado</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="assigned_to" class="form-label fw-bold">Asignado a</label>
                                <select class="form-select" id="assigned_to" name="assigned_to">
                                    <option value="">No asignado</option>
                                    <?php
                                    foreach ($it_staff as $staff) {
                                        $selected = ($staff['user_id'] == $support->assigned_to) ? 'selected' : '';
                                        echo "<option value='{$staff['user_id']}' {$selected}>{$staff['first_name']} {$staff['last_name']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="resolution_notes" class="form-label fw-bold">Notas de Resolución</label>
                            <textarea class="form-control" id="resolution_notes" name="resolution_notes" rows="4"><?php echo htmlspecialchars($support->resolution_notes); ?></textarea>
                            <div class="form-text">Añada notas sobre cómo se resolvió o se está resolviendo el problema.</div>
                        </div>
                        <?php else: ?>
                        <input type="hidden" id="status" value="<?php echo $support->status; ?>">
                        <input type="hidden" id="assigned_to" value="<?php echo $support->assigned_to; ?>">
                        <input type="hidden" id="resolution_notes" value="<?php echo htmlspecialchars($support->resolution_notes); ?>">
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Inicializar validación de formulario
            $('select, textarea').on('change input', function() {
                $(this).removeClass('is-invalid');
            });
            
            // Guardar cambios
            $('#saveChanges').click(function() {
                updateSupportTicket();
            });
        });
        
        function updateSupportTicket() {
            // Validar formulario
            let isValid = true;
            
            if (!$('#request_type').val() || !$('#priority').val() || !$('#description').val().trim()) {
                if (!$('#request_type').val()) $('#request_type').addClass('is-invalid');
                if (!$('#priority').val()) $('#priority').addClass('is-invalid');
                if (!$('#description').val().trim()) $('#description').addClass('is-invalid');
                isValid = false;
                return;
            }
            
            const formData = {
                request_id: $('#request_id').val(),
                request_type: $('#request_type').val(),
                hardware_id: $('#hardware_id').val() || null,
                description: $('#description').val(),
                priority: $('#priority').val(),
                status: $('#status').val(),
                assigned_to: $('#assigned_to').val() || null,
                resolution_notes: $('#resolution_notes').val() || ''
            };

            fetch('app/controllers/SupportController.php', {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(formData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.message) {
                    alert(data.message);
                    if (data.message.includes('actualizada')) {
                        window.location.href = 'index.php?page=support&action=view&id=' + $('#request_id').val();
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al actualizar la solicitud de soporte.');
            });
        }
    </script>
</body>
</html> 