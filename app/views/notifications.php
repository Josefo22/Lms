<?php 
$page_title = "Notificaciones";

// Cargar controlador
require_once 'app/controllers/NotificationController.php';

// Instanciar el controlador
$notificationController = new NotificationController();

// Obtener todas las notificaciones
$notifications = $notificationController->getNotifications();

// Obtener datos específicos
$warrantyAlerts = $notificationController->getWarrantyAlerts();
$supportRequests = $notificationController->getPendingSupportRequests();
$repairEquipment = $notificationController->getRepairEquipment();
$recentAssignments = $notificationController->getRecentAssignments();
?>

<div id="content">
    <div class="container-fluid px-4 py-4">
        <h2 class="mb-4 fw-bold">Notificaciones</h2>
        
        <!-- Resumen de notificaciones -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <i class="bi bi-bell fs-4 text-primary me-3"></i>
                            <h5 class="fw-bold mb-0">Centro de Notificaciones</h5>
                        </div>
                        <p class="text-muted">Este es el centro de notificaciones donde puedes ver alertas importantes sobre el inventario IT.</p>
                        
                        <?php if ($notifications['total'] == 0): ?>
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle me-2"></i> No hay notificaciones nuevas en este momento.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <?php if (count($warrantyAlerts) > 0): ?>
        <!-- Garantías por vencer -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded">
                    <div class="card-header bg-white d-flex align-items-center">
                        <div class="notification-icon bg-warning-subtle rounded-circle p-2 me-3">
                            <i class="bi bi-exclamation-circle text-warning"></i>
                        </div>
                        <h5 class="card-title fw-bold mb-0">Garantías por vencer</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Equipo</th>
                                        <th>Fecha de vencimiento</th>
                                        <th>Días restantes</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($warrantyAlerts as $item): ?>
                                    <tr>
                                        <td><?php echo $item['asset_tag']; ?></td>
                                        <td>
                                            <div class="fw-medium"><?php echo $item['brand_name'] . ' ' . $item['model_name']; ?></div>
                                        </td>
                                        <td><?php echo date('d/m/Y', strtotime($item['warranty_expiry_date'])); ?></td>
                                        <td>
                                            <span class="badge bg-warning"><?php echo $item['days_left']; ?> días</span>
                                        </td>
                                        <td>
                                            <a href="?page=inventory_details&id=<?php echo $item['hardware_id']; ?>" class="btn btn-sm btn-primary">Ver detalles</a>
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
        <?php endif; ?>
        
        <?php if (count($supportRequests) > 0): ?>
        <!-- Solicitudes de soporte pendientes -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded">
                    <div class="card-header bg-white d-flex align-items-center">
                        <div class="notification-icon bg-danger-subtle rounded-circle p-2 me-3">
                            <i class="bi bi-headset text-danger"></i>
                        </div>
                        <h5 class="card-title fw-bold mb-0">Solicitudes de soporte pendientes</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Tipo</th>
                                        <th>Usuario</th>
                                        <th>Estado</th>
                                        <th>Prioridad</th>
                                        <th>Fecha</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($supportRequests as $request): ?>
                                    <tr>
                                        <td><?php echo $request['request_id']; ?></td>
                                        <td><?php echo $request['request_type']; ?></td>
                                        <td><?php echo $request['user_name']; ?></td>
                                        <td>
                                            <?php 
                                            $statusClass = 'bg-secondary';
                                            switch($request['status']) {
                                                case 'New':
                                                    $statusClass = 'bg-info';
                                                    break;
                                                case 'Assigned':
                                                    $statusClass = 'bg-primary';
                                                    break;
                                                case 'In Progress':
                                                    $statusClass = 'bg-warning';
                                                    break;
                                            }
                                            ?>
                                            <span class="badge <?php echo $statusClass; ?>"><?php echo $request['status']; ?></span>
                                        </td>
                                        <td>
                                            <?php 
                                            $priorityClass = 'bg-secondary';
                                            switch($request['priority']) {
                                                case 'High':
                                                    $priorityClass = 'bg-danger';
                                                    break;
                                                case 'Medium':
                                                    $priorityClass = 'bg-warning';
                                                    break;
                                                case 'Low':
                                                    $priorityClass = 'bg-success';
                                                    break;
                                            }
                                            ?>
                                            <span class="badge <?php echo $priorityClass; ?>"><?php echo $request['priority']; ?></span>
                                        </td>
                                        <td><?php echo date('d/m/Y', strtotime($request['created_at'])); ?></td>
                                        <td>
                                            <a href="?page=support_details&id=<?php echo $request['request_id']; ?>" class="btn btn-sm btn-primary">Ver detalles</a>
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
        <?php endif; ?>
        
        <?php if (count($repairEquipment) > 0): ?>
        <!-- Equipos en reparación -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded">
                    <div class="card-header bg-white d-flex align-items-center">
                        <div class="notification-icon bg-primary-subtle rounded-circle p-2 me-3">
                            <i class="bi bi-tools text-primary"></i>
                        </div>
                        <h5 class="card-title fw-bold mb-0">Equipos en reparación</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Equipo</th>
                                        <th>Serie</th>
                                        <th>Desde</th>
                                        <th>Notas</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($repairEquipment as $item): ?>
                                    <tr>
                                        <td><?php echo $item['asset_tag']; ?></td>
                                        <td><?php echo $item['brand_name'] . ' ' . $item['model_name']; ?></td>
                                        <td><?php echo $item['serial_number']; ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($item['updated_at'])); ?></td>
                                        <td>
                                            <?php if(!empty($item['notes'])): ?>
                                                <span class="text-truncate d-inline-block" style="max-width: 200px;"><?php echo $item['notes']; ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">Sin notas</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="?page=inventory_details&id=<?php echo $item['hardware_id']; ?>" class="btn btn-sm btn-primary">Ver detalles</a>
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
        <?php endif; ?>
        
        <?php if (count($recentAssignments) > 0): ?>
        <!-- Asignaciones recientes -->
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded">
                    <div class="card-header bg-white d-flex align-items-center">
                        <div class="notification-icon bg-success-subtle rounded-circle p-2 me-3">
                            <i class="bi bi-check-circle text-success"></i>
                        </div>
                        <h5 class="card-title fw-bold mb-0">Asignaciones recientes</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Equipo</th>
                                        <th>Asignado a</th>
                                        <th>Fecha</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($recentAssignments as $item): ?>
                                    <tr>
                                        <td><?php echo $item['asset_tag']; ?></td>
                                        <td><?php echo $item['model_name']; ?></td>
                                        <td><?php echo $item['user_name']; ?></td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($item['assigned_date'])); ?></td>
                                        <td>
                                            <a href="?page=inventory_details&id=<?php echo $item['hardware_id']; ?>" class="btn btn-sm btn-primary">Ver equipo</a>
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
        <?php endif; ?>
    </div>
</div> 