<?php
session_start();
header('Content-Type: application/json');
require_once 'conexion.php';
$conn = conectar();

function send_json_response($success, $message, $data = null, $statusCode = 200) {
    http_response_code($statusCode );
    $response = ['success' => $success, 'message' => $message];
    if ($data !== null) {
        $response['data'] = $data;
    }
    echo json_encode($response);
    exit;
}

if (!$conn) {
    send_json_response(false, 'Error interno: No se pudo conectar a la base de datos.', null, 500);
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? null;
$user_id = $_SESSION['id'] ?? 0;

if ($user_id === 0) {
    send_json_response(false, 'Sesión no válida. Por favor, inicie sesión.', null, 401);
}

if ($method === 'GET') {
        if ($action === 'check_budget') {
        $id_categoria = filter_var($_GET['category_id'] ?? 0, FILTER_VALIDATE_INT);
        if ($id_categoria <= 0) {
            send_json_response(false, 'ID de categoría no válido.', null, 400);
        }
        $sql = "
            SELECT 
                b.amount AS budgeted,
                COALESCE((
                    SELECT SUM(g.monto) 
                    FROM T_GASTOS g 
                    WHERE g.usuario_id = b.user_id 
                      AND g.categoria = (SELECT c.nombre_categoria FROM T_CATEGORIAS_USUARIO c WHERE c.id = b.id_categoria)
                      AND g.fecha_gasto BETWEEN b.start_date AND b.end_date
                ), 0) as spent
            FROM T_PRESUPUESTOS b
            WHERE b.user_id = ? AND b.id_categoria = ? AND MONTH(b.start_date) = MONTH(CURDATE()) AND YEAR(b.start_date) = YEAR(CURDATE())
            LIMIT 1
        ";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $user_id, $id_categoria);
        $stmt->execute();
        $result = $stmt->get_result();
        $budget_data = $result->fetch_assoc();
        $stmt->close();
        if ($budget_data) {
            $budget_data['budgeted'] = floatval($budget_data['budgeted']);
            $budget_data['spent'] = floatval($budget_data['spent']);
            $budget_data['remaining'] = $budget_data['budgeted'] - $budget_data['spent'];
            send_json_response(true, 'Presupuesto encontrado.', $budget_data);
        } else {
            send_json_response(true, 'No se encontró presupuesto para esta categoría.', ['budget_exists' => false]);
        }
    } 
    else if ($action === 'getCategories') {
        $sql = "SELECT id, nombre_categoria, icono FROM T_CATEGORIAS_USUARIO WHERE id_usuario = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $categories = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        send_json_response(true, 'Categorías cargadas', $categories);
    } 
    else {
        
        $sql = "
            SELECT 
                b.id_categoria,
                c.nombre_categoria,
                c.icono,
                b.amount,
                COALESCE((
                    SELECT SUM(g.monto) 
                    FROM T_GASTOS g 
                    WHERE g.usuario_id = b.user_id 
                      AND g.categoria = c.nombre_categoria
                      AND g.fecha_gasto BETWEEN b.start_date AND b.end_date
                ), 0) as total_expenses
            FROM 
                T_PRESUPUESTOS b
            JOIN 
                T_CATEGORIAS_USUARIO c ON b.id_categoria = c.id
            WHERE 
                b.user_id = ? 
                AND MONTH(b.start_date) = MONTH(CURDATE()) AND YEAR(b.start_date) = YEAR(CURDATE())
        ";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        send_json_response(true, 'Presupuestos cargados', $data);
    }

} elseif ($method === 'POST') {
    // La lógica para crear un presupuesto no cambia.
    $id_categoria = filter_var($_POST['category'] ?? 0, FILTER_VALIDATE_INT);
    $amount = filter_var($_POST['amount'] ?? 0, FILTER_VALIDATE_FLOAT);
    $period = trim($_POST['period'] ?? '');
    $start_date = trim($_POST['start_date'] ?? '');
    $end_date = trim($_POST['end_date'] ?? '');

    if ($id_categoria <= 0 || $amount <= 0 || empty($period) || empty($start_date) || empty($end_date)) {
        send_json_response(false, 'Todos los campos son obligatorios y válidos.', null, 400);
    }
    if (strtotime($start_date) >= strtotime($end_date)) {
        send_json_response(false, 'La fecha de fin debe ser posterior a la fecha de inicio.', null, 400);
    }

    $sql = "INSERT INTO T_PRESUPUESTOS (user_id, id_categoria, amount, period, start_date, end_date) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iidsss", $user_id, $id_categoria, $amount, $period, $start_date, $end_date);
  
    if ($stmt->execute()) {
        send_json_response(true, 'Presupuesto agregado exitosamente.', null, 201);
    } else {
        send_json_response(false, 'Error al guardar el presupuesto.', null, 500);
    }
    $stmt->close();
}

$conn->close();
send_json_response(false, 'Método no permitido.', null, 405);
?>