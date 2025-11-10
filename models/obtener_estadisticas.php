<?php
// Tu configuración de errores y sesión es excelente.
session_start();
ini_set('display_errors', 0);
error_reporting(E_ALL);

set_error_handler(function ($severity, $message, $file, $line) {
    header('Content-Type: application/json');
    http_response_code(500 );
    echo json_encode(['success' => false, 'message' => "Error: $message en $file línea $line"]);
    exit;
});

header('Content-Type: application/json');
require_once 'conexion.php';

// Verificaciones de sesión y método (tu código es correcto).
if (!isset($_SESSION['id'])) {
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
try {
    $sql_general_stats = "
        SELECT
            SUM(monto) AS total_ingresos,
            SUM(CASE WHEN MONTH(fecha) = MONTH(CURRENT_DATE()) AND YEAR(fecha) = YEAR(CURRENT_DATE()) THEN monto ELSE 0 END) AS mes_actual,
            COUNT(*) AS total_transacciones,
            AVG(monto) AS promedio,
            MAX(monto) AS maximo
        FROM T_INGRESOS
        WHERE usuario_id = ?
    ";
    $stmt = $conn->prepare($sql_general_stats);
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $general_stats = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $sql_por_categoria = "
        SELECT 
            tci.nombre_categoria AS categoria, 
            SUM(ti.monto) AS total 
        FROM 
            T_INGRESOS AS ti
        JOIN 
            T_CATEGORIAS_INGRESOS_USUARIO AS tci ON ti.id_categoria_ingreso = tci.id
        WHERE 
            ti.usuario_id = ? 
        GROUP BY 
            tci.nombre_categoria
    ";
    $stmt = $conn->prepare($sql_por_categoria);
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $por_categoria = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // --- Consulta de últimos meses (esta ya estaba bien, la mantenemos separada) ---
    $sql_ultimos_meses = "
        SELECT 
            DATE_FORMAT(fecha, '%Y-%m') AS mes, 
            SUM(monto) AS total 
        FROM T_INGRESOS 
        WHERE 
            usuario_id = ? AND fecha >= DATE_SUB(CURRENT_DATE(), INTERVAL 6 MONTH) 
        GROUP BY 
            DATE_FORMAT(fecha, '%Y-%m') 
        ORDER BY mes
    ";
    $stmt = $conn->prepare($sql_ultimos_meses);
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $ultimos_meses = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // Respuesta exitosa con los datos ya formateados
    echo json_encode([
        'success' => true,
        'data' => [
            'total_ingresos' => floatval($general_stats['total_ingresos'] ?? 0),
            'mes_actual' => floatval($general_stats['mes_actual'] ?? 0),
            'total_transacciones' => intval($general_stats['total_transacciones'] ?? 0),
            'promedio' => floatval($general_stats['promedio'] ?? 0),
            'maximo' => floatval($general_stats['maximo'] ?? 0),
            'por_categoria' => $por_categoria,
            'ultimos_meses' => $ultimos_meses
        ]
    ]);

} catch (Exception $e) {
    error_log("Error al obtener estadísticas: " . $e->getMessage());
    http_response_code(500 );
    echo json_encode([
        'success' => false,
        'message' => 'Error interno del servidor al obtener estadísticas.'
    ]);
} finally {
    if ($conn) {
        $conn->close();
    }
}
?>