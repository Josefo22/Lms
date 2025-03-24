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

$database = new Database();
$db = $database->connect();
$support = new SupportRequest($db);
$user = new User($db);

// Obtener todas las solicitudes o filtrar por estado
$status_filter = isset($_GET['status']) ? $_GET['status'] : null;
$priority_filter = isset($_GET['priority']) ? $_GET['priority'] : null;
$search_term = isset($_GET['search']) ? $_GET['search'] : null;

// Aplicar filtros
if ($status_filter) {
    $stmt = $support->readByStatus($status_filter);
} else {
    $stmt = $support->read();
}
$num = $stmt->rowCount();

// Obtener contadores por estado
$status_counts = [];
$statusStmt = $support->getStatusCounts();
while ($row = $statusStmt->fetch(PDO::FETCH_ASSOC)) {
    $status_counts[$row['status']] = $row['count'];
}

// Calcular contadores específicos
$total_requests = $num;
$new_count = isset($status_counts['New']) ? $status_counts['New'] : 0;
$in_progress_count = isset($status_counts['In Progress']) ? $status_counts['In Progress'] : 0;
$resolved_count = isset($status_counts['Resolved']) ? $status_counts['Resolved'] : 0;

// Verificar si el usuario actual es del personal de IT
$is_it_staff = isset($_SESSION['is_it_staff']) && $_SESSION['is_it_staff'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Soporte Técnico - LMS IT Inventory</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Contenido principal -->
    <div id="content" class="p-4 p-md-5 pt-5">
        <div class="container-fluid">
            <!-- Encabezado principal y botón de acción -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">Sistema de Soporte Técnico</h2>
                <a href="index.php?page=support&action=new" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>Nueva Solicitud
                </a>
            </div>

            <!-- Tarjetas de estadísticas -->
            <div class="row mb-4">
                <div class="col-md-3 mb-3">
                    <a href="index.php?page=support" class="text-decoration-none">
                        <div class="card h-100 border-0 shadow-sm rounded">
                            <div class="card-body bg-primary text-white rounded">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-subtitle mb-2">Todas las solicitudes</h6>
                                        <h2 class="card-title mb-0"><?php echo $total_requests; ?></h2>
                                    </div>
                                    <i class="bi bi-list-check fs-1"></i>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
                
                <div class="col-md-3 mb-3">
                    <a href="index.php?page=support&status=New" class="text-decoration-none">
                        <div class="card h-100 border-0 shadow-sm rounded">
                            <div class="card-body bg-info text-white rounded">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-subtitle mb-2">Nuevas</h6>
                                        <h2 class="card-title mb-0"><?php echo $new_count; ?></h2>
                                    </div>
                                    <i class="bi bi-file-earmark-plus fs-1"></i>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
                
                <div class="col-md-3 mb-3">
                    <a href="index.php?page=support&status=In Progress" class="text-decoration-none">
                        <div class="card h-100 border-0 shadow-sm rounded">
                            <div class="card-body bg-warning rounded">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-subtitle mb-2">En Progreso</h6>
                                        <h2 class="card-title mb-0"><?php echo $in_progress_count; ?></h2>
                                    </div>
                                    <i class="bi bi-gear fs-1"></i>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
                
                <div class="col-md-3 mb-3">
                    <a href="index.php?page=support&status=Resolved" class="text-decoration-none">
                        <div class="card h-100 border-0 shadow-sm rounded">
                            <div class="card-body bg-success text-white rounded">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-subtitle mb-2">Resueltas</h6>
                                        <h2 class="card-title mb-0"><?php echo $resolved_count; ?></h2>
                                    </div>
                                    <i class="bi bi-check-circle fs-1"></i>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Filtros y búsqueda -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card border-0 shadow-sm rounded">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="filterStatus" class="form-label">Estado</label>
                                    <select id="filterStatus" class="form-select">
                                        <option value="">Todos los estados</option>
                                        <option value="New" <?php echo $status_filter == 'New' ? 'selected' : ''; ?>>Nuevos</option>
                                        <option value="Assigned" <?php echo $status_filter == 'Assigned' ? 'selected' : ''; ?>>Asignados</option>
                                        <option value="In Progress" <?php echo $status_filter == 'In Progress' ? 'selected' : ''; ?>>En Progreso</option>
                                        <option value="Resolved" <?php echo $status_filter == 'Resolved' ? 'selected' : ''; ?>>Resueltos</option>
                                        <option value="Closed" <?php echo $status_filter == 'Closed' ? 'selected' : ''; ?>>Cerrados</option>
                                        <option value="Cancelled" <?php echo $status_filter == 'Cancelled' ? 'selected' : ''; ?>>Cancelados</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="filterPriority" class="form-label">Prioridad</label>
                                    <select id="filterPriority" class="form-select">
                                        <option value="">Todas las prioridades</option>
                                        <option value="Low" <?php echo $priority_filter == 'Low' ? 'selected' : ''; ?>>Baja</option>
                                        <option value="Medium" <?php echo $priority_filter == 'Medium' ? 'selected' : ''; ?>>Media</option>
                                        <option value="High" <?php echo $priority_filter == 'High' ? 'selected' : ''; ?>>Alta</option>
                                        <option value="Urgent" <?php echo $priority_filter == 'Urgent' ? 'selected' : ''; ?>>Urgente</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="searchInput" class="form-label">Buscar</label>
                                    <div class="input-group">
                                        <input type="text" id="searchInput" class="form-control" placeholder="Buscar solicitudes..." value="<?php echo htmlspecialchars($search_term ?? ''); ?>">
                                        <button id="searchBtn" class="btn btn-primary">
                                            <i class="bi bi-search"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-12 d-flex justify-content-end mt-2">
                                    <button id="clearFiltersBtn" class="btn btn-outline-secondary me-2">
                                        <i class="bi bi-x-circle me-1"></i> Limpiar
                                    </button>
                                    <button id="applyFiltersBtn" class="btn btn-primary">
                                        <i class="bi bi-funnel me-1"></i> Filtrar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabla de Solicitudes -->
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="supportTable">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Usuario</th>
                                    <th>Tipo</th>
                                    <th>Descripción</th>
                                    <th>Prioridad</th>
                                    <th>Estado</th>
                                    <th>Asignado a</th>
                                    <th>Fecha</th>
                                    <th class="text-end">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if($num > 0) {
                                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                        extract($row);
                                        
                                        // Determinar clase para la prioridad
                                        $priority_class = '';
                                        switch($priority) {
                                            case 'Low':
                                                $priority_class = 'bg-info';
                                                break;
                                            case 'Medium':
                                                $priority_class = 'bg-primary';
                                                break;
                                            case 'High':
                                                $priority_class = 'bg-warning';
                                                break;
                                            case 'Urgent':
                                                $priority_class = 'bg-danger';
                                                break;
                                        }
                                        
                                        // Determinar clase para el estado
                                        $status_class = '';
                                        switch($status) {
                                            case 'New':
                                                $status_class = 'bg-info';
                                                break;
                                            case 'Assigned':
                                                $status_class = 'bg-primary';
                                                break;
                                            case 'In Progress':
                                                $status_class = 'bg-warning';
                                                break;
                                            case 'Resolved':
                                                $status_class = 'bg-success';
                                                break;
                                            case 'Closed':
                                                $status_class = 'bg-secondary';
                                                break;
                                            case 'Cancelled':
                                                $status_class = 'bg-danger';
                                                break;
                                        }
                                        
                                        // Formatear fecha
                                        $created_date = new DateTime($created_at);
                                        $formatted_date = $created_date->format('d/m/Y H:i');
                                        
                                        echo "<tr>";
                                        echo "<td>{$request_id}</td>";
                                        echo "<td>{$user_name}</td>";
                                        echo "<td>{$request_type}</td>";
                                        echo "<td class='text-truncate' style='max-width: 200px;'>{$description}</td>";
                                        echo "<td><span class='badge rounded-pill {$priority_class}'>{$priority}</span></td>";
                                        echo "<td><span class='badge rounded-pill {$status_class}'>{$status}</span></td>";
                                        echo "<td>" . ($assigned_name ?? 'No asignado') . "</td>";
                                        echo "<td>{$formatted_date}</td>";
                                        echo "<td class='text-end'>";
                                        echo "<div class='btn-group'>";
                                        echo "<a href='index.php?page=support&action=view&id={$request_id}' class='btn btn-sm btn-outline-info me-1' title='Ver detalles'><i class='bi bi-eye'></i></a>";
                                        if ($is_it_staff || $user_id == $_SESSION['user_id']) {
                                            echo "<a href='index.php?page=support&action=edit&id={$request_id}' class='btn btn-sm btn-outline-primary me-1' title='Editar'><i class='bi bi-pencil'></i></a>";
                                        }
                                        if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']) {
                                            echo "<button class='btn btn-sm btn-outline-danger' onclick='deleteTicket({$request_id})' title='Eliminar'><i class='bi bi-trash'></i></button>";
                                        }
                                        echo "</div>";
                                        echo "</td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='9' class='text-center py-4'>No se encontraron solicitudes de soporte</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Paginación -->
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>
                    <span>Mostrando <?php echo $num; ?> solicitudes</span>
                </div>
                <div>
                    <button class="btn btn-sm btn-outline-primary me-1" disabled>
                        <i class="bi bi-chevron-left me-1"></i>Anterior
                    </button>
                    <button class="btn btn-sm btn-outline-primary" disabled>
                        Siguiente<i class="bi bi-chevron-right ms-1"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Crear Solicitud -->
    <div class="modal fade" id="createTicketModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nueva Solicitud de Soporte</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="createTicketForm">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="request_type" class="form-label">Tipo de Solicitud</label>
                                <select class="form-select" id="request_type" required>
                                    <option value="">Seleccione tipo</option>
                                    <option value="Hardware Issue">Problema de Hardware</option>
                                    <option value="Peripheral Request">Solicitud de Periférico</option>
                                    <option value="Replacement">Reemplazo</option>
                                    <option value="Return">Devolución</option>
                                    <option value="Other">Otro</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="priority" class="form-label">Prioridad</label>
                                <select class="form-select" id="priority" required>
                                    <option value="">Seleccione prioridad</option>
                                    <option value="Low">Baja</option>
                                    <option value="Medium">Media</option>
                                    <option value="High">Alta</option>
                                    <option value="Urgent">Urgente</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="hardware_id" class="form-label">Hardware Relacionado (Opcional)</label>
                            <select class="form-select" id="hardware_id">
                                <option value="">Ninguno</option>
                                <!-- Se llenará dinámicamente con JavaScript -->
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Descripción del Problema</label>
                            <textarea class="form-control" id="description" rows="5" required></textarea>
                        </div>
                        <input type="hidden" id="user_id" value="<?php echo $_SESSION['user_id']; ?>">
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="submitTicket">Enviar Solicitud</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Eliminar la inclusión de DataTables para evitar duplicación de paginación -->
    <!-- <script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script> -->
    <script>
        $(document).ready(function() {
            // Comentar inicialización de DataTables para evitar duplicación de controles de paginación
            /*
            $('#supportTable').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json'
                },
                order: [[0, 'desc']]
            });
            */

            // Toggle Sidebar
            $('#sidebarCollapse').on('click', function() {
                $('#sidebar').toggleClass('active');
            });

            // Filtrar por estado
            $('#filterStatus').change(function() {
                const status = $(this).val();
                if (status) {
                    window.location.href = 'index.php?status=' + status;
                } else {
                    window.location.href = 'index.php';
                }
            });

            // Cargar hardware para el formulario
            loadHardwareOptions();

            // Manejar envío de solicitud
            $('#submitTicket').click(function() {
                createSupportTicket();
            });

            // Aplicar filtros
            document.getElementById('applyFiltersBtn').addEventListener('click', function() {
                applyFilters();
            });
            
            // Limpiar filtros
            document.getElementById('clearFiltersBtn').addEventListener('click', function() {
                window.location.href = 'index.php?page=support';
            });
            
            // Búsqueda
            document.getElementById('searchBtn').addEventListener('click', function() {
                applyFilters();
            });
            
            // Tecla Enter en campo de búsqueda
            document.getElementById('searchInput').addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    applyFilters();
                }
            });
            
            // Función para aplicar filtros
            function applyFilters() {
                const status = document.getElementById('filterStatus').value;
                const priority = document.getElementById('filterPriority').value;
                const search = document.getElementById('searchInput').value;
                
                let url = 'index.php?page=support';
                
                if(status) url += '&status=' + encodeURIComponent(status);
                if(priority) url += '&priority=' + encodeURIComponent(priority);
                if(search) url += '&search=' + encodeURIComponent(search);
                
                window.location.href = url;
            }
        });

        // Cargar opciones de hardware
        function loadHardwareOptions() {
            fetch('../../app/controllers/HardwareController.php')
                .then(response => response.json())
                .then(data => {
                    if (data.records) {
                        const select = document.getElementById('hardware_id');
                        data.records.forEach(hardware => {
                            const option = document.createElement('option');
                            option.value = hardware.hardware_id;
                            option.textContent = `${hardware.asset_tag} - ${hardware.serial_number}`;
                            select.appendChild(option);
                        });
                    }
                })
                .catch(error => console.error('Error:', error));
        }

        // Crear solicitud de soporte
        function createSupportTicket() {
            const formData = {
                user_id: document.getElementById('user_id').value,
                request_type: document.getElementById('request_type').value,
                hardware_id: document.getElementById('hardware_id').value || null,
                description: document.getElementById('description').value,
                priority: document.getElementById('priority').value,
                status: 'New'
            };

            // Validar campos requeridos
            if (!formData.request_type || !formData.description || !formData.priority) {
                alert('Por favor complete todos los campos requeridos.');
                return;
            }

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
                    alert(data.message);
                    if (data.message.includes('creada')) {
                        $('#createTicketModal').modal('hide');
                        location.reload();
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al crear la solicitud de soporte.');
            });
        }

        // Ver detalles de la solicitud
        function viewTicket(id) {
            window.location.href = `view.php?id=${id}`;
        }

        // Actualizar solicitud
        function updateTicket(id) {
            window.location.href = `edit.php?id=${id}`;
        }

        // Eliminar solicitud
        function deleteTicket(id) {
            if (confirm('¿Está seguro de eliminar esta solicitud?')) {
                const data = {
                    request_id: id
                };

                fetch('../../app/controllers/SupportController.php', {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(data)
                })
                .then(response => response.json())
                .then(data => {
                    alert(data.message);
                    if (data.message.includes('eliminada')) {
                        location.reload();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al eliminar la solicitud.');
                });
            }
        }
    </script>
</body>
</html> 