# UserController - Documentación

## Descripción

El `UserController` centraliza toda la lógica de gestión de usuarios siguiendo el patrón MVC. Este controlador maneja las operaciones CRUD (Crear, Leer, Actualizar, Desactivar) para usuarios del sistema.

## Características Principales

- ✅ Separación completa de la lógica de negocio de las vistas
- ✅ Manejo centralizado de permisos y validaciones
- ✅ Métodos para todas las operaciones CRUD
- ✅ Validación de datos de entrada
- ✅ Manejo de errores y respuestas consistentes
- ✅ Compatible con roles de RRHH y Jefes de Departamento

## Métodos Disponibles

### 1. `index($params = [])`
**Propósito:** Listar usuarios con filtros y paginación

**Parámetros:**
- `page` (opcional): Número de página para paginación
- `department` (opcional): Filtro por departamento (solo para RRHH)

**Retorna:**
```php
[
    'success' => true/false,
    'users' => array,           // Lista de usuarios
    'total_users' => int,       // Total de usuarios
    'total_pages' => int,       // Total de páginas
    'current_page' => int,      // Página actual
    'departments' => array,     // Departamentos disponibles
    'department_filter' => string,
    'page_title' => string,
    'is_manager' => array/false,
    'is_rrhh' => bool
]
```

### 2. `create($params = [])`
**Propósito:** Mostrar formulario y procesar creación de usuarios

**Parámetros (cuando `_method` = 'POST'):**
- `staff_id`: ID del personal a asociar
- `username`: Nombre de usuario
- `password`: Contraseña
- `confirm_password`: Confirmación de contraseña
- `email`: Email del usuario

**Retorna:**
```php
[
    'success' => true/false,
    'message' => string,
    'messageType' => string,    // 'success', 'danger'
    'errors' => array,
    'staff_id' => string,
    'username' => string,
    'email' => string,
    'available_staff' => array,
    'is_manager' => array/false,
    'is_rrhh' => bool
]
```

### 3. `view($user_id)`
**Propósito:** Mostrar detalles de un usuario específico

**Parámetros:**
- `user_id`: ID del usuario a ver

**Retorna:**
```php
[
    'success' => true/false,
    'user' => array,           // Datos del usuario
    'is_manager' => array/false,
    'is_rrhh' => bool,
    'message' => string,       // En caso de error
    'redirect' => string       // URL de redirección si hay error
]
```

### 4. `edit($user_id, $params = [])`
**Propósito:** Mostrar formulario y procesar edición de usuarios

**Parámetros:**
- `user_id`: ID del usuario a editar
- `params` (cuando `_method` = 'POST'):
  - `username`: Nuevo nombre de usuario
  - `email`: Nuevo email
  - `status`: Estado del usuario ('active'/'inactive')
  - `change_password`: Checkbox para cambiar contraseña
  - `password`: Nueva contraseña (si change_password está marcado)
  - `confirm_password`: Confirmación de nueva contraseña

**Retorna:**
```php
[
    'success' => true/false,
    'user' => array,
    'message' => string,
    'messageType' => string,
    'errors' => array,
    'is_manager' => array/false,
    'is_rrhh' => bool
]
```

### 5. `deactivate($user_id)`
**Propósito:** Desactivar un usuario

**Parámetros:**
- `user_id`: ID del usuario a desactivar

**Retorna:**
```php
[
    'success' => true/false,
    'message' => string,
    'redirect' => string       // URL de redirección
]
```

### 6. `reactivate($user_id)`
**Propósito:** Reactivar un usuario

**Parámetros:**
- `user_id`: ID del usuario a reactivar

**Retorna:**
```php
[
    'success' => true/false,
    'message' => string,
    'redirect' => string       // URL de redirección
]
```

## Ejemplos de Uso

### 1. Listar Usuarios en una Vista

```php
<?php
require_once __DIR__ . '/../../controllers/UserController.php';

$userController = new UserController();

// Preparar parámetros
$params = [
    'page' => $_GET['page'] ?? 1,
    'department' => $_GET['department'] ?? ''
];

// Obtener datos
$result = $userController->index($params);

// Verificar permisos
if (!$result['success'] && isset($result['redirect'])) {
    header('Location: ' . $result['redirect']);
    exit;
}

// Usar las variables
$users = $result['users'];
$total_pages = $result['total_pages'];
// ... resto de variables
?>
```

### 2. Crear Usuario

```php
<?php
require_once __DIR__ . '/../../controllers/UserController.php';

$userController = new UserController();

// Preparar parámetros
$params = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $params = array_merge($_POST, ['_method' => 'POST']);
}

// Procesar
$result = $userController->create($params);

// Extraer variables para la vista
$message = $result['message'];
$errors = $result['errors'];
$available_staff = $result['available_staff'];
// ... resto de variables
?>
```

### 3. Desactivar Usuario

```php
<?php
require_once __DIR__ . '/../../controllers/UserController.php';

$userController = new UserController();
$user_id = $_GET['id'] ?? null;

$result = $userController->deactivate($user_id);

// Redireccionar según resultado
if (isset($result['redirect'])) {
    header('Location: ' . $result['redirect']);
} else {
    header('Location: index.php?error=unknown');
}
exit;
?>
```

### 4. Editar Usuario

```php
<?php
require_once __DIR__ . '/../../controllers/UserController.php';

$userController = new UserController();
$user_id = $_GET['id'] ?? null;

// Preparar parámetros
$params = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $params = array_merge($_POST, ['_method' => 'POST']);
}

// Procesar
$result = $userController->edit($user_id, $params);

// Verificar errores de permisos
if (!$result['success'] && isset($result['redirect'])) {
    header('Location: ' . $result['redirect']);
    exit;
}

// Usar variables
$user = $result['user'];
$message = $result['message'];
$errors = $result['errors'];
?>
```

## Validaciones Implementadas

### Creación de Usuarios
- Staff ID requerido
- Username: mínimo 3 caracteres, único
- Password: mínimo 6 caracteres
- Confirmación de contraseña
- Email: formato válido, único

### Edición de Usuarios
- Username: mínimo 3 caracteres, único (excepto usuario actual)
- Email: formato válido, único (excepto usuario actual)
- Estado: solo 'active' o 'inactive'
- Password (si se cambia): mínimo 6 caracteres con confirmación

## Permisos y Seguridad

### RRHH (Recursos Humanos)
- ✅ Ver todos los usuarios
- ✅ Crear usuarios para cualquier departamento
- ✅ Editar cualquier usuario
- ✅ Desactivar/reactivar cualquier usuario

### Jefes de Departamento
- ✅ Ver usuarios de su departamento únicamente
- ✅ Crear usuarios solo para personal de su departamento
- ✅ Editar usuarios de su departamento únicamente
- ✅ Desactivar/reactivar usuarios de su departamento únicamente

## Manejo de Errores

El controlador maneja varios tipos de errores:

1. **Errores de Permisos**: Redirección automática con mensaje
2. **Errores de Validación**: Array de errores para mostrar en la vista
3. **Errores de Base de Datos**: Mensajes de error amigables
4. **Recursos No Encontrados**: Redirección con mensaje apropiado

## Migración desde Vistas Existentes

Para migrar las vistas existentes:

1. **Reemplazar la lógica de negocio** con llamadas al controlador
2. **Mantener solo la presentación** en las vistas
3. **Usar las variables** retornadas por el controlador
4. **Manejar redirecciones** según las respuestas del controlador

## Archivos de Ejemplo

- `views/users/create_new.php`: Ejemplo de vista de creación
- `views/users/index_new.php`: Ejemplo de vista de listado
- `views/users/deactivate_new.php`: Ejemplo de acción de desactivación

## Dependencias

- `models/UserModel.php`: Modelo de datos de usuarios
- `middleware/AuthMiddleware.php`: Middleware de autenticación y autorización

## Notas Importantes

1. **Sesiones**: El controlador maneja automáticamente el inicio de sesiones cuando es necesario
2. **Transacciones**: Las operaciones críticas deberían usar transacciones de base de datos
3. **Logs**: Los errores se registran automáticamente en el modelo
4. **Seguridad**: Todas las entradas se validan antes del procesamiento