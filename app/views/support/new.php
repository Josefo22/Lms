<?php
require_once 'config/database.php';
require_once 'app/models/SupportRequest.php';
require_once 'app/models/User.php';
require_once 'app/models/Hardware.php';

// Authentication check
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Initialize database and models
$database = new Database();
$db = $database->connect();
$support = new SupportRequest($db);
$user = new User($db);
$hardware = new Hardware($db);

// Check if user is IT staff
$is_it_staff = isset($_SESSION['is_it_staff']) && $_SESSION['is_it_staff'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Solicitud de Soporte - LMS IT Inventory</title>
    
    <!-- Bootstrap and Icons -->
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
                <h2 class="mb-0">Nueva Solicitud de Soporte</h2>
                <a href="index.php?page=support" class="btn btn-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Volver a la lista
                </a>
            </div>

            <!-- Formulario de solicitud -->
            <div class="card border-0 shadow-sm rounded">
                <div class="card-body p-4">
                    <form id="createTicketForm">
                        <!-- Sección tipo y prioridad -->
                        <div class="row mb-4">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <label for="request_type" class="form-label">Tipo de Solicitud</label>
                                <select class="form-select" id="request_type" name="request_type" required>
                                    <option value="">Seleccione tipo</option>
                                    <option value="Hardware Issue">Problema de Hardware</option>
                                    <option value="Peripheral Request">Solicitud de Periférico</option>
                                    <option value="Replacement">Reemplazo</option>
                                    <option value="Return">Devolución</option>
                                    <option value="Other">Otro</option>
                                </select>
                                <div class="invalid-feedback">Por favor seleccione un tipo de solicitud</div>
                            </div>
                            <div class="col-md-6">
                                <label for="priority" class="form-label">Prioridad</label>
                                <select class="form-select" id="priority" name="priority" required>
                                    <option value="">Seleccione prioridad</option>
                                    <option value="Low">Baja</option>
                                    <option value="Medium">Media</option>
                                    <option value="High">Alta</option>
                                    <option value="Urgent">Urgente</option>
                                </select>
                                <div class="invalid-feedback">Por favor seleccione la prioridad</div>
                            </div>
                        </div>
                        
                        <!-- Sección hardware relacionado -->
                        <div class="mb-4">
                            <label for="hardware_id" class="form-label">Hardware Relacionado (Opcional)</label>
                            <select class="form-select" id="hardware_id" name="hardware_id">
                                <option value="">Ninguno</option>
                                <?php
                                $stmt = $hardware->readByUser($_SESSION['user_id']);
                                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    echo "<option value='{$row['hardware_id']}'>{$row['asset_tag']} - {$row['model_name']}</option>";
                                }
                                ?>
                            </select>
                            <div class="form-text">Seleccione el equipo relacionado con su solicitud, si corresponde.</div>
                        </div>
                        
                        <!-- Sección descripción -->
                        <div class="mb-4">
                            <label for="description" class="form-label">Descripción del Problema</label>
                            <textarea class="form-control" id="description" name="description" rows="5" required placeholder="Describa detalladamente el problema..."></textarea>
                            <div class="invalid-feedback">Por favor proporcione una descripción del problema</div>
                            <div class="form-text">Proporcione todos los detalles posibles para ayudar al equipo de soporte a resolver su problema rápidamente.</div>
                        </div>
                        
                        <!-- Campos ocultos -->
                        <input type="hidden" id="user_id" name="user_id" value="<?php echo $_SESSION['user_id']; ?>">
                        <input type="hidden" id="status" name="status" value="New">
                        
                        <!-- Botones de acción -->
                        <div class="d-flex justify-content-between mt-4">
                            <button type="button" id="cancelBtn" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle me-2"></i>Cancelar
                            </button>
                            <button type="button" id="submitTicket" class="btn btn-primary">
                                <i class="bi bi-save me-2"></i>Guardar Solicitud
                            </button>
                        </div>
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
            // Inicializar elementos de formulario
            $('#request_type, #priority').on('change', function() {
                $(this).removeClass('is-invalid');
            });
            
            $('#description').on('input', function() {
                $(this).removeClass('is-invalid');
            });
            
            $('#cancelBtn').click(function() {
                window.location.href = 'index.php?page=support';
            });
            
            $('#submitTicket').click(function() {
                // Validar formulario
                let isValid = true;
                
                if (!$('#request_type').val()) {
                    $('#request_type').addClass('is-invalid');
                    isValid = false;
                }
                
                if (!$('#priority').val()) {
                    $('#priority').addClass('is-invalid');
                    isValid = false;
                }
                
                if (!$('#description').val().trim()) {
                    $('#description').addClass('is-invalid');
                    isValid = false;
                }
                
                if (isValid) {
                    // Mostrar spinner en botón
                    const submitBtn = $(this);
                    const originalContent = submitBtn.html();
                    submitBtn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Guardando...');
                    submitBtn.prop('disabled', true);
                    
                    createSupportTicket(submitBtn, originalContent);
                }
            });
        });
        
        function createSupportTicket(button, originalContent) {
            const formData = {
                user_id: $('#user_id').val(),
                request_type: $('#request_type').val(),
                hardware_id: $('#hardware_id').val() || null,
                description: $('#description').val(),
                priority: $('#priority').val(),
                status: 'New'
            };

            fetch('app/controllers/SupportController.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(formData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.message) {
                    // Restaurar botón
                    if (button) {
                        button.html(originalContent);
                        button.prop('disabled', false);
                    }
                    
                    // Mostrar notificación
                    const alertHTML = `
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            ${data.message}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    `;
                    $('#createTicketForm').prepend(alertHTML);
                    
                    // Redireccionar después de 1 segundo si fue exitoso
                    if (data.message.includes('creada')) {
                        setTimeout(() => {
                            window.location.href = 'index.php?page=support';
                        }, 1000);
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                
                // Restaurar botón
                if (button) {
                    button.html(originalContent);
                    button.prop('disabled', false);
                }
                
                // Mostrar error
                const alertHTML = `
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        Error al crear la solicitud de soporte.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                `;
                $('#createTicketForm').prepend(alertHTML);
            });
        }
    </script>
</body>
</html>