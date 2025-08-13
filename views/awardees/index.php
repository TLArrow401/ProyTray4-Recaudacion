<?php
// Vista de listado de adjudicatarios

// Incluir el controlador
require_once __DIR__ . '/../../controllers/AwardeesController.php';

$awardeesController = new AwardeesController();

// Preparar parámetros desde la petición
$params = [
    'page' => $_GET['page'] ?? 1,
    'search' => $_GET['search'] ?? ''
];

// Usar el controlador para obtener los datos
$result = $awardeesController->index($params);

// El método index() siempre devuelve datos válidos o lanza una excepción
// No necesitamos verificar 'success' aquí

// Extraer variables para la vista
$awardees = $result['awardees'];
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
                            <i class="ri-user-star-line mr-1"></i>
                            <?php echo htmlspecialchars($page_title); ?>
                        </h5>
                        <a href="create.php" class="btn btn-primary">
                            <i class="ri-add-line"></i> Nuevo Adjudicatario
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
                                           placeholder="Buscar por nombre, apellido, cédula o email..."
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
                        <?php if (empty($awardees)): ?>
                            <div class="text-center py-4">
                                <i class="ri-user-star-line text-muted" style="font-size: 3rem;"></i>
                                <h5 class="text-muted mt-2">
                                    <?php echo $has_search ? 'No se encontraron adjudicatarios con ese criterio de búsqueda' : 'No hay adjudicatarios registrados'; ?>
                                </h5>
                                <?php if (!$has_search): ?>
                                <p class="text-muted">Comienza creando el primer adjudicatario</p>
                                <a href="create.php" class="btn btn-primary">
                                    <i class="ri-add-line"></i> Crear Primer Adjudicatario
                                </a>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>ID</th>
                                            <th>Nombre Completo</th>
                                            <th>Número de Identificación</th>
                                            <th>Teléfono</th>
                                            <th>Email</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($awardees as $awardee): ?>
                                        <?php 
                                        $fullName = trim(implode(' ', array_filter([
                                            $awardee['first_name'],
                                            $awardee['middle_name'],
                                            $awardee['last_name'],
                                            $awardee['second_last_name']
                                        ])));
                                        ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($awardee['id']); ?></td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($fullName); ?></strong>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary">
                                                    <?php echo htmlspecialchars($awardee['id_number']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if (!empty($awardee['phone'])): ?>
                                                    <i class="ri-phone-line text-muted"></i>
                                                    <?php echo htmlspecialchars($awardee['phone']); ?>
                                                <?php else: ?>
                                                    <small class="text-muted">No registrado</small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (!empty($awardee['email'])): ?>
                                                    <i class="ri-mail-line text-muted"></i>
                                                    <a href="mailto:<?php echo htmlspecialchars($awardee['email']); ?>" class="text-decoration-none">
                                                        <?php echo htmlspecialchars($awardee['email']); ?>
                                                    </a>
                                                <?php else: ?>
                                                    <small class="text-muted">No registrado</small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="view.php?id=<?php echo $awardee['id']; ?>" 
                                                       class="btn btn-sm btn-outline-primary" 
                                                       title="Ver detalles">
                                                        <i class="ri-eye-line"></i>
                                                    </a>
                                                    <a href="edit.php?id=<?php echo $awardee['id']; ?>" 
                                                       class="btn btn-sm btn-outline-warning" 
                                                       title="Editar">
                                                        <i class="ri-edit-line"></i>
                                                    </a>
                                                    <button type="button" 
                                                            class="btn btn-sm btn-outline-danger" 
                                                            onclick="confirmDelete(<?php echo $awardee['id']; ?>, '<?php echo htmlspecialchars($fullName, ENT_QUOTES); ?>')"
                                                            title="Eliminar">
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
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <div class="text-muted">
                                    Mostrando página <?php echo $current_page; ?> de <?php echo $total_pages; ?>
                                    (<?php echo $total_records; ?> adjudicatario<?php echo $total_records != 1 ? 's' : ''; ?> total<?php echo $total_records != 1 ? 'es' : ''; ?>)
                                </div>
                                
                                <nav>
                                    <ul class="pagination pagination-sm mb-0">
                                        <!-- Botón anterior -->
                                        <?php if ($current_page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $current_page - 1; ?><?php echo $has_search ? '&search=' . urlencode($search) : ''; ?>">
                                                <i class="ri-arrow-left-line"></i>
                                            </a>
                                        </li>
                                        <?php else: ?>
                                        <li class="page-item disabled">
                                            <span class="page-link"><i class="ri-arrow-left-line"></i></span>
                                        </li>
                                        <?php endif; ?>

                                        <!-- Números de página -->
                                        <?php 
                                        $start_page = max(1, $current_page - 2);
                                        $end_page = min($total_pages, $current_page + 2);
                                        
                                        if ($start_page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=1<?php echo $has_search ? '&search=' . urlencode($search) : ''; ?>">1</a>
                                        </li>
                                        <?php if ($start_page > 2): ?>
                                        <li class="page-item disabled">
                                            <span class="page-link">...</span>
                                        </li>
                                        <?php endif; ?>
                                        <?php endif; ?>

                                        <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                                        <li class="page-item <?php echo $i == $current_page ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?><?php echo $has_search ? '&search=' . urlencode($search) : ''; ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        </li>
                                        <?php endfor; ?>

                                        <?php if ($end_page < $total_pages): ?>
                                        <?php if ($end_page < $total_pages - 1): ?>
                                        <li class="page-item disabled">
                                            <span class="page-link">...</span>
                                        </li>
                                        <?php endif; ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $total_pages; ?><?php echo $has_search ? '&search=' . urlencode($search) : ''; ?>">
                                                <?php echo $total_pages; ?>
                                            </a>
                                        </li>
                                        <?php endif; ?>

                                        <!-- Botón siguiente -->
                                        <?php if ($current_page < $total_pages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $current_page + 1; ?><?php echo $has_search ? '&search=' . urlencode($search) : ''; ?>">
                                                <i class="ri-arrow-right-line"></i>
                                            </a>
                                        </li>
                                        <?php else: ?>
                                        <li class="page-item disabled">
                                            <span class="page-link"><i class="ri-arrow-right-line"></i></span>
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

<!-- Script para confirmación de eliminación -->
<script>
function confirmDelete(id, fullName) {
    if (confirm('¿Estás seguro de que deseas eliminar al adjudicatario "' + fullName + '"?\n\nEsta acción no se puede deshacer.')) {
        // Hacer petición AJAX para eliminar
        fetch('delete.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'id=' + id
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Recargar la página para mostrar el mensaje y actualizar la lista
                window.location.reload();
            } else {
                alert('Error al eliminar: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Ocurrió un error al eliminar el adjudicatario');
        });
    }
}
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>