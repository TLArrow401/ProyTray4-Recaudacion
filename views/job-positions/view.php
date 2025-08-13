<?php
// Vista de detalles de un cargo específico

// Incluir el controlador
require_once __DIR__ . '/../../controllers/JobPositionsController.php';

$jobPositionsController = new JobPositionsController();

// Obtener el ID del cargo
$id = $_GET['id'] ?? null;

if (!$id) {
    header('Location: index.php');
    exit;
}

// Usar el controlador para obtener los datos
$result = $jobPositionsController->view($id);

// Si hay error, mostrar mensaje o redirigir
if (!$result['success']) {
    $_SESSION['flash_message'] = [
        'type' => 'error',
        'message' => $result['message']
    ];
    header('Location: index.php');
    exit;
}

// Extraer variables para la vista
$job_position = $result['job_position'];
$staff = $result['staff'];
$page_title = $result['page_title'];

// Incluir header y layouts
require_once __DIR__ . '/../layouts/header.php';
include __DIR__ . '/../layouts/navigation.php';
include __DIR__ . '/../layouts/navigation-top.php';
?>

<div class="main-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <!-- Navegación de breadcrumb -->
                <nav aria-label="breadcrumb" class="mb-3">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php">Cargos</a></li>
                        <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($job_position['name']); ?></li>
                    </ol>
                </nav>
                
                <!-- Información del cargo -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="ri-briefcase-line mr-1"></i>
                            <?php echo htmlspecialchars($page_title); ?>
                        </h5>
                        <div class="btn-group" role="group">
                            <a href="index.php" class="btn btn-secondary">
                                <i class="ri-arrow-left-line"></i> Volver al listado
                            </a>
                            <a href="edit.php?id=<?php echo $job_position['id']; ?>" class="btn btn-warning">
                                <i class="ri-edit-line"></i> Editar
                            </a>
                            <?php if ($job_position['staff_count'] == 0): ?>
                            <button type="button" 
                                    class="btn btn-danger" 
                                    onclick="confirmDelete(<?php echo $job_position['id']; ?>, '<?php echo htmlspecialchars($job_position['name'], ENT_QUOTES); ?>')">
                                <i class="ri-delete-bin-line"></i> Eliminar
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <th width="30%">ID:</th>
                                        <td><?php echo htmlspecialchars($job_position['id']); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Nombre del Cargo:</th>
                                        <td><strong><?php echo htmlspecialchars($job_position['name']); ?></strong></td>
                                    </tr>
                                    <tr>
                                        <th>Personal Asignado:</th>
                                        <td>
                                            <span class="badge bg-<?php echo $job_position['staff_count'] > 0 ? 'primary' : 'secondary'; ?> fs-6">
                                                <?php echo $job_position['staff_count']; ?> persona<?php echo $job_position['staff_count'] != 1 ? 's' : ''; ?>
                                            </span>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <div class="alert alert-info">
                                    <h6 class="alert-heading">
                                        <i class="ri-information-line"></i> Información
                                    </h6>
                                    <p class="mb-0">
                                        <?php if ($job_position['staff_count'] > 0): ?>
                                            Este cargo tiene personal asignado, por lo que no puede ser eliminado hasta que se reasigne o desactive a todos los empleados con este cargo.
                                        <?php else: ?>
                                            Este cargo no tiene personal asignado y puede ser eliminado si es necesario.
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Personal asignado al cargo -->
                <?php if (!empty($staff)): ?>
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="ri-team-line mr-1"></i>
                            Personal Asignado (<?php echo count($staff); ?>)
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre Completo</th>
                                        <th>Cédula</th>
                                        <th>Departamento</th>
                                        <th>Fecha de Ingreso</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($staff as $person): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($person['id']); ?></td>
                                        <td>
                                            <strong>
                                                <?php echo htmlspecialchars($person['first_name'] . ' ' . $person['last_name']); ?>
                                            </strong>
                                        </td>
                                        <td><?php echo htmlspecialchars($person['id_number']); ?></td>
                                        <td>
                                            <span class="badge bg-secondary">
                                                <?php echo htmlspecialchars($person['department_name']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php 
                                            $hire_date = new DateTime($person['hire_date']);
                                            echo $hire_date->format('d/m/Y'); 
                                            ?>
                                        </td>
                                        <td>
                                            <?php
                                            $status_colors = [
                                                'active' => 'success',
                                                'inactive' => 'danger',
                                                'vacation' => 'info',
                                                'leave' => 'warning',
                                                'suspended' => 'dark'
                                            ];
                                            $status_labels = [
                                                'active' => 'Activo',
                                                'inactive' => 'Inactivo',
                                                'vacation' => 'Vacaciones',
                                                'leave' => 'Permiso',
                                                'suspended' => 'Suspendido'
                                            ];
                                            $color = $status_colors[$person['status']] ?? 'secondary';
                                            $label = $status_labels[$person['status']] ?? $person['status'];
                                            ?>
                                            <span class="badge bg-<?php echo $color; ?>">
                                                <?php echo $label; ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <?php else: ?>
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="ri-team-line mr-1"></i>
                            Personal Asignado
                        </h5>
                    </div>
                    <div class="card-body text-center py-5">
                        <i class="ri-team-line text-muted" style="font-size: 3rem;"></i>
                        <h5 class="text-muted mt-2">No hay personal asignado</h5>
                        <p class="text-muted">Este cargo actualmente no tiene empleados asignados.</p>
                    </div>
                </div>
                <?php endif; ?>
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