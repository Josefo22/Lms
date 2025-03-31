<?php 
$page_title = "Dashboard";

// Cargar controlador
require_once 'app/controllers/DashboardController.php';

// Instanciar el controlador
$dashboardController = new DashboardController();

// Obtener datos para el dashboard
$statsData = $dashboardController->getStats();

// Extraer los datos
$totalHardware = $statsData['hardware_count'] ?? 0;
$availableHardware = $statsData['available_count'] ?? 0;
$inRepairHardware = $statsData['repair_count'] ?? 0;
$retiredHardware = $statsData['retired_count'] ?? 0;

// Datos de solicitudes
$supportStats = $dashboardController->getSupportStats();
$newRequests = $supportStats['new'] ?? 0;
$inProgressRequests = $supportStats['in_progress'] ?? 0;
$resolvedRequests = $supportStats['resolved'] ?? 0;

// Actividades recientes
$recentActivities = $dashboardController->getRecentActivities();
?>

<div id="content">
    <div class="container-fluid px-4 py-4">
        <h2 class="mb-4 fw-bold">Dashboard</h2>
        
        <!-- Tarjetas de estadísticas -->
        <div class="row mb-5 g-4">
            <div class="col-md-3">
                <div class="card h-100 border-0 shadow-sm rounded">
                    <div class="card-body bg-primary text-white rounded">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h6 class="card-subtitle mb-2">Total Equipos</h6>
                                <h2 class="card-title display-4 fw-bold mb-0"><?php echo $totalHardware; ?></h2>
                            </div>
                            <i class="bi bi-pc-display display-5"></i>
                        </div>
                        <a href="?page=inventory" class="btn btn-light btn-sm fw-bold py-2 px-3 mt-3">VER DETALLES <i class="bi bi-arrow-right ms-1"></i></a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card h-100 border-0 shadow-sm rounded">
                    <div class="card-body bg-success text-white rounded">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h6 class="card-subtitle mb-2">Disponibles</h6>
                                <h2 class="card-title display-4 fw-bold mb-0"><?php echo $availableHardware; ?></h2>
                            </div>
                            <i class="bi bi-check-circle display-5"></i>
                        </div>
                        <a href="?page=inventory&status=In+Stock" class="btn btn-light btn-sm fw-bold py-2 px-3 mt-3">VER DETALLES <i class="bi bi-arrow-right ms-1"></i></a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card h-100 border-0 shadow-sm rounded">
                    <div class="card-body bg-warning text-dark rounded">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h6 class="card-subtitle mb-2">En Reparación</h6>
                                <h2 class="card-title display-4 fw-bold mb-0"><?php echo $inRepairHardware; ?></h2>
                            </div>
                            <i class="bi bi-tools display-5"></i>
                        </div>
                        <a href="?page=inventory&status=In+Repair" class="btn btn-dark btn-sm fw-bold py-2 px-3 mt-3">VER DETALLES <i class="bi bi-arrow-right ms-1"></i></a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card h-100 border-0 shadow-sm rounded">
                    <div class="card-body bg-danger text-white rounded">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h6 class="card-subtitle mb-2">Baja</h6>
                                <h2 class="card-title display-4 fw-bold mb-0"><?php echo $retiredHardware; ?></h2>
                            </div>
                            <i class="bi bi-trash display-5"></i>
                        </div>
                        <a href="?page=inventory&status=Retired" class="btn btn-light btn-sm fw-bold py-2 px-3 mt-3">VER DETALLES <i class="bi bi-arrow-right ms-1"></i></a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Solicitudes -->
        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm rounded">
                    <div class="card-header bg-white border-0">
                        <h5 class="card-title fw-bold mb-0">Solicitudes de Soporte</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row text-center g-3">
                            <div class="col-md-4">
                                <h2 class="fw-bold text-primary display-5"><?php echo $newRequests; ?></h2>
                                <p class="text-muted mb-0 fw-medium">Nuevas</p>
                            </div>
                            <div class="col-md-4">
                                <h2 class="fw-bold text-warning display-5"><?php echo $inProgressRequests; ?></h2>
                                <p class="text-muted mb-0 fw-medium">En Progreso</p>
                            </div>
                            <div class="col-md-4">
                                <h2 class="fw-bold text-success display-5"><?php echo $resolvedRequests; ?></h2>
                                <p class="text-muted mb-0 fw-medium">Resueltas</p>
                            </div>
                        </div>
                        <div class="d-flex justify-content-center mt-4">
                            <a href="?page=support" class="btn btn-primary fw-semibold px-4 py-2">Ver todas las solicitudes</a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card border-0 shadow-sm rounded h-100">
                    <div class="card-header bg-white border-0">
                        <h5 class="card-title fw-bold mb-0">Solicitudes Pendientes</h5>
                    </div>
                    <div class="card-body">
                        <?php if (isset($supportStats['pending_requests']) && count($supportStats['pending_requests']) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Tipo</th>
                                            <th>Usuario</th>
                                            <th>Prioridad</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($supportStats['pending_requests'] as $request): ?>
                                            <tr>
                                                <td><?php echo $request['request_id'] ?? ''; ?></td>
                                                <td><?php echo $request['type'] ?? ''; ?></td>
                                                <td><?php echo $request['user_name'] ?? ''; ?></td>
                                                <td>
                                                    <?php 
                                                    $priorityClass = 'bg-secondary';
                                                    switch($request['priority'] ?? '') {
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
                                                    <span class="badge <?php echo $priorityClass; ?>"><?php echo $request['priority'] ?? ''; ?></span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info text-center py-4 my-3">
                                No hay solicitudes pendientes
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Últimas Actividades -->
        <?php if (isset($recentActivities) && count($recentActivities) > 0): ?>
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded">
                    <div class="card-header bg-white border-0">
                        <h5 class="card-title fw-bold mb-0">Actividades Recientes</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Usuario</th>
                                        <th>Acción</th>
                                        <th>Detalles</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($recentActivities as $activity): ?>
                                    <tr>
                                        <td><?php echo date('d/m/Y H:i', strtotime($activity['timestamp'] ?? '')); ?></td>
                                        <td><?php echo $activity['user_name'] ?? ''; ?></td>
                                        <td>
                                            <?php 
                                            $badgeClass = 'bg-primary';
                                            echo '<span class="badge ' . $badgeClass . '">' . ($activity['action'] ?? '') . '</span>';
                                            ?>
                                        </td>
                                        <td><?php echo $activity['details'] ?? ''; ?></td>
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