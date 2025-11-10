<?php 
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Captura errores y los retorna como JSON
set_error_handler(function ($severity, $message, $file, $line) {
    http_response_code(500 );
    echo json_encode([
        'success' => false,
        'error' => $message,
        'linea' => $line,
        'archivo' => $file
    ]);
    exit;
});

header('Content-Type: application/json');
require_once('conexion.php');
$conn = conectar();

// Verificar método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405 );
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

// Leer datos del formulario
$datos = $_POST;

// Validar que llegaron datos
if (empty($datos)) {
    http_response_code(400 );
    echo json_encode(['success' => false, 'message' => 'No se recibieron datos']);
    exit;
}

// --- VALIDACIONES ---
$errors = [];
$nombre = trim($datos['nombre'] ?? '');
$apellido = trim($datos['apellido'] ?? '');
$correo = trim($datos['correo'] ?? '');
$telefono = trim($datos['telefono'] ?? '');
$fecha_nacimiento_str = trim($datos['fecha_nacimiento'] ?? '');
$usuario = trim($datos['usuario'] ?? '');
$password = $datos['password'] ?? '';
$terminos = $datos['terminos'] ?? '';

if (empty($nombre)) $errors[] = 'El nombre es obligatorio.';
if (empty($apellido)) $errors[] = 'El apellido es obligatorio.';
if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) $errors[] = 'El formato del correo no es válido.';
if (strlen($password) < 8) $errors[] = 'La contraseña debe tener al menos 8 caracteres.';
if (empty($fecha_nacimiento_str)) {
    $errors[] = 'La fecha de nacimiento es obligatoria.';
} else {
    try {
        $fecha_nacimiento = new DateTime($fecha_nacimiento_str);
        $hoy = new DateTime();
        $edad = $hoy->diff($fecha_nacimiento)->y;
        if ($edad < 18) {
            $errors[] = 'Debes ser mayor de edad para registrarte.';
        }
    } catch (Exception $e) {
        $errors[] = 'El formato de la fecha de nacimiento no es válido.';
    }
}
if ($terminos !== 'on') $errors[] = 'Debes aceptar los términos y condiciones.';

if (!empty($errors)) {
    http_response_code(400 );
    echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
    exit;
}

// --- VERIFICAR SI USUARIO O CORREO YA EXISTEN ---
$stmt_check = $conn->prepare("SELECT correo, usuario FROM T_USUARIOS WHERE correo = ? OR usuario = ?");
$stmt_check->bind_param("ss", $correo, $usuario);
$stmt_check->execute();
$result = $stmt_check->get_result();

if ($result->num_rows > 0) {
    $existing_user = $result->fetch_assoc();
    if ($existing_user['correo'] === $correo) {
        $message = 'El correo electrónico ya está registrado.';
    } else {
        $message = 'El nombre de usuario ya está en uso.';
    }
    http_response_code(409 ); // Conflict
    echo json_encode(['success' => false, 'message' => $message]);
    $stmt_check->close();
    exit;
}
$stmt_check->close();

// --- INSERTAR USUARIO ---
$password_hash = password_hash($password, PASSWORD_BCRYPT);
$terminos_val = 1;
$marketing = isset($datos['newsletter']) && $datos['newsletter'] === 'on' ? 1 : 0;

$query = "INSERT INTO T_USUARIOS (nombre, apellido, correo, telefono, fecha_nacimiento, usuario, password, terminos, marketing, fecha_registro)
          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

$stmt_insert = $conn->prepare($query);
$stmt_insert->bind_param(
    "sssssssii",
    $nombre,
    $apellido,
    $correo,
    $telefono,
    $fecha_nacimiento_str,
    $usuario,
    $password_hash,
    $terminos_val,
    $marketing
);

if ($stmt_insert->execute()) {
    $user_id = $stmt_insert->insert_id;
    error_log("Usuario registrado exitosamente: ID=$user_id, Usuario=$usuario, Email=$correo");
    
    http_response_code(201 ); // Created
    echo json_encode([
        'success' => true, 
        'message' => 'Usuario registrado correctamente',
        'user_id' => $user_id
    ]);
} else {
    error_log("Error al registrar usuario: " . $stmt_insert->error);
    http_response_code(500 );
    echo json_encode(['success' => false, 'message' => 'Error al registrar el usuario: ' . $stmt_insert->error]);
}

$stmt_insert->close();
$conn->close();
?>