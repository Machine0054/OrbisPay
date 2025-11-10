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
        $limit = 8; 
        $offset = ($page - 1) * $limit;
        $baseQuery = "
            (
                -- Parte de los Gastos: Selecciona 'categoria' directamente del VARCHAR
                SELECT 
                    id, 
                    fecha_gasto AS fecha, 
                    descripcion, 
                    categoria, -- Se selecciona directamente de la columna VARCHAR
                    'Gasto' AS tipo, 
                    monto, 
                    fecha_registro 
                FROM T_GASTOS
                WHERE usuario_id = ?
            )
            UNION ALL
            (
                -- Parte de los Ingresos: Usa JOIN para obtener el nombre de la categoría
                SELECT 
                    i.id, 
                    i.fecha, 
                    i.concepto AS descripcion, 
                    ci.nombre_categoria AS categoria, -- Se obtiene con el JOIN
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
        } else { 
            $whereConditions[] = "MONTH(fecha) = MONTH(CURDATE()) AND YEAR(fecha) = YEAR(CURDATE())";
        }

        $whereClause = count($whereConditions) > 0 ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
        
        $countQuery = "SELECT COUNT(*) as total FROM ({$baseQuery}) AS transacciones {$whereClause}";
        $stmtCount = $conn->prepare($countQuery);
        if (!$stmtCount) throw new Exception("Error al preparar la consulta de conteo: " . $conn->error);
        
        $stmtCount->bind_param($types, ...$params);
        $stmtCount->execute();
        $totalRows = $stmtCount->get_result()->fetch_assoc()['total'] ?? 0;
        $totalPages = ceil($totalRows / $limit);
        $stmtCount->close();
    
        $finalQuery = "SELECT * FROM ({$baseQuery}) AS transacciones {$whereClause} ORDER BY fecha DESC, fecha_registro DESC";
        $paginationData = null;
       if (!$isExportRequest) {
            
            $countQuery = "SELECT COUNT(*) as total FROM ({$baseQuery}) AS transacciones {$whereClause}";
            $stmtCount = $conn->prepare($countQuery);
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

            $finalQuery .= " LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
            $types .= "ii";
        }
        
        $stmt = $conn->prepare($finalQuery);
        if (!$stmt) throw new Exception("Error al preparar la consulta de datos: " . $conn->error);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        $transacciones = $result->fetch_all(MYSQLI_ASSOC);
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
        http_response_code(500 );
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
        
        $sql_saldo = "SELECT 
                        (SELECT COALESCE(SUM(monto), 0) FROM T_TRANSACCIONES WHERE id_usuario = ? AND tipo = 'Ingreso') - 
                        (SELECT COALESCE(SUM(monto), 0) FROM T_TRANSACCIONES WHERE id_usuario = ? AND tipo = 'Gasto') 
                      AS saldo_actual";
        $stmt_saldo = $conn->prepare($sql_saldo);
        $stmt_saldo->bind_param("ii", $usuario_id, $usuario_id);
        $stmt_saldo->execute();
        $saldo_actual = $stmt_saldo->get_result()->fetch_assoc()['saldo_actual'] ?? 0;
        $stmt_saldo->close();

        $sql_gastos_avg = "
            SELECT AVG(gastos_diarios) as promedio_gasto_diario
            FROM (
                SELECT SUM(monto) as gastos_diarios
                FROM T_TRANSACCIONES
                WHERE id_usuario = ? 
                  AND tipo = 'Gasto'
                  AND fecha >= DATE_SUB(CURDATE(), INTERVAL 60 DAY)
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

       
        $dias_restantes = date('t') - date('j'); // Días en el mes actual - día actual
        $gastos_proyectados = $promedio_gasto_diario * $dias_restantes;

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

        
        $proyeccion_final = $saldo_actual + $ingresos_recurrentes_restantes - $gastos_proyectados;

        echo json_encode([
            'success' => true,
            'data' => [
                'saldo_actual' => floatval($saldo_actual),
                'promedio_gasto_diario' => floatval($promedio_gasto_diario),
                'dias_restantes' => intval($dias_restantes),
                'gastos_proyectados' => floatval($gastos_proyectados),
                'ingresos_recurrentes_restantes' => floatval($ingresos_recurrentes_restantes),
                'proyeccion_final' => floatval($proyeccion_final)
            ]
        ]);

    } catch (Exception $e) {
        http_response_code(500 );
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}


function detectarGastosRecurrentes($conn, $usuario_id) {
    try {
        
        $sql = "SELECT
                    TRIM(SUBSTRING_INDEX(descripcion, ' ', 2)) AS descripcion_base, 
                    AVG(monto) AS monto_promedio,
                    COUNT(DISTINCT DATE_FORMAT(fecha, '%Y-%m')) AS meses_detectado,
                    MAX(fecha) AS fecha_ultimo_pago,
                    GROUP_CONCAT(id_transaccion) AS ids_transacciones
                FROM 
                    T_TRANSACCIONES
                WHERE 
                    id_usuario = ? 
                    AND tipo = 'Gasto'
                    AND fecha >= DATE_SUB(CURDATE(), INTERVAL 4 MONTH)
                GROUP BY
                    descripcion_base, ROUND(monto, -3)
                HAVING 
                    meses_detectado >= 2
                ORDER BY
                    monto_promedio DESC";
        
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Error al preparar la consulta: " . $conn->error);
        }
        
        $stmt->bind_param("i", $usuario_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $gastos_recurrentes = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

       
        if (count($gastos_recurrentes) > 0) {
            $total_recurrente = array_sum(array_column($gastos_recurrentes, 'monto_promedio'));
            $cantidad_recurrente = count($gastos_recurrentes);
            
            $mensaje = sprintf(
                "Detectamos %d pagos recurrentes este mes, sumando un total aproximado de $%s.",
                $cantidad_recurrente,
                number_format($total_recurrente, 0, ',', '.')
            );

            $sql_notif = "INSERT INTO T_NOTIFICACIONES (id_usuario, tipo, mensaje) VALUES (?, 'gasto_recurrente', ?)";
            $stmt_notif = $conn->prepare($sql_notif);
            $stmt_notif->bind_param("is", $usuario_id, $mensaje);
            $stmt_notif->execute();
            $stmt_notif->close();
        }
        $total_mensual_recurrente = 0;
        foreach ($gastos_recurrentes as &$gasto) {
            $gasto['monto_promedio'] = floatval($gasto['monto_promedio']);
            $total_mensual_recurrente += $gasto['monto_promedio'];
        }

        echo json_encode([
            'success' => true,
            'data' => [
                'gastos_detectados' => $gastos_recurrentes,
                'cantidad_detectada' => count($gastos_recurrentes),
                'total_mensual_recurrente' => $total_mensual_recurrente
            ]
        ]);

    } catch (Exception $e) {
        http_response_code(500 );
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
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