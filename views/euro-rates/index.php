<?php
// Vista de listado de tasas de euro

// Incluir el controlador
require_once __DIR__ . '/../../controllers/EuroRatesController.php';

$euroRatesController = new EuroRatesController();

// Preparar parámetros desde la petición
$params = [
    'page' => $_GET['page'] ?? 1,
    'search' => $_GET['search'] ?? ''
];

// Usar el controlador para obtener los datos
$result = $euroRatesController->index($params);

// Si hay error, manejar el resultado
if (!$result['success']) {
    $error_message = $result['message'];
    $euro_rates = [];
    $total_items = 0;
    $total_pages = 0;
    $current_page = 1;
    $search = '';
    $page_title = 'Gestión de Tasas de Euro';
} else {
    // Extraer variables para la vista
    $euro_rates = $result['euro_rates'];
    $total_items = $result['total_items'];
    $total_pages = $result['total_pages'];
    $current_page = $result['current_page'];
    $search = $result['search'];
    $page_title = $result['page_title'];
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
                            <i class="ri-exchange-euro-line mr-1"></i>
                            <?php echo htmlspecialchars($page_title); ?>
                        </h5>
                        <a href="create.php" class="btn btn-primary">
                            <i class="ri-add-line"></i> Nueva Tasa de Euro
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
                                           placeholder="Buscar por valor, mes o año..."
                                           value="<?php echo htmlspecialchars($search); ?>">
                                    <button class="btn btn-outline-secondary" type="submit">
                                        <i class="ri-search-line"></i>
                                    </button>
                                </div>
                            </div>
                            <?php if (!empty($search)): ?>
                            <div class="col-md-3">
                                <a href="index.php" class="btn btn-outline-info">
                                    <i class="ri-close-line"></i> Limpiar filtros
                                </a>
                            </div>
                            <?php endif; ?>
                        </form>
                        
                        <?php if (!empty($search)): ?>
                        <div class="mt-2">
                            <small class="text-muted">
                                Mostrando resultados para: <strong>"<?php echo htmlspecialchars($search); ?>"</strong>
                                (<?php echo $total_items; ?> resultado<?php echo $total_items != 1 ? 's' : ''; ?>)
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

                    <!-- Mostrar error si existe -->
                    <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger mx-3 mt-3" role="alert">
                        <i class="ri-error-warning-line"></i>
                        <?php echo htmlspecialchars($error_message); ?>
                    </div>
                    <?php endif; ?>

                    <div class="card-body">
                        <?php if (empty($euro_rates)): ?>
                            <div class="text-center py-4">
                                <i class="ri-exchange-euro-line text-muted" style="font-size: 3rem;"></i>
                                <h5 class="text-muted mt-2">
                                    <?php echo !empty($search) ? 'No se encontraron tasas de euro con ese criterio de búsqueda' : 'No hay tasas de euro registradas'; ?>
                                </h5>
                                <?php if (empty($search)): ?>
                                <p class="text-muted">Comienza creando la primera tasa de euro</p>
                                <a href="create.php" class="btn btn-primary">
                                    <i class="ri-add-line"></i> Crear Primera Tasa
                                </a>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>ID</th>
                                            <th>Valor en Bolívares</th>
                                            <th>Período</th>
                                            <th class="text-center">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $euroRatesModel = new EuroRatesModel();
                                        foreach ($euro_rates as $rate): 
                                        ?>
                                        <tr>
                                            <td>
                                                <span class="badge bg-secondary">#<?php echo htmlspecialchars($rate['id']); ?></span>
                                            </td>
                                            <td>
                                                <strong class="text-success">
                                                    <i class="ri-money-euro-circle-line"></i>
                                                    <?php echo htmlspecialchars($euroRatesModel->formatBsValue($rate['bs_value'])); ?>
                                                </strong>
                                            </td>
                                            <td>
                                                <?php 
                                                $period = $euroRatesModel->formatPeriod($rate);
                                                if ($period === 'Sin período especificado'):
                                                ?>
                                                    <span class="badge bg-warning text-dark">
                                                        <i class="ri-calendar-line"></i>
                                                        Sin período especificado
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-info">
                                                        <i class="ri-calendar-line"></i>
                                                        <?php echo htmlspecialchars($period); ?>
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group" role="group">
                                                    <a href="view.php?id=<?php echo $rate['id']; ?>" 
                                                       class="btn btn-sm btn-outline-primary" 
                                                       title="Ver detalles">
                                                        <i class="ri-eye-line"></i>
                                                    </a>
                                                    <a href="edit.php?id=<?php echo $rate['id']; ?>" 
                                                       class="btn btn-sm btn-outline-warning" 
                                                       title="Editar">
                                                        <i class="ri-edit-line"></i>
                                                    </a>
                                                    <button type="button" 
                                                            class="btn btn-sm btn-outline-danger" 
                                                            onclick="confirmDelete(<?php echo $rate['id']; ?>, '<?php echo htmlspecialchars($euroRatesModel->formatBsValue($rate['bs_value']), ENT_QUOTES); ?>', '<?php echo htmlspecialchars($period, ENT_QUOTES); ?>')"
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
                                    (<?php echo $total_items; ?> tasa<?php echo $total_items != 1 ? 's' : ''; ?> total<?php echo $total_items != 1 ? 'es' : ''; ?>)
                                </div>
                                
                                <nav>
                                    <ul class="pagination pagination-sm mb-0">
                                        <!-- Botón anterior -->
                                        <?php if ($current_page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $current_page - 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">
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
                                            <a class="page-link" href="?page=1<?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">1</a>
                                        </li>
                                        <?php if ($start_page > 2): ?>
                                        <li class="page-item disabled">
                                            <span class="page-link">...</span>
                                        </li>
                                        <?php endif; ?>
                                        <?php endif; ?>

                                        <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                                        <li class="page-item <?php echo $i == $current_page ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">
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
                                            <a class="page-link" href="?page=<?php echo $total_pages; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">
                                                <?php echo $total_pages; ?>
                                            </a>
                                        </li>
                                        <?php endif; ?>

                                        <!-- Botón siguiente -->
                                        <?php if ($current_page < $total_pages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $current_page + 1; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">
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
function confirmDelete(id, value, period) {
    let confirmMessage = '¿Estás seguro de que deseas eliminar la tasa de euro con valor ' + value;
    if (period && period !== 'Sin período especificado') {
        confirmMessage += ' del período ' + period;
    }
    confirmMessage += '?\n\nEsta acción no se puede deshacer.';
    
    if (confirm(confirmMessage)) {
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
            alert('Ocurrió un error al eliminar la tasa de euro');
        });
    }
}

// Función para mostrar la tasa más reciente
function showLatestRate() {
    fetch('index.php?ajax=get_latest_rate')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Tasa más reciente: ' + data.formatted_value + ' (' + data.formatted_period + ')');
        } else {
            alert('No se encontró ninguna tasa registrada');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al obtener la tasa más reciente');
    });
}
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>