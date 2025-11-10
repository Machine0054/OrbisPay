<?php
declare(strict_types=1);
session_start();

require __DIR__ . '/../vendor/autoload.php';

/* === Config Google === */
$client = new Google_Client();
$client->setClientId('452066751265-c9488mfv2dc1ht5p369mtmgm03ioraj9.apps.googleusercontent.com');
$client->setClientSecret('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');
$client->setRedirectUri('https://orbispay.com.co/orbispay/controllers/google_callback.php');


/* === Validar state (CSRF) === */
if (!isset($_GET['state'], $_SESSION['oauth2state']) || $_GET['state'] !== $_SESSION['oauth2state']) {
    http_response_code(400);
    exit('Error de seguridad: state inválido');
}
unset($_SESSION['oauth2state']);

/* === Validar code === */
if (!isset($_GET['code'])) {
    http_response_code(400);
    exit('Falta el parámetro code en la URL');
}

/* === Intercambiar code por token === */
$token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
if (isset($token['error'])) {
    http_response_code(400);
    exit('Error al obtener token: ' . ($token['error_description'] ?? $token['error']));
}
$client->setAccessToken($token);

/* === Obtener datos del perfil === */
$oauth2 = new Google_Service_Oauth2($client);
$googleUser = $oauth2->userinfo->get();

$email    = $googleUser->email;
$nombre   = $googleUser->givenName ?? '';
$apellido = $googleUser->familyName ?? '';
$googleId = $googleUser->id;   // este es el "sub" único de Google
$foto     = $googleUser->picture;

/* === Conectar a la BD === */
$mysqli = new mysqli('158.220.120.23', 'FINANZAS', 'R3t4m0z4$%.', 'FINANZAS');
if ($mysqli->connect_errno) {
    http_response_code(500);
    exit('Error de conexión a la BD: ' . $mysqli->connect_error);
}
$mysqli->set_charset('utf8mb4');

/* === Buscar usuario por GOOGLE_SUB o CORREO === */
$stmt = $mysqli->prepare("SELECT * FROM T_USUARIOS WHERE GOOGLE_SUB = ? OR CORREO = ? LIMIT 1");
$stmt->bind_param("ss", $googleId, $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if ($user) {
    // ✅ Usuario ya existe → actualizar último ingreso y setear GOOGLE_SUB si no lo tenía
    if (empty($user['GOOGLE_SUB'])) {
        $stmt = $mysqli->prepare("UPDATE T_USUARIOS SET ULTIMO_INGRESO = NOW(), GOOGLE_SUB = ? WHERE ID = ?");
        $stmt->bind_param("si", $googleId, $user['ID']);
    } else {
        $stmt = $mysqli->prepare("UPDATE T_USUARIOS SET ULTIMO_INGRESO = NOW() WHERE ID = ?");
        $stmt->bind_param("i", $user['ID']);
    }
    $stmt->execute();
    $stmt->close();

    $userId   = $user['ID'];
    $usuario  = $user['USUARIO'];
    $rol      = $user['ROL'] ?? 'USER';

} else {
    // 🚀 Usuario nuevo → insertarlo
    $usuarioBase = strtolower(explode('@', $email)[0]);
    $usuarioBase = preg_replace('/[^a-z0-9._-]/i', '', $usuarioBase); // limpia caracteres raros
    $usuario = substr($usuarioBase, 0, 32); // respeta varchar(32)

    // 🔐 Generar username único si ya existe
    $n = 0;
    while (true) {
        $stmt = $mysqli->prepare("SELECT 1 FROM T_USUARIOS WHERE USUARIO = ? LIMIT 1");
        $stmt->bind_param("s", $usuario);
        $stmt->execute();
        $exists = $stmt->get_result()->fetch_row();
        $stmt->close();

        if (!$exists) break; // no existe → lo usamos
        $n++;
        $usuario = substr($usuarioBase . $n, 0, 32); // agrega número y respeta varchar(32)
    }

    $passwordFake = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);
    $telefono = null;
    $fechaNac = null;

    $stmt = $mysqli->prepare("
        INSERT INTO T_USUARIOS
          (NOMBRE, APELLIDO, CORREO, TELEFONO, FECHA_NACIMIENTO, USUARIO, PASSWORD,
           TERMINOS, MARKETING, FECHA_REGISTRO, ESTADO, ULTIMO_INGRESO, ROL, GOOGLE_SUB)
        VALUES (?, ?, ?, ?, ?, ?, ?, b'1', NULL, NOW(), b'1', NOW(), 'USER', ?)
    ");
    $stmt->bind_param("ssssssss", 
        $nombre, $apellido, $email, $telefono, $fechaNac, $usuario, $passwordFake, $googleId
    );
    $stmt->execute();
    $userId = $stmt->insert_id;
    $stmt->close();

    $rol = 'USER';
}

/* === Crear sesión === */
$_SESSION['id']         = $userId;
$_SESSION['usuario']    = $usuario;
$_SESSION['nombre']     = $nombre;
$_SESSION['apellido']   = $apellido;
$_SESSION['correo']     = $email;
$_SESSION['rol']        = $rol;
$_SESSION['proveedor']  = 'google';
$_SESSION['avatar']     = $foto;
$_SESSION['login_time'] = time();

/* === Redirigir al dashboard === */
header('Location: /orbispay/views/dashboard2.php');

exit;
