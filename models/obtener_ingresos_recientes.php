<?php

ini_set('display_errors', 0);
error_reporting(E_ALL);

set_error_handler(function ($severity, $message, $file, $line) {
    header('Content-Type: application/json');
    http_response_code(500 );
    echo json_encode([
        'success' => false,
        'message' => "Error interno: $message en $file línea $line"
    ]);
    exit;
});

session_start();
header('Content-Type: application/json');

// Verificación de sesión y método (tu código es correcto)
if (!isset($_SESSION['id'])) {
    http_response_code(401 );
    echo json_encode(['success' => false, 'message' => 'Usuario no autenticado']);
    exit;
}

// El método para obtener datos debería ser GET, pero si tu JS usa POST, lo dejamos así.
// Considera cambiar a GET en el futuro para seguir las convenciones REST.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405 );
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

require_once 'conexion.php';
$conn = conectar();

$usuario_id = $_SESSION['id'];

// Tu validación de usuario_id es buena.
if (!is_numeric($usuario_id) || $usuario_id <= 0) {
    http_response_code(400 );
    echo json_encode(['success' => false, 'message' => 'ID de usuario inválido']);
    exit;
}

try {
    $sql = "SELECT 
    ti.id, 
    ti.concepto, 
    ti.monto, 
    tci.nombre_categoria AS categoria, 
    ti.fecha, 
    ti.notas 
    FROM 
        T_INGRESOS AS ti
    JOIN 
        T_CATEGORIAS_INGRESOS_USUARIO AS tci ON ti.id_categoria_ingreso = tci.id
    WHERE 
        ti.usuario_id = ? 
        AND MONTH(ti.fecha) = MONTH(CURRENT_DATE()) -- <-- AÑADIDO: Filtra por el mes actual
        AND YEAR(ti.fecha) = YEAR(CURRENT_DATE())   -- <-- AÑADIDO: Filtra por el año actual
    ORDER BY 
    ti.fecha DESC, ti.id DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $ingresos = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    $formatted_incomes = array_map(function($ingreso) {
        return [
            'id' => intval($ingreso['id']),
            'concepto' => $ingreso['concepto'],
            'monto' => floatval($ingreso['monto']),
            'categoria' => $ingreso['categoria'], 
            'fecha' => $ingreso['fecha'],
            'notas' => $ingreso['notas'] ?? ''
        ];
    }, $ingresos);
    echo json_encode([
        'success' => true,
        'data' => $formatted_incomes
    ]);

} catch (Exception $e) {
    error_log("Error al obtener ingresos recientes: " . $e->getMessage());
    http_response_code(500 );
    echo json_encode([
        'success' => false,
        'message' => 'Error interno del servidor al obtener los ingresos.'
    ]);
} finally {
    if ($conn) {
        $conn->close();
    }
}
?>