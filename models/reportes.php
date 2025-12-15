<?php
session_start();
ini_set('display_errors', 0); // No mostrar errores en el output
error_reporting(E_ALL);
set_error_handler(function ($severity, $message, $file, $line) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => "Error: $message en $file línea $line"
    ]);
    exit;
});


header('Content-Type: application/json');


if (!isset($_SESSION['id'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Usuario no autenticado'
    ]);
    exit;
}

require_once 'conexion.php';
$conn = conectar();


$input = []; 

if ($_SERVER['REQUEST_METHOD'] === 'POST' && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
    $json_body = file_get_contents('php://input');
    
    $input = json_decode($json_body, true);
} else {
    $input = $_REQUEST;
}

$action = $input['action'] ?? '';
$usuario_id = $_SESSION['id'];




switch ($action) {
    case 'agregar_transaccion': 
        agregarTransaccion($conn, $usuario_id, $input);
        break;
    case 'eliminar_transaccion': 
        eliminarTransaccion($conn, $usuario_id);
        break;
    case 'obtener_transacciones': 
        obtenerTransacciones($conn, $usuario_id);
        break;
    case 'obtener_resumen':
        obtenerResumen($conn, $usuario_id);
        break;
    case 'obtener_gastos_por_categoria':
        obtenerGastosPorCategoria($conn, $usuario_id);
        break;
    case 'obtener_tendencia_mensual':
        obtenerTendenciaMensual($conn, $usuario_id);
        break;
    case 'obtener_proyeccion_saldo':
        obtenerProyeccionSaldo($conn, $usuario_id);
        break;
    case 'detectar_gastos_recurrentes':
        detectarGastosRecurrentes($conn, $usuario_id);
        break;
    case 'sugerir_categoria':
        sugerirCategoria($conn, $usuario_id, $input);
        break;
    default:
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Acción no válida'
        ]);
}

function agregarTransaccion($conn, $usuario_id, $input) {
     $conn->begin_transaction();
    try {
       $fecha = trim($input['fecha'] ?? '');
        $descripcion = trim($input['descripcion'] ?? '');
        $id_categoria = intval($input['id_categoria'] ?? 0); 
        $tipo = trim($input['tipo'] ?? '');
        $monto = floatval(str_replace(['.', ','], ['', '.'], $input['monto'] ?? '0'));
        $es_recurrente = filter_var($input['es_recurrente'] ?? false, FILTER_VALIDATE_BOOLEAN);

        if (empty($fecha)) {
            throw new Exception('La fecha es obligatoria');
        }
        $dateTime = DateTime::createFromFormat('Y-m-d', $fecha);
        if (!$dateTime || $dateTime->format('Y-m-d') !== $fecha) {
            throw new Exception('Formato de fecha inválido');
        }
        $today = new DateTime();
        if ($dateTime > $today) {
            throw new Exception('La fecha no puede ser futura');
        }
        if (empty($descripcion) || strlen($descripcion) < 3) {
            throw new Exception('La descripción debe tener al menos 3 caracteres');
        }
        
        if ($tipo === 'Gasto' && $id_categoria <= 0) {
            throw new Exception('La categoría es obligatoria');
        }
        $valid_tipos = ['Ingreso', 'Gasto'];
        if (!in_array($tipo, $valid_tipos)) {
            throw new Exception('Tipo de transacción inválido');
        }
        if ($monto <= 0) {
            throw new Exception('El monto debe ser mayor a 0');
        }


        $sql = "INSERT INTO T_TRANSACCIONES (id_usuario, fecha, descripcion, id_categoria, tipo, monto, es_recurrente) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
         $stmt->bind_param("issisdi", $usuario_id, $fecha, $descripcion, $id_categoria, $tipo, $monto, $es_recurrente);
        
        if (!$stmt->execute()) {
            throw new Exception('Error al agregar la transacción: ' . $stmt->error);
        }
        $id_transaccion = $conn->insert_id;
        $stmt->close();
        $alerta = null;
            if ($tipo === 'Gasto') {
            $sql_stats = "SELECT AVG(monto) as avg_monto, STDDEV(monto) as std_dev_monto 
                  FROM T_TRANSACCIONES 
                  WHERE id_usuario = ? AND tipo = 'Gasto' AND id_categoria = ? AND fecha >= DATE_SUB(CURDATE(), INTERVAL 90 DAY)
                  AND id_transaccion != ?"; 
            
            $stmt_stats = $conn->prepare($sql_stats);
             $stmt_stats->bind_param("iii", $usuario_id, $id_categoria, $id_transaccion);
            $stmt_stats->execute();
            $stats = $stmt_stats->get_result()->fetch_assoc();
            $stmt_stats->close();

            $avg_monto = $stats['avg_monto'] ?? 0;
            $std_dev_monto = $stats['std_dev_monto'] ?? 0;
            if ($avg_monto > 0) {
                $umbral = $avg_monto + (2 * $std_dev_monto);
                if ($monto > $umbral) {
                    $formattedMonto = number_format($monto, 0, ',', '.');
                    $alerta = "¡Atención! Este gasto de $$formattedMonto parece inusualmente alto para esta categoría.";
                }
            }
        }
        $conn->commit();
        $respuesta = [
            'success' => true,
            'message' => 'Transacción registrada exitosamente',
            'id' => $id_transaccion
        ];
        if ($alerta) {
            $respuesta['alerta'] = $alerta;
            $sql_notif = "INSERT INTO T_NOTIFICACIONES (id_usuario, tipo, mensaje, id_transaccion_relacionada) VALUES (?, 'alerta_gasto', ?, ?)";
            $stmt_notif = $conn->prepare($sql_notif);
            $stmt_notif->bind_param("isi", $usuario_id, $alerta, $id_transaccion);
            $stmt_notif->execute();
            $stmt_notif->close();
        }
        echo json_encode($respuesta);
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}
function eliminarTransaccion($conn, $usuario_id) {
    try {
        $id = intval($_POST['id'] ?? 0);
        $ip = $_SERVER['REMOTE_ADDR'];

        if ($id <= 0) {
            $sql_log = "INSERT INTO T_LOG_ELIMINACIONES_TRANSACCIONES (transaccion_id, usuario_id, ip, fecha_intento, exitoso, mensaje) VALUES (?, ?, ?, NOW(), 0, ?)";
            $stmt_log = $conn->prepare($sql_log);
            $mensaje = 'ID de transacción inválido';
            $stmt_log->bind_param("iiss", $id, $usuario_id, $ip, $mensaje);
            $stmt_log->execute();
            $stmt_log->close();

            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => $mensaje
            ]);
            exit;
        }
        $sql = "SELECT id FROM T_TRANSACCIONES WHERE id = ? AND usuario_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $id, $usuario_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if (!$result->fetch_assoc()) {
            $stmt->close();
            
            $sql_log = "INSERT INTO T_LOG_ELIMINACIONES_TRANSACCIONES (transaccion_id, usuario_id, ip, fecha_intento, exitoso, mensaje) VALUES (?, ?, ?, NOW(), 0, ?)";
            $stmt_log = $conn->prepare($sql_log);
            $mensaje = 'Transacción no encontrada o no autorizada';
            $stmt_log->bind_param("iiss", $id, $usuario_id, $ip, $mensaje);
            $stmt_log->execute();
            $stmt_log->close();

            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => $mensaje
            ]);
            exit;
        }
        $stmt->close();
        $sql = "DELETE FROM T_TRANSACCIONES WHERE id = ? AND usuario_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $id, $usuario_id);
        $success = $stmt->execute();
    
        $sql_log = "INSERT INTO T_LOG_ELIMINACIONES_TRANSACCIONES (transaccion_id, usuario_id, ip, fecha_intento, exitoso, mensaje) VALUES (?, ?, ?, NOW(), ?, ?)";
        $stmt_log = $conn->prepare($sql_log);
        $exitoso = $success ? 1 : 0;
        $mensaje = $success ? 'Transacción eliminada exitosamente' : 'Error al eliminar transacción';
        $stmt_log->bind_param("iisis", $id, $usuario_id, $ip, $exitoso, $mensaje);
        $stmt_log->execute();
        $stmt_log->close();

        if ($success) {
            echo json_encode([
                'success' => true,
                'message' => $mensaje
            ]);
        } else {
            throw new Exception($mensaje);
        }
        $stmt->close();
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}

function obtenerTransacciones($conn, $usuario_id) {
    try {
        $periodo = $_POST['periodo'] ?? 'month';
        $start_date = $_POST['start_date'] ?? null;
        $end_date = $_POST['end_date'] ?? null;
        $limit_param = filter_input(INPUT_POST, 'limit', FILTER_VALIDATE_INT, ['options' => ['default' => 8]]);
        $isExportRequest = ($limit_param === -1);
        $page = filter_input(INPUT_POST, 'page', FILTER_VALIDATE_INT, ['options' => ['default' => 1, 'min_range' => 1]]);
        
        $baseQuery = "
            (
                -- Parte de los Gastos
                SELECT 
                    id, 
                    fecha_gasto AS fecha, 
                    descripcion, 
                    categoria,
                    'Gasto' AS tipo, 
                    monto, 
                    fecha_registro 
                FROM T_GASTOS
                WHERE usuario_id = ?
            )
            UNION ALL
            (
                -- Parte de los Ingresos
                SELECT 
                    i.id, 
                    i.fecha, 
                    i.concepto AS descripcion, 
                    ci.nombre_categoria AS categoria,
                    'Ingreso' AS tipo, 
                    i.monto, 
                    i.creado_en AS fecha_registro 
                FROM T_INGRESOS AS i
                JOIN T_CATEGORIAS_INGRESOS_USUARIO AS ci ON i.id_categoria_ingreso = ci.id
                WHERE i.usuario_id = ?
            )
        ";
        
        $whereConditions = [];
        $params = [$usuario_id, $usuario_id];
        $types = "ii";

        // Filtros de período
        if ($periodo === 'day') {
            $whereConditions[] = "DATE(fecha) = CURDATE()";
        } elseif ($periodo === 'week') {
            $whereConditions[] = "YEARWEEK(fecha, 1) = YEARWEEK(CURDATE(), 1)";
        } elseif ($periodo === 'year') {
            $whereConditions[] = "YEAR(fecha) = YEAR(CURDATE())";
        } elseif ($periodo === 'custom' && !empty($start_date) && !empty($end_date)) {
            $whereConditions[] = "fecha BETWEEN ? AND ?";
            $params[] = $start_date;
            $params[] = $end_date;
            $types .= "ss";
         } 
        //else { 
        //     $whereConditions[] = "MONTH(fecha) = MONTH(CURDATE()) AND YEAR(fecha) = YEAR(CURDATE())";
        // }

        $whereClause = count($whereConditions) > 0 ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
        
        // Construir consulta base
        $finalQuery = "SELECT * FROM ({$baseQuery}) AS transacciones {$whereClause} ORDER BY fecha DESC, fecha_registro DESC";
        $paginationData = null;
        //print_r($finalQuery);
        // Solo aplicar paginación si NO es una exportación
        if (!$isExportRequest) {
            $limit = 8;
            $offset = ($page - 1) * $limit;
            
            // Contar total de registros para paginación
            $countQuery = "SELECT COUNT(*) as total FROM ({$baseQuery}) AS transacciones {$whereClause}";
            $stmtCount = $conn->prepare($countQuery);
            if (!$stmtCount) throw new Exception("Error al preparar la consulta de conteo: " . $conn->error);
            
            $stmtCount->bind_param($types, ...$params);
            $stmtCount->execute();
            $totalRows = $stmtCount->get_result()->fetch_assoc()['total'] ?? 0;
            $totalPages = ceil($totalRows / $limit);
            $stmtCount->close();

            $paginationData = [
                'currentPage' => $page,
                'totalPages' => $totalPages,
                'totalRows' => $totalRows,
                'limit' => $limit
            ];

            // Agregar LIMIT y OFFSET a la consulta
            $finalQuery .= " LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
            $types .= "ii";
        }
        
        // Ejecutar la consulta final
        $stmt = $conn->prepare($finalQuery);
        if (!$stmt) throw new Exception("Error al preparar la consulta de datos: " . $conn->error);
        
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        $transacciones = $result->fetch_all(MYSQLI_ASSOC);
        
        // Convertir montos a float
        foreach ($transacciones as &$t) {
            $t['monto'] = floatval($t['monto']);
        }

        $response = [
            'success' => true,
            'data' => $transacciones
        ];
        
        if (!$isExportRequest) {
            $response['pagination'] = $paginationData;
        }

        echo json_encode($response);
        $stmt->close();

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}

function obtenerResumen($conn, $usuario_id) {
    try {
        $periodo = $_GET['periodo'] ?? 'month'; // Cambié de POST a GET para alinearme con el JavaScript
        $start_date = $_GET['start_date'] ?? null;
        $end_date = $_GET['end_date'] ?? null;
        $query_ingresos = "SELECT COALESCE(SUM(monto), 0) as ingresos FROM T_INGRESOS WHERE usuario_id = ?";
        $query_gastos = "SELECT COALESCE(SUM(monto), 0) as gastos FROM T_GASTOS WHERE usuario_id = ?";
        $params = [$usuario_id];
        $types = "i";

        if ($periodo === 'day') {
            $query_ingresos .= " AND DATE(fecha) = CURDATE()";
            $query_gastos .= " AND DATE(fecha_gasto) = CURDATE()";
        } elseif ($periodo === 'week') {
            $query_ingresos .= " AND YEARWEEK(fecha) = YEARWEEK(CURDATE())";
            $query_gastos .= " AND YEARWEEK(fecha_gasto) = YEARWEEK(CURDATE())";
        } elseif ($periodo === 'year') {
            $query_ingresos .= " AND YEAR(fecha) = YEAR(CURDATE())";
            $query_gastos .= " AND YEAR(fecha_gasto) = YEAR(CURDATE())";
        } elseif ($periodo === 'custom' && $start_date && $end_date) {
            $query_ingresos .= " AND fecha BETWEEN ? AND ?";
            $query_gastos .= " AND fecha_gasto BETWEEN ? AND ?";
            $params[] = $start_date;
            $params[] = $end_date;
            $types .= "ss";
        } else {
            $query_ingresos .= " AND MONTH(fecha) = MONTH(CURDATE()) AND YEAR(fecha) = YEAR(CURDATE())";
            $query_gastos .= " AND MONTH(fecha_gasto) = MONTH(CURDATE()) AND YEAR(fecha_gasto) = YEAR(CURDATE())";
        }

        // Ejecutar consulta para ingresos
        $stmt_ingresos = $conn->prepare($query_ingresos);
        $stmt_ingresos->bind_param($types, ...$params);
        $stmt_ingresos->execute();
        $result_ingresos = $stmt_ingresos->get_result();
        $ingresos = $result_ingresos->fetch_assoc()['ingresos'] ?? 0;
        $stmt_ingresos->close();

        // Ejecutar consulta para gastos
        $stmt_gastos = $conn->prepare($query_gastos);
        $stmt_gastos->bind_param($types, ...$params);
        $stmt_gastos->execute();
        $result_gastos = $stmt_gastos->get_result();
        $gastos = $result_gastos->fetch_assoc()['gastos'] ?? 0;
        $stmt_gastos->close();

        $resumen = [
            'ingresos' => floatval($ingresos),
            'gastos' => floatval($gastos),
            'balance' => floatval($ingresos - $gastos)
        ];

        echo json_encode([
            'success' => true,
            'data' => $resumen
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}
function obtenerGastosPorCategoria($conn, $usuario_id) {
    try {
        $periodo = $_GET['periodo'] ?? 'month';
        $start_date = $_GET['start_date'] ?? null;
        $end_date = $_GET['end_date'] ?? null;

        $query = "SELECT categoria, SUM(monto) as total 
                  FROM T_GASTOS 
                  WHERE usuario_id = ?";
        $params = [$usuario_id];
        $types = "i";

        if ($periodo === 'day') {
            $query .= " AND DATE(fecha_gasto) = CURDATE()";
        } elseif ($periodo === 'week') {
            $query .= " AND YEARWEEK(fecha_gasto) = YEARWEEK(CURDATE())";
        } elseif ($periodo === 'year') {
            $query .= " AND YEAR(fecha_gasto) = YEAR(CURDATE())";
        } elseif ($periodo === 'custom' && $start_date && $end_date) {
            $query .= " AND fecha_gasto BETWEEN ? AND ?";
            $params[] = $start_date;
            $params[] = $end_date;
            $types .= "ss";
        } else {
            $query .= " AND MONTH(fecha_gasto) = MONTH(CURDATE()) AND YEAR(fecha_gasto) = YEAR(CURDATE())";
        }


        $query .= " GROUP BY categoria";
        $stmt = $conn->prepare($query);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        $categorias = $result->fetch_all(MYSQLI_ASSOC);

        foreach ($categorias as &$categoria) {
            $categoria['total'] = floatval($categoria['total']);
        }

        echo json_encode([
            'success' => true,
            'data' => $categorias
        ]);
        $stmt->close();
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}

function obtenerTendenciaMensual($conn, $usuario_id) {
    try {
        // Consulta para ingresos
        $query_ingresos = "SELECT 
            DATE_FORMAT(fecha, '%Y-%m') as mes,
            COALESCE(SUM(monto), 0) as ingresos
            FROM T_INGRESOS 
            WHERE usuario_id = ? 
            AND fecha >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
            GROUP BY DATE_FORMAT(fecha, '%Y-%m')";
        
        // Consulta para gastos
        $query_gastos = "SELECT 
            DATE_FORMAT(fecha_gasto, '%Y-%m') as mes,
            COALESCE(SUM(monto), 0) as gastos
            FROM T_GASTOS 
            WHERE usuario_id = ? 
            AND fecha_gasto >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
            GROUP BY DATE_FORMAT(fecha_gasto, '%Y-%m')";
        
        // Ejecutar consulta para ingresos
        $stmt_ingresos = $conn->prepare($query_ingresos);
        $stmt_ingresos->bind_param("i", $usuario_id);
        $stmt_ingresos->execute();
        $result_ingresos = $stmt_ingresos->get_result();
        $ingresos_data = $result_ingresos->fetch_all(MYSQLI_ASSOC);
        $stmt_ingresos->close();

        // Ejecutar consulta para gastos
        $stmt_gastos = $conn->prepare($query_gastos);
        $stmt_gastos->bind_param("i", $usuario_id);
        $stmt_gastos->execute();
        $result_gastos = $stmt_gastos->get_result();
        $gastos_data = $result_gastos->fetch_all(MYSQLI_ASSOC);
        $stmt_gastos->close();

        // Generar lista de meses de los últimos 6 meses
        $meses = [];
        $current_date = new DateTime();
        for ($i = 5; $i >= 0; $i--) {
            $meses[] = $current_date->modify("-$i months")->format('Y-m');
            $current_date->modify("+$i months"); // Restaurar fecha actual
        }
        $current_date->modify('-5 months'); // Restaurar para el bucle

        // Combinar resultados
        $tendencia = [];
        foreach ($meses as $mes) {
            $ingresos = 0;
            $gastos = 0;

            // Buscar ingresos para el mes
            foreach ($ingresos_data as $row) {
                if ($row['mes'] === $mes) {
                    $ingresos = floatval($row['ingresos']);
                    break;
                }
            }

            // Buscar gastos para el mes
            foreach ($gastos_data as $row) {
                if ($row['mes'] === $mes) {
                    $gastos = floatval($row['gastos']);
                    break;
                }
            }

            $tendencia[] = [
                'mes' => $mes,
                'ingresos' => $ingresos,
                'gastos' => $gastos
            ];
        }

        echo json_encode([
            'success' => true,
            'data' => $tendencia
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}

function obtenerProyeccionSaldo($conn, $usuario_id) {
    try {
        $sql_mes_actual = "SELECT 
                                COALESCE(SUM(CASE WHEN tipo = 'Ingreso' THEN monto ELSE 0 END), 0) as ingresos_mes_actual,
                                COALESCE(SUM(CASE WHEN tipo = 'Gasto' THEN monto ELSE 0 END), 0) as gastos_mes_actual
                           FROM T_TRANSACCIONES 
                           WHERE id_usuario = ? 
                             AND MONTH(fecha) = MONTH(CURDATE())
                             AND YEAR(fecha) = YEAR(CURDATE())";
        
        $stmt_mes_actual = $conn->prepare($sql_mes_actual);
        $stmt_mes_actual->bind_param("i", $usuario_id);
        $stmt_mes_actual->execute();
        $result = $stmt_mes_actual->get_result()->fetch_assoc();
        $ingresos_mes_actual = $result['ingresos_mes_actual'] ?? 0;
        $gastos_mes_actual = $result['gastos_mes_actual'] ?? 0;
        $stmt_mes_actual->close();

        $sql_gastos_avg = "
            SELECT AVG(gastos_diarios) as promedio_gasto_diario
            FROM (
                SELECT SUM(monto) as gastos_diarios
                FROM T_TRANSACCIONES
                WHERE id_usuario = ? AND tipo = 'Gasto' AND fecha >= DATE_SUB(CURDATE(), INTERVAL 60 DAY)
                GROUP BY fecha
            ) as subquery
            WHERE gastos_diarios < (
                SELECT AVG(gastos_diarios) + (3 * STDDEV(gastos_diarios)) 
                FROM (
                    SELECT SUM(monto) as gastos_diarios
                    FROM T_TRANSACCIONES
                    WHERE id_usuario = ? AND tipo = 'Gasto' AND fecha >= DATE_SUB(CURDATE(), INTERVAL 60 DAY)
                    GROUP BY fecha
                ) as inner_stddev
            )
        ";
        $stmt_gastos_avg = $conn->prepare($sql_gastos_avg);
        $stmt_gastos_avg->bind_param("ii", $usuario_id, $usuario_id);
        $stmt_gastos_avg->execute();
        $promedio_gasto_diario = $stmt_gastos_avg->get_result()->fetch_assoc()['promedio_gasto_diario'] ?? 0;
        $stmt_gastos_avg->close();

       
        $dias_restantes = date('t') - date('j'); // Días que quedan en el mes.
        $gastos_proyectados_restantes = $promedio_gasto_diario * $dias_restantes;

       
        $sql_ingresos_rec = "SELECT COALESCE(SUM(monto), 0) as ingresos_recurrentes_restantes
                             FROM T_TRANSACCIONES
                             WHERE id_usuario = ?
                               AND tipo = 'Ingreso'
                               AND es_recurrente = TRUE
                               AND MONTH(fecha) = MONTH(CURDATE())
                               AND YEAR(fecha) = YEAR(CURDATE())
                               AND DAY(fecha) > DAY(CURDATE())";
        $stmt_ingresos_rec = $conn->prepare($sql_ingresos_rec);
        $stmt_ingresos_rec->bind_param("i", $usuario_id);
        $stmt_ingresos_rec->execute();
        $ingresos_recurrentes_restantes = $stmt_ingresos_rec->get_result()->fetch_assoc()['ingresos_recurrentes_restantes'] ?? 0;
        $stmt_ingresos_rec->close();

        
        $proyeccion_ingresos_totales_mes = $ingresos_mes_actual + $ingresos_recurrentes_restantes;
        $proyeccion_gastos_totales_mes = $gastos_mes_actual + $gastos_proyectados_restantes;
        $proyeccion_saldo_fin_de_mes = $proyeccion_ingresos_totales_mes - $proyeccion_gastos_totales_mes;

        echo json_encode([
            'success' => true,
            'data' => [
                'proyeccion_final' => floatval($proyeccion_saldo_fin_de_mes),
                'ingresos_mes_actual' => floatval($ingresos_mes_actual),
                'gastos_mes_actual' => floatval($gastos_mes_actual),
                'promedio_gasto_diario' => floatval($promedio_gasto_diario),
                'gastos_proyectados_restantes' => floatval($gastos_proyectados_restantes),
                'ingresos_recurrentes_restantes' => floatval($ingresos_recurrentes_restantes),
                'proyeccion_ingresos_totales_mes' => floatval($proyeccion_ingresos_totales_mes),
                'proyeccion_gastos_totales_mes' => floatval($proyeccion_gastos_totales_mes)
            ]
        ]);

    } catch (Exception $e) {
        http_response_code(500 );
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function detectarGastosRecurrentes($conn, $usuario_id)
{
    try {
        // 1. GASTOS CONFIRMADOS (Alta confianza: 3+ meses, patrón claro)
        $sqlConfirmados = "
            WITH gastos_agrupados AS (
                SELECT 
                    LOWER(TRIM(SUBSTRING_INDEX(descripcion, ' ', 3))) AS descripcion_base,
                    ROUND(monto, -2) AS monto_rango,
                    DATE_FORMAT(fecha_gasto, '%Y-%m') AS mes_ano,
                    fecha_gasto,
                    monto
                FROM T_GASTOS
                WHERE usuario_id = ?
                    AND fecha_gasto >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
            ),
            analisis AS (
                SELECT 
                    descripcion_base,
                    COUNT(DISTINCT mes_ano) AS meses_detectados,
                    COUNT(*) AS total_transacciones,
                    AVG(monto) AS monto_promedio,
                    MIN(monto) AS monto_minimo,
                    MAX(monto) AS monto_maximo,
                    STDDEV(monto) AS desviacion_monto,
                    MAX(fecha_gasto) AS fecha_ultimo_pago,
                    MIN(fecha_gasto) AS fecha_primer_pago,
                    DATEDIFF(CURDATE(), MAX(fecha_gasto)) AS dias_desde_ultimo,
                    DATEDIFF(MAX(fecha_gasto), MIN(fecha_gasto)) AS dias_totales
                FROM gastos_agrupados
                GROUP BY descripcion_base
            )
            SELECT 
                descripcion_base,
                monto_promedio,
                monto_minimo,
                monto_maximo,
                desviacion_monto,
                meses_detectados AS repeticiones,
                fecha_ultimo_pago,
                dias_desde_ultimo,
                CASE
                    WHEN dias_totales = 0 THEN 30
                    ELSE ROUND(dias_totales / GREATEST(total_transacciones - 1, 1))
                END AS promedio_dias_entre_pagos,
                CASE 
                    WHEN desviacion_monto IS NULL OR desviacion_monto < 100 THEN 'FIJO'
                    WHEN desviacion_monto < monto_promedio * 0.15 THEN 'FIJO'
                    ELSE 'VARIABLE'
                END AS patron_monto,
                ROUND((meses_detectados / 6.0) * 100) AS confianza
            FROM analisis
            WHERE meses_detectados >= 3
            ORDER BY monto_promedio DESC
        ";

        $stmt = $conn->prepare($sqlConfirmados);
        $stmt->bind_param("i", $usuario_id);
        $stmt->execute();
        $confirmados = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // 2. GASTOS PROBABLES (Confianza media: 2 meses)
        $sqlProbables = "
            WITH gastos_agrupados AS (
                SELECT 
                    LOWER(TRIM(SUBSTRING_INDEX(descripcion, ' ', 3))) AS descripcion_base,
                    DATE_FORMAT(fecha_gasto, '%Y-%m') AS mes_ano,
                    fecha_gasto,
                    monto
                FROM T_GASTOS
                WHERE usuario_id = ?
                    AND fecha_gasto >= DATE_SUB(CURDATE(), INTERVAL 4 MONTH)
            ),
            analisis AS (
                SELECT 
                    descripcion_base,
                    COUNT(DISTINCT mes_ano) AS meses_detectados,
                    COUNT(*) AS total_transacciones,
                    AVG(monto) AS monto_promedio,
                    STDDEV(monto) AS desviacion_monto,
                    MAX(fecha_gasto) AS fecha_ultimo_pago,
                    DATEDIFF(CURDATE(), MAX(fecha_gasto)) AS dias_desde_ultimo,
                    DATEDIFF(MAX(fecha_gasto), MIN(fecha_gasto)) AS dias_totales
                FROM gastos_agrupados
                GROUP BY descripcion_base
            )
            SELECT 
                descripcion_base,
                monto_promedio,
                meses_detectados AS repeticiones,
                fecha_ultimo_pago,
                dias_desde_ultimo,
                CASE
                    WHEN dias_totales = 0 THEN 30
                    ELSE ROUND(dias_totales / GREATEST(total_transacciones - 1, 1))
                END AS promedio_dias_entre_pagos,
                CASE 
                    WHEN desviacion_monto IS NULL OR desviacion_monto < 100 THEN 'FIJO'
                    ELSE 'VARIABLE'
                END AS patron_monto,
                75 AS confianza
            FROM analisis
            WHERE meses_detectados = 2
            ORDER BY monto_promedio DESC
        ";

        $stmt2 = $conn->prepare($sqlProbables);
        $stmt2->bind_param("i", $usuario_id);
        $stmt2->execute();
        $probables = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt2->close();

        // 3. POR CONFIRMAR (Baja detección pero posible patrón)
        $sqlPorConfirmar = "
            WITH gastos_similares AS (
                SELECT 
                    LOWER(TRIM(SUBSTRING_INDEX(descripcion, ' ', 3))) AS descripcion_base,
                    ROUND(monto, -2) AS monto_rango,
                    COUNT(*) AS repeticiones,
                    AVG(monto) AS monto_promedio,
                    MAX(fecha_gasto) AS fecha_ultimo_pago,
                    DATEDIFF(CURDATE(), MAX(fecha_gasto)) AS dias_desde_ultimo
                FROM T_GASTOS
                WHERE usuario_id = ?
                    AND fecha_gasto >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH)
                GROUP BY descripcion_base, monto_rango
            )
            SELECT 
                descripcion_base,
                monto_promedio,
                repeticiones,
                fecha_ultimo_pago,
                dias_desde_ultimo,
                'FIJO' AS patron_monto,
                60 AS confianza,
                'Solo detectado 2 veces - ¿Es un pago recurrente?' AS razon
            FROM gastos_similares
            WHERE repeticiones = 2
            ORDER BY monto_promedio DESC
            LIMIT 5
        ";

        $stmt3 = $conn->prepare($sqlPorConfirmar);
        $stmt3->bind_param("i", $usuario_id);
        $stmt3->execute();
        $porConfirmar = $stmt3->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt3->close();

        // Función para parsear números
        function parseNumber($n) {
            if (is_numeric($n)) return floatval($n);
            if (preg_match('/^\d{1,3}(,\d{3})*(\.\d+)?$/', $n)) {
                return floatval(str_replace(',', '', $n));
            }
            if (preg_match('/^\d{1,3}(\.\d{3})*(,\d+)?$/', $n)) {
                $n = str_replace('.', '', $n);
                $n = str_replace(',', '.', $n);
                return floatval($n);
            }
            return floatval($n);
        }

        // Procesar confirmados
        foreach ($confirmados as &$c) {
            $c["monto_promedio"] = parseNumber($c["monto_promedio"]);
            $c["monto_minimo"] = parseNumber($c["monto_minimo"] ?? 0);
            $c["monto_maximo"] = parseNumber($c["monto_maximo"] ?? 0);
            $c["repeticiones"] = intval($c["repeticiones"]);
            $c["dias_desde_ultimo"] = intval($c["dias_desde_ultimo"]);
            $c["promedio_dias_entre_pagos"] = intval($c["promedio_dias_entre_pagos"]);
            $c["confianza"] = intval($c["confianza"]);
            
            // Calcular próximo pago estimado
            $proximoPago = date('Y-m-d', strtotime($c["fecha_ultimo_pago"] . ' + ' . $c["promedio_dias_entre_pagos"] . ' days'));
            $c["proximo_pago_estimado"] = $proximoPago;
            $c["dias_hasta_proximo"] = intval((strtotime($proximoPago) - time()) / 86400);
            
            // Variación porcentual
            if ($c["patron_monto"] === 'VARIABLE' && $c["monto_promedio"] > 0) {
                $c["variacion_porcentaje"] = round((($c["monto_maximo"] - $c["monto_minimo"]) / $c["monto_promedio"]) * 100);
            } else {
                $c["variacion_porcentaje"] = 0;
            }
        }

        // Procesar probables
        foreach ($probables as &$p) {
            $p["monto_promedio"] = parseNumber($p["monto_promedio"]);
            $p["repeticiones"] = intval($p["repeticiones"]);
            $p["dias_desde_ultimo"] = intval($p["dias_desde_ultimo"]);
            $p["promedio_dias_entre_pagos"] = intval($p["promedio_dias_entre_pagos"]);
            $p["confianza"] = intval($p["confianza"]);
            
            $proximoPago = date('Y-m-d', strtotime($p["fecha_ultimo_pago"] . ' + ' . $p["promedio_dias_entre_pagos"] . ' days'));
            $p["proximo_pago_estimado"] = $proximoPago;
            $p["dias_hasta_proximo"] = intval((strtotime($proximoPago) - time()) / 86400);
            $p["variacion_porcentaje"] = 0;
        }

        // Procesar por confirmar
        foreach ($porConfirmar as &$pc) {
            $pc["monto_promedio"] = parseNumber($pc["monto_promedio"]);
            $pc["repeticiones"] = intval($pc["repeticiones"]);
            $pc["dias_desde_ultimo"] = intval($pc["dias_desde_ultimo"]);
            $pc["confianza"] = intval($pc["confianza"]);
        }

        // Calcular totales y proyecciones
        $total_mensual_confirmado = array_sum(array_column($confirmados, "monto_promedio"));
        $total_mensual_probable = array_sum(array_column($probables, "monto_promedio"));
        $total_anual_proyectado = $total_mensual_confirmado * 12;

        // Calcular ahorro potencial (ejemplo: suma de los 3 más pequeños confirmados)
        $montosConfirmados = array_column($confirmados, "monto_promedio");
        sort($montosConfirmados);
        $ahorro_potencial = array_sum(array_slice($montosConfirmados, 0, min(3, count($montosConfirmados)))) * 12;

        echo json_encode([
            "success" => true,
            "data" => [
                "confirmados" => $confirmados,
                "probables" => $probables,
                "por_confirmar" => $porConfirmar,
                "resumen" => [
                    "total_mensual_confirmado" => $total_mensual_confirmado,
                    "total_mensual_probable" => $total_mensual_probable,
                    "total_anual_proyectado" => $total_anual_proyectado,
                    "cantidad_confirmados" => count($confirmados),
                    "cantidad_probables" => count($probables),
                    "cantidad_por_confirmar" => count($porConfirmar),
                    "ahorro_potencial" => $ahorro_potencial
                ]
            ]
        ]);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "message" => $e->getMessage()
        ]);
    }
}
function sugerirCategoria($conn, $usuario_id, $input) {
    try {
        
        $descripcion = trim($input['descripcion'] ?? '');

        if (empty($descripcion)) {
            echo json_encode(['success' => true, 'sugerencia' => null]);
            return;
        }

        $descripcion_lower = strtolower($descripcion);
        $sql = "SELECT
                    rc.id_categoria,
                    c.nombre AS nombre_categoria,
                    c.icono_svg,
                    c.color_hex
                FROM T_REGLAS_CATEGORIZACION rc
                JOIN T_CATEGORIAS c ON rc.id_categoria = c.id
                WHERE LOCATE(rc.termino_clave, ?) > 0
                ORDER BY LENGTH(rc.termino_clave) DESC 
                LIMIT 1";
        
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Error al preparar la consulta de sugerencia: " . $conn->error);
        }
        $stmt->bind_param("s", $descripcion_lower);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $sugerencia = null;
        if ($row = $result->fetch_assoc()) {
            $sugerencia = [
                'id_categoria' => $row['id_categoria'],
                'nombre_categoria' => $row['nombre_categoria'],
                'icono_svg' => $row['icono_svg'],
                'color_hex' => $row['color_hex']
            ];
        }
        
        echo json_encode([
            'success' => true,
            'sugerencia' => $sugerencia
        ]);
        
        $stmt->close();

    } catch (Exception $e) {
        http_response_code(500 );
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}

$conn->close();
?>