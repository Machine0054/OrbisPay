<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json');

require_once 'conexion.php';

function send_json_response($success, $message) {
    echo json_encode(['success' => $success, 'message' => $message]);
    exit;
}

// 1. Validar la petición
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json_response(false, 'Método no permitido.');
}

$token = $_POST['token'] ?? '';
$newPassword = $_POST['newPassword'] ?? '';
$confirmPassword = $_POST['confirmPassword'] ?? '';

if (empty($token) || empty($newPassword) || empty($confirmPassword)) {
    send_json_response(false, 'Todos los campos son obligatorios.');
}

if ($newPassword !== $confirmPassword) {
    send_json_response(false, 'Las contraseñas no coinciden.');
}

if (strlen($newPassword) < 8) {
    send_json_response(false, 'La contraseña debe tener al menos 8 caracteres.');
}

$conn = conectar();

// 2. Validar el token en la base de datos
$stmt = $conn->prepare("SELECT email, expira_en FROM T_PASSWORD_RESETS WHERE token = ?");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();
$reset_request = $result->fetch_assoc();

if (!$reset_request) {
    send_json_response(false, 'El enlace de restablecimiento no es válido o ya fue utilizado.');
}

// 3. Verificar si el token ha expirado
$ahora = date('Y-m-d H:i:s');
if ($ahora > $reset_request['expira_en']) {
    send_json_response(false, 'El enlace de restablecimiento ha expirado. Por favor, solicita uno nuevo.');
}

// 4. Actualizar la contraseña del usuario
$email = $reset_request['email'];
$hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT); // ¡Siempre hashear la contraseña!

$stmt = $conn->prepare("UPDATE T_USUARIOS SET password = ? WHERE correo = ?");
$stmt->bind_param("ss", $hashedPassword, $email);

if (!$stmt->execute()) {
    send_json_response(false, 'Error interno al actualizar la contraseña.');
}

// 5. Invalidar el token (eliminarlo) para que no se pueda volver a usar
$stmt = $conn->prepare("DELETE FROM T_PASSWORD_RESETS WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();

// 6. Enviar respuesta de éxito
send_json_response(true, '¡Tu contraseña ha sido actualizada exitosamente! Ya puedes iniciar sesión.');

$conn->close();
?>