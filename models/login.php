<?php
// --- CONFIGURACIÓN INICIAL ---
ini_set('display_errors', 0); // Oculta errores en producción
error_reporting(E_ALL);

// Manejador de errores para devolver siempre una respuesta JSON
set_error_handler(function ($severity, $message, $file, $line) {
    http_response_code(500 );
    echo json_encode([
        'success' => false,
        'message' => "Error interno del servidor. Por favor, contacta a soporte.",
        'error_details' => "Error: $message en $file línea $line" // Para tu depuración
    ]);
    exit;
});

// Iniciar sesión y cabeceras
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json');

// --- VALIDACIÓN DE MÉTODO HTTP ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405 ); // Method Not Allowed
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}


require_once('conexion.php');
$conn = conectar();
if (!$conn) {
    http_response_code(500 );
    echo json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos.']);
    exit;
}


function registrarIntentoLogin($conn, $usuarioId, $identificador, $exitoso) {
    $sql = "INSERT INTO T_INTENTOS_LOGIN (usuario_id, identificador_ingresado, ip_address, user_agent, exitoso) VALUES (?, ?, ?, ?, ?)";
    
    if ($stmt = $conn->prepare($sql)) {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        $isSuccess = $exitoso ? 1 : 0;
        
        // Si el ID de usuario no es válido (ej. en un fallo de usuario no encontrado), se inserta NULL
        $userIdToLog = ($usuarioId > 0) ? $usuarioId : null;

        $stmt->bind_param("isssi", $userIdToLog, $identificador, $ip, $userAgent, $isSuccess);
        $stmt->execute();
        $stmt->close();
    }
}


try {
    // 1. Obtener y validar datos del formulario
    $usuarioInput = trim($_POST['usuario'] ?? '');
    $password     = trim($_POST['password'] ?? '');
    $recordar     = isset($_POST['recordar']) ? (bool)$_POST['recordar'] : false;

    if (empty($usuarioInput) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Todos los campos son requeridos']);
        exit;
    }

   
    $sql = "SELECT ID, NOMBRE, APELLIDO, CORREO, USUARIO, PASSWORD, ROL, ESTADO, FECHA_REGISTRO, GOOGLE_SUB
            FROM T_USUARIOS
            WHERE (USUARIO = ? OR CORREO = ?) AND ESTADO = b'1'
            LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $usuarioInput, $usuarioInput);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        

        // Bloquear si es una cuenta de Google
        if (!empty($row['GOOGLE_SUB'])) {
            echo json_encode([
                'success' => false,
                'message' => 'Tu cuenta está vinculada con Google. Inicia sesión con el botón "Google".'
            ]);
            $stmt->close();
            $conn->close();
            exit;
        }

        
        if (password_verify($password, $row['PASSWORD'])) {
            
           
            registrarIntentoLogin($conn, $row['ID'], $usuarioInput, true);

            
            $fechaHoraActual = date('Y-m-d H:i:s');
            $sqlUpdate = "UPDATE T_USUARIOS SET ULTIMO_INGRESO = ? WHERE ID = ?";
            if ($stmtUpdate = $conn->prepare($sqlUpdate)) {
                $stmtUpdate->bind_param("si", $fechaHoraActual, $row['ID']);
                $stmtUpdate->execute();
                $stmtUpdate->close();
            }

            $_SESSION['id']             = $row['ID'];
            $_SESSION['usuario']        = $row['USUARIO'];
            $_SESSION['fecha_creacion'] = $row['FECHA_REGISTRO'];
            $_SESSION['ultimo_ingreso'] = $fechaHoraActual;
            $_SESSION['nombre']         = $row['NOMBRE'];
            $_SESSION['apellido']       = $row['APELLIDO'];
            $_SESSION['rol']            = $row['ROL'];
            $_SESSION['correo']         = $row['CORREO'];
            $_SESSION['estado']         = $row['ESTADO'];
            $_SESSION['login_time']     = time();

            // Crear cookie "Recordarme" si es necesario
            if ($recordar) {
                $cookieExpire = time() + (30 * 24 * 60 * 60); // 30 días
                $cookieValue  = base64_encode($row['ID'].'|'.$row['USUARIO']);
                setcookie('remember_user', $cookieValue, [
                    'expires'  => $cookieExpire,
                    'path'     => '/',
                    'secure'   => true, // Solo en HTTPS
                    'httponly' => true, // No accesible por JS
                    'samesite' => 'Lax',
                ] );
            }

            
            echo json_encode(['success' => true, 'message' => 'Inicio de sesión exitoso']);

        } else {
            
            registrarIntentoLogin($conn, $row['ID'], $usuarioInput, false);
            echo json_encode(['success' => false, 'message' => 'Usuario o contraseña incorrectos']);
        }

    } else {
       
        registrarIntentoLogin($conn, null, $usuarioInput, false);
        echo json_encode(['success' => false, 'message' => 'Usuario no encontrado o inactivo']);
    }

    $stmt->close();

} catch (Exception $e) {
   
    http_response_code(500 );
    echo json_encode([
        'success' => false,
        'message' => 'Error interno del servidor.',
        'error'   => $e->getMessage() // Para depuración
    ]);
} finally {
    // Cerrar la conexión en todos los casos
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
}
?>