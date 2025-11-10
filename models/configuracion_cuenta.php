<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json');

require_once 'conexion.php';
$conn = conectar();

// Validar sesión
$user_id = $_SESSION['id'] ?? 0;
if ($user_id === 0) {
    http_response_code(401 );
    echo json_encode(['success' => false, 'message' => 'Sesión no válida.']);
    exit;
}

// Determinar la acción (GET o POST)
$method = $_SERVER['REQUEST_METHOD'];
$action = ($method === 'GET') ? ($_GET['action'] ?? '') : ($_POST['action'] ?? '');

switch ($action) {
    case 'getUserData':
        getUserData($conn, $user_id);
        break;
    case 'updateUserData':
        updateUserData($conn, $user_id);
        break;
    case 'updatePassword':
        updatePassword($conn, $user_id);
        break;
    default:
        http_response_code(400 );
        echo json_encode(['success' => false, 'message' => 'Acción no reconocida.']);
}

$conn->close();

// --- FUNCIONES ---

function getUserData($conn, $user_id) {
    // Asegúrate de seleccionar las columnas que necesitas (TELEFONO, MONEDA_PREF, etc.)
    $sql = "SELECT NOMBRE, APELLIDO, CORREO, TELEFONO, MONEDA_PREF, AVATAR_URL FROM T_USUARIOS WHERE ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user) {
        echo json_encode(['success' => true, 'data' => $user]);
    } else {
        http_response_code(404 );
        echo json_encode(['success' => false, 'message' => 'Usuario no encontrado.']);
    }
    $stmt->close();
}

function updateUserData($conn, $user_id) {
    // Recoger datos del POST
    $nombre = trim($_POST['nombre'] ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $telefono = trim($_POST['telefono'] ?? '');
    $moneda = trim($_POST['moneda'] ?? 'COP');

    // Validaciones básicas
    if (empty($nombre) || empty($apellido) || !$email) {
        http_response_code(400 );
        echo json_encode(['success' => false, 'message' => 'Nombre, apellido y correo electrónico válido son requeridos.']);
        exit;
    }

     $userIdInt = (int)$user_id;

    $sql = "UPDATE T_USUARIOS SET NOMBRE = ?, APELLIDO = ?, CORREO = ?, TELEFONO = ?, MONEDA_PREF = ? WHERE ID = ?";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        // Añadir manejo de error si la preparación falla
        http_response_code(500 );
        echo json_encode(['success' => false, 'message' => 'Error al preparar la consulta: ' . $conn->error]);
        exit;
    }

    // Usar la variable forzada a entero
    $stmt->bind_param('sssssi', $nombre, $apellido, $email, $telefono, $moneda, $userIdInt);

    if ($stmt->execute()) {
        // Actualizar la sesión con los nuevos datos
        $_SESSION['nombre'] = $nombre;
        $_SESSION['apellido'] = $apellido;
        $_SESSION['correo'] = $email;
        echo json_encode(['success' => true, 'message' => 'Perfil actualizado.']);
    } else {
        http_response_code(500 );
        echo json_encode(['success' => false, 'message' => 'Error al actualizar el perfil en la base de datos.']);
    }
    $stmt->close();
}
/**
 * Actualizar la contraseña del usuario de forma segura.
 */
function updatePassword($conn, $user_id) {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        http_response_code(400 );
        echo json_encode(['success' => false, 'message' => 'Todos los campos son obligatorios.']);
        exit;
    }
    if (strlen($new_password) < 8) {
        http_response_code(400 );
        echo json_encode(['success' => false, 'message' => 'La nueva contraseña debe tener al menos 8 caracteres.']);
        exit;
    }
    if ($new_password !== $confirm_password) {
        http_response_code(400 );
        echo json_encode(['success' => false, 'message' => 'Las nuevas contraseñas no coinciden.']);
        exit;
    }

    $sql_select = "SELECT PASSWORD FROM T_USUARIOS WHERE ID = ?";
    $stmt_select = $conn->prepare($sql_select);
    $stmt_select->bind_param('i', $user_id);
    $stmt_select->execute();
    $result = $stmt_select->get_result();
    $user = $result->fetch_assoc();
    $stmt_select->close();

    if (!$user || !password_verify($current_password, $user['PASSWORD'])) {
        http_response_code(403 ); // Forbidden
        echo json_encode(['success' => false, 'message' => 'La contraseña actual es incorrecta.']);
        exit;
    }

    
    $new_password_hashed = password_hash($new_password, PASSWORD_DEFAULT);
    $sql_update = "UPDATE T_USUARIOS SET PASSWORD = ? WHERE ID = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param('si', $new_password_hashed, $user_id);

    if ($stmt_update->execute()) {
        echo json_encode(['success' => true, 'message' => 'Contraseña actualizada exitosamente.']);
    } else {
        http_response_code(500 );
        echo json_encode(['success' => false, 'message' => 'Error al actualizar la contraseña en la base de datos.']);
    }
    $stmt_update->close();
}
?>