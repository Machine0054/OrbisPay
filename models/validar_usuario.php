<?php
// validar_usuario.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
require_once('conexion.php');

// Verificar método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$conn = conectar();

// Verificar que se envió la acción
if (!isset($_POST['action']) || $_POST['action'] !== 'check_exists') {
    echo json_encode(['success' => false, 'message' => 'Acción no válida']);
    exit;
}

// Verificar qué campo se está validando
if (isset($_POST['usuario'])) {
    $usuario = trim($_POST['usuario']);
    
    if (empty($usuario)) {
        echo json_encode(['success' => false, 'message' => 'Usuario requerido']);
        exit;
    }
    
    // Validar formato de usuario
    if (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $usuario)) {
        echo json_encode(['success' => false, 'message' => 'Formato de usuario no válido']);
        exit;
    }
    
    // Verificar si el usuario ya existe
    $stmt = $conn->prepare("SELECT id FROM T_USUARIOS WHERE usuario = ?");
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Error en la consulta']);
        exit;
    }
    
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'El nombre de usuario ya está registrado']);
    } else {
        echo json_encode(['success' => true, 'message' => 'Usuario disponible']);
    }
    
    $stmt->close();
    
} elseif (isset($_POST['correo'])) {
    $correo = trim($_POST['correo']);
    
    if (empty($correo)) {
        echo json_encode(['success' => false, 'message' => 'Correo requerido']);
        exit;
    }
    
    // Validar formato de correo
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Formato de correo no válido']);
        exit;
    }
    
    // Verificar si el correo ya existe
    $stmt = $conn->prepare("SELECT id FROM T_USUARIOS WHERE correo = ?");
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Error en la consulta']);
        exit;
    }
    
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'El correo electrónico ya está registrado']);
    } else {
        echo json_encode(['success' => true, 'message' => 'Correo disponible']);
    }
    
    $stmt->close();
    
} else {
    echo json_encode(['success' => false, 'message' => 'No se especificó qué validar']);
}

$conn->close();
?>