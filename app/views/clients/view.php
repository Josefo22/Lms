<?php
if (!isset($_GET['id'])) {
    header('Location: ?page=clients');
    exit;
}

$clientController = new ClientController();
$client = $clientController->getClientDetails($_GET['id']);

if (!$client) {
    header('Location: ?page=clients');
    exit;
}

// Obtener estadísticas del cliente
$stats = [
    'hardware_count' => $client['hardware_count'] ?? 0,
    'user_count' => $client['user_count'] ?? 0,
    'active_hardware' => $client['active_hardware'] ?? 0,
    'support_tickets' => $client['support_tickets'] ?? 0
];
?>

<div id="content" class="p-4 p-md-5 pt-5">
    <div class="container-fluid">
        <!-- Encabezado -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0"><?php echo htmlspecialchars($client['client_name']); ?></h2>
            <div class="btn-group">
                <a href="?page=clients&action=edit&id=<?php echo $client['client_id']; ?>" class="btn btn-primary">
                    <i class="bi bi-pencil me-2"></i> Editar
                </a>
                <a href="?page=clients" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i> Volver
                </a>
            </div>
        </div>

        <!-- Tarjetas de estadísticas -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card h-100 border-0 shadow-sm rounded">
                    <div class="card-body bg-primary text-white rounded">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-subtitle mb-2">Total Hardware</h6>
                                <h2 class="card-title mb-0"><?php echo $stats['hardware_count']; ?></h2>
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
                                <h6 class="card-subtitle mb-2">Hardware Activo</h6>
                                <h2 class="card-title mb-0"><?php echo $stats['active_hardware']; ?></h2>
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
                                <h6 class="card-subtitle mb-2">Total Usuarios</h6>
                                <h2 class="card-title mb-0"><?php echo $stats['user_count']; ?></h2>
                            </div>
                            <i class="bi bi-people fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-3">
                <div class="card h-100 border-0 shadow-sm rounded">
                    <div class="card-body bg-warning text-white rounded">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-subtitle mb-2">Tickets Soporte</h6>
                                <h2 class="card-title mb-0"><?php echo $stats['support_tickets']; ?></h2>
                            </div>
                            <i class="bi bi-ticket-detailed fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Información del Cliente -->
            <div class="col-md-6 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-light py-3">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-info-circle me-2"></i>
                            Información General
                        </h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless mb-0">
                            <tr>
                                <th class="ps-0" style="width: 150px;">Cliente:</th>
                                <td><?php echo htmlspecialchars($client['client_name']); ?></td>
                            </tr>
                            <tr>
                                <th class="ps-0">Contacto:</th>
                                <td><?php echo htmlspecialchars($client['contact_person'] ?: 'No especificado'); ?></td>
                            </tr>
                            <tr>
                                <th class="ps-0">Email:</th>
                                <td><?php echo htmlspecialchars($client['contact_email'] ?: 'No especificado'); ?></td>
                            </tr>
                            <tr>
                                <th class="ps-0">Teléfono:</th>
                                <td><?php echo htmlspecialchars($client['contact_phone'] ?: 'No especificado'); ?></td>
                            </tr>
                            <tr>
                                <th class="ps-0">Dirección:</th>
                                <td><?php echo nl2br(htmlspecialchars($client['address'] ?: 'No especificada')); ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Información Adicional -->
            <div class="col-md-6 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-light py-3">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-clock-history me-2"></i>
                            Información Adicional
                        </h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless mb-0">
                            <tr>
                                <th class="ps-0" style="width: 150px;">Fecha de Alta:</th>
                                <td><?php echo date('d/m/Y', strtotime($client['created_at'])); ?></td>
                            </tr>
                            <tr>
                                <th class="ps-0">Última Actualización:</th>
                                <td><?php echo date('d/m/Y H:i', strtotime($client['updated_at'])); ?></td>
                            </tr>
                            <tr>
                                <th class="ps-0">Estado:</th>
                                <td>
                                    <span class="badge bg-success">Activo</span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Acciones -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex gap-2">
                            <a href="?page=inventory&client=<?php echo $client['client_id']; ?>" class="btn btn-info text-white">
                                <i class="bi bi-pc-display me-2"></i> Ver Hardware
                            </a>
                            <a href="?page=users&client=<?php echo $client['client_id']; ?>" class="btn btn-warning text-white">
                                <i class="bi bi-people me-2"></i> Ver Usuarios
                            </a>
                            <button type="button" class="btn btn-danger" onclick="confirmDelete(<?php echo $client['client_id']; ?>)">
                                <i class="bi bi-trash me-2"></i> Eliminar Cliente
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(clientId) {
    if (confirm('¿Está seguro de que desea eliminar este cliente? Esta acción no se puede deshacer y eliminará todo el hardware y usuarios asociados.')) {
        window.location.href = '?page=clients&action=delete&id=' + clientId;
    }
}
</script> 