<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);

session_start();
header('Content-Type: application/json');

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Usuario no autenticado'
    ]);
    exit;
}


// Verificar que sea una petición POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Método no permitido'
    ]);
    exit;
}

// Incluir la conexión a la base de datos
require_once 'conexion.php';
$conn = conectar();

$usuario_id = $_SESSION['id'];

try {

    // Obtener los gastos más recientes
    $sql = "SELECT 
            g.id,
            g.descripcion,
            g.monto,
            g.fecha_gasto,
            g.fecha_registro,
            COALESCE(cat_u.nombre_categoria, cat_g.nombre) AS categoria_nombre,
            COALESCE(cat_u.icono, cat_g.icono_svg) AS categoria_icono
        FROM T_GASTOS g
        LEFT JOIN T_CATEGORIAS_USUARIO cat_u ON g.categoria = cat_u.nombre_categoria AND cat_u.id_usuario = ?
        LEFT JOIN T_CATEGORIAS cat_g ON g.categoria = cat_g.nombre
        WHERE g.usuario_id = ?
        ORDER BY g.fecha_gasto DESC, g.id DESC 
        LIMIT 10";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $usuario_id, $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $recent_expenses = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // Formatear los datos
    $formatted_expenses = [];
    foreach ($recent_expenses as $expense) {
        $formatted_expenses[] = [
            'id' => intval($expense['id']),
            'descripcion' => $expense['descripcion'],
            'monto' => floatval($expense['monto']),
            'categoria' => $expense['categoria'],
            'fecha_gasto' => $expense['fecha_gasto'],
            'fecha_registro' => $expense['fecha_registro']
        ];
    }


    // Obtener estadísticas
    $stats = [
        'today' => 0,
        'month' => 0,
        'budget_remaining' => 0,
        'budget_used_percent' => 0
    ];

    // Total de hoy
    $sql_today = "SELECT SUM(monto) FROM T_GASTOS WHERE usuario_id = ? AND DATE(fecha_gasto) = CURDATE()";
    $stmt = $conn->prepare($sql_today);
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $stmt->bind_result($total_hoy);
    $stmt->fetch();
    $stats['today'] = floatval($total_hoy ?? 0);
    $stmt->close();

    // Total del mes
    $sql_month = "SELECT SUM(monto) FROM T_GASTOS WHERE usuario_id = ? AND MONTH(fecha_gasto) = MONTH(CURDATE()) AND YEAR(fecha_gasto) = YEAR(CURDATE())";
    $stmt = $conn->prepare($sql_month);
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $stmt->bind_result($total_mes);
    $stmt->fetch();
    $stats['month'] = floatval($total_mes ?? 0);
    $stmt->close();

   
    // Calcular dinámicamente sumando todos los presupuestos activos del mes actual
    $sql_budget = "SELECT SUM(amount) 
                   FROM T_PRESUPUESTOS 
                   WHERE user_id = ? 
                   AND (
                       (MONTH(start_date) <= MONTH(CURDATE()) AND YEAR(start_date) <= YEAR(CURDATE()))
                       AND 
                       (MONTH(end_date) >= MONTH(CURDATE()) AND YEAR(end_date) >= YEAR(CURDATE()))
                   )
                   AND (
                       (start_date <= LAST_DAY(CURDATE()))
                       AND 
                       (end_date >= DATE_FORMAT(CURDATE(), '%Y-%m-01'))
                   )";
    
    $stmt = $conn->prepare($sql_budget);
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $stmt->bind_result($presupuesto);
    $stmt->fetch();
    $presupuesto = floatval($presupuesto ?? 0);
    $stmt->close();

    // Calcular restante y porcentaje
    $stats['budget_remaining'] = max(0, $presupuesto - $stats['month']);
    $stats['budget_used_percent'] = $presupuesto > 0
        ? round(($stats['month'] / $presupuesto) * 100, 2)
        : 0;

    // Respuesta JSON final
    echo json_encode([
        'success' => true,
        'recent_expenses' => $recent_expenses, // Usamos directamente el resultado de la consulta
        'stats' => $stats
    ]);

} catch (Exception $e) {
    error_log("Error al obtener datos de gastos: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error interno del servidor'
    ]);
} finally {
    $conn->close();
}
?>