<?php
// Título de la página
$page_title = "Detalles del Equipo";

// Incluir el controlador
require_once __DIR__ . '/../controllers/InventoryController.php';
$inventoryController = new InventoryController();

// Obtener ID del hardware desde la URL
$hardware_id = isset($_GET['id']) ? $_GET['id'] : null;

// Redireccionar si no hay ID
if (!$hardware_id) {
    header("Location: ?page=inventory");
    exit;
}

// Obtener detalles del hardware
$hardwareDetails = $inventoryController->getHardwareDetails($hardware_id);

// Verificar si el hardware existe
if (!$hardwareDetails['hardware']) {
    echo '<div class="alert alert-danger">Equipo no encontrado</div>';
    echo '<p><a href="?page=inventory" class="btn btn-primary">Volver al inventario</a></p>';
    exit;
}

$hardware = $hardwareDetails['hardware'];
$history = $hardwareDetails['history'];
?>

<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <div class="row align-items-center">
                        <div class="col">
                            <h5 class="mb-0">
                                <i class="bi bi-pc-display me-2"></i> 
                                Detalles del Equipo: <?php echo htmlspecialchars($hardware['model_name']); ?> 
                                <?php if($hardware['asset_tag']): ?>
                                    (<?php echo htmlspecialchars($hardware['asset_tag']); ?>)
                                <?php endif; ?>
                            </h5>
                        </div>
                        <div class="col text-end">
                            <a href="?page=inventory" class="btn btn-sm btn-light me-2">
                                <i class="bi bi-arrow-left me-1"></i> Volver
                            </a>
                            <a href="?page=inventory&action=edit&id=<?php echo $hardware_id; ?>" class="btn btn-sm btn-warning">
                                <i class="bi bi-pencil me-1"></i> Editar
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Información del equipo -->
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">Información General</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-bordered">
                                        <tr>
                                            <th style="width: 35%">Número de Serie</th>
                                            <td><?php echo htmlspecialchars($hardware['serial_number']); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Asset Tag</th>
                                            <td><?php echo htmlspecialchars($hardware['asset_tag'] ?? 'N/A'); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Modelo</th>
                                            <td><?php echo htmlspecialchars($hardware['model_name']); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Marca</th>
                                            <td><?php echo htmlspecialchars($hardware['brand_name']); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Categoría</th>
                                            <td><?php echo htmlspecialchars($hardware['category_name']); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Estado</th>
                                            <td>
                                                <?php 
                                                $statusClass = '';
                                                $statusText = '';
                                                
                                                switch($hardware['status']) {
                                                    case 'In Stock':
                                                        $statusClass = 'bg-success';
                                                        $statusText = 'Disponible';
                                                        break;
                                                    case 'In Use':
                                                        $statusClass = 'bg-primary';
                                                        $statusText = 'En Uso';
                                                        break;
                                                    case 'In Repair':
                                                        $statusClass = 'bg-warning';
                                                        $statusText = 'En Reparación';
                                                        break;
                                                    case 'In Transit':
                                                        $statusClass = 'bg-info';
                                                        $statusText = 'En Tránsito';
                                                        break;
                                                    case 'Retired':
                                                        $statusClass = 'bg-danger';
                                                        $statusText = 'Retirado';
                                                        break;
                                                    case 'Lost':
                                                        $statusClass = 'bg-dark';
                                                        $statusText = 'Perdido';
                                                        break;
                                                    default:
                                                        $statusClass = 'bg-secondary';
                                                        $statusText = $hardware['status'];
                                                }
                                                ?>
                                                <span class="badge <?php echo $statusClass; ?>"><?php echo $statusText; ?></span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Condición</th>
                                            <td>
                                                <?php 
                                                $conditionText = '';
                                                switch($hardware['condition_status']) {
                                                    case 'New':
                                                        $conditionText = 'Nuevo';
                                                        break;
                                                    case 'Good':
                                                        $conditionText = 'Bueno';
                                                        break;
                                                    case 'Fair':
                                                        $conditionText = 'Regular';
                                                        break;
                                                    case 'Poor':
                                                        $conditionText = 'Malo';
                                                        break;
                                                    default:
                                                        $conditionText = $hardware['condition_status'];
                                                }
                                                echo $conditionText; 
                                                ?>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Información adicional -->
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">Información Adicional</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-bordered">
                                        <tr>
                                            <th style="width: 35%">Fecha de Compra</th>
                                            <td><?php echo date('d/m/Y', strtotime($hardware['purchase_date'])); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Garantía Hasta</th>
                                            <td>
                                                <?php 
                                                if ($hardware['warranty_expiry_date']) {
                                                    echo date('d/m/Y', strtotime($hardware['warranty_expiry_date']));
                                                    
                                                    // Verificar si la garantía ha expirado
                                                    $today = new DateTime();
                                                    $warranty = new DateTime($hardware['warranty_expiry_date']);
                                                    if ($today > $warranty) {
                                                        echo ' <span class="badge bg-danger">Expirada</span>';
                                                    } else {
                                                        // Calcular días restantes
                                                        $diff = $today->diff($warranty);
                                                        $days = $diff->days;
                                                        
                                                        if ($days <= 30) {
                                                            echo ' <span class="badge bg-warning">'.($days).' días restantes</span>';
                                                        }
                                                    }
                                                } else {
                                                    echo 'No especificada';
                                                }
                                                ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Cliente Actual</th>
                                            <td>
                                                <?php if($hardware['client_name']): ?>
                                                    <?php echo htmlspecialchars($hardware['client_name']); ?>
                                                <?php else: ?>
                                                    <span class="text-muted">No asignado</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Ubicación</th>
                                            <td>
                                                <?php if($hardware['location_name']): ?>
                                                    <?php echo htmlspecialchars($hardware['location_name']); ?>
                                                <?php else: ?>
                                                    <span class="text-muted">No especificada</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Usuario Actual</th>
                                            <td>
                                                <?php if($hardware['user_name']): ?>
                                                    <?php echo htmlspecialchars($hardware['user_name']); ?>
                                                <?php else: ?>
                                                    <span class="text-muted">No asignado</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Fecha de Creación</th>
                                            <td><?php echo date('d/m/Y H:i', strtotime($hardware['created_at'])); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Última Actualización</th>
                                            <td><?php echo date('d/m/Y H:i', strtotime($hardware['updated_at'])); ?></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Notas -->
                        <div class="col-md-12">
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">Notas</h6>
                                </div>
                                <div class="card-body">
                                    <?php if(trim($hardware['notes']) !== ''): ?>
                                        <div class="p-3 bg-light rounded">
                                            <?php echo nl2br(htmlspecialchars($hardware['notes'])); ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-muted">No hay notas disponibles</div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Historial de asignaciones -->
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">Historial de Asignaciones</h6>
                                </div>
                                <div class="card-body">
                                    <?php if(count($history) > 0): ?>
                                        <div class="table-responsive">
                                            <table class="table table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>Fecha de Asignación</th>
                                                        <th>Fecha de Devolución</th>
                                                        <th>Cliente</th>
                                                        <th>Usuario</th>
                                                        <th>Asignado por</th>
                                                        <th>Devolución recibida por</th>
                                                        <th>Notas</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach($history as $record): ?>
                                                    <tr>
                                                        <td><?php echo date('d/m/Y', strtotime($record['assigned_date'])); ?></td>
                                                        <td>
                                                            <?php 
                                                            if($record['return_date']) {
                                                                echo date('d/m/Y', strtotime($record['return_date']));
                                                            } else {
                                                                echo '<span class="badge bg-primary">Activo</span>';
                                                            }
                                                            ?>
                                                        </td>
                                                        <td><?php echo htmlspecialchars($record['client_name'] ?? 'N/A'); ?></td>
                                                        <td><?php echo htmlspecialchars($record['user_name'] ?? 'N/A'); ?></td>
                                                        <td><?php echo htmlspecialchars($record['assigned_by_name']); ?></td>
                                                        <td>
                                                            <?php 
                                                            if($record['return_received_by_name']) {
                                                                echo htmlspecialchars($record['return_received_by_name']);
                                                            } else {
                                                                echo 'N/A';
                                                            }
                                                            ?>
                                                        </td>
                                                        <td>
                                                            <?php if(trim($record['notes']) !== ''): ?>
                                                                <button type="button" class="btn btn-sm btn-info" data-bs-toggle="popover" title="Notas" data-bs-content="<?php echo htmlspecialchars($record['notes']); ?>">
                                                                    <i class="bi bi-info-circle"></i>
                                                                </button>
                                                            <?php else: ?>
                                                                -
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php else: ?>
                                        <div class="alert alert-info">
                                            <i class="bi bi-info-circle me-2"></i> Este equipo no tiene historial de asignaciones.
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Inicializar popovers para las notas
document.addEventListener('DOMContentLoaded', function() {
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function(popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl, {
            html: true,
            trigger: 'click'
        });
    });
});
</script> 