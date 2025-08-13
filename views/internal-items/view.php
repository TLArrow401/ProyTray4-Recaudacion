<?php
// Verificar acceso primero - ANTES de cualquier output
require_once __DIR__ . '/../../middleware/AuthMiddleware.php';
AuthMiddleware::requireUserManagementAccess();

// Incluir el controlador
require_once __DIR__ . '/../../controllers/InternalItemsController.php';

$internalItemsController = new InternalItemsController();

// Obtener ID del parámetro
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Usar el controlador para obtener los datos
$result = $internalItemsController->view(['id' => $id]);

// Si hay error de permisos o redirección, manejarla
if (!$result['success'] && isset($result['redirect'])) {
    // La sesión ya está iniciada por AuthMiddleware
    $_SESSION['message'] = $result['message'];
    $_SESSION['messageType'] = 'error';
    header('Location: ' . $result['redirect']);
    exit;
}

$internal_item = $result['internal_item'] ?? null;

// Incluir header y layouts
require_once __DIR__ . '/../layouts/header.php';
include __DIR__ . '/../layouts/navigation.php';
include __DIR__ . '/../layouts/navigation-top.php';
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title">
                            <i class="ri ri-eye-line mr-1"></i>
                            Detalles del Rubro Interno
                        </h5>
                        <div>
                            <a href="edit.php?id=<?php echo $id; ?>" class="btn btn-outline-primary mr-2">
                                <i class="ri ri-edit-line mr-1"></i>
                                Editar
                            </a>
                            <a href="index.php" class="btn btn-outline-secondary">
                                <i class="ri ri-arrow-left-line mr-1"></i>
                                Volver al listado
                            </a>
                        </div>
                    </div>
                    
                    <div class="card-body">
                        <!-- Mostrar mensajes -->
                        <?php if (isset($_SESSION['message'])): ?>
                        <div class="alert alert-<?php echo $_SESSION['messageType'] ?? 'info'; ?> alert-dismissible fade show" role="alert">
                            <?php 
                            echo htmlspecialchars($_SESSION['message']); 
                            unset($_SESSION['message'], $_SESSION['messageType']);
                            ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <?php endif; ?>

                        <?php if ($internal_item): ?>
                        <div class="row gy-4">
                            <div class="col-md-12">
                                <div class="row g-2">
                                    <div class="col-sm-3">
                                        <strong>ID:</strong>
                                    </div>
                                    <div class="col-sm-9">
                                        <?php echo htmlspecialchars($internal_item['id']); ?>
                                    </div>
                                
                                    <div class="col-sm-3">
                                        <strong>Nombre:</strong>
                                    </div>
                                    <div class="col-sm-9">
                                        <?php echo htmlspecialchars($internal_item['name']); ?>
                                    </div>
                                    <div class="col-sm-3">
                                        <strong>Número de Cobros:</strong>
                                    </div>
                                    <div class="col-sm-9">
                                        <?php echo number_format($internal_item['payment_count'], 2); ?>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <?php else: ?>
                        <div class="text-center py-4">
                            <div class="mb-3">
                                <i class="ri ri-file-warning-line" style="font-size: 48px; color: #6c757d;"></i>
                            </div>
                            <h5 class="text-muted">Rubro Interno no encontrado</h5>
                            <p class="text-muted">
                                El rubro interno que buscas no existe o ha sido eliminado.
                                <br>
                                <a href="index.php" class="btn btn-primary mt-2">
                                    <i class="ri ri-arrow-left-line mr-1"></i>
                                    Volver al listado
                                </a>
                            </p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmación para eliminar -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirmar Eliminación</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de que deseas eliminar el rubro interno <strong id="itemName"></strong>?</p>
                <p class="text-muted">Esta acción no se puede deshacer.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Eliminar</button>
            </div>
        </div>
    </div>
</div>

<script>
let itemToDelete = null;

function confirmDelete(id, name) {
    itemToDelete = id;
    document.getElementById('itemName').textContent = name;
    $('#deleteModal').modal('show');
}

document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
    if (itemToDelete) {
        // Realizar petición AJAX para eliminar
        fetch('delete.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'id=' + itemToDelete
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                $('#deleteModal').modal('hide');
                // Redirigir al listado con mensaje de éxito
                if (data.message) {
                    sessionStorage.setItem('deleteMessage', data.message);
                }
                window.location.href = 'index.php';
            } else {
                alert('Error al eliminar el rubro interno: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error de conexión al eliminar el rubro interno');
        });
    }
});

// Mostrar mensaje de eliminación si existe en sessionStorage
document.addEventListener('DOMContentLoaded', function() {
    const deleteMessage = sessionStorage.getItem('deleteMessage');
    if (deleteMessage) {
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-success alert-dismissible fade show';
        alertDiv.innerHTML = `
            ${deleteMessage}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        `;
        
        const cardBody = document.querySelector('.card-body');
        cardBody.insertBefore(alertDiv, cardBody.firstChild);
        
        sessionStorage.removeItem('deleteMessage');
    }
});
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>