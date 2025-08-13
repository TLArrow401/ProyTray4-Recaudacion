<?php
// Verificar acceso primero - ANTES de cualquier output
require_once __DIR__ . '/../../middleware/AuthMiddleware.php';
AuthMiddleware::requireUserManagementAccess();

// Incluir el controlador
require_once __DIR__ . '/../../controllers/FiscalYearController.php';

$fiscalYearController = new FiscalYearController();

// Obtener ID del parámetro
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$id) {
    header('Location: index.php');
    exit;
}

// Usar el controlador para obtener los datos
$result = $fiscalYearController->show($id);

// Si hay error de permisos o redirección, manejarla
if (!$result['success']) {
    // La sesión ya está iniciada por AuthMiddleware
    $_SESSION['message'] = $result['message'];
    $_SESSION['messageType'] = 'error';
    header('Location: index.php');
    exit;
}

$fiscal_year = $result['fiscal_year'] ?? null;

// Obtener estadísticas adicionales
require_once __DIR__ . '/../../models/FiscalYearModel.php';
$fiscalYearModel = new FiscalYearModel();
$stats = $fiscalYearModel->getStatistics($id);

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
                            <i class="ri-eye-line mr-1"></i>
                            Detalles del Año Fiscal: <?php echo htmlspecialchars($fiscal_year['year']); ?>
                        </h5>
                        <div>
                            <a href="edit.php?id=<?php echo $id; ?>" class="btn btn-outline-primary mr-2">
                                <i class="ri-edit-line mr-1"></i>
                                Editar
                            </a>
                            <a href="index.php" class="btn btn-outline-secondary">
                                <i class="ri-arrow-left-line mr-1"></i>
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

                        <?php if ($fiscal_year): ?>
                        
                        <!-- Información básica -->
                        <div class="row mb-4">
                            <div class="col-md-8">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="card-title mb-0">
                                            <i class="ri-information-line"></i> Información Básica
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <div class="col-sm-3">
                                                <strong>ID:</strong>
                                            </div>
                                            <div class="col-sm-9">
                                                <?php echo htmlspecialchars($fiscal_year['id']); ?>
                                            </div>
                                        
                                            <div class="col-sm-3">
                                                <strong>Año:</strong>
                                            </div>
                                            <div class="col-sm-9">
                                                <span class="badge bg-primary fs-6">
                                                    <?php echo htmlspecialchars($fiscal_year['year']); ?>
                                                </span>
                                            </div>

                                            <div class="col-sm-3">
                                                <strong>Fecha de Inicio:</strong>
                                            </div>
                                            <div class="col-sm-9">
                                                <?php echo date('d/m/Y', strtotime($fiscal_year['start_date'])); ?>
                                                <small class="text-muted">
                                                    (<?php echo date('l', strtotime($fiscal_year['start_date'])); ?>)
                                                </small>
                                            </div>

                                            <div class="col-sm-3">
                                                <strong>Fecha de Fin:</strong>
                                            </div>
                                            <div class="col-sm-9">
                                                <?php echo date('d/m/Y', strtotime($fiscal_year['end_date'])); ?>
                                                <small class="text-muted">
                                                    (<?php echo date('l', strtotime($fiscal_year['end_date'])); ?>)
                                                </small>
                                            </div>

                                            <div class="col-sm-3">
                                                <strong>Duración:</strong>
                                            </div>
                                            <div class="col-sm-9">
                                                <?php
                                                $start_date = new DateTime($fiscal_year['start_date']);
                                                $end_date = new DateTime($fiscal_year['end_date']);
                                                $diff = $start_date->diff($end_date);
                                                $days = $diff->days;
                                                $months = round($days / 30.44);
                                                ?>
                                                <?php echo number_format($days); ?> días 
                                                <small class="text-muted">(≈ <?php echo $months; ?> meses)</small>
                                            </div>

                                            <div class="col-sm-3">
                                                <strong>Estado:</strong>
                                            </div>
                                            <div class="col-sm-9">
                                                <?php
                                                $status = $fiscal_year['status'] ?? 'active';
                                                $status_class = $status === 'active' ? 'bg-success' : 'bg-secondary';
                                                $status_text = $status === 'active' ? 'Activo' : 'Inactivo';
                                                ?>
                                                <span class="badge <?php echo $status_class; ?>">
                                                    <i class="ri-checkbox-circle-line"></i> <?php echo $status_text; ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Estadísticas -->
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="card-title mb-0">
                                            <i class="ri-bar-chart-line"></i> Estadísticas
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-3 text-center">
                                            <div class="col-12">
                                                <div class="border rounded p-3 bg-light">
                                                    <div class="h4 text-primary mb-1">
                                                        <?php echo number_format($stats['total_contracts']); ?>
                                                    </div>
                                                    <small class="text-muted">Contratos Totales</small>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="border rounded p-3 bg-light">
                                                    <div class="h4 text-success mb-1">
                                                        <?php echo number_format($stats['active_contracts']); ?>
                                                    </div>
                                                    <small class="text-muted">Contratos Activos</small>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="border rounded p-3 bg-light">
                                                    <div class="h4 text-info mb-1">
                                                        $<?php echo number_format($stats['total_payments'], 2); ?>
                                                    </div>
                                                    <small class="text-muted">Total en Pagos</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Información de fechas relevantes -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="card-title mb-0">
                                            <i class="ri-calendar-2-line"></i> Análisis de Fechas
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <?php
                                                $today = new DateTime();
                                                $start = new DateTime($fiscal_year['start_date']);
                                                $end = new DateTime($fiscal_year['end_date']);
                                                
                                                $status_periodo = '';
                                                $status_class = '';
                                                $progress = 0;
                                                
                                                if ($today < $start) {
                                                    $status_periodo = 'Por comenzar';
                                                    $status_class = 'text-warning';
                                                    $days_to_start = $today->diff($start)->days;
                                                    $additional_info = "Comienza en {$days_to_start} días";
                                                } elseif ($today > $end) {
                                                    $status_periodo = 'Finalizado';
                                                    $status_class = 'text-danger';
                                                    $days_since_end = $end->diff($today)->days;
                                                    $additional_info = "Finalizó hace {$days_since_end} días";
                                                    $progress = 100;
                                                } else {
                                                    $status_periodo = 'En curso';
                                                    $status_class = 'text-success';
                                                    $days_passed = $start->diff($today)->days;
                                                    $total_days = $start->diff($end)->days;
                                                    $progress = ($days_passed / $total_days) * 100;
                                                    $days_remaining = $today->diff($end)->days;
                                                    $additional_info = "Quedan {$days_remaining} días";
                                                }
                                                ?>
                                                
                                                <h6>Estado del Período:</h6>
                                                <p class="<?php echo $status_class; ?>">
                                                    <i class="ri-time-line"></i> 
                                                    <strong><?php echo $status_periodo; ?></strong>
                                                </p>
                                                <p class="text-muted"><?php echo $additional_info; ?></p>
                                                
                                                <div class="progress mb-2" style="height: 8px;">
                                                    <div class="progress-bar <?php echo $status_periodo === 'En curso' ? 'bg-success' : ($status_periodo === 'Finalizado' ? 'bg-danger' : 'bg-warning'); ?>" 
                                                         role="progressbar" 
                                                         style="width: <?php echo $progress; ?>%" 
                                                         aria-valuenow="<?php echo $progress; ?>" 
                                                         aria-valuemin="0" 
                                                         aria-valuemax="100">
                                                    </div>
                                                </div>
                                                <small class="text-muted">Progreso: <?php echo number_format($progress, 1); ?>%</small>
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <h6>Fechas Importantes:</h6>
                                                <ul class="list-unstyled">
                                                    <li class="mb-2">
                                                        <i class="ri-play-circle-line text-success"></i>
                                                        <strong>Inicio:</strong> <?php echo $start->format('d/m/Y'); ?>
                                                    </li>
                                                    <li class="mb-2">
                                                        <i class="ri-stop-circle-line text-danger"></i>
                                                        <strong>Fin:</strong> <?php echo $end->format('d/m/Y'); ?>
                                                    </li>
                                                    <li class="mb-2">
                                                        <i class="ri-calendar-check-line text-info"></i>
                                                        <strong>Hoy:</strong> <?php echo $today->format('d/m/Y'); ?>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Acciones rápidas -->
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="card-title mb-0">
                                            <i class="ri-tools-line"></i> Acciones Rápidas
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="d-flex flex-wrap gap-2">
                                            <a href="edit.php?id=<?php echo $id; ?>" class="btn btn-outline-primary">
                                                <i class="ri-edit-line"></i> Editar Año Fiscal
                                            </a>
                                            
                                            <?php if ($stats['total_contracts'] > 0): ?>
                                            <a href="../contracts/index.php?fiscal_year_id=<?php echo $id; ?>" class="btn btn-outline-info">
                                                <i class="ri-file-list-line"></i> Ver Contratos (<?php echo $stats['total_contracts']; ?>)
                                            </a>
                                            <?php endif; ?>
                                            
                                            <button type="button" 
                                                    class="btn btn-outline-<?php echo ($fiscal_year['status'] ?? 'active') === 'active' ? 'warning' : 'success'; ?>"
                                                    onclick="toggleStatus(<?php echo $id; ?>, '<?php echo ($fiscal_year['status'] ?? 'active') === 'active' ? 'inactive' : 'active'; ?>')">
                                                <i class="ri-toggle-line"></i>
                                                <?php echo ($fiscal_year['status'] ?? 'active') === 'active' ? 'Desactivar' : 'Activar'; ?>
                                            </button>
                                            
                                            <?php if ($stats['total_contracts'] == 0): ?>
                                            <button type="button" 
                                                    class="btn btn-outline-danger"
                                                    onclick="confirmDelete(<?php echo $id; ?>, '<?php echo htmlspecialchars($fiscal_year['year'], ENT_QUOTES); ?>')">
                                                <i class="ri-delete-bin-line"></i> Eliminar
                                            </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php else: ?>
                        <div class="text-center py-4">
                            <div class="mb-3">
                                <i class="ri-calendar-line" style="font-size: 48px; color: #6c757d;"></i>
                            </div>
                            <h5 class="text-muted">Año fiscal no encontrado</h5>
                            <p class="text-muted">El año fiscal solicitado no existe o ha sido eliminado.</p>
                            <a href="index.php" class="btn btn-primary">
                                <i class="ri-arrow-left-line mr-1"></i>
                                Volver al listado
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleStatus(id, newStatus) {
    const statusText = newStatus === 'active' ? 'activar' : 'desactivar';
    
    if (confirm(`¿Está seguro de que desea ${statusText} este año fiscal?`)) {
        fetch('toggle-status.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `id=${id}&status=${newStatus}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error al cambiar el estado: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error de conexión al cambiar el estado');
        });
    }
}

function confirmDelete(id, year) {
    if (confirm(`¿Está seguro de que desea eliminar el año fiscal ${year}?\n\nEsta acción no se puede deshacer.`)) {
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
                window.location.href = 'index.php';
            } else {
                alert('Error al eliminar el año fiscal: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error de conexión al eliminar el año fiscal');
        });
    }
}
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>