<?php
// Vista para ver detalles del adjudicatario

// Incluir el controlador
require_once __DIR__ . '/../../controllers/AwardeesController.php';

$awardeesController = new AwardeesController();

// Obtener ID del adjudicatario
$id = $_GET['id'] ?? null;

if (!$id) {
    $_SESSION['flash_message'] = [
        'type' => 'error',
        'message' => 'ID de adjudicatario no proporcionado'
    ];
    header('Location: index.php');
    exit;
}

// Obtener datos del adjudicatario
$viewData = $awardeesController->view($id);

if (!$viewData['success']) {
    $_SESSION['flash_message'] = [
        'type' => 'error',
        'message' => $viewData['message']
    ];
    header('Location: index.php');
    exit;
}

$awardee = $viewData['awardee'];
$full_name = $viewData['full_name'];
$page_title = $viewData['page_title'];

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
                            <!-- Información Personal -->
                            <div class="col-lg-8">
                                <div class="card border-0 shadow-sm">
                                    <div class="card-header bg-primary text-white">
                                        <h6 class="card-title mb-0">
                                            <i class="ri-user-line"></i>
                                            Información Personal
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold text-muted">Primer Nombre:</label>
                                                <p class="fs-5 mb-2"><?php echo htmlspecialchars($awardee['first_name']); ?></p>
                                            </div>
                                            
                                            <?php if (!empty($awardee['middle_name'])): ?>
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold text-muted">Segundo Nombre:</label>
                                                <p class="fs-5 mb-2"><?php echo htmlspecialchars($awardee['middle_name']); ?></p>
                                            </div>
                                            <?php endif; ?>
                                            
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold text-muted">Primer Apellido:</label>
                                                <p class="fs-5 mb-2"><?php echo htmlspecialchars($awardee['last_name']); ?></p>
                                            </div>
                                            
                                            <?php if (!empty($awardee['second_last_name'])): ?>
                                            <div class="col-md-6">
                                                <label class="form-label fw-bold text-muted">Segundo Apellido:</label>
                                                <p class="fs-5 mb-2"><?php echo htmlspecialchars($awardee['second_last_name']); ?></p>
                                            </div>
                                            <?php endif; ?>
                                            
                                            <div class="col-12">
                                                <label class="form-label fw-bold text-muted">Nombre Completo:</label>
                                                <p class="fs-4 mb-2 text-primary fw-bold"><?php echo htmlspecialchars($full_name); ?></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Información de Identificación -->
                            <div class="col-lg-4">
                                <div class="card border-0 shadow-sm">
                                    <div class="card-header bg-info text-white">
                                        <h6 class="card-title mb-0">
                                            <i class="ri-id-card-line"></i>
                                            Identificación
                                        </h6>
                                    </div>
                                    <div class="card-body text-center">
                                        <div class="mb-3">
                                            <i class="ri-id-card-line display-4 text-info"></i>
                                        </div>
                                        <h5 class="text-muted">Número de Identificación</h5>
                                        <h3 class="text-primary fw-bold"><?php echo htmlspecialchars($awardee['id_number']); ?></h3>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-4 mt-2">
                            <!-- Información de Contacto -->
                            <div class="col-lg-6">
                                <div class="card border-0 shadow-sm">
                                    <div class="card-header bg-success text-white">
                                        <h6 class="card-title mb-0">
                                            <i class="ri-contacts-line"></i>
                                            Información de Contacto
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <div class="col-12">
                                                <label class="form-label fw-bold text-muted">
                                                    <i class="ri-phone-line"></i> Teléfono:
                                                </label>
                                                <?php if (!empty($awardee['phone'])): ?>
                                                    <p class="fs-5 mb-2">
                                                        <a href="tel:<?php echo htmlspecialchars($awardee['phone']); ?>" class="text-decoration-none">
                                                            <?php echo htmlspecialchars($awardee['phone']); ?>
                                                        </a>
                                                    </p>
                                                <?php else: ?>
                                                    <p class="text-muted fst-italic">No registrado</p>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <div class="col-12">
                                                <label class="form-label fw-bold text-muted">
                                                    <i class="ri-mail-line"></i> Correo Electrónico:
                                                </label>
                                                <?php if (!empty($awardee['email'])): ?>
                                                    <p class="fs-5 mb-2">
                                                        <a href="mailto:<?php echo htmlspecialchars($awardee['email']); ?>" class="text-decoration-none">
                                                            <?php echo htmlspecialchars($awardee['email']); ?>
                                                        </a>
                                                    </p>
                                                <?php else: ?>
                                                    <p class="text-muted fst-italic">No registrado</p>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Dirección -->
                            <div class="col-lg-6">
                                <div class="card border-0 shadow-sm">
                                    <div class="card-header bg-warning text-dark">
                                        <h6 class="card-title mb-0">
                                            <i class="ri-map-pin-line"></i>
                                            Dirección
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <?php if (!empty($awardee['address'])): ?>
                                            <p class="fs-5 mb-0" style="line-height: 1.6;">
                                                <?php echo nl2br(htmlspecialchars($awardee['address'])); ?>
                                            </p>
                                        <?php else: ?>
                                            <p class="text-muted fst-italic text-center py-4">
                                                <i class="ri-map-pin-line display-6 d-block mb-2"></i>
                                                No se ha registrado dirección
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Información del Sistema -->
                        <div class="row g-4 mt-2">
                            <div class="col-12">
                                <div class="card border-0 shadow-sm">
                                    <div class="card-header bg-secondary text-white">
                                        <h6 class="card-title mb-0">
                                            <i class="ri-information-line"></i>
                                            Información del Sistema
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row text-muted">
                                            <div class="col-md-6">
                                                <small>
                                                    <strong>ID del Registro:</strong> 
                                                    #<?php echo htmlspecialchars($awardee['id']); ?>
                                                </small>
                                            </div>
                                            <div class="col-md-6">
                                                <small>
                                                    <strong>Tipo de Registro:</strong> 
                                                    Adjudicatario
                                                </small>
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
                                            <i class="ri-edit-line"></i> Editar Adjudicatario
                                        </a>
                                        <button type="button" 
                                                class="btn btn-danger" 
                                                onclick="confirmDelete(<?php echo $awardee['id']; ?>, '<?php echo htmlspecialchars($full_name, ENT_QUOTES); ?>')">
                                            <i class="ri-delete-bin-line"></i> Eliminar
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
                // Redirigir al listado
                window.location.href = 'index.php';
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

// Función para copiar información al portapapeles
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        // Mostrar mensaje de éxito
        const toast = document.createElement('div');
        toast.className = 'toast-notification';
        toast.innerHTML = '<i class="ri-check-line"></i> Copiado al portapapeles';
        toast.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: #198754;
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
            z-index: 9999;
            opacity: 0;
            transition: opacity 0.3s;
        `;
        document.body.appendChild(toast);
        
        setTimeout(() => toast.style.opacity = '1', 100);
        setTimeout(() => {
            toast.style.opacity = '0';
            setTimeout(() => document.body.removeChild(toast), 300);
        }, 2000);
    });
}

// Hacer que los números de teléfono y emails sean clicables para copiar
document.addEventListener('DOMContentLoaded', function() {
    // Agregar función de copia a elementos específicos
    const phoneElement = document.querySelector('a[href^="tel:"]');
    const emailElement = document.querySelector('a[href^="mailto:"]');
    
    if (phoneElement) {
        phoneElement.addEventListener('dblclick', function(e) {
            e.preventDefault();
            copyToClipboard(this.textContent);
        });
        phoneElement.title = 'Doble click para copiar';
    }
    
    if (emailElement) {
        emailElement.addEventListener('dblclick', function(e) {
            e.preventDefault();
            copyToClipboard(this.textContent);
        });
        emailElement.title = 'Doble click para copiar';
    }
});
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>