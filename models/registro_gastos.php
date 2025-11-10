<?php
session_start();
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Enviar los errores como JSON
set_error_handler(function ($severity, $message, $file, $line) {
    header('Content-Type: application/json');
    http_response_code(500 ); // Es un error del servidor
    echo json_encode([
        'success' => false,
        'message' => "Error interno: $message en $file línea $line"
    ]);
    exit;
});

header('Content-Type: application/json');

// Incluir la conexión a la base de datos
require_once 'conexion.php';
$conn = conectar();

// Verificar si el usuario está logueado
if (!isset($_SESSION['id'])) {
    http_response_code(401 );
    echo json_encode([
        'success' => false,
        'message' => 'Usuario no autenticado'
    ]);
    exit;
}

// Verificar que sea una petición POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405 );
    echo json_encode([
        'success' => false,
        'message' => 'Método no permitido'
    ]);
    exit;
}

// --- INICIO DE LA MODIFICACIÓN ---
// Leer el cuerpo de la petición JSON en lugar de usar $_POST
$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true);

// Si el JSON es inválido, $data será null
if ($data === null) {
    http_response_code(400 );
    echo json_encode([
        'success' => false,
        'message' => 'Error: No se recibieron datos o el formato JSON es inválido.'
    ]);
    exit;
}

// Asignar variables desde el array decodificado
$description   = trim(strip_tags($data['description'] ?? ''));
$amount        = filter_var($data['amount'] ?? null, FILTER_VALIDATE_FLOAT);
$category      = trim(strip_tags($data['category'] ?? ''));
$expense_date  = trim(strip_tags($data['date'] ?? ''));
$usuario_id    = $_SESSION['id'];
// --- FIN DE LA MODIFICACIÓN ---


// Validar datos
$errors = [];

if (!$description || strlen($description) < 3) {
    $errors[] = 'La descripción debe tener al menos 3 caracteres';
}

if ($amount === false || $amount <= 0) { // Se ajusta la validación para filter_var
    $errors[] = 'El monto debe ser mayor a 0';
}

if (!$category) {
    $errors[] = 'La categoría es obligatoria';
}

if (!$expense_date) {
    $errors[] = 'La fecha es obligatoria';
} else {
    $dateTime = DateTime::createFromFormat('Y-m-d', $expense_date);
    if (!$dateTime || $dateTime->format('Y-m-d') !== $expense_date) {
        $errors[] = 'Formato de fecha inválido';
    }
}

// Lista de categorías válidas
// $valid_categories = ['alimentacion', 'transporte', 'servicios', 'entretenimiento', 'salud', 'otros'];
// if (!in_array($category, $valid_categories)) {
//     $errors[] = 'Categoría inválida';
// }

if (!empty($errors)) {
    http_response_code(400 );
    echo json_encode([
        'success' => false,
        'message' => implode(', ', $errors)
    ]);
    exit;
}

try {
    $sql = "INSERT INTO T_GASTOS (usuario_id, descripcion, monto, categoria, fecha_gasto, fecha_registro)
            VALUES (?, ?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    // El tipo para 'monto' debe ser 'd' (double) para decimales
    $stmt->bind_param("isdss", $usuario_id, $description, $amount, $category, $expense_date);
    $success = $stmt->execute();

    if ($success) {
        echo json_encode([
            'success' => true,
            'message' => 'Gasto registrado exitosamente',
            'gasto_id' => $conn->insert_id
        ]);
    } else {
        http_response_code(500 );
        echo json_encode([
            'success' => false,
            'message' => 'Error al registrar el gasto: ' . $stmt->error
        ]);
    }
    
    $stmt->close();
} catch (Exception $e) {
    error_log("Error al registrar gasto: " . $e->getMessage());

    http_response_code(500 );
    echo json_encode([
        'success' => false,
        'message' => 'Error interno del servidor',
        'error' => $e->getMessage()
    ]);
} finally {
    $conn->close();
}
?>