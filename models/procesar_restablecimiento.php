<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
header('Content-Type: application/json');

require_once __DIR__ . '/conexion.php';

function out($ok, $msg){ echo json_encode(['success'=>$ok,'message'=>$msg]); exit; }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') out(false, 'Método no permitido');

$token    = trim($_POST['token'] ?? '');
$pass     = trim($_POST['password'] ?? '');
$pass2    = trim($_POST['password_confirm'] ?? '');

if ($token === '' || $pass === '' || $pass2 === '') out(false, 'Campos incompletos');
if ($pass !== $pass2) out(false, 'Las contraseñas no coinciden');
if (strlen($pass) < 8) out(false, 'La contraseña debe tener mínimo 8 caracteres');

$conn = conectar();

/* 1) Validar token */
$stmt = $conn->prepare("SELECT email, expira_en FROM T_PASSWORD_RESETS WHERE token=? LIMIT 1");
$stmt->bind_param("s", $token);
$stmt->execute();
$res = $stmt->get_result();
if (!$row = $res->fetch_assoc()) { out(false, 'Token inválido.'); }
if (strtotime($row['expira_en']) <= time()) { out(false, 'El token ha expirado.'); }

$email = $row['email'];
$stmt->close();

/* 2) Actualizar password del usuario asociado a ese correo */
$hash = password_hash($pass, PASSWORD_DEFAULT);

$stmt = $conn->prepare("UPDATE T_USUARIOS SET PASSWORD=?, ULTIMO_INGRESO=ULTIMO_INGRESO WHERE CORREO=? LIMIT 1");
$stmt->bind_param("ss", $hash, $email);
if (!$stmt->execute() || $stmt->affected_rows < 0) {
    $stmt->close();
    out(false, 'No fue posible actualizar tu contraseña.');
}
$stmt->close();

/* 3) Consumir token (eliminarlo) */
$stmt = $conn->prepare("DELETE FROM T_PASSWORD_RESETS WHERE token=?");
$stmt->bind_param("s", $token);
$stmt->execute();
$stmt->close();

$conn->close();

out(true, 'Tu contraseña se actualizó correctamente. Ahora puedes iniciar sesión.');