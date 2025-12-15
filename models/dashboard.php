<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json');

require_once 'conexion.php';

function send_json_response($success, $message, $data = null) {
    echo json_encode(['success' => $success, 'message' => $message, 'data' => $data]);
    exit;
}


$conn = conectar();
if (!$conn) {
    send_json_response(false, 'Error de conexión a la base de datos.');
}

$user_id = $_SESSION['id'] ?? 0;
if ($user_id === 0) {
    send_json_response(false, 'Sesión no válida.');
}

// Fechas para el mes actual y el mes anterior
$inicio_mes_actual = date('Y-m-01');
$fin_mes_actual = date('Y-m-t');
$inicio_mes_anterior = date('Y-m-01', strtotime('-1 month'));
$fin_mes_anterior = date('Y-m-t', strtotime('-1 month'));

// Función auxiliar para ejecutar consultas de suma
function get_sum($conn, $table, $date_col, $user_id, $start, $end) {
    $sql = "SELECT SUM(monto) as total FROM {$table} WHERE usuario_id = ? AND {$date_col} BETWEEN ? AND ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('iss', $user_id, $start, $end);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc()['total'] ?? 0;
}

// Obtener los totales
$ingresos_mes_actual = get_sum($conn, 'T_INGRESOS', 'fecha', $user_id, $inicio_mes_actual, $fin_mes_actual);
$gastos_mes_actual = get_sum($conn, 'T_GASTOS', 'fecha_gasto', $user_id, $inicio_mes_actual, $fin_mes_actual);
$ingresos_mes_anterior = get_sum($conn, 'T_INGRESOS', 'fecha', $user_id, $inicio_mes_anterior, $fin_mes_anterior);
$gastos_mes_anterior = get_sum($conn, 'T_GASTOS', 'fecha_gasto', $user_id, $inicio_mes_anterior, $fin_mes_anterior);

$sql_total_ingresos = "SELECT SUM(monto) as total FROM T_INGRESOS WHERE usuario_id = ?";
$stmt_total_ingresos = $conn->prepare($sql_total_ingresos);
$stmt_total_ingresos->bind_param('i', $user_id);
$stmt_total_ingresos->execute();
$total_ingresos_historico = $stmt_total_ingresos->get_result()->fetch_assoc()['total'] ?? 0;
$stmt_total_ingresos->close();

$sql_total_gastos = "SELECT SUM(monto) as total FROM T_GASTOS WHERE usuario_id = ?";
$stmt_total_gastos = $conn->prepare($sql_total_gastos);
$stmt_total_gastos->bind_param('i', $user_id);
$stmt_total_gastos->execute();
$total_gastos_historico = $stmt_total_gastos->get_result()->fetch_assoc()['total'] ?? 0;
$stmt_total_gastos->close();

$saldo_restante_total = $total_ingresos_historico - $total_gastos_historico;



// Calcular porcentajes de cambio
$porcentaje_ingresos = ($ingresos_mes_anterior > 0) ? (($ingresos_mes_actual - $ingresos_mes_anterior) / $ingresos_mes_anterior) * 100 : ($ingresos_mes_actual > 0 ? 100 : 0);
$porcentaje_gastos = ($gastos_mes_anterior > 0) ? (($gastos_mes_actual - $gastos_mes_anterior) / $gastos_mes_anterior) * 100 : ($gastos_mes_actual > 0 ? 100 : 0);
$porcentaje_ahorro = ($ingresos_mes_actual > 0) ? (($ingresos_mes_actual - $gastos_mes_actual) / $ingresos_mes_actual) * 100 : 0;

// --- 2. DATOS PARA EL GRÁFICO DE TENDENCIA MENSUAL (Últimos 6 meses) ---
$tendencia_mensual = [];
for ($i = 5; $i >= 0; $i--) {
    $mes_inicio = date('Y-m-01', strtotime("-$i months"));
    $mes_fin = date('Y-m-t', strtotime("-$i months"));
    $mes_nombre = strftime('%b', strtotime($mes_inicio)); // 'Ene', 'Feb', etc.

    $ingresos = get_sum($conn, 'T_INGRESOS', 'fecha', $user_id, $mes_inicio, $mes_fin);
    $gastos = get_sum($conn, 'T_GASTOS', 'fecha_gasto', $user_id, $mes_inicio, $mes_fin);

    $tendencia_mensual['labels'][] = ucfirst($mes_nombre);
    $tendencia_mensual['ingresos'][] = $ingresos;
    $tendencia_mensual['gastos'][] = $gastos;
}

// --- 3. DATOS PARA TRANSACCIONES RECIENTES (Últimas 5) ---
$sql_recientes = "
    (SELECT id, concepto as descripcion, monto, 'ingreso' as tipo, fecha FROM T_INGRESOS WHERE usuario_id = ?)
    UNION ALL
    (SELECT id, descripcion, monto, 'gasto' as tipo, fecha_gasto as fecha FROM T_GASTOS WHERE usuario_id = ?)
    ORDER BY fecha DESC, id DESC
    LIMIT 5
";
$stmt_recientes = $conn->prepare($sql_recientes);
$stmt_recientes->bind_param('ii', $user_id, $user_id);
$stmt_recientes->execute();
$transacciones_recientes = $stmt_recientes->get_result()->fetch_all(MYSQLI_ASSOC);

// --- 4. CONSTRUIR Y ENVIAR LA RESPUESTA JSON ---
$data = [
    'cards' => [
        'ingresosMes' => $ingresos_mes_actual,
        'gastosMes' => $gastos_mes_actual,
        'balanceMes' => $ingresos_mes_actual - $gastos_mes_actual,
        'saldoRestante' => $saldo_restante_total,
        'porcentajeIngresos' => $porcentaje_ingresos,
        'porcentajeGastos' => $porcentaje_gastos,
        'porcentajeAhorro' => $porcentaje_ahorro

    ],
    'monthlyTrend' => $tendencia_mensual,
    'recentTransactions' => $transacciones_recientes
];

send_json_response(true, 'Datos del dashboard cargados.', $data);

$conn->close();
?>