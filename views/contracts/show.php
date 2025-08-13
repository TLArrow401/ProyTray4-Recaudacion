<?php
// Vista para mostrar detalles del contrato

// Incluir el controlador
require_once __DIR__ . '/../../controllers/ContractsController.php';

$contractsController = new ContractsController();

// Obtener ID del contrato
$contract_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$contract_id) {
    header('Location: index.php');
    exit;
}

// Obtener datos del contrato
$showData = $contractsController->show($contract_id);

if (isset($showData['success']) && !$showData['success']) {
    $error_message = $showData['message'];
    $contract = null;
    $contract_categories = [];
    $contract_locations = [];
    $contract_payments = [];
} else {
    $contract = $showData['contract'] ?? null;
    $contract_categories = $showData['contract_categories'] ?? [];
    $contract_locations = $showData['contract_locations'] ?? [];
    $contract_payments = $showData['contract_payments'] ?? [];
    $page_title = $showData['page_title'] ?? 'Detalles del Contrato';
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
                            <i class="ri-file-text-line mr-1"></i>
                            <?php echo htmlspecialchars($page_title ?? 'Detalles del Contrato'); ?>
                        </h5>
                        <div>
                            <a href="edit.php?id=<?php echo $contract_id; ?>" class="btn btn-primary me-2">
                                <i class="ri-edit-line"></i> Editar Contrato
                            </a>
                            <a href="index.php" class="btn btn-secondary">
                                <i class="ri-arrow-left-line"></i> Volver al Listado
                            </a>
                        </div>
                    </div>

                    <div class="card-body">
                        <!-- Mostrar errores -->
                        <?php if (isset($error_message)): ?>
                        <div class="alert alert-danger" role="alert">
                            <i class="ri-error-warning-line"></i> <?php echo htmlspecialchars($error_message); ?>
                        </div>
                        <?php return; endif; ?>

                        <?php if ($contract): ?>
                        <!-- Información básica del contrato -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="border-bottom pb-2 mb-3">
                                    <i class="ri-information-line"></i> Información del Contrato
                                </h6>
                            </div>
                            <div class="col-md-6">
                                <div class="info-group">
                                    <label class="fw-bold">Adjudicatario:</label>
                                    <p class="mb-2"><?php echo htmlspecialchars($contract['awardee_name']); ?></p>
                                </div>
                                <div class="info-group">
                                    <label class="fw-bold">Cédula:</label>
                                    <p class="mb-2"><?php echo htmlspecialchars($contract['awardee_id_number']); ?></p>
                                </div>
                                <div class="info-group">
                                    <label class="fw-bold">Teléfono:</label>
                                    <p class="mb-2"><?php echo htmlspecialchars($contract['awardee_phone'] ?? 'No especificado'); ?></p>
                                </div>
                                <div class="info-group">
                                    <label class="fw-bold">Email:</label>
                                    <p class="mb-2"><?php echo htmlspecialchars($contract['awardee_email'] ?? 'No especificado'); ?></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-group">
                                    <label class="fw-bold">Año Fiscal:</label>
                                    <p class="mb-2"><?php echo htmlspecialchars($contract['fiscal_year']); ?></p>
                                </div>
                                <div class="info-group">
                                    <label class="fw-bold">Período:</label>
                                    <p class="mb-2">
                                        <?php echo date('d/m/Y', strtotime($contract['start_date'])); ?> - 
                                        <?php echo date('d/m/Y', strtotime($contract['end_date'])); ?>
                                    </p>
                                </div>
                                <div class="info-group">
                                    <label class="fw-bold">Tipo de Contrato:</label>
                                    <p class="mb-2">
                                        <span class="badge <?php echo $contract['type'] === 'advance' ? 'bg-info' : 'bg-secondary'; ?>">
                                            <?php echo $contract['type'] === 'advance' ? 'Adelantado' : 'Simultáneo'; ?>
                                        </span>
                                    </p>
                                </div>
                                <div class="info-group">
                                    <label class="fw-bold">Modalidad de Pago:</label>
                                    <p class="mb-2">
                                        <span class="badge <?php echo $contract['contract_mode'] === 'monthly' ? 'bg-primary' : 'bg-success'; ?>">
                                            <?php echo $contract['contract_mode'] === 'monthly' ? 'Mensual' : 'Semanal'; ?>
                                        </span>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Categorías de Negocio -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="border-bottom pb-2 mb-3">
                                    <i class="ri-price-tag-3-line"></i> Categorías de Negocio (<?php echo count($contract_categories); ?>)
                                </h6>
                            </div>
                            <?php if (!empty($contract_categories)): ?>
                            <div class="col-12">
                                <div class="row g-3">
                                    <?php foreach ($contract_categories as $category): ?>
                                    <div class="col-md-6">
                                        <div class="card border-primary">
                                            <div class="card-body">
                                                <h6 class="card-title">
                                                    <span class="badge <?php echo $category['type'] === 'external' ? 'bg-warning' : 'bg-info'; ?> me-2">
                                                        <?php echo $category['type'] === 'external' ? 'Externa' : 'Interna'; ?>
                                                    </span>
                                                    <?php echo htmlspecialchars($category['category_name']); ?>
                                                </h6>
                                                <p class="card-text">
                                                    <small class="text-muted">
                                                        <strong>Tipo:</strong> <?php echo htmlspecialchars($category['installation_type']); ?><br>
                                                        <strong>Cantidad de pagos:</strong> <?php echo $category['payment_count']; ?> vez(es)
                                                    </small>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php else: ?>
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <i class="ri-information-line"></i> No hay categorías de negocio asignadas a este contrato.
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Locales Asignados -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="border-bottom pb-2 mb-3">
                                    <i class="ri-store-2-line"></i> Locales Asignados (<?php echo count($contract_locations); ?>)
                                </h6>
                            </div>
                            <?php if (!empty($contract_locations)): ?>
                            <div class="col-12">
                                <div class="row g-3">
                                    <?php foreach ($contract_locations as $location): ?>
                                    <div class="col-md-6">
                                        <div class="card border-success">
                                            <div class="card-body">
                                                <h6 class="card-title">
                                                    <span class="badge bg-success me-2">Local</span>
                                                    <?php echo htmlspecialchars($location['stall_number']); ?>
                                                </h6>
                                                <p class="card-text">
                                                    <small class="text-muted">
                                                        <strong>Descripción:</strong> <?php echo htmlspecialchars($location['description']); ?><br>
                                                        <strong>Sector:</strong> <?php echo htmlspecialchars($location['sector_name']); ?><br>
                                                        <strong>Zona:</strong> <?php echo htmlspecialchars($location['zone_name']); ?>
                                                    </small>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php else: ?>
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <i class="ri-information-line"></i> No hay locales asignados a este contrato.
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Pagos del Contrato -->
                        <div class="row">
                            <div class="col-12">
                                <h6 class="border-bottom pb-2 mb-3">
                                    <i class="ri-money-dollar-circle-line"></i> Pagos del Contrato (<?php echo count($contract_payments); ?>)
                                </h6>
                            </div>
                            <?php if (!empty($contract_payments)): ?>
                            <div class="col-12">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>Referencia</th>
                                                <th>Fecha de Pago</th>
                                                <th>Factor (Veces)</th>
                                                <th>Tasa Euro</th>
                                                <th>Monto</th>
                                                <th>Estado</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($contract_payments as $payment): ?>
                                            <tr>
                                                <td><strong><?php echo htmlspecialchars($payment['payment_reference']); ?></strong></td>
                                                <td><?php echo date('d/m/Y', strtotime($payment['payment_date'])); ?></td>
                                                <td>
                                                    <strong><?php echo number_format($payment['multiplier_factor'], 2); ?></strong>
                                                    <small class="text-muted d-block">veces</small>
                                                </td>
                                                <td>
                                                    <?php if ($payment['euro_rate']): ?>
                                                        €<?php echo number_format($payment['euro_rate'], 4); ?>
                                                        <small class="text-muted d-block">
                                                            <?php echo $payment['rate_date']; ?>
                                                        </small>
                                                    <?php else: ?>
                                                        <span class="text-muted">No asignada</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <strong>$<?php echo number_format( $payment['euro_rate'] * $payment['multiplier_factor'], 2); ?></strong>
                                                    <?php if ($payment['euro_rate'] && $payment['multiplier_factor']): ?>
                                                        <small class="text-muted d-block">
                                                            <?php echo number_format($payment['multiplier_factor'], 2); ?> × €<?php echo number_format($payment['euro_rate'], 2); ?>
                                                        </small>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php
                                                    $status_badges = [
                                                        'pending' => 'bg-warning text-dark',
                                                        'paid' => 'bg-success',
                                                        'cancelled' => 'bg-danger',
                                                        'refunded' => 'bg-info'
                                                    ];
                                                    $status_texts = [
                                                        'pending' => 'Pendiente',
                                                        'paid' => 'Pagado',
                                                        'cancelled' => 'Cancelado',
                                                        'refunded' => 'Reembolsado'
                                                    ];
                                                    ?>
                                                    <span class="badge <?php echo $status_badges[$payment['status']] ?? 'bg-secondary'; ?>">
                                                        <?php echo $status_texts[$payment['status']] ?? $payment['status']; ?>
                                                    </span>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <?php else: ?>
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <i class="ri-information-line"></i> No hay pagos generados para este contrato.
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>

                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.info-group {
    margin-bottom: 1rem;
}

.info-group label {
    color: #6c757d;
    font-size: 0.9rem;
    margin-bottom: 0.25rem;
}

.info-group p {
    color: #212529;
    font-size: 1rem;
    font-weight: 500;
}
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>