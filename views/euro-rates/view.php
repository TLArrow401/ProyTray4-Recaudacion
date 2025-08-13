<?php
// Vista para ver detalles de tasa de euro

// Incluir el controlador
require_once __DIR__ . '/../../controllers/EuroRatesController.php';

$euroRatesController = new EuroRatesController();

// Obtener ID de la URL
$id = $_GET['id'] ?? null;

if (!$id) {
    $_SESSION['flash_message'] = [
        'type' => 'error',
        'message' => 'ID de tasa de euro no proporcionado'
    ];
    header('Location: index.php');
    exit;
}

// Usar el controlador para obtener los datos
$result = $euroRatesController->view(['id' => $id]);

// Si hay error, redirigir
if (!$result['success']) {
    $_SESSION['flash_message'] = [
        'type' => 'error',
        'message' => $result['message']
    ];
    if (isset($result['redirect'])) {
        header('Location: ' . $result['redirect']);
    } else {
        header('Location: index.php');
    }
    exit;
}

// Extraer variables para la vista
$euro_rate = $result['euro_rate'];
$formatted_period = $result['formatted_period'];
$formatted_value = $result['formatted_value'];
$page_title = $result['page_title'];

// Crear instancia del modelo para funciones auxiliares
$euroRatesModel = new EuroRatesModel();

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
                        <div class="btn-group">
                            <a href="edit.php?id=<?php echo $id; ?>" class="btn btn-warning">
                                <i class="ri-edit-line"></i> Editar
                            </a>
                            <a href="index.php" class="btn btn-secondary">
                                <i class="ri-arrow-left-line"></i> Volver al Listado
                            </a>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="row g-4">
                            <!-- Información Principal -->
                            <div class="col-lg-8">
                                <div class="card border-0 shadow-sm">
                                    <div class="card-header bg-primary text-white">
                                        <h6 class="card-title mb-0">
                                            <i class="ri-money-euro-circle-line"></i>
                                            Información de la Tasa
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <div class="col-12 text-center">
                                                <div class="mb-3">
                                                    <i class="ri-exchange-euro-line display-1 text-primary"></i>
                                                </div>
                                                <h2 class="text-primary fw-bold mb-1">
                                                    <?php echo htmlspecialchars($formatted_value); ?>
                                                </h2>
                                                <p class="text-muted">Valor de 1 Euro en Bolívares</p>
                                            </div>
                                            
                                            <div class="col-12">
                                                <hr>
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold text-muted">Valor Numérico:</label>
                                                <p class="fs-5 mb-2"><?php echo number_format($euro_rate['bs_value'], 2, '.', ','); ?></p>
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold text-muted">Equivalencia:</label>
                                                <p class="fs-5 mb-2">1 EUR = <?php echo number_format($euro_rate['bs_value'], 2, '.', ','); ?> VES</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Información del Período -->
                            <div class="col-lg-4">
                                <div class="card border-0 shadow-sm">
                                    <div class="card-header bg-info text-white">
                                        <h6 class="card-title mb-0">
                                            <i class="ri-calendar-line"></i>
                                            Período
                                        </h6>
                                    </div>
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            <i class="ri-calendar-check-line display-4 text-info"></i>
                                        </div>
                                        <?php if ($formatted_period === 'Sin período especificado'): ?>
                                            <h5 class="text-muted">Sin período específico</h5>
                                            <p class="small text-muted">Esta tasa no está asociada a un mes/año particular</p>
                                        <?php else: ?>
                                            <h4 class="text-primary fw-bold"><?php echo htmlspecialchars($formatted_period); ?></h4>
                                            <p class="small text-muted">Período de vigencia de la tasa</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-4 mt-2">
                            <!-- Calculadora de Conversión -->
                            <div class="col-lg-6">
                                <div class="card border-0 shadow-sm">
                                    <div class="card-header bg-success text-white">
                                        <h6 class="card-title mb-0">
                                            <i class="ri-calculator-line"></i>
                                            Calculadora de Conversión
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <div class="col-12">
                                                <label for="euros" class="form-label fw-bold">Cantidad en Euros:</label>
                                                <div class="input-group">
                                                    <span class="input-group-text">€</span>
                                                    <input type="number" class="form-control" id="euros" placeholder="1.00" step="0.01" min="0">
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <label for="bolivares" class="form-label fw-bold">Equivalente en Bolívares:</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control" id="bolivares" readonly>
                                                    <span class="input-group-text">Bs.</span>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <button type="button" class="btn btn-success w-100" onclick="calculate()">
                                                    <i class="ri-refresh-line"></i> Calcular
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Información Adicional -->
                            <div class="col-lg-6">
                                <div class="card border-0 shadow-sm">
                                    <div class="card-header bg-warning text-dark">
                                        <h6 class="card-title mb-0">
                                            <i class="ri-information-line"></i>
                                            Detalles del Registro
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <div class="col-12">
                                                <label class="form-label fw-bold text-muted">ID del Registro:</label>
                                                <p class="mb-2">
                                                    <span class="badge bg-secondary fs-6">#<?php echo htmlspecialchars($euro_rate['id']); ?></span>
                                                </p>
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold text-muted">Mes:</label>
                                                <p class="mb-2">
                                                    <?php if (!empty($euro_rate['month'])): ?>
                                                        <?php echo htmlspecialchars(ucfirst($euro_rate['month'])); ?>
                                                    <?php else: ?>
                                                        <span class="text-muted fst-italic">No especificado</span>
                                                    <?php endif; ?>
                                                </p>
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold text-muted">Año:</label>
                                                <p class="mb-2">
                                                    <?php if (!empty($euro_rate['year'])): ?>
                                                        <?php echo htmlspecialchars($euro_rate['year']); ?>
                                                    <?php else: ?>
                                                        <span class="text-muted fst-italic">No especificado</span>
                                                    <?php endif; ?>
                                                </p>
                                            </div>
                                            
                                            <div class="col-12">
                                                <label class="form-label fw-bold text-muted">Tipo de Tasa:</label>
                                                <p class="mb-2">
                                                    <?php if (!empty($euro_rate['month']) && !empty($euro_rate['year'])): ?>
                                                        <span class="badge bg-info">Tasa Mensual</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary">Tasa General</span>
                                                    <?php endif; ?>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Comparación y Análisis -->
                        <div class="row g-4 mt-2">
                            <div class="col-12">
                                <div class="card border-0 shadow-sm">
                                    <div class="card-header bg-secondary text-white">
                                        <h6 class="card-title mb-0">
                                            <i class="ri-line-chart-line"></i>
                                            Análisis de la Tasa
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row text-center">
                                            <div class="col-md-3">
                                                <div class="border-end">
                                                    <h6 class="text-muted">Valor Base</h6>
                                                    <p class="h5 text-primary"><?php echo number_format($euro_rate['bs_value'], 2); ?></p>
                                                    <small class="text-muted">Bs. por Euro</small>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="border-end">
                                                    <h6 class="text-muted">10 Euros</h6>
                                                    <p class="h5 text-success"><?php echo number_format($euro_rate['bs_value'] * 10, 2); ?></p>
                                                    <small class="text-muted">Bolívares</small>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="border-end">
                                                    <h6 class="text-muted">100 Euros</h6>
                                                    <p class="h5 text-warning"><?php echo number_format($euro_rate['bs_value'] * 100, 2); ?></p>
                                                    <small class="text-muted">Bolívares</small>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <h6 class="text-muted">1000 Euros</h6>
                                                <p class="h5 text-danger"><?php echo number_format($euro_rate['bs_value'] * 1000, 2); ?></p>
                                                <small class="text-muted">Bolívares</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Acciones -->
                        <div class="row g-4 mt-3">
                            <div class="col-12">
                                <div class="d-flex justify-content-between flex-wrap gap-2">
                                    <div>
                                        <a href="index.php" class="btn btn-secondary">
                                            <i class="ri-arrow-left-line"></i> Volver al Listado
                                        </a>
                                    </div>
                                    
                                    <div class="btn-group">
                                        <a href="edit.php?id=<?php echo $id; ?>" class="btn btn-warning">
                                            <i class="ri-edit-line"></i> Editar Tasa
                                        </a>
                                        <button type="button" 
                                                class="btn btn-danger" 
                                                onclick="confirmDelete(<?php echo $euro_rate['id']; ?>, '<?php echo htmlspecialchars($formatted_value, ENT_QUOTES); ?>', '<?php echo htmlspecialchars($formatted_period, ENT_QUOTES); ?>')">
                                            <i class="ri-delete-bin-line"></i> Eliminar
                                        </button>
                                        <button type="button" 
                                                class="btn btn-info" 
                                                onclick="copyToClipboard('<?php echo number_format($euro_rate['bs_value'], 2, '.', ''); ?>')">
                                            <i class="ri-file-copy-line"></i> Copiar Valor
                                        </button>
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

<!-- Scripts -->
<script>
// Variables globales para cálculos
const RATE_VALUE = <?php echo $euro_rate['bs_value']; ?>;

// Función para calcular conversión
function calculate() {
    const euros = parseFloat(document.getElementById('euros').value) || 0;
    const bolivares = euros * RATE_VALUE;
    
    document.getElementById('bolivares').value = bolivares.toLocaleString('es-VE', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

// Calcular automáticamente mientras se escribe
document.getElementById('euros').addEventListener('input', calculate);

// Configurar valor inicial
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('euros').value = '1.00';
    calculate();
});

// Función para confirmación de eliminación
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
                // Redirigir al listado
                window.location.href = 'index.php';
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

// Función para copiar al portapapeles
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        // Mostrar mensaje de éxito
        const toast = document.createElement('div');
        toast.className = 'toast-notification';
        toast.innerHTML = '<i class="ri-check-line"></i> Valor copiado al portapapeles: ' + text;
        toast.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: #198754;
            color: white;
            padding: 15px 20px;
            border-radius: 5px;
            z-index: 9999;
            opacity: 0;
            transition: opacity 0.3s;
            max-width: 300px;
        `;
        document.body.appendChild(toast);
        
        setTimeout(() => toast.style.opacity = '1', 100);
        setTimeout(() => {
            toast.style.opacity = '0';
            setTimeout(() => document.body.removeChild(toast), 300);
        }, 3000);
    }).catch(function(error) {
        console.error('Error al copiar al portapapeles:', error);
        alert('No se pudo copiar al portapapeles');
    });
}

// Función para formatear números mientras se escribe en la calculadora
document.getElementById('euros').addEventListener('input', function(e) {
    let value = e.target.value;
    
    // Permitir solo números y punto decimal
    value = value.replace(/[^0-9.]/g, '');
    
    // Asegurar solo un punto decimal
    const parts = value.split('.');
    if (parts.length > 2) {
        value = parts[0] + '.' + parts.slice(1).join('');
    }
    
    // Limitar decimales a 2 posiciones
    if (parts.length === 2 && parts[1].length > 2) {
        value = parts[0] + '.' + parts[1].substring(0, 2);
    }
    
    e.target.value = value;
});

// Función para mostrar información adicional
function showRateInfo() {
    const info = `
        Información de la Tasa:
        - Valor: <?php echo $formatted_value; ?>
        - Período: <?php echo $formatted_period; ?>
        - ID: #<?php echo $euro_rate['id']; ?>
        
        Conversiones rápidas:
        - 1 EUR = <?php echo number_format($euro_rate['bs_value'], 2); ?> Bs.
        - 10 EUR = <?php echo number_format($euro_rate['bs_value'] * 10, 2); ?> Bs.
        - 100 EUR = <?php echo number_format($euro_rate['bs_value'] * 100, 2); ?> Bs.
    `;
    alert(info);
}
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>