<?php
$reportController = new ReportController();
$inventory = $reportController->getHardwareInventory();

// Preparar datos para gráficos
$statusData = $reportController->getHardwareStatus();
?>

<div id="content" class="p-4 p-md-5 pt-5">
    <div class="container-fluid">
        <!-- Encabezado -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Reporte de Inventario de Hardware</h2>
            <div class="btn-group">
                <button type="button" class="btn btn-outline-primary" onclick="exportToCSV()">
                    <i class="bi bi-file-earmark-excel me-2"></i>Exportar CSV
                </button>
                <button type="button" class="btn btn-outline-danger" onclick="exportToPDF()">
                    <i class="bi bi-file-earmark-pdf me-2"></i>Exportar PDF
                </button>
                <a href="?page=reports" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Volver
                </a>
            </div>
        </div>

        <!-- Filtros -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <form id="filterForm" class="row g-3">
                    <div class="col-md-3">
                        <label for="client" class="form-label">Cliente</label>
                        <select class="form-select" id="client" name="client">
                            <option value="">Todos</option>
                            <?php foreach ($clients as $client): ?>
                            <option value="<?php echo $client['client_id']; ?>">
                                <?php echo htmlspecialchars($client['client_name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="status" class="form-label">Estado</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">Todos</option>
                            <option value="In Use">En Uso</option>
                            <option value="In Stock">En Stock</option>
                            <option value="In Transit">En Tránsito</option>
                            <option value="In Repair">En Reparación</option>
                            <option value="Retired">Retirado</option>
                            <option value="Lost">Perdido</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="category" class="form-label">Categoría</label>
                        <select class="form-select" id="category" name="category">
                            <option value="">Todas</option>
                            <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['category_id']; ?>">
                                <?php echo htmlspecialchars($category['category_name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="search" class="form-label">Buscar</label>
                        <input type="text" class="form-control" id="search" name="search" placeholder="Serial, Asset Tag...">
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search me-2"></i>Filtrar
                        </button>
                        <button type="reset" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle me-2"></i>Limpiar
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Gráficos -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-light py-3">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-pie-chart me-2"></i>
                            Distribución por Estado
                        </h5>
                    </div>
                    <div class="card-body">
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-light py-3">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-bar-chart me-2"></i>
                            Hardware por Cliente
                        </h5>
                    </div>
                    <div class="card-body">
                        <canvas id="clientChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de Inventario -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-light py-3">
                <h5 class="card-title mb-0">
                    <i class="bi bi-list-check me-2"></i>
                    Listado de Hardware
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Asset Tag</th>
                                <th>Serial</th>
                                <th>Modelo</th>
                                <th>Cliente</th>
                                <th>Ubicación</th>
                                <th>Usuario Actual</th>
                                <th>Estado</th>
                                <th>Condición</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($inventory as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['asset_tag']); ?></td>
                                <td><?php echo htmlspecialchars($item['serial_number']); ?></td>
                                <td><?php echo htmlspecialchars($item['brand_name'] . ' ' . $item['model_name']); ?></td>
                                <td><?php echo htmlspecialchars($item['client_name']); ?></td>
                                <td><?php echo htmlspecialchars($item['location_name']); ?></td>
                                <td>
                                    <?php if ($item['first_name']): ?>
                                        <?php echo htmlspecialchars($item['first_name'] . ' ' . $item['last_name']); ?>
                                    <?php else: ?>
                                        <span class="text-muted">No asignado</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $statusClass = [
                                        'In Use' => 'success',
                                        'In Stock' => 'primary',
                                        'In Transit' => 'info',
                                        'In Repair' => 'warning',
                                        'Retired' => 'secondary',
                                        'Lost' => 'danger'
                                    ];
                                    $status = $item['status'];
                                    $class = $statusClass[$status] ?? 'secondary';
                                    ?>
                                    <span class="badge bg-<?php echo $class; ?>">
                                        <?php echo htmlspecialchars($status); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php
                                    $conditionClass = [
                                        'New' => 'success',
                                        'Good' => 'primary',
                                        'Fair' => 'warning',
                                        'Poor' => 'danger'
                                    ];
                                    $condition = $item['condition_status'];
                                    $class = $conditionClass[$condition] ?? 'secondary';
                                    ?>
                                    <span class="badge bg-<?php echo $class; ?>">
                                        <?php echo htmlspecialchars($condition); ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Datos para el gráfico de estado
const statusData = <?php echo json_encode($statusData); ?>;
const statusCtx = document.getElementById('statusChart').getContext('2d');
new Chart(statusCtx, {
    type: 'pie',
    data: {
        labels: statusData.map(item => item.status),
        datasets: [{
            data: statusData.map(item => item.count),
            backgroundColor: [
                '#0d6efd', // primary
                '#198754', // success
                '#0dcaf0', // info
                '#ffc107', // warning
                '#6c757d', // secondary
                '#dc3545'  // danger
            ]
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

// Función para exportar a CSV
function exportToCSV() {
    window.location.href = '?page=reports&type=hardware_inventory&format=csv';
}

// Función para exportar a PDF
function exportToPDF() {
    window.location.href = '?page=reports&type=hardware_inventory&format=pdf';
}

// Inicializar filtros
document.getElementById('filterForm').addEventListener('submit', function(e) {
    e.preventDefault();
    // Implementar lógica de filtrado
});

document.getElementById('filterForm').addEventListener('reset', function(e) {
    setTimeout(() => {
        // Recargar datos sin filtros
        window.location.href = '?page=reports&type=hardware_inventory';
    }, 0);
});
</script> 