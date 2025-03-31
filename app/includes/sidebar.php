<nav id="sidebar">
    <div class="sidebar-header">
        <a href="<?php echo ROOT_URL; ?>" class="logo">
            <i class="bi bi-pc-display me-2"></i>
            <span>IT Inventory</span>
        </a>
    </div>
    <ul class="list-unstyled components">
        <li class="<?php echo $page === 'dashboard' ? 'active' : ''; ?>">
            <a href="<?php echo ROOT_URL; ?>index.php?page=dashboard">
                <i class="bi bi-speedometer2"></i> 
                <span>Dashboard</span>
            </a>
        </li>
        <li class="<?php echo $page === 'inventory' ? 'active' : ''; ?>">
            <a href="<?php echo ROOT_URL; ?>index.php?page=inventory">
                <i class="bi bi-box-seam"></i> 
                <span>Inventario</span>
            </a>
        </li>
        <li class="<?php echo ($page === 'clients' || $page === 'client_details' || $page === 'client_edit' || $page === 'client_create') ? 'active' : ''; ?>">
            <a href="<?php echo ROOT_URL; ?>index.php?page=clients">
                <i class="bi bi-building"></i> 
                <span>Clientes</span>
            </a>
        </li>
        <li class="<?php echo $page === 'users' ? 'active' : ''; ?>">
            <a href="<?php echo ROOT_URL; ?>index.php?page=users">
                <i class="bi bi-people"></i> 
                <span>Usuarios</span>
            </a>
        </li>
        <li class="<?php echo $page === 'support' ? 'active' : ''; ?>">
            <a href="<?php echo ROOT_URL; ?>index.php?page=support">
                <i class="bi bi-headset"></i> 
                <span>Soporte</span>
            </a>
        </li>
        <li class="<?php echo $page === 'reports' ? 'active' : ''; ?>">
            <a href="<?php echo ROOT_URL; ?>index.php?page=reports">
                <i class="bi bi-file-earmark-bar-graph"></i> 
                <span>Reportes</span>
            </a>
        </li>
        <li class="<?php echo $page === 'settings' ? 'active' : ''; ?>">
            <a href="<?php echo ROOT_URL; ?>index.php?page=settings">
                <i class="bi bi-gear"></i> 
                <span>Configuración</span>
            </a>
        </li>
    </ul>

    <div class="footer">
        <p><?php echo date('Y'); ?> &copy; <?php echo SITE_NAME; ?></p>
        <div class="d-flex justify-content-center mt-2">
            <a href="#" class="text-secondary mx-1"><i class="bi bi-github"></i></a>
            <a href="#" class="text-secondary mx-1"><i class="bi bi-question-circle"></i></a>
        </div>
    </div>
</nav>
