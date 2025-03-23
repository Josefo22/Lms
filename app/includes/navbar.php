<?php
// Cargar controlador de notificaciones
require_once 'app/controllers/NotificationController.php';
$notificationController = new NotificationController();
$notifications = $notificationController->getNotifications();
?>
<nav class="navbar navbar-expand-lg fixed-top">
    <div class="container-fluid px-4">
        <a class="navbar-brand d-flex align-items-center" href="<?php echo ROOT_URL; ?>">
            <i class="bi bi-pc-display me-2"></i> IT Inventory
        </a>
        
        <button id="sidebarToggle" class="btn d-lg-none" type="button">
            <i class="bi bi-list"></i>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item dropdown me-3">
                    <a class="nav-link dropdown-toggle position-relative" href="#" id="notificationsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-bell fs-5"></i>
                        <?php if ($notifications['total'] > 0): ?>
                        <span class="badge bg-danger rounded-pill position-absolute"><?php echo $notifications['total']; ?></span>
                        <?php endif; ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm" aria-labelledby="notificationsDropdown">
                        <li><h6 class="dropdown-header fw-bold">Notificaciones</h6></li>
                        
                        <?php if (count($notifications['items']) > 0): ?>
                            <?php foreach ($notifications['items'] as $notification): ?>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center py-2" href="<?php echo $notification['link']; ?>">
                                        <div class="notification-icon <?php echo $notification['bg_class']; ?> rounded-circle p-2 me-3">
                                            <i class="bi bi-<?php echo $notification['icon']; ?> <?php echo $notification['icon_class']; ?>"></i>
                                        </div>
                                        <div>
                                            <div class="fw-semibold"><?php echo $notification['title']; ?></div>
                                            <small class="text-muted"><?php echo $notification['details']; ?></small>
                                        </div>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-center fw-semibold text-primary" href="?page=notifications">Ver todas</a></li>
                        <?php else: ?>
                            <li>
                                <div class="dropdown-item text-center py-3">
                                    <span class="text-muted">No hay notificaciones nuevas</span>
                                </div>
                            </li>
                        <?php endif; ?>
                    </ul>
                </li>
                
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="profileDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <?php if(isset($_SESSION['profile_image']) && !empty($_SESSION['profile_image'])): ?>
                            <img src="<?php echo ROOT_URL . 'assets/img/profiles/' . $_SESSION['profile_image']; ?>" class="user-avatar me-2 border border-2 border-white shadow-sm" alt="Profile">
                        <?php else: ?>
                            <i class="bi bi-person-circle fs-5 me-2"></i>
                        <?php endif; ?>
                        <span class="d-none d-md-inline fw-semibold"><?php echo isset($_SESSION['username']) ? $_SESSION['username'] : 'Usuario'; ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow" style="min-width: 280px;" aria-labelledby="profileDropdown">
                        <li>
                            <div class="dropdown-header d-flex flex-column align-items-center py-3">
                                <?php if(isset($_SESSION['profile_image']) && !empty($_SESSION['profile_image'])): ?>
                                    <img src="<?php echo ROOT_URL . 'assets/img/profiles/' . $_SESSION['profile_image']; ?>" class="user-avatar mb-2 border border-3 border-white shadow" style="width: 80px; height: 80px;" alt="Profile">
                                <?php else: ?>
                                    <i class="bi bi-person-circle mb-2" style="font-size: 4rem;"></i>
                                <?php endif; ?>
                                <span class="fw-bold fs-5 mt-2"><?php echo isset($_SESSION['username']) ? $_SESSION['username'] : 'Usuario'; ?></span>
                                <?php if(!empty($_SESSION['email'])): ?>
                                    <small class="text-muted"><?php echo $_SESSION['email']; ?></small>
                                <?php endif; ?>
                                <?php if(!empty($_SESSION['job_title'])): ?>
                                    <span class="badge bg-primary mt-2 py-2 px-3"><?php echo $_SESSION['job_title']; ?></span>
                                <?php endif; ?>
                            </div>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item py-2" href="<?php echo ROOT_URL; ?>index.php?page=profile"><i class="bi bi-person me-2 text-primary"></i> Mi Perfil</a></li>
                        <li><a class="dropdown-item py-2" href="<?php echo ROOT_URL; ?>index.php?page=settings"><i class="bi bi-gear me-2 text-primary"></i> Configuración</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item py-2 text-danger" href="<?php echo ROOT_URL; ?>logout.php"><i class="bi bi-box-arrow-right me-2"></i> Cerrar sesión</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<style>
.notification-icon {
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.bg-primary-subtle {
    background-color: rgba(13, 110, 253, 0.1);
}
.bg-warning-subtle {
    background-color: rgba(255, 193, 7, 0.1);
}
.bg-success-subtle {
    background-color: rgba(25, 135, 84, 0.1);
}
.bg-danger-subtle {
    background-color: rgba(220, 53, 69, 0.1);
}
</style>
