<?php
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');

define('DEBUG_RESET', true);
function dlog($msg) {
  if (!DEBUG_RESET) return;
  $line = '['.date('Y-m-d H:i:s').'] '.$msg."\n";
  @file_put_contents('/tmp/reset_debug.log', $line, FILE_APPEND);
}

require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/../vendor/autoload.php';
use SendGrid\Mail\Mail;

// URL de la página que leerá el token (ajústala si usas otra)
$reset_url = "https://orbispay.com.co/views/reset_password.php";

function respond($arr) {
  echo json_encode($arr);
  exit;
}

// 1) Validaciones básicas
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  respond(['success'=>false,'message'=>'Método no permitido']);
}
$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
if (!$email) {
  respond(['success'=>false,'message'=>'Correo inválido']);
}

dlog("REQ email={$email}");

// 2) BD: verificar usuario
$conn = conectar();
$stmt = $conn->prepare("SELECT ID FROM T_USUARIOS WHERE CORREO = ? LIMIT 1");
$stmt->bind_param("s", $email);
$stmt->execute();
$existe = $stmt->get_result()->num_rows > 0;
$stmt->close();

if (!$existe) {
  dlog("Email no existe -> respuesta genérica");
  respond(['success'=>true,'message'=>'Si tu correo está en nuestro sistema, recibirás un enlace para restablecer tu contraseña.']);
}

// 3) Token
$token = bin2hex(random_bytes(32));
$expira = date('Y-m-d H:i:s', time() + 3600);

$stmt = $conn->prepare("DELETE FROM T_PASSWORD_RESETS WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->close();

$stmt = $conn->prepare("INSERT INTO T_PASSWORD_RESETS (email, token, expira_en) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $email, $token, $expira);
if (!$stmt->execute()) {
  dlog("Error insert token: ".$stmt->error);
  $stmt->close();
  respond(['success'=>false,'message'=>'Error interno al guardar el token']);
}
$stmt->close();

// 4) SendGrid
$apiKey = getenv('SENDGRID_API_KEY');
if (!$apiKey) {
  dlog("SENDGRID_API_KEY no definida");
  respond(['success'=>false,'message'=>'Error de configuración de correo']);
}

$link = $reset_url.'?token='.urlencode($token);
$html = "
  <div style='font-family:Arial,sans-serif;line-height:1.6'>
    <h2>Hola,</h2>
    <p>Recibimos una solicitud para restablecer tu contraseña en OrbisPay.</p>
    <p><a href='{$link}' style='background:#2563eb;color:#fff;padding:10px 16px;border-radius:6px;text-decoration:none;'>Restablecer Contraseña</a></p>
    <p>Si el botón no funciona, copia este enlace:<br><a href='{$link}'>{$link}</a></p>
    <p style='color:#777;font-size:12px'>El enlace expira en 1 hora.</p>
  </div>
";

$emailObj = new Mail();
$emailObj->setFrom("no-reply@orbispay.com.co", "OrbisPay");
$emailObj->setSubject("Restablece tu contraseña en OrbisPay");
$emailObj->addTo($email);
$emailObj->addContent("text/plain", "Para restablecer tu contraseña, visita: {$link}");
$emailObj->addContent("text/html", $html);

$sg = new \SendGrid($apiKey);

try {
  $resp = $sg->send($emailObj);
  $status = $resp->statusCode();
  $body   = $resp->body();
  dlog("SG status={$status} body=".substr($body,0,500));

  if ($status >= 200 && $status < 300) {
    respond(['success'=>true,'message'=>'Si tu correo está en nuestro sistema, recibirás un enlace para restablecer tu contraseña.',
             'sg_status'=>$status]); // <- detalle temporal
  } else {
    respond(['success'=>false,'message'=>'No se pudo enviar el correo. Intenta más tarde.',
             'sg_status'=>$status,'sg_body'=>$body]); // <- detalle temporal
  }
} catch (Throwable $e) {
  dlog("SG exception: ".$e->getMessage());
  respond(['success'=>false,'message'=>'No se pudo enviar el correo. Intenta más tarde.','error'=>$e->getMessage()]);
} finally {
  $conn->close();
}