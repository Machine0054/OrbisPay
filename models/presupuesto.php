<?php
session_start();
header('Content-Type: application/json');
require_once 'conexion.php';
$conn = conectar();
function send_json_response($success, $message, $data = null) {
    $response = ['success' => $success, 'message' => $message];
    if ($data !== null) {
        $response['data'] = $data;
    }
    echo json_encode($response);
    exit;
}


if (!$conn) {
    http_response_code(500 ); // Internal Server Error
    send_json_response(false, 'Error interno del servidor: No se pudo conectar a la base de datos.');
}
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? null; 

// ==================================================================
// --- MANEJO DE GET PARA CARGAR CATEGORÍAS ---
// ==================================================================
if ($method === 'GET' && $action === 'getCategories') {
    
    // Obtenemos el user_id de la sesión. Si no existe, lo dejamos como 0 o un valor inválido.
    $user_id = $_SESSION['id'] ?? 0;

    // Si no hay usuario en sesión, no podemos continuar.
    if ($user_id === 0) {
        http_response_code(401 );
        send_json_response(false, 'Sesión no válida. Por favor, inicie sesión.');
    }

    // Consulta corregida y simplificada
    $sql = "SELECT id, nombre_categoria 
            FROM T_CATEGORIAS_USUARIO 
            WHERE id_usuario = ? ";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        http_response_code(500 );
        send_json_response(false, 'Error al preparar la consulta de categorías.');
    }
    
    $stmt->bind_param("i", $user_id);
    
    if (!$stmt->execute()) {
        http_response_code(500 );
        send_json_response(false, 'Error al ejecutar la consulta de categorías.');
    }
    
    $result = $stmt->get_result();
    $categories = [];
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }

    $stmt->close();
    $conn->close();

    send_json_response(true, 'Categorías cargadas', $categories);
}


if ($method === 'POST') {
    $user_id = $_SESSION['id'];
    $category = trim($_POST['category'] ?? '');
    $amount = filter_var($_POST['amount'] ?? 0, FILTER_VALIDATE_FLOAT);
    $period = trim($_POST['period'] ?? '');
    $start_date = trim($_POST['start_date'] ?? '');
    $end_date = trim($_POST['end_date'] ?? '');
    


    if (empty($user_id)) {
        http_response_code(401 ); // Unauthorized
        send_json_response(false, 'Error de autenticación. Por favor, inicia sesión de nuevo.');
    }
    if (empty($category) || empty($period) || empty($start_date) || empty($end_date)) {
        http_response_code(400 ); // Bad Request
        send_json_response(false, 'Todos los campos son obligatorios.');
    }
    if ($amount <= 0) {
        http_response_code(400 );
        send_json_response(false, 'El monto debe ser un número positivo.');
    }
    if (strtotime($start_date) >= strtotime($end_date)) {
        http_response_code(400 );
        send_json_response(false, 'La fecha de fin debe ser posterior a la fecha de inicio.');
    }

    
    $sql = "INSERT INTO T_PRESUPUESTOS (user_id, category, amount, period, start_date, end_date) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        
        http_response_code(500 );
        send_json_response(false, 'Error interno del servidor: No se pudo preparar la consulta.');
    }


    $stmt->bind_param("isssss", $user_id, $category, $amount, $period, $start_date, $end_date);
  
    if ($stmt->execute()) {
        send_json_response(true, 'Presupuesto agregado exitosamente.');
    } else {
        // Error en la ejecución
        http_response_code(500 );
        send_json_response(false, 'Error al guardar el presupuesto en la base de datos.');
    }


    $stmt->close();
    $conn->close();
}


if ($method === 'GET' && $action === null) {
    
    $user_id =  $_SESSION['id'];

    if (empty($user_id)) {
        http_response_code(401 );
        send_json_response(false, 'Error de autenticación.');
    }

    
    // Consulta para obtener los presupuestos y la suma de gastos por categoría
    $sql = "SELECT 
                b.category, 
                b.amount,
                (SELECT SUM(g.monto) 
                 FROM T_GASTOS g 
                 WHERE g.usuario_id = b.user_id 
                   AND g.categoria = b.category 
                   AND g.fecha_gasto BETWEEN b.start_date AND b.end_date) as total_expenses
            FROM 
                T_PRESUPUESTOS b
            WHERE 
                b.user_id = ? 
                AND CURDATE() BETWEEN b.start_date AND b.end_date"; // Solo presupuestos activos

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    send_json_response(true, 'Presupuestos cargados correctamente.', $data);

    $stmt->close();
    $conn->close();
}


http_response_code(405 ); // Method Not Allowed
send_json_response(false, 'Método no permitido.');

?>