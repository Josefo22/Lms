<?php
$clientController = new ClientController();
$clients = $clientController->getClients($_GET);
$stats = $clientController->getClientStats();
?>

<div id="content" class="p-4 p-md-5 pt-5">
    <div class="container-fluid">
        <!-- Encabezado principal y botón de acción -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Gestión de Clientes</h2>
            <a href="?page=clients&action=create" class="btn btn-primary">
                <i class="bi bi-plus-circle me-2"></i> Nuevo Cliente
            </a>
        </div>

        <!-- Tarjetas de estadísticas -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card h-100 border-0 shadow-sm rounded">
                    <div class="card-body bg-primary text-white rounded">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-subtitle mb-2">Total Clientes</h6>
                                <h2 class="card-title mb-0"><?php echo $stats['total_clients']; ?></h2>
                            </div>
                            <i class="bi bi-building fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-3">
                <div class="card h-100 border-0 shadow-sm rounded">
                    <div class="card-body bg-success text-white rounded">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-subtitle mb-2">Clientes Activos</h6>
                                <h2 class="card-title mb-0"><?php echo count($clients); ?></h2>
                            </div>
                            <i class="bi bi-check-circle fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-3">
                <div class="card h-100 border-0 shadow-sm rounded">
                    <div class="card-body bg-info text-white rounded">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-subtitle mb-2">Total Hardware</h6>
                                <h2 class="card-title mb-0"><?php echo array_sum(array_column($clients, 'hardware_count')); ?></h2>
                            </div>
                            <i class="bi bi-pc-display fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-3">
                <div class="card h-100 border-0 shadow-sm rounded">
                    <div class="card-body bg-warning text-white rounded">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-subtitle mb-2">Total Usuarios</h6>
                                <h2 class="card-title mb-0"><?php echo array_sum(array_column($clients, 'user_count')); ?></h2>
                            </div>
                            <i class="bi bi-people fs-1"></i>
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
                        <form method="GET" class="row">
                            <input type="hidden" name="page" value="clients">
                            
                            <div class="col-md-4 mb-3">
                                <label for="search" class="form-label">Buscar Cliente</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                                    <input type="text" class="form-control" id="search" name="search" 
                                           placeholder="Nombre, contacto o email..."
                                           value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                                </div>
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <label for="status" class="form-label">Estado</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="">Todos</option>
                                    <option value="active" <?php echo isset($_GET['status']) && $_GET['status'] == 'active' ? 'selected' : ''; ?>>Activo</option>
                                    <option value="inactive" <?php echo isset($_GET['status']) && $_GET['status'] == 'inactive' ? 'selected' : ''; ?>>Inactivo</option>
                                </select>
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <label for="sort" class="form-label">Ordenar por</label>
                                <select class="form-select" id="sort" name="sort">
                                    <option value="name" <?php echo (!isset($_GET['sort']) || $_GET['sort'] == 'name') ? 'selected' : ''; ?>>Nombre</option>
                                    <option value="hardware" <?php echo isset($_GET['sort']) && $_GET['sort'] == 'hardware' ? 'selected' : ''; ?>>Hardware</option>
                                    <option value="users" <?php echo isset($_GET['sort']) && $_GET['sort'] == 'users' ? 'selected' : ''; ?>>Usuarios</option>
                                </select>
                            </div>

                            <div class="col-md-2 mb-3 d-flex align-items-end">
                                <div class="d-flex gap-2 w-100">
                                    <button type="submit" class="btn btn-primary flex-grow-1">
                                        <i class="bi bi-filter me-1"></i> Filtrar
                                    </button>
                                    <a href="?page=clients" class="btn btn-outline-secondary">
                                        <i class="bi bi-x-circle me-1"></i>
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de Clientes -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Cliente</th>
                                <th>Contacto</th>
                                <th>Email</th>
                                <th>Teléfono</th>
                                <th class="text-center">Hardware</th>
                                <th class="text-center">Usuarios</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($clients)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="bi bi-info-circle fs-4 d-block mb-2"></i>
                                        No se encontraron clientes
                                    </div>
                                </td>
                            </tr>
                            <?php else: ?>
                                <?php foreach ($clients as $client): ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold"><?php echo htmlspecialchars($client['client_name']); ?></div>
                                    </td>
                                    <td><?php echo htmlspecialchars($client['contact_person']); ?></td>
                                    <td><?php echo htmlspecialchars($client['contact_email']); ?></td>
                                    <td><?php echo htmlspecialchars($client['contact_phone']); ?></td>
                                    <td class="text-center">
                                        <span class="badge bg-info"><?php echo $client['hardware_count']; ?></span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-warning"><?php echo $client['user_count']; ?></span>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group">
                                            <a href="?page=clients&action=view&id=<?php echo $client['client_id']; ?>" 
                                               class="btn btn-sm btn-info text-white" title="Ver">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="?page=clients&action=edit&id=<?php echo $client['client_id']; ?>" 
                                               class="btn btn-sm btn-primary" title="Editar">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-danger" 
                                                    onclick="confirmDelete(<?php echo $client['client_id']; ?>)" title="Eliminar">
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
function confirmDelete(clientId) {
    if (confirm('¿Está seguro de que desea eliminar este cliente? Esta acción no se puede deshacer.')) {
        window.location.href = '?page=clients&action=delete&id=' + clientId;
    }
}
</script> 