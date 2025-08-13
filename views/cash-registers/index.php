<?php
// Verificar acceso primero - ANTES de cualquier output
require_once __DIR__ . '/../../middleware/AuthMiddleware.php';
AuthMiddleware::requireUserManagementAccess();

// Incluir el controlador
require_once __DIR__ . '/../../controllers/CashRegistersController.php';

$cashRegistersController = new CashRegistersController();

// Preparar parámetros desde la petición
$params = [
    'page' => $_GET['page'] ?? 1,
    'search' => $_GET['search'] ?? ''
];

// Usar el controlador para obtener los datos
$result = $cashRegistersController->index($params);

// Si hay error de permisos o redirección, manejarla
if (!$result['success'] && isset($result['redirect'])) {
    header('Location: ' . $result['redirect']);
    exit;
}

// Extraer variables para la vista
$cash_registers = $result['cash_registers'] ?? [];
$total_items = $result['total_items'] ?? 0;
$total_pages = $result['total_pages'] ?? 1;
$current_page = $result['current_page'] ?? 1;
$search = $result['search'] ?? '';
$page_title = $result['page_title'] ?? 'Cajas Registradoras';

// Incluir header y layouts
require_once __DIR__ . '/../layouts/header.php';
include __DIR__ . '/../layouts/navigation.php';
include __DIR__ . '/../layouts/navigation-top.php';

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
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title">
                            <i class="ri ri-cash-line mr-1"></i>
                            <?php echo htmlspecialchars($page_title); ?>
                        </h5>
                        <a href="create.php" class="btn btn-primary">
                            <i class="ri ri-add-line mr-1"></i>
                            Nueva Caja Registradora
                        </a>
                    </div>
                    
                    <div class="card-body">
                        <!-- Formulario de búsqueda -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <form method="GET" action="index.php" class="d-flex">
                                    <input type="text" 
                                           name="search" 
                                           class="form-control mr-2" 
                                           placeholder="Buscar por nombre, usuario o estado..." 
                                           value="<?php echo htmlspecialchars($search); ?>">
                                    <button type="submit" class="btn btn-outline-primary">
                                        <i class="ri ri-search-line"></i>
                                    </button>
                                    <?php if (!empty($search)): ?>
                                    <a href="index.php" class="btn btn-outline-secondary ml-1">
                                        <i class="ri ri-close-line"></i>
                                    </a>
                                    <?php endif; ?>
                                </form>
                            </div>
                            <div class="col-md-6 text-right">
                                <small class="text-muted">
                                    Total: <?php echo number_format($total_items); ?> cajas registradoras
                                </small>
                            </div>
                        </div>

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

                        <!-- Tabla de cajas registradoras -->
                        <?php if (!empty($cash_registers)): ?>
                        <div class="table-responsive mb-4">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre de la Caja</th>
                                        <th>Usuario Asignado</th>
                                        <th>Estado</th>
                                        <th>Fecha de Creación</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($cash_registers as $cash_register): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($cash_register['id']); ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($cash_register['name']); ?></strong>
                                        </td>
                                        <td>
                                            <div>
                                                <strong><?php echo htmlspecialchars($cash_register['user_name'] ?? 'N/A'); ?></strong>
                                                <?php if (!empty($cash_register['user_email'])): ?>
                                                <br><small class="text-muted"><?php echo htmlspecialchars($cash_register['user_email']); ?></small>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge text-bg-<?php echo getStatusColor($cash_register['status']); ?>">
                                                <?php echo getStatusText($cash_register['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if (!empty($cash_register['created_at'])): ?>
                                                <?php echo date('d/m/Y H:i', strtotime($cash_register['created_at'])); ?>
                                            <?php else: ?>
                                                <span class="text-muted">N/A</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="view.php?id=<?php echo $cash_register['id']; ?>" 
                                                   class="btn btn-sm btn-outline-info" 
                                                   title="Ver detalles">
                                                    <i class="ri ri-eye-line"></i>
                                                </a>
                                                <a href="edit.php?id=<?php echo $cash_register['id']; ?>" 
                                                   class="btn btn-sm btn-outline-primary" 
                                                   title="Editar">
                                                    <i class="ri ri-edit-line"></i>
                                                </a>
                                                <button type="button" 
                                                        class="btn btn-sm btn-outline-danger" 
                                                        title="Eliminar"
                                                        onclick="confirmDelete(<?php echo $cash_register['id']; ?>, '<?php echo htmlspecialchars($cash_register['name'], ENT_QUOTES); ?>')">
                                                    <i class="ri ri-delete-bin-line"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Paginación -->
                        <?php if ($total_pages > 1): ?>
                        <nav aria-label="Paginación">
                            <ul class="pagination justify-content-center">
                                <!-- Página anterior -->
                                <?php if ($current_page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo ($current_page - 1); ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">
                                        <i class="ri-arrow-left-s-line"></i>
                                    </a>
                                </li>
                                <?php endif; ?>

                                <!-- Páginas -->
                                <?php
                                $start_page = max(1, $current_page - 2);
                                $end_page = min($total_pages, $current_page + 2);
                                
                                for ($i = $start_page; $i <= $end_page; $i++):
                                ?>
                                <li class="page-item <?php echo $i == $current_page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                                <?php endfor; ?>

                                <!-- Página siguiente -->
                                <?php if ($current_page < $total_pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo ($current_page + 1); ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">
                                        <i class="ri-arrow-right-s-line"></i>
                                    </a>
                                </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                        <?php endif; ?>

                        <?php else: ?>
                        <div class="text-center py-4">
                            <div class="mb-3">
                                <i class="ri ri-cash-line" style="font-size: 48px; color: #6c757d;"></i>
                            </div>
                            <h5 class="text-muted">No se encontraron cajas registradoras</h5>
                            <p class="text-muted">
                                <?php if (!empty($search)): ?>
                                No se encontraron resultados para "<?php echo htmlspecialchars($search); ?>"
                                <br>
                                <a href="index.php" class="btn btn-outline-primary mt-2">
                                    Ver todas las cajas registradoras
                                </a>
                                <?php else: ?>
                                Comienza creando tu primera caja registradora
                                <br>
                                <a href="create.php" class="btn btn-primary mt-2">
                                    <i class="ri ri-add-line mr-1"></i>
                                    Crear Caja Registradora
                                </a>
                                <?php endif; ?>
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
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de que deseas eliminar la caja registradora <strong id="itemName"></strong>?</p>
                <p class="text-muted">Esta acción no se puede deshacer.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
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
                location.reload();
            } else {
                alert('Error al eliminar la caja registradora: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error de conexión al eliminar la caja registradora');
        });
    }
});
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>