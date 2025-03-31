<?php
// Obtener el ID del hardware
$hardware_id = $_GET['id'] ?? null;

if (!$hardware_id) {
    $_SESSION['message'] = 'ID de hardware no especificado';
    $_SESSION['message_type'] = 'danger';
    header('Location: ?page=inventory');
    exit;
}

// Obtener detalles del hardware
$hardwareDetails = $inventoryController->getHardwareDetails($hardware_id);

if (isset($hardwareDetails['error'])) {
    $_SESSION['message'] = $hardwareDetails['error'];
    $_SESSION['message_type'] = 'danger';
    header('Location: ?page=inventory');
    exit;
}

$hardware = $hardwareDetails['hardware'];
$history = $hardwareDetails['history'];
?>

<div id="content" class="p-4 p-md-5 pt-5">
    <div class="container-fluid">
        <!-- Encabezado principal y botones de acción -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Detalles del Equipo</h2>
            <div>
                <a href="?page=inventory&action=edit&id=<?php echo $hardware_id; ?>" class="btn btn-warning me-2">
                    <i class="bi bi-pencil me-1"></i> Editar
                </a>
                <a href="?page=inventory" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Volver al Inventario
                </a>
            </div>
        </div>

        <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-<?php echo $_SESSION['message_type']; ?> alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['message']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php 
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
        endif; 
        ?>

        <div class="row">
            <!-- Información básica -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Información Básica</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless">
                            <tr>
                                <th class="w-25">Serial:</th>
                                <td><?php echo htmlspecialchars($hardware['serial_number']); ?></td>
                            </tr>
                            <tr>
                                <th>Asset Tag:</th>
                                <td><?php echo htmlspecialchars($hardware['asset_tag'] ?? 'N/A'); ?></td>
                            </tr>
                            <tr>
                                <th>Modelo:</th>
                                <td>
                                    <?php echo htmlspecialchars($hardware['model_name']); ?>
                                    <br>
                                    <small class="text-muted"><?php echo htmlspecialchars($hardware['brand_name']); ?></small>
                                </td>
                            </tr>
                            <tr>
                                <th>Estado:</th>
                                <td>
                                    <span class="badge bg-<?php 
                                        echo match($hardware['status']) {
                                            'In Stock' => 'success',
                                            'In Use' => 'primary',
                                            'In Repair' => 'warning',
                                            'In Transit' => 'info',
                                            'Retired' => 'secondary',
                                            'Lost' => 'danger',
                                            default => 'secondary'
                                        };
                                    ?>">
                                        <?php echo htmlspecialchars($hardware['status']); ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>Condición:</th>
                                <td>
                                    <span class="badge bg-<?php 
                                        echo match($hardware['condition_status']) {
                                            'New' => 'success',
                                            'Good' => 'primary',
                                            'Fair' => 'warning',
                                            'Poor' => 'danger',
                                            default => 'secondary'
                                        };
                                    ?>">
                                        <?php echo htmlspecialchars($hardware['condition_status']); ?>
                                    </span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Fechas -->
                <div class="card border-0 shadow-sm rounded mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Fechas</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless">
                            <tr>
                                <th class="w-25">Compra:</th>
                                <td><?php echo date('d/m/Y', strtotime($hardware['purchase_date'])); ?></td>
                            </tr>
                            <tr>
                                <th>Garantía:</th>
                                <td>
                                    <?php if ($hardware['warranty_expiry_date']): ?>
                                        <?php echo date('d/m/Y', strtotime($hardware['warranty_expiry_date'])); ?>
                                        <?php
                                        $today = new DateTime();
                                        $warranty = new DateTime($hardware['warranty_expiry_date']);
                                        if ($warranty < $today) {
                                            echo '<span class="badge bg-danger ms-2">Vencida</span>';
                                        } elseif ($warranty->diff($today)->days <= 30) {
                                            echo '<span class="badge bg-warning ms-2">Por vencer</span>';
                                        }
                                        ?>
                                    <?php else: ?>
                                        N/A
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Registro:</th>
                                <td><?php echo date('d/m/Y H:i', strtotime($hardware['created_at'])); ?></td>
                            </tr>
                            <tr>
                                <th>Última actualización:</th>
                                <td><?php echo date('d/m/Y H:i', strtotime($hardware['updated_at'])); ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Asignación y Ubicación -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Asignación y Ubicación</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless">
                            <tr>
                                <th class="w-25">Cliente:</th>
                                <td><?php echo htmlspecialchars($hardware['client_name'] ?? 'Sin asignar'); ?></td>
                            </tr>
                            <tr>
                                <th>Ubicación:</th>
                                <td><?php echo htmlspecialchars($hardware['location_name'] ?? 'Sin asignar'); ?></td>
                            </tr>
                            <tr>
                                <th>Usuario:</th>
                                <td><?php echo htmlspecialchars($hardware['user_name'] ?? 'Sin asignar'); ?></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Notas -->
                <div class="card border-0 shadow-sm rounded mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Notas</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($hardware['notes']): ?>
                            <p class="mb-0"><?php echo nl2br(htmlspecialchars($hardware['notes'])); ?></p>
                        <?php else: ?>
                            <p class="text-muted mb-0">No hay notas registradas</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Historial de Asignaciones -->
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Historial de Asignaciones</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($history)): ?>
                            <p class="text-muted mb-0">No hay registros de asignaciones</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Fecha</th>
                                            <th>Usuario</th>
                                            <th>Asignado por</th>
                                            <th>Estado</th>
                                            <th>Notas</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($history as $record): ?>
                                        <tr>
                                            <td><?php echo date('d/m/Y H:i', strtotime($record['assigned_date'])); ?></td>
                                            <td><?php echo htmlspecialchars($record['user_name']); ?></td>
                                            <td><?php echo htmlspecialchars($record['assigned_by_name']); ?></td>
                                            <td>
                                                <span class="badge bg-<?php 
                                                    echo match($record['status']) {
                                                        'Assigned' => 'success',
                                                        'Returned' => 'info',
                                                        'Pending Return' => 'warning',
                                                        default => 'secondary'
                                                    };
                                                ?>">
                                                    <?php echo htmlspecialchars($record['status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo htmlspecialchars($record['assignment_notes'] ?? ''); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> 