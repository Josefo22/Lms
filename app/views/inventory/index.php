<?php
// Obtener datos del controlador
$hardware = $inventoryController->getAllHardware();
$stats = $inventoryController->getInventoryStats();
?>

<div id="content" class="p-4 p-md-5 pt-5">
    <div class="container-fluid">
        <!-- Encabezado principal y botón de acción -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Inventario de Hardware</h2>
            <a href="?page=add_hardware" class="btn btn-success">
                <i class="bi bi-plus-circle me-1"></i> Nuevo Equipo
            </a>
        </div>
        
        <!-- Estadísticas -->
        <div class="row mt-4">
            <div class="col-xl-3 col-md-6">
                <div class="card bg-primary text-white mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="small">Total Equipos</div>
                                <div class="fs-3"><?php echo $stats['total']; ?></div>
                            </div>
                            <i class="bi bi-pc-display fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card bg-success text-white mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="small">En Stock</div>
                                <div class="fs-3"><?php echo $stats['in_stock']; ?></div>
                            </div>
                            <i class="bi bi-box-seam fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card bg-warning text-white mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="small">En Uso</div>
                                <div class="fs-3"><?php echo $stats['in_use']; ?></div>
                            </div>
                            <i class="bi bi-person-workspace fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card bg-danger text-white mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="small">En Reparación</div>
                                <div class="fs-3"><?php echo $stats['in_repair']; ?></div>
                            </div>
                            <i class="bi bi-tools fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Filtros y Búsqueda -->
        <div class="card mb-4">
            <div class="card-body">
                <form id="filterForm" class="row g-3">
                    <div class="col-md-3">
                        <label for="search" class="form-label">Buscar</label>
                        <input type="text" class="form-control" id="search" placeholder="Serial, Asset Tag, Modelo...">
                    </div>
                    <div class="col-md-2">
                        <label for="status" class="form-label">Estado</label>
                        <select class="form-select" id="status">
                            <option value="">Todos</option>
                            <option value="In Stock">En Stock</option>
                            <option value="In Use">En Uso</option>
                            <option value="In Repair">En Reparación</option>
                            <option value="In Transit">En Tránsito</option>
                            <option value="Retired">Retirado</option>
                            <option value="Lost">Perdido</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="client" class="form-label">Cliente</label>
                        <select class="form-select" id="client">
                            <option value="">Todos</option>
                            <?php foreach($inventoryController->getClients() as $client): ?>
                            <option value="<?php echo $client['client_id']; ?>">
                                <?php echo htmlspecialchars($client['client_name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="bi bi-search"></i> Filtrar
                        </button>
                        <button type="reset" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Limpiar
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Tabla de Inventario -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="bi bi-table me-1"></i>
                Listado de Equipos
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="hardwareTable">
                        <thead>
                            <tr>
                                <th>Serial</th>
                                <th>Asset Tag</th>
                                <th>Modelo</th>
                                <th>Estado</th>
                                <th>Cliente</th>
                                <th>Usuario</th>
                                <th>Ubicación</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($hardware)): ?>
                            <tr>
                                <td colspan="8" class="text-center">No hay equipos registrados</td>
                            </tr>
                            <?php else: ?>
                                <?php foreach($hardware as $item): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['serial_number']); ?></td>
                                    <td><?php echo htmlspecialchars($item['asset_tag'] ?? 'N/A'); ?></td>
                                    <td>
                                        <?php echo htmlspecialchars($item['model_name']); ?>
                                        <br>
                                        <small class="text-muted"><?php echo htmlspecialchars($item['brand_name']); ?></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo match($item['status']) {
                                                'In Stock' => 'success',
                                                'In Use' => 'primary',
                                                'In Repair' => 'warning',
                                                'In Transit' => 'info',
                                                'Retired' => 'secondary',
                                                'Lost' => 'danger',
                                                default => 'secondary'
                                            };
                                        ?>">
                                            <?php echo htmlspecialchars($item['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($item['client_name'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($item['user_name'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($item['location_name'] ?? 'N/A'); ?></td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="?page=inventory&action=view&id=<?php echo $item['hardware_id']; ?>" 
                                               class="btn btn-sm btn-info" title="Ver">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="?page=inventory&action=edit&id=<?php echo $item['hardware_id']; ?>" 
                                               class="btn btn-sm btn-warning" title="Editar">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-danger" title="Eliminar"
                                                    onclick="deleteHardware(<?php echo $item['hardware_id']; ?>)">
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar DataTable
    const table = new DataTable('#hardwareTable', {
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json',
        },
    });
    
    // Manejar filtros
    const filterForm = document.getElementById('filterForm');
    filterForm.addEventListener('submit', function(e) {
        e.preventDefault();
        applyFilters();
    });
    
    filterForm.addEventListener('reset', function() {
        setTimeout(applyFilters, 1); // Permitir que el formulario se reinicie primero
    });
    
    function applyFilters() {
        const search = document.getElementById('search').value.toLowerCase();
        const status = document.getElementById('status').value;
        const client = document.getElementById('client').value;
        
        table.search(search).draw();
        
        // Filtros adicionales personalizados
        $.fn.dataTable.ext.search.push(
            function(settings, data, dataIndex) {
                let statusMatch = !status || data[3].includes(status);
                let clientMatch = !client || data[4].includes(client);
                return statusMatch && clientMatch;
            }
        );
        
        table.draw();
        $.fn.dataTable.ext.search.pop(); // Limpiar filtro personalizado
    }
    
    // Inicializar tooltips de Bootstrap
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});

// Función para eliminar hardware
function deleteHardware(id) {
    if(confirm('¿Está seguro de que desea eliminar este equipo?')) {
        fetch(`ajax_handler.php?action=delete_hardware&id=${id}`, {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                location.reload();
            } else {
                alert('Error al eliminar el equipo: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al procesar la solicitud');
        });
    }
}
</script> 