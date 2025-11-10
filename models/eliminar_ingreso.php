<?php
session_start();
// Incluir la conexión a la base de datos
require_once 'conexion.php';
$conn = conectar();
ini_set('display_errors', 0); // No mostrar errores en el output
error_reporting(E_ALL);

// Headers de seguridad
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

// Enviar los errores como JSON
set_error_handler(function ($severity, $message, $file, $line) {
    echo json_encode([
        'success' => false,
        'message' => "Error: $message en $file línea $line"
    ]);
    exit;
});



// Verificar si el usuario está logueado
if (!isset($_SESSION['id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Usuario no autenticado'
    ]);
    exit;
}

// Verificar que sea una petición POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Método no permitido'
    ]);
    exit;
}



// Variables principales
$usuario_id = $_SESSION['id'];
$ip = $_SERVER['REMOTE_ADDR'];

$income_id = isset($_POST['id']) ? intval($_POST['id']) : 0;

// Validar ID
if ($income_id <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'ID de ingreso inválido'
    ]);
    exit;
}

try {
    // Verificar que el ingreso pertenezca al usuario
    $sql = "SELECT id FROM T_INGRESOS WHERE id = ? AND usuario_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $income_id, $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if (!$result->fetch_assoc()) {
        $stmt->close();
        
        echo json_encode([
            'success' => false,
            'message' => 'Ingreso no encontrado o no autorizado'
        ]);
        exit;
    }
    $stmt->close();

    // Eliminar el ingreso
    $sql = "DELETE FROM T_INGRESOS WHERE id = ? AND usuario_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $income_id, $usuario_id);
    
    $success = $stmt->execute();
    $stmt->close();
    
    if ($success) {
        echo json_encode([
            'success' => true,
            'message' => 'Ingreso eliminado exitosamente'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Error al eliminar el ingreso'
        ]);
    }
    
} catch (Exception $e) {
    // Log del error
    error_log("Error al eliminar ingreso: " . $e->getMessage());

    echo json_encode([
        'success' => false,
        'message' => 'Error interno del servidor'
    ]);
} finally {
    $conn->close();
}


?>