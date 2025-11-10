<?php

session_start();
ini_set('display_errors', 0); // No mostrar errores PHP en la respuesta final
error_reporting(E_ALL);

// Un manejador de errores para capturar cualquier problema y devolverlo como JSON
set_error_handler(function ($severity, $message, $file, $line) {
    header('Content-Type: application/json');
    http_response_code(500 ); // Error interno del servidor
    echo json_encode([
        'success' => false,
        'message' => "Error interno del servidor en $file línea $line: $message"
    ]);
    exit;
});

header('Content-Type: application/json');

if (!isset($_SESSION['id'])) {
    http_response_code(401 ); // No autorizado
    echo json_encode(['success' => false, 'message' => 'Acceso no autorizado. Por favor, inicie sesión.']);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405 ); // Método no permitido
    echo json_encode(['success' => false, 'message' => 'Método de solicitud no permitido.']);
    exit;
}

require_once 'conexion.php';
$conn = conectar();

$usuario_id = $_SESSION['id'];
$nombre_categoria = trim($_POST['name'] ?? '');

// Validar el nombre de la categoría
if (empty($nombre_categoria) || strlen($nombre_categoria) < 3) {
    http_response_code(400 ); // Solicitud incorrecta
    echo json_encode(['success' => false, 'message' => 'El nombre de la categoría debe tener al menos 3 caracteres.']);
    exit;
}

try {
   
    $sql_check = "SELECT id FROM T_CATEGORIAS_INGRESOS_USUARIO WHERE nombre_categoria = ? AND id_usuario = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("si", $nombre_categoria, $usuario_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        http_response_code(409 ); // Conflicto: el recurso ya existe
        echo json_encode(['success' => false, 'message' => 'Ya existe una categoría con ese nombre.']);
        $stmt_check->close();
        $conn->close();
        exit;
    }
    $stmt_check->close();

    $sql_insert = "INSERT INTO T_CATEGORIAS_INGRESOS_USUARIO (nombre_categoria, id_usuario) VALUES (?, ?)";
    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bind_param("si", $nombre_categoria, $usuario_id);

    if ($stmt_insert->execute()) {
       
        http_response_code(201 ); // Creado
        echo json_encode(['success' => true, 'message' => 'Categoría creada exitosamente.']);
    } else {
        // Error durante la ejecución de la inserción
        throw new Exception("Error al ejecutar la inserción en la base de datos.");
    }
    
    $stmt_insert->close();

} catch (Exception $e) {
    
    error_log("Error en crear_categoria_ingreso.php: " . $e->getMessage());
    http_response_code(500 );
    echo json_encode([
        'success' => false,
        'message' => 'Ocurrió un error inesperado al intentar guardar la categoría.'
    ]);
} finally {
    // Asegurarse de cerrar la conexión
    if ($conn) {
        $conn->close();
    }
}
?>