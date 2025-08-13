<?php
// Verificar acceso primero - ANTES de cualquier output
require_once __DIR__ . '/../../middleware/AuthMiddleware.php';
AuthMiddleware::requireUserManagementAccess();

// Incluir el controlador
require_once __DIR__ . '/../../controllers/CashRegistersController.php';

$cashRegistersController = new CashRegistersController();

// Obtener ID desde la URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$id) {
    $_SESSION['message'] = 'ID de caja registradora no válido';
    $_SESSION['messageType'] = 'danger';
    header('Location: index.php');
    exit;
}

// Obtener datos de la caja registradora
$result = $cashRegistersController->view(['id' => $id]);

// Si hay error al cargar los datos, redirigir
if (!$result['success'] && isset($result['redirect'])) {
    $_SESSION['message'] = $result['message'];
    $_SESSION['messageType'] = 'danger';
    header('Location: ' . $result['redirect']);
    exit;
}

$cash_register = $result['cash_register'];

// Función para obtener el color del estado
function getStatusColor($status) {
    switch($status) {
        case 'active': return 'success';
        case 'inactive': return 'secondary';
        case 'maintenance': return 'warning';
        default: return 'secondary';
    }
}

// Función para obtener el texto del estado
function getStatusText($status) {
    switch($status) {
        case 'active': return 'Activa';
        case 'inactive': return 'Inactiva';
        case 'maintenance': return 'Mantenimiento';
        default: return ucfirst($status);
    }
}

// Función para obtener el icono del estado
function getStatusIcon($status) {
    switch($status) {
        case 'active': return '🟢';
        case 'inactive': return '⚫';
        case 'maintenance': return '🟡';
        default: return '⚪';
    }
}

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
                            <i class="ri ri-cash-line mr-1"></i>
                            Detalles de Caja Registradora
                        </h5>
                        <div>
                            <a href="edit.php?id=<?php echo $cash_register['id']; ?>" class="btn btn-primary">
                                <i class="ri ri-edit-line mr-1"></i>
                                Editar
                            </a>
                            <a href="index.php" class="btn btn-outline-secondary ml-1">
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
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php endif; ?>

                        <div class="row">
                            <div class="col-md-8">
                                <!-- Información principal -->
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h6 class="card-title mb-0">
                                            <i class="ri ri-information-line mr-1"></i>
                                            Información General
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label text-muted">ID de la Caja</label>
                                                    <div class="form-control-plaintext">
                                                        <strong>#<?php echo htmlspecialchars($cash_register['id']); ?></strong>
                                                    </div>
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <label class="form-label text-muted">Nombre de la Caja</label>
                                                    <div class="form-control-plaintext">
                                                        <h5 class="text-primary mb-0">
                                                            <?php echo htmlspecialchars($cash_register['name']); ?>
                                                        </h5>
                                                    </div>
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <label class="form-label text-muted">Estado Operativo</label>
                                                    <div class="form-control-plaintext">
                                                        <span class="badge text-bg-<?php echo getStatusColor($cash_register['status']); ?> p-2">
                                                            <?php echo getStatusIcon($cash_register['status']); ?>
                                                            <?php echo getStatusText($cash_register['status']); ?>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label text-muted">Usuario Asignado</label>
                                                    <div class="form-control-plaintext">
                                                        <?php if (!empty($cash_register['user_name'])): ?>
                                                        <div class="d-flex align-items-center">
                                                            <div class="avatar-sm bg-primary rounded-circle d-flex align-items-center justify-content-center mr-2">
                                                                <i class="ri ri-user-line text-white"></i>
                                                            </div>
                                                            <div>
                                                                <strong><?php echo htmlspecialchars($cash_register['user_name']); ?></strong>
                                                                <?php if (!empty($cash_register['user_email'])): ?>
                                                                <br><small class="text-muted"><?php echo htmlspecialchars($cash_register['user_email']); ?></small>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                        <?php else: ?>
                                                        <span class="text-muted">No asignado</span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <label class="form-label text-muted">Fecha de Creación</label>
                                                    <div class="form-control-plaintext">
                                                        <?php if (!empty($cash_register['created_at'])): ?>
                                                            <i class="ri ri-calendar-line mr-1"></i>
                                                            <?php echo date('d/m/Y', strtotime($cash_register['created_at'])); ?>
                                                            <br>
                                                            <small class="text-muted">
                                                                <i class="ri ri-time-line mr-1"></i>
                                                                <?php echo date('H:i:s', strtotime($cash_register['created_at'])); ?>
                                                            </small>
                                                        <?php else: ?>
                                                            <span class="text-muted">No disponible</span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <label class="form-label text-muted">Última Actualización</label>
                                                    <div class="form-control-plaintext">
                                                        <?php if (!empty($cash_register['updated_at'])): ?>
                                                            <i class="ri ri-refresh-line mr-1"></i>
                                                            <?php echo date('d/m/Y H:i:s', strtotime($cash_register['updated_at'])); ?>
                                                        <?php else: ?>
                                                            <span class="text-muted">No disponible</span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <!-- Panel de acciones -->
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h6 class="card-title mb-0">
                                            <i class="ri ri-tools-line mr-1"></i>
                                            Acciones
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="d-grid gap-2">
                                            <a href="edit.php?id=<?php echo $cash_register['id']; ?>" class="btn btn-primary">
                                                <i class="ri ri-edit-line mr-1"></i>
                                                Editar Caja
                                            </a>
                                            
                                            <button type="button" 
                                                    class="btn btn-outline-danger" 
                                                    onclick="confirmDelete(<?php echo $cash_register['id']; ?>, '<?php echo htmlspecialchars($cash_register['name'], ENT_QUOTES); ?>')">
                                                <i class="ri ri-delete-bin-line mr-1"></i>
                                                Eliminar Caja
                                            </button>
                                            
                                            <hr>
                                            
                                            <a href="index.php" class="btn btn-outline-secondary">
                                                <i class="ri ri-arrow-left-line mr-1"></i>
                                                Volver al Listado
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <!-- Información del estado -->
                                <div class="card <?php echo $cash_register['status'] === 'active' ? 'border-success' : ($cash_register['status'] === 'maintenance' ? 'border-warning' : 'border-secondary'); ?>">
                                    <div class="card-header bg-<?php echo getStatusColor($cash_register['status']); ?> text-white">
                                        <h6 class="card-title mb-0 text-white">
                                            <i class="ri ri-information-line mr-1"></i>
                                            Estado de la Caja
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="text-center">
                                            <div class="mb-3">
                                                <span style="font-size: 3rem;"><?php echo getStatusIcon($cash_register['status']); ?></span>
                                            </div>
                                            <h5><?php echo getStatusText($cash_register['status']); ?></h5>
                                            <p class="text-muted mb-0">
                                                <?php 
                                                switch($cash_register['status']) {
                                                    case 'active':
                                                        echo 'La caja está operativa y disponible para realizar transacciones.';
                                                        break;
                                                    case 'inactive':
                                                        echo 'La caja está fuera de servicio y no puede procesar transacciones.';
                                                        break;
                                                    case 'maintenance':
                                                        echo 'La caja está en mantenimiento o reparación.';
                                                        break;
                                                    default:
                                                        echo 'Estado desconocido.';
                                                }
                                                ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
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
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <i class="ri ri-alert-line text-danger" style="font-size: 3rem;"></i>
                </div>
                <p class="text-center">¿Estás seguro de que deseas eliminar la caja registradora <strong id="itemName"></strong>?</p>
                <div class="alert alert-warning">
                    <p class="mb-0"><strong>Advertencia:</strong> Esta acción no se puede deshacer y eliminará permanentemente la caja registradora del sistema.</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="ri ri-close-line mr-1"></i>
                    Cancelar
                </button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                    <i class="ri ri-delete-bin-line mr-1"></i>
                    Eliminar Definitivamente
                </button>
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
        // Deshabilitar botón para evitar doble clic
        this.disabled = true;
        this.innerHTML = '<i class="ri ri-loader-line mr-1"></i> Eliminando...';
        
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
                window.location.href = 'index.php?deleted=1&message=' + encodeURIComponent(data.message);
            } else {
                alert('Error al eliminar la caja registradora: ' + data.message);
                // Reactivar botón
                this.disabled = false;
                this.innerHTML = '<i class="ri ri-delete-bin-line mr-1"></i> Eliminar Definitivamente';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error de conexión al eliminar la caja registradora');
            // Reactivar botón
            this.disabled = false;
            this.innerHTML = '<i class="ri ri-delete-bin-line mr-1"></i> Eliminar Definitivamente';
        });
    }
});

// Agregar clase CSS para la animación del avatar
document.addEventListener('DOMContentLoaded', function() {
    const avatarStyle = document.createElement('style');
    avatarStyle.textContent = `
        .avatar-sm {
            width: 40px;
            height: 40px;
            font-size: 1.1rem;
        }
        
        .form-control-plaintext {
            min-height: auto;
            padding: 0.375rem 0;
        }
    `;
    document.head.appendChild(avatarStyle);
});
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>