<?php
// Vista de listado de cargos

// Incluir el controlador
require_once __DIR__ . '/../../controllers/JobPositionsController.php';

$jobPositionsController = new JobPositionsController();

// Preparar parámetros desde la petición
$params = [
    'page' => $_GET['page'] ?? 1,
    'search' => $_GET['search'] ?? ''
];

// Usar el controlador para obtener los datos
$result = $jobPositionsController->index($params);

// El método index() siempre devuelve datos válidos o lanza una excepción
// No necesitamos verificar 'success' aquí

// Extraer variables para la vista
$job_positions = $result['job_positions'];
$current_page = $result['current_page'];
$total_pages = $result['total_pages'];
$total_records = $result['total_records'];
$search = $result['search'];
$page_title = $result['page_title'];
$has_search = $result['has_search'];

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
                            <i class="ri-briefcase-line mr-1"></i>
                            <?php echo htmlspecialchars($page_title); ?>
                        </h5>
                        <a href="create.php" class="btn btn-primary">
                            <i class="ri-add-line"></i> Nuevo Cargo
                        </a>
                    </div>
                    
                    <!-- Filtros de búsqueda -->
                    <div class="card-body border-bottom">
                        <form method="GET" class="row g-3">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <input type="text" 
                                           class="form-control" 
                                           name="search" 
                                           placeholder="Buscar por nombre de cargo..."
                                           value="<?php echo htmlspecialchars($search); ?>">
                                    <button class="btn btn-outline-secondary" type="submit">
                                        <i class="ri-search-line"></i>
                                    </button>
                                </div>
                            </div>
                            <?php if ($has_search): ?>
                            <div class="col-md-3">
                                <a href="index.php" class="btn btn-outline-info">
                                    <i class="ri-close-line"></i> Limpiar filtros
                                </a>
                            </div>
                            <?php endif; ?>
                        </form>
                        
                        <?php if ($has_search): ?>
                        <div class="mt-2">
                            <small class="text-muted">
                                Mostrando resultados para: <strong>"<?php echo htmlspecialchars($search); ?>"</strong>
                                (<?php echo $total_records; ?> resultado<?php echo $total_records != 1 ? 's' : ''; ?>)
                            </small>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Mostrar mensajes flash -->
                    <?php if (isset($_SESSION['flash_message'])): ?>
                    <div class="alert alert-<?php echo $_SESSION['flash_message']['type'] === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show mx-3 mt-3" role="alert">
                        <?php echo htmlspecialchars($_SESSION['flash_message']['message']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['flash_message']); ?>
                    <?php endif; ?>

                    <div class="card-body">
                        <?php if (empty($job_positions)): ?>
                            <div class="text-center py-4">
                                <i class="ri-briefcase-line text-muted" style="font-size: 3rem;"></i>
                                <h5 class="text-muted mt-2">
                                    <?php echo $has_search ? 'No se encontraron cargos con ese criterio de búsqueda' : 'No hay cargos registrados'; ?>
                                </h5>
                                <?php if (!$has_search): ?>
                                <p class="text-muted">Comienza creando el primer cargo</p>
                                <a href="create.php" class="btn btn-primary">
                                    <i class="ri-add-line"></i> Crear Primer Cargo
                                </a>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>ID</th>
                                            <th>Nombre del Cargo</th>
                                            <th>Personal Asignado</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($job_positions as $position): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($position['id']); ?></td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($position['name']); ?></strong>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php echo $position['staff_count'] > 0 ? 'primary' : 'secondary'; ?>">
                                                    <?php echo $position['staff_count']; ?> persona<?php echo $position['staff_count'] != 1 ? 's' : ''; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="view.php?id=<?php echo $position['id']; ?>" 
                                                       class="btn btn-sm btn-outline-primary" 
                                                       title="Ver detalles">
                                                        <i class="ri-eye-line"></i>
                                                    </a>
                                                    <a href="edit.php?id=<?php echo $position['id']; ?>" 
                                                       class="btn btn-sm btn-outline-warning" 
                                                       title="Editar">
                                                        <i class="ri-edit-line"></i>
                                                    </a>
                                                    <?php if ($position['staff_count'] == 0): ?>
                                                    <button type="button" 
                                                            class="btn btn-sm btn-outline-danger" 
                                                            onclick="confirmDelete(<?php echo $position['id']; ?>, '<?php echo htmlspecialchars($position['name'], ENT_QUOTES); ?>')"
                                                            title="Eliminar">
                                                        <i class="ri-delete-bin-line"></i>
                                                    </button>
                                                    <?php else: ?>
                                                    <button type="button" 
                                                            class="btn btn-sm btn-outline-secondary disabled" 
                                                            title="No se puede eliminar: tiene personal asignado"
                                                            disabled>
                                                        <i class="ri-delete-bin-line"></i>
                                                    </button>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Paginación -->
                            <?php if ($total_pages > 1): ?>
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <div>
                                    <small class="text-muted">
                                        Mostrando página <?php echo $current_page; ?> de <?php echo $total_pages; ?>
                                        (<?php echo $total_records; ?> registro<?php echo $total_records != 1 ? 's' : ''; ?> total<?php echo $total_records != 1 ? 'es' : ''; ?>)
                                    </small>
                                </div>
                                <nav aria-label="Paginación de cargos">
                                    <ul class="pagination pagination-sm mb-0">
                                        <?php if ($current_page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo ($current_page - 1); ?><?php echo $has_search ? '&search=' . urlencode($search) : ''; ?>">
                                                <i class="ri-arrow-left-s-line"></i>
                                            </a>
                                        </li>
                                        <?php endif; ?>

                                        <?php for ($i = max(1, $current_page - 2); $i <= min($total_pages, $current_page + 2); $i++): ?>
                                        <li class="page-item <?php echo $i == $current_page ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?><?php echo $has_search ? '&search=' . urlencode($search) : ''; ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        </li>
                                        <?php endfor; ?>

                                        <?php if ($current_page < $total_pages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo ($current_page + 1); ?><?php echo $has_search ? '&search=' . urlencode($search) : ''; ?>">
                                                <i class="ri-arrow-right-s-line"></i>
                                            </a>
                                        </li>
                                        <?php endif; ?>
                                    </ul>
                                </nav>
                            </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmación para eliminar -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirmar Eliminación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro que desea eliminar el cargo <strong id="positionName"></strong>?</p>
                <p class="text-danger"><small>Esta acción no se puede deshacer.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Eliminar</button>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>

<script>
let deletePositionId = null;

function confirmDelete(id, name) {
    deletePositionId = id;
    document.getElementById('positionName').textContent = name;
    
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}

document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
    if (deletePositionId) {
        // Crear formulario para enviar DELETE request
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'delete.php';
        
        const idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = 'id';
        idInput.value = deletePositionId;
        form.appendChild(idInput);
        
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'DELETE';
        form.appendChild(methodInput);
        
        document.body.appendChild(form);
        form.submit();
    }
});
</script>