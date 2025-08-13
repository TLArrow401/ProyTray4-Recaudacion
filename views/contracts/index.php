<?php
// Vista para listar contratos

// Incluir el controlador
require_once __DIR__ . '/../../controllers/ContractsController.php';

$contractsController = new ContractsController();

// Obtener parámetros de la URL
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$search = isset($_GET['search']) ? $_GET['search'] : '';
$awardee_filter = isset($_GET['awardee_id']) ? $_GET['awardee_id'] : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

// Obtener datos para la vista
$params = [
    'page' => $page,
    'search' => $search,
    'awardee_id' => $awardee_filter,
    'status' => $status_filter
];

$indexData = $contractsController->index($params);

// Verificar si hay errores
if (isset($indexData['success']) && !$indexData['success']) {
    $error_message = $indexData['message'];
    $contracts = [];
    $total_pages = 0;
    $current_page = 1;
    $awardees = [];
} else {
    $contracts = $indexData['contracts'] ?? [];
    $total_pages = $indexData['total_pages'] ?? 0;
    $current_page = $indexData['current_page'] ?? 1;
    $awardees = $indexData['awardees'] ?? [];
    $page_title = $indexData['page_title'] ?? 'Gestión de Contratos';
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
                            <i class="ri-file-list-3-line mr-1"></i>
                            <?php echo htmlspecialchars($page_title ?? 'Gestión de Contratos'); ?>
                        </h5>
                        <a href="create.php" class="btn btn-primary">
                            <i class="ri-add-line"></i> Nuevo Contrato
                        </a>
                    </div>

                    <div class="card-body">
                        <!-- Mostrar errores -->
                        <?php if (isset($error_message)): ?>
                        <div class="alert alert-danger" role="alert">
                            <i class="ri-error-warning-line"></i> <?php echo htmlspecialchars($error_message); ?>
                        </div>
                        <?php endif; ?>

                        <!-- Filtros de búsqueda -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <form method="GET" class="d-flex flex-wrap gap-2">
                                    <div class="flex-grow-1">
                                        <input type="text" 
                                               class="form-control" 
                                               name="search" 
                                               placeholder="Buscar por adjudicatario o cédula..." 
                                               value="<?php echo htmlspecialchars($search); ?>">
                                    </div>
                                    <div class="flex-shrink-0">
                                        <select name="awardee_id" class="form-select">
                                            <option value="">Todos los adjudicatarios</option>
                                            <?php foreach ($awardees as $awardee): ?>
                                            <option value="<?php echo $awardee['id']; ?>" 
                                                    <?php echo $awardee_filter == $awardee['id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($awardee['name'] . ' (' . $awardee['id_number'] . ')'); ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="flex-shrink-0">
                                        <button type="submit" class="btn btn-outline-primary">
                                            <i class="ri-search-line"></i> Buscar
                                        </button>
                                    </div>
                                    <div class="flex-shrink-0">
                                        <a href="index.php" class="btn btn-outline-secondary">
                                            <i class="ri-refresh-line"></i> Limpiar
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Tabla de contratos -->
                        <?php if (!empty($contracts)): ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>Adjudicatario</th>
                                        <th>Año Fiscal</th>
                                        <th>Período</th>
                                        <th>Tipo</th>
                                        <th>Modalidad</th>
                                        <th>Categorías</th>
                                        <th>Locales</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($contracts as $contract): ?>
                                    <tr>
                                        <td><strong>#<?php echo $contract['id']; ?></strong></td>
                                        <td>
                                            <div>
                                                <strong><?php echo htmlspecialchars($contract['awardee_name']); ?></strong>
                                            </div>
                                            <small class="text-muted">
                                                <?php echo htmlspecialchars($contract['awardee_id_number']); ?>
                                            </small>
                                        </td>
                                        <td><?php echo htmlspecialchars($contract['fiscal_year']); ?></td>
                                        <td>
                                            <small class="text-muted">
                                                <?php echo date('d/m/Y', strtotime($contract['start_date'])); ?> - 
                                                <?php echo date('d/m/Y', strtotime($contract['end_date'])); ?>
                                            </small>
                                        </td>
                                        <td>
                                            <span class="badge <?php echo $contract['type'] === 'advance' ? 'bg-info' : 'bg-secondary'; ?>">
                                                <?php echo $contract['type'] === 'advance' ? 'Adelantado' : 'Simultáneo'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge <?php echo $contract['contract_mode'] === 'monthly' ? 'bg-primary' : 'bg-success'; ?>">
                                                <?php echo $contract['contract_mode'] === 'monthly' ? 'Mensual' : 'Semanal'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-warning text-dark">
                                                <?php echo $contract['categories_count']; ?> categoría(s)
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">
                                                <?php echo $contract['locations_count']; ?> local(es)
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="show.php?id=<?php echo $contract['id']; ?>" 
                                                   class="btn btn-sm btn-outline-info" 
                                                   title="Ver detalles">
                                                    <i class="ri-eye-line"></i>
                                                </a>
                                                <a href="edit.php?id=<?php echo $contract['id']; ?>" 
                                                   class="btn btn-sm btn-outline-primary" 
                                                   title="Editar">
                                                    <i class="ri-edit-line"></i>
                                                </a>
                                                <button type="button" 
                                                        class="btn btn-sm btn-outline-danger" 
                                                        title="Eliminar"
                                                        onclick="confirmDelete(<?php echo $contract['id']; ?>)">
                                                    <i class="ri-delete-bin-line"></i>
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
                        <div class="d-flex justify-content-center mt-4">
                            <nav aria-label="Paginación de contratos">
                                <ul class="pagination">
                                    <!-- Página anterior -->
                                    <?php if ($current_page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $current_page - 1; ?>&search=<?php echo urlencode($search); ?>&awardee_id=<?php echo urlencode($awardee_filter); ?>">
                                            <i class="ri-arrow-left-line"></i>
                                        </a>
                                    </li>
                                    <?php endif; ?>

                                    <!-- Páginas numeradas -->
                                    <?php for ($i = max(1, $current_page - 2); $i <= min($total_pages, $current_page + 2); $i++): ?>
                                    <li class="page-item <?php echo $i === $current_page ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&awardee_id=<?php echo urlencode($awardee_filter); ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    </li>
                                    <?php endfor; ?>

                                    <!-- Página siguiente -->
                                    <?php if ($current_page < $total_pages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $current_page + 1; ?>&search=<?php echo urlencode($search); ?>&awardee_id=<?php echo urlencode($awardee_filter); ?>">
                                            <i class="ri-arrow-right-line"></i>
                                        </a>
                                    </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        </div>
                        <?php endif; ?>

                        <?php else: ?>
                        <!-- Estado vacío -->
                        <div class="text-center py-5">
                            <i class="ri-file-list-3-line text-muted" style="font-size: 3rem;"></i>
                            <h5 class="text-muted mt-2">No hay contratos registrados</h5>
                            <p class="text-muted">Comienza creando tu primer contrato.</p>
                            <a href="create.php" class="btn btn-primary">
                                <i class="ri-add-line"></i> Crear Primer Contrato
                            </a>
                        </div>
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
                ¿Está seguro de que desea eliminar este contrato? Esta acción no se puede deshacer.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    <input type="hidden" name="_method" value="DELETE">
                    <button type="submit" class="btn btn-danger">Eliminar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(contractId) {
    const deleteForm = document.getElementById('deleteForm');
    deleteForm.action = 'delete.php?id=' + contractId;
    
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    deleteModal.show();
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>