<?php
// Título de la página
$page_title = "Inventario";

// Instanciar el controlador
require_once 'app/controllers/InventoryController.php';
$inventoryController = new InventoryController();

// Obtener parámetros
$status = isset($_GET['status']) ? $_GET['status'] : null;
$category = isset($_GET['category']) ? $_GET['category'] : null;
$client = isset($_GET['client']) ? $_GET['client'] : null;

// Obtener datos para la vista
$hardwareItems = $inventoryController->getInventory($status, $category, $client);
$categories = $inventoryController->getFormOptions()['categories'] ?? [];
$clients = $inventoryController->getFormOptions()['clients'] ?? [];
$statusCounts = [
    'total' => $hardwareItems ? count($hardwareItems['hardware']) : 0,
    'In Stock' => $hardwareItems ? count(array_filter($hardwareItems['hardware'], function($item) { return $item['status'] == 'In Stock'; })) : 0,
    'Assigned' => $hardwareItems ? count(array_filter($hardwareItems['hardware'], function($item) { return $item['status'] == 'Assigned'; })) : 0,
    'In Repair' => $hardwareItems ? count(array_filter($hardwareItems['hardware'], function($item) { return $item['status'] == 'In Repair'; })) : 0
];
?>

<div id="content" class="p-4 p-md-5 pt-5">
    <div class="container-fluid">
        <!-- Encabezado principal y botón de acción -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Inventario de Hardware</h2>
            <a href="?page=add_hardware" class="btn btn-primary">
                <i class="bi bi-plus-circle me-2"></i> Nuevo Equipo
            </a>
        </div>
        
        <!-- Tarjetas de estadísticas -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card h-100 border-0 shadow-sm rounded">
                    <div class="card-body bg-primary text-white rounded">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-subtitle mb-2">Total Equipos</h6>
                                <h2 class="card-title mb-0"><?php echo $statusCounts['total']; ?></h2>
                            </div>
                            <i class="bi bi-pc-display fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-3">
                <div class="card h-100 border-0 shadow-sm rounded">
                    <div class="card-body bg-success text-white rounded">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-subtitle mb-2">Disponibles</h6>
                                <h2 class="card-title mb-0"><?php echo $statusCounts['In Stock'] ?? 0; ?></h2>
                            </div>
                            <i class="bi bi-check-circle fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-3">
                <div class="card h-100 border-0 shadow-sm rounded">
                    <div class="card-body bg-warning rounded">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-subtitle mb-2">Asignados</h6>
                                <h2 class="card-title mb-0"><?php echo $statusCounts['Assigned'] ?? 0; ?></h2>
                            </div>
                            <i class="bi bi-person-check fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-3">
                <div class="card h-100 border-0 shadow-sm rounded">
                    <div class="card-body bg-danger text-white rounded">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-subtitle mb-2">En Reparación</h6>
                                <h2 class="card-title mb-0"><?php echo $statusCounts['In Repair'] ?? 0; ?></h2>
                            </div>
                            <i class="bi bi-tools fs-1"></i>
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
                            <div class="col-md-4 mb-3">
                                <label for="categoryFilter" class="form-label">Categoría</label>
                                <select id="categoryFilter" class="form-select">
                                    <option value="">Todas</option>
                                    <?php foreach($categories as $cat): ?>
                                        <?php $selected = ($category == $cat['category_id']) ? 'selected' : ''; ?>
                                        <option value="<?php echo $cat['category_id']; ?>" <?php echo $selected; ?>>
                                            <?php echo $cat['category_name']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="clientFilter" class="form-label">Cliente</label>
                                <select id="clientFilter" class="form-select">
                                    <option value="">Todos</option>
                                    <?php foreach($clients as $cl): ?>
                                        <?php $selected = ($client == $cl['client_id']) ? 'selected' : ''; ?>
                                        <option value="<?php echo $cl['client_id']; ?>" <?php echo $selected; ?>>
                                            <?php echo $cl['client_name']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="statusFilter" class="form-label">Estado</label>
                                <select id="statusFilter" class="form-select">
                                    <option value="">Todos</option>
                                    <?php $statuses = ['In Stock', 'Assigned', 'In Repair', 'Retired']; ?>
                                    <?php foreach($statuses as $st): ?>
                                        <?php $selected = ($status == $st) ? 'selected' : ''; ?>
                                        <option value="<?php echo $st; ?>" <?php echo $selected; ?>>
                                            <?php 
                                                switch($st) {
                                                    case 'In Stock':
                                                        echo 'Disponible';
                                                        break;
                                                    case 'Assigned':
                                                        echo 'Asignado';
                                                        break;
                                                    case 'In Repair':
                                                        echo 'En Reparación';
                                                        break;
                                                    case 'Retired':
                                                        echo 'Baja';
                                                        break;
                                                    default:
                                                        echo $st;
                                                }
                                            ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-end">
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
        
        <!-- Tabla de inventario -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="inventoryTable" class="table mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Etiqueta</th>
                                <th>Categoría</th>
                                <th>Marca/Modelo</th>
                                <th>N° Serie</th>
                                <th>Estado</th>
                                <th>Asignado a</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(isset($hardwareItems['hardware']) && count($hardwareItems['hardware']) > 0): ?>
                                <?php foreach($hardwareItems['hardware'] as $row): ?>
                                    <tr>
                                        <td><?php echo $row['hardware_id'] ?? ''; ?></td>
                                        <td><?php echo $row['asset_tag'] ?? ''; ?></td>
                                        <td><?php echo $row['category_name'] ?? ''; ?></td>
                                        <td><?php echo ($row['brand_name'] ?? '') . ' ' . ($row['model_name'] ?? ''); ?></td>
                                        <td><?php echo $row['serial_number'] ?? ''; ?></td>
                                        <td>
                                            <?php 
                                            $badgeClass = 'bg-secondary';
                                            $statusText = $row['status'] ?? '';
                                            
                                            switch($row['status'] ?? '') {
                                                case 'In Stock':
                                                    $badgeClass = 'bg-success';
                                                    $statusText = 'Disponible';
                                                    break;
                                                case 'Assigned':
                                                case 'In Use':
                                                    $badgeClass = 'bg-primary';
                                                    $statusText = 'Asignado';
                                                    break;
                                                case 'In Repair':
                                                    $badgeClass = 'bg-warning';
                                                    $statusText = 'En Reparación';
                                                    break;
                                                case 'Retired':
                                                    $badgeClass = 'bg-danger';
                                                    $statusText = 'Baja';
                                                    break;
                                            }
                                            ?>
                                            <span class="badge <?php echo $badgeClass; ?>"><?php echo $statusText; ?></span>
                                        </td>
                                        <td>
                                            <?php if(($row['status'] == 'Assigned' || $row['status'] == 'In Use') && !empty($row['user_name'])): ?>
                                                <?php echo $row['user_name'] ?? ''; ?>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="?page=inventory&action=view&id=<?php echo $row['hardware_id'] ?? ''; ?>" class="btn btn-sm btn-info text-white">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="?page=inventory&action=edit&id=<?php echo $row['hardware_id'] ?? ''; ?>" class="btn btn-sm btn-primary">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <?php if(($row['status'] ?? '') == 'In Stock'): ?>
                                                    <a href="?page=inventory&action=assign&id=<?php echo $row['hardware_id'] ?? ''; ?>" class="btn btn-sm btn-success">
                                                        <i class="bi bi-person-plus"></i>
                                                    </a>
                                                <?php endif; ?>
                                                <?php if(($row['status'] ?? '') == 'Assigned' || ($row['status'] ?? '') == 'In Use'): ?>
                                                    <a href="?page=inventory&action=return&id=<?php echo $row['hardware_id'] ?? ''; ?>" class="btn btn-sm btn-warning">
                                                        <i class="bi bi-arrow-return-left"></i>
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center p-4">No se encontraron equipos con los filtros seleccionados</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
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
            const category = document.getElementById('categoryFilter').value;
            const client = document.getElementById('clientFilter').value;
            const status = document.getElementById('statusFilter').value;
            
            let url = 'index.php?page=inventory';
            
            if(category) url += '&category=' + category;
            if(client) url += '&client=' + client;
            if(status) url += '&status=' + status;
            
            window.location.href = url;
        }
        
        // Función para limpiar filtros
        function clearFilters() {
            window.location.href = 'index.php?page=inventory';
        }
    });
</script> 