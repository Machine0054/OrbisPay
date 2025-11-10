<?php
session_start();
ini_set('display_errors', 1); // En desarrollo es mejor ver los errores
error_reporting(E_ALL);

// Tu manejador de errores es bueno, lo mantenemos.
set_error_handler(function ($severity, $message, $file, $line) {
    header('Content-Type: application/json');
    http_response_code(500 );
    echo json_encode(['success' => false, 'message' => "Error interno: $message en $file línea $line"]);
    exit;
});

header('Content-Type: application/json');
require_once 'conexion.php';

// ==================== 1. AUTENTICACIÓN Y MÉTODO ====================
if (!isset($_SESSION['id'])) { // Simplificado, si 'id' existe, 'usuario' también debería.
    http_response_code(401 );
    echo json_encode(['success' => false, 'message' => 'Usuario no autenticado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405 );
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$conn = conectar();
$usuario_id = $_SESSION['id'];

// ==================== 2. OBTENER Y VALIDAR DATOS ====================
$concept = trim($_POST['concept'] ?? '');
$amount = floatval($_POST['amount'] ?? 0);
$category_name = trim($_POST['category'] ?? '');
$date = trim($_POST['date'] ?? '');
$notes = trim($_POST['notes'] ?? '');

// Tu validación es excelente, la mantenemos igual.
$errors = [];
if (empty($concept) || strlen($concept) < 3) $errors[] = 'El concepto debe tener al menos 3 caracteres';
if ($amount <= 0) $errors[] = 'El monto debe ser mayor a 0';
if (empty($category_name)) $errors[] = 'Debes seleccionar o crear una categoría';
if (empty($date)) {
    $errors[] = 'Debes seleccionar una fecha';
} else {
    $dateTime = DateTime::createFromFormat('Y-m-d', $date);
    if (!$dateTime || $dateTime->format('Y-m-d') !== $date) $errors[] = 'Formato de fecha inválido';
    // Opcional: Permitir fechas futuras para proyecciones. Si no, tu validación es correcta.
    // if (new DateTime() < $dateTime) $errors[] = 'La fecha no puede ser futura';
}

if (!empty($errors)) {
    http_response_code(400 );
    echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
    exit;
}

// ==================== 3. OBTENER O CREAR ID DE CATEGORÍA ====================
$id_categoria = null;

try {
    // Paso 3.1: Verificar si la categoría ya existe para este usuario
    // Usamos la nueva tabla T_CATEGORIAS_INGRESOS_USUARIO
    $stmt_check = $conn->prepare("SELECT id FROM T_CATEGORIAS_INGRESOS_USUARIO WHERE nombre_categoria = ? AND id_usuario = ?");
    $stmt_check->bind_param("si", $category_name, $usuario_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($row = $result_check->fetch_assoc()) {
        // **CORRECCIÓN CLAVE 1:** Si existe, guardamos su ID
        $id_categoria = $row['id'];
    } else {
        // Paso 3.2: Si no existe, la creamos
        $stmt_create = $conn->prepare("INSERT INTO T_CATEGORIAS_INGRESOS_USUARIO (nombre_categoria, id_usuario) VALUES (?, ?)");
        $stmt_create->bind_param("si", $category_name, $usuario_id);
        $stmt_create->execute();
        
        // **CORRECCIÓN CLAVE 2:** Obtenemos el ID de la categoría que acabamos de crear
        $id_categoria = $conn->insert_id;
        $stmt_create->close();
    }
    $stmt_check->close();

} catch (Exception $e) {
    error_log("Error al procesar categoría: " . $e->getMessage());
    http_response_code(500 );
    echo json_encode(['success' => false, 'message' => 'Error al procesar la categoría.']);
    exit;
}

// ==================== 4. INSERTAR EL INGRESO CON EL ID DE CATEGORÍA ====================
try {
    // **CORRECCIÓN CLAVE 3:** La consulta ahora usa `id_categoria_ingreso`
    $sql = "INSERT INTO T_INGRESOS (concepto, monto, id_categoria_ingreso, fecha, notas, usuario_id) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    
    // **CORRECCIÓN CLAVE 4:** Vinculamos el $id_categoria (que es un entero 'i')
    $stmt->bind_param("sdissi", $concept, $amount, $id_categoria, $date, $notes, $usuario_id);
    
    if ($stmt->execute()) {
        http_response_code(201 );
        echo json_encode([
            'success' => true,
            'message' => 'Ingreso registrado exitosamente',
            'id' => $conn->insert_id
        ]);
    } else {
        throw new Exception('Error al ejecutar la inserción del ingreso.');
    }
} catch (Exception $e) {
    error_log("Error al guardar ingreso: " . $e->getMessage());
    http_response_code(500 );
    echo json_encode(['success' => false, 'message' => 'Error interno del servidor al guardar el ingreso.']);
} finally {
    if (isset($stmt)) $stmt->close();
    $conn->close();
}
?>