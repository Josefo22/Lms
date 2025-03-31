<?php
$reportController = new ReportController();
?>

<div id="content" class="p-4 p-md-5 pt-5">
    <div class="container-fluid">
        <!-- Encabezado -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Reportes</h2>
        </div>

        <!-- Categorías de Reportes -->
        <div class="row">
            <!-- Reportes de Hardware -->
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <i class="bi bi-pc-display text-primary fs-1 me-3"></i>
                            <h4 class="card-title mb-0">Hardware</h4>
                        </div>
                        <p class="card-text text-muted mb-4">Reportes relacionados con el inventario de hardware y su estado.</p>
                        <div class="d-grid gap-2">
                            <a href="?page=reports&type=hardware_inventory" class="btn btn-outline-primary">
                                <i class="bi bi-list-check me-2"></i>Inventario General
                            </a>
                            <a href="?page=reports&type=hardware_status" class="btn btn-outline-primary">
                                <i class="bi bi-pie-chart me-2"></i>Estado del Hardware
                            </a>
                            <a href="?page=reports&type=hardware_assignments" class="btn btn-outline-primary">
                                <i class="bi bi-person-check me-2"></i>Asignaciones
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Reportes de Clientes -->
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <i class="bi bi-building text-success fs-1 me-3"></i>
                            <h4 class="card-title mb-0">Clientes</h4>
                        </div>
                        <p class="card-text text-muted mb-4">Informes sobre la distribución y uso de recursos por cliente.</p>
                        <div class="d-grid gap-2">
                            <a href="?page=reports&type=client_resources" class="btn btn-outline-success">
                                <i class="bi bi-graph-up me-2"></i>Recursos por Cliente
                            </a>
                            <a href="?page=reports&type=client_users" class="btn btn-outline-success">
                                <i class="bi bi-people me-2"></i>Usuarios por Cliente
                            </a>
                            <a href="?page=reports&type=client_support" class="btn btn-outline-success">
                                <i class="bi bi-ticket-detailed me-2"></i>Soporte por Cliente
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Reportes de Soporte -->
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <i class="bi bi-headset text-info fs-1 me-3"></i>
                            <h4 class="card-title mb-0">Soporte</h4>
                        </div>
                        <p class="card-text text-muted mb-4">Estadísticas y análisis de tickets de soporte.</p>
                        <div class="d-grid gap-2">
                            <a href="?page=reports&type=support_summary" class="btn btn-outline-info">
                                <i class="bi bi-clipboard-data me-2"></i>Resumen de Tickets
                            </a>
                            <a href="?page=reports&type=support_performance" class="btn btn-outline-info">
                                <i class="bi bi-speedometer2 me-2"></i>Rendimiento
                            </a>
                            <a href="?page=reports&type=support_trends" class="btn btn-outline-info">
                                <i class="bi bi-graph-up-arrow me-2"></i>Tendencias
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Reportes de Auditoría -->
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <i class="bi bi-clipboard-check text-warning fs-1 me-3"></i>
                            <h4 class="card-title mb-0">Auditoría</h4>
                        </div>
                        <p class="card-text text-muted mb-4">Informes de auditorías y control de inventario.</p>
                        <div class="d-grid gap-2">
                            <a href="?page=reports&type=audit_results" class="btn btn-outline-warning">
                                <i class="bi bi-list-stars me-2"></i>Resultados de Auditorías
                            </a>
                            <a href="?page=reports&type=audit_discrepancies" class="btn btn-outline-warning">
                                <i class="bi bi-exclamation-triangle me-2"></i>Discrepancias
                            </a>
                            <a href="?page=reports&type=audit_schedule" class="btn btn-outline-warning">
                                <i class="bi bi-calendar-check me-2"></i>Calendario
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Reportes de Envíos -->
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <i class="bi bi-truck text-danger fs-1 me-3"></i>
                            <h4 class="card-title mb-0">Envíos</h4>
                        </div>
                        <p class="card-text text-muted mb-4">Seguimiento y estado de envíos de hardware.</p>
                        <div class="d-grid gap-2">
                            <a href="?page=reports&type=shipment_status" class="btn btn-outline-danger">
                                <i class="bi bi-box-seam me-2"></i>Estado de Envíos
                            </a>
                            <a href="?page=reports&type=shipment_history" class="btn btn-outline-danger">
                                <i class="bi bi-clock-history me-2"></i>Historial
                            </a>
                            <a href="?page=reports&type=shipment_pending" class="btn btn-outline-danger">
                                <i class="bi bi-hourglass-split me-2"></i>Pendientes
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Reportes Personalizados -->
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <i class="bi bi-gear text-secondary fs-1 me-3"></i>
                            <h4 class="card-title mb-0">Personalizado</h4>
                        </div>
                        <p class="card-text text-muted mb-4">Crea reportes personalizados según tus necesidades.</p>
                        <div class="d-grid gap-2">
                            <a href="?page=reports&type=custom_new" class="btn btn-outline-secondary">
                                <i class="bi bi-plus-circle me-2"></i>Nuevo Reporte
                            </a>
                            <a href="?page=reports&type=custom_saved" class="btn btn-outline-secondary">
                                <i class="bi bi-save me-2"></i>Reportes Guardados
                            </a>
                            <a href="?page=reports&type=custom_schedule" class="btn btn-outline-secondary">
                                <i class="bi bi-clock me-2"></i>Programar
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> 