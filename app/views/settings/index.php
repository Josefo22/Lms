<?php
$settingsController = new SettingsController();
?>

<div id="content" class="p-4 p-md-5 pt-5">
    <div class="container-fluid">
        <!-- Encabezado -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Configuración del Sistema</h2>
        </div>

        <!-- Tarjetas de Configuración -->
        <div class="row">
            <!-- Configuración General -->
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <i class="bi bi-gear text-primary fs-1 me-3"></i>
                            <h4 class="card-title mb-0">General</h4>
                        </div>
                        <p class="card-text text-muted mb-4">Configuración general del sistema, incluyendo nombre de la empresa, zona horaria y preferencias.</p>
                        <div class="d-grid">
                            <a href="?page=settings&action=general" class="btn btn-outline-primary">
                                <i class="bi bi-sliders me-2"></i>Configurar
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notificaciones -->
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <i class="bi bi-bell text-success fs-1 me-3"></i>
                            <h4 class="card-title mb-0">Notificaciones</h4>
                        </div>
                        <p class="card-text text-muted mb-4">Configuración de notificaciones del sistema, alertas y recordatorios.</p>
                        <div class="d-grid">
                            <a href="?page=settings&action=notifications" class="btn btn-outline-success">
                                <i class="bi bi-bell-fill me-2"></i>Configurar
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Roles y Permisos -->
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <i class="bi bi-shield-lock text-info fs-1 me-3"></i>
                            <h4 class="card-title mb-0">Roles y Permisos</h4>
                        </div>
                        <p class="card-text text-muted mb-4">Gestión de roles de usuario y sus permisos en el sistema.</p>
                        <div class="d-grid">
                            <a href="?page=settings&action=roles" class="btn btn-outline-info">
                                <i class="bi bi-people me-2"></i>Configurar
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Configuración de Correo -->
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <i class="bi bi-envelope text-warning fs-1 me-3"></i>
                            <h4 class="card-title mb-0">Correo Electrónico</h4>
                        </div>
                        <p class="card-text text-muted mb-4">Configuración del servidor de correo y plantillas de notificaciones.</p>
                        <div class="d-grid">
                            <a href="?page=settings&action=email" class="btn btn-outline-warning">
                                <i class="bi bi-envelope-paper me-2"></i>Configurar
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Respaldos -->
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <i class="bi bi-cloud-arrow-up text-danger fs-1 me-3"></i>
                            <h4 class="card-title mb-0">Respaldos</h4>
                        </div>
                        <p class="card-text text-muted mb-4">Configuración de respaldos automáticos y gestión de copias de seguridad.</p>
                        <div class="d-grid">
                            <a href="?page=settings&action=backup" class="btn btn-outline-danger">
                                <i class="bi bi-cloud-download me-2"></i>Configurar
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Registro de Actividad -->
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <i class="bi bi-activity text-secondary fs-1 me-3"></i>
                            <h4 class="card-title mb-0">Registro de Actividad</h4>
                        </div>
                        <p class="card-text text-muted mb-4">Visualización y gestión del registro de actividades del sistema.</p>
                        <div class="d-grid">
                            <a href="?page=settings&action=activity" class="btn btn-outline-secondary">
                                <i class="bi bi-list-check me-2"></i>Ver Registro
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Información del Sistema -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-light py-3">
                <h5 class="card-title mb-0">
                    <i class="bi bi-info-circle me-2"></i>
                    Información del Sistema
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th class="ps-0" style="width: 200px;">Versión del Sistema:</th>
                                <td>1.0.0</td>
                            </tr>
                            <tr>
                                <th class="ps-0">Última Actualización:</th>
                                <td><?php echo date('d/m/Y H:i'); ?></td>
                            </tr>
                            <tr>
                                <th class="ps-0">Servidor:</th>
                                <td><?php echo $_SERVER['SERVER_SOFTWARE']; ?></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th class="ps-0" style="width: 200px;">PHP Version:</th>
                                <td><?php echo PHP_VERSION; ?></td>
                            </tr>
                            <tr>
                                <th class="ps-0">Base de Datos:</th>
                                <td>MySQL <?php echo mysqli_get_client_version(); ?></td>
                            </tr>
                            <tr>
                                <th class="ps-0">Zona Horaria:</th>
                                <td><?php echo date_default_timezone_get(); ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> 