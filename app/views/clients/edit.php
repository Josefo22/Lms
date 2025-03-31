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
?>

<div id="content" class="p-4 p-md-5 pt-5">
    <div class="container-fluid">
        <!-- Encabezado -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Editar Cliente</h2>
            <a href="?page=clients" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i> Volver
            </a>
        </div>

        <!-- Formulario -->
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <form action="?page=clients&action=update" method="POST" id="editClientForm">
                    <input type="hidden" name="client_id" value="<?php echo $client['client_id']; ?>">
                    
                    <div class="row">
                        <!-- Información General -->
                        <div class="col-md-6">
                            <h5 class="card-title mb-3">Información General</h5>
                            
                            <div class="mb-3">
                                <label for="client_name" class="form-label">Nombre del Cliente *</label>
                                <input type="text" class="form-control" id="client_name" name="client_name" 
                                       value="<?php echo htmlspecialchars($client['client_name']); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="contact_person" class="form-label">Persona de Contacto</label>
                                <input type="text" class="form-control" id="contact_person" name="contact_person"
                                       value="<?php echo htmlspecialchars($client['contact_person']); ?>">
                            </div>

                            <div class="mb-3">
                                <label for="contact_email" class="form-label">Email de Contacto</label>
                                <input type="email" class="form-control" id="contact_email" name="contact_email"
                                       value="<?php echo htmlspecialchars($client['contact_email']); ?>">
                            </div>
                        </div>

                        <!-- Información de Contacto -->
                        <div class="col-md-6">
                            <h5 class="card-title mb-3">Información de Contacto</h5>
                            
                            <div class="mb-3">
                                <label for="contact_phone" class="form-label">Teléfono de Contacto</label>
                                <input type="tel" class="form-control" id="contact_phone" name="contact_phone"
                                       value="<?php echo htmlspecialchars($client['contact_phone']); ?>">
                            </div>

                            <div class="mb-3">
                                <label for="address" class="form-label">Dirección</label>
                                <textarea class="form-control" id="address" name="address" rows="4"><?php echo htmlspecialchars($client['address']); ?></textarea>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="d-flex justify-content-end gap-2">
                        <a href="?page=clients" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle me-2"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-2"></i> Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('editClientForm').addEventListener('submit', function(e) {
    const requiredFields = ['client_name'];
    let missingFields = [];

    requiredFields.forEach(field => {
        if (!document.getElementById(field).value.trim()) {
            missingFields.push(field);
        }
    });

    if (missingFields.length > 0) {
        e.preventDefault();
        alert('Por favor complete los campos requeridos marcados con *');
    }
});
</script> 