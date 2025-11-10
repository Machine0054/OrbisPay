<?php
// Iniciar sesión y establecer cabeceras
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json');

// Dependencias y funciones de utilidad
require_once 'conexion.php';

function send_json_response($success, $message, $data = null, $statusCode = 200) {
    http_response_code($statusCode );
    $response = ['success' => $success, 'message' => $message];
    if ($data !== null) {
        $response['data'] = $data;
    }
    echo json_encode($response);
    exit;
}

// Conexión a la Base de Datos
$conn = conectar();
if (!$conn) {
    send_json_response(false, 'Error de conexión a la base de datos.', null, 500);
}

// Determinar el método y validar la sesión
$method = $_SERVER['REQUEST_METHOD'];
$user_id = $_SESSION['id'] ?? 0;

if ($user_id === 0) {
    send_json_response(false, 'Acceso no autorizado. Por favor, inicia sesión de nuevo.', null, 401);
}

// =================================================================
// ROUTER PRINCIPAL: Decide qué hacer según el método y la acción
// =================================================================

if ($method === 'POST') {
    $action = $_POST['action'] ?? null;

    if ($action === 'delete') {
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $type = $_POST['type'] ?? '';
        if (!$id || !in_array($type, ['ingreso', 'gasto'])) { send_json_response(false, 'Parámetros inválidos para eliminar.', null, 400); }
        
        $table = ($type === 'ingreso') ? 'T_INGRESOS' : 'T_GASTOS';
        $sql = "DELETE FROM {$table} WHERE id = ? AND usuario_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ii', $id, $user_id);

        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) { send_json_response(true, 'Transacción eliminada exitosamente.'); }
            else { send_json_response(false, 'La transacción no se encontró o no tienes permiso para eliminarla.', null, 404); }
        } else { send_json_response(false, 'Error en la base de datos al intentar eliminar.', null, 500); }
        $stmt->close();

    } elseif ($action === 'update') {
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $type = $_POST['type'] ?? '';
        $description = trim($_POST['description'] ?? '');
        $amount = filter_input(INPUT_POST, 'amount', FILTER_VALIDATE_FLOAT);
        $date = $_POST['date'] ?? '';
        $category = $_POST['category'] ?? '';

        if (!$id || !$description || !$amount || !$date || !$category || !in_array($type, ['ingreso', 'gasto'])) { send_json_response(false, 'Todos los campos son obligatorios para actualizar.', null, 400); }
        
        $table = ($type === 'ingreso') ? 'T_INGRESOS' : 'T_GASTOS';
        $desc_col = ($type === 'ingreso') ? 'concepto' : 'descripcion';
        $date_col = ($type === 'ingreso') ? 'fecha' : 'fecha_gasto';
        
        $sql = "UPDATE {$table} SET {$desc_col} = ?, monto = ?, categoria = ?, {$date_col} = ? WHERE id = ? AND usuario_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('sdssii', $description, $amount, $category, $date, $id, $user_id);

        if ($stmt->execute()) { send_json_response(true, 'Transacción actualizada exitosamente.'); }
        else { send_json_response(false, 'Error al actualizar en la base de datos.', null, 500); }
        $stmt->close();

    } else {
        send_json_response(false, 'Acción POST no reconocida.', null, 400);
    }

} elseif ($method === 'GET') {
    $action = $_GET['action'] ?? 'getHistory';

    if ($action === 'getTransaction') {
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        $type = $_GET['type'] ?? '';
        if (!$id || !in_array($type, ['ingreso', 'gasto'])) { send_json_response(false, 'Parámetros inválidos para obtener la transacción.', null, 400); }
        
        if ($type === 'ingreso') {
            $sql = "SELECT id, concepto AS descripcion, monto, categoria, fecha, 'ingreso' AS tipo FROM T_INGRESOS WHERE id = ? AND usuario_id = ?";
        } else {
            $sql = "SELECT id, descripcion, monto, categoria, fecha_gasto AS fecha, 'gasto' AS tipo FROM T_GASTOS WHERE id = ? AND usuario_id = ?";
        }
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ii', $id, $user_id);
        $stmt->execute();
        $transaction = $stmt->get_result()->fetch_assoc();

        if ($transaction) { send_json_response(true, 'Transacción encontrada', $transaction); }
        else { send_json_response(false, 'Transacción no encontrada.', null, 404); }
        $stmt->close();

    } elseif ($action === 'getHistory') {

       try {
        // 1. OBTENER PARÁMETROS DE FILTRADO Y PAGINACIÓN
        $page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT, ['options' => ['default' => 1, 'min_range' => 1]]);
        $limit = filter_input(INPUT_GET, 'limit', FILTER_VALIDATE_INT, ['options' => ['default' => 10, 'min_range' => 1]]);
        $offset = ($page - 1) * $limit;

        $type = $_GET['type'] ?? 'todos';
        $category = $_GET['category'] ?? 'todas';
        $search = $_GET['search'] ?? '';
        $startDate = $_GET['startDate'] ?? null;
        $endDate = $_GET['endDate'] ?? null;

        $baseQuery = "
            (
            SELECT 
                g.id, 
                g.usuario_id, 
                g.descripcion, 
                g.monto, 
                g.categoria, 
                g.fecha_gasto AS fecha, 
                'gasto' AS tipo 
            FROM T_GASTOS g
            WHERE g.usuario_id = ?
        ) 
        UNION ALL 
        (
            SELECT 
                i.id, 
                i.usuario_id, 
                i.concepto AS descripcion, 
                i.monto, 
                ci.nombre_categoria AS categoria, -- Obtenemos el nombre de la categoría con el JOIN
                i.fecha, 
                'ingreso' AS tipo 
            FROM T_INGRESOS AS i
            JOIN T_CATEGORIAS_INGRESOS_USUARIO AS ci ON i.id_categoria_ingreso = ci.id
            WHERE i.usuario_id = ?
        )
        ";
        // Parámetros para la consulta base (el ID de usuario para cada parte del UNION)
        $baseParams = [$user_id, $user_id];
        $baseTypes = 'ii';

        // 3. CONSTRUIR DINÁMICAMENTE LA CLÁUSULA WHERE Y LOS PARÁMETROS DE FILTRO
        $whereConditions = [];
        $filterParams = [];
        $filterTypes = '';

        if (!empty($search)) {
            $whereConditions[] = "descripcion LIKE ?";
            $filterParams[] = "%{$search}%";
            $filterTypes .= 's';
        }
        if ($category !== 'todas') {
            $whereConditions[] = "categoria = ?";
            $filterParams[] = $category;
            $filterTypes .= 's';
        }
        if (!empty($startDate) && $startDate !== 'null' && !empty($endDate) && $endDate !== 'null') {
            $whereConditions[] = "fecha BETWEEN ? AND ?";
            $filterParams[] = $startDate;
            $filterParams[] = $endDate;
            $filterTypes .= 'ss';
        }
        if ($type !== 'todos') {
            $whereConditions[] = "tipo = ?";
            $filterParams[] = $type;
            $filterTypes .= 's';
        }
        
        
        $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

        // 4. COMBINAR PARÁMETROS Y TIPOS PARA LA CONSULTA FINAL
        $finalParams = array_merge($baseParams, $filterParams);
        $finalTypes = $baseTypes . $filterTypes;
        
        // La consulta completa (sin LIMIT/OFFSET todavía) que se usará para totales y conteo
        $finalQuery = "SELECT * FROM ({$baseQuery}) as transacciones {$whereClause}";

        // 5. OBTENER TOTALES (INGRESOS Y GASTOS)
        $totalsQuery = "SELECT 
                            SUM(CASE WHEN tipo = 'ingreso' THEN monto ELSE 0 END) as totalIngresos, 
                            SUM(CASE WHEN tipo = 'gasto' THEN monto ELSE 0 END) as totalGastos 
                        FROM ({$finalQuery}) as totals_subquery";
        $stmtTotals = $conn->prepare($totalsQuery);
        if (!empty($finalParams)) {
            $stmtTotals->bind_param($finalTypes, ...$finalParams);
        }
        $stmtTotals->execute();
        $totals = $stmtTotals->get_result()->fetch_assoc();
        $stmtTotals->close();
        
        // 6. CONTAR EL NÚMERO TOTAL DE FILAS PARA LA PAGINACIÓN
        $countQuery = "SELECT COUNT(*) as total FROM ({$finalQuery}) as counted_transacciones";
        $stmtCount = $conn->prepare($countQuery);
        if (!empty($finalParams)) {
            $stmtCount->bind_param($finalTypes, ...$finalParams);
        }
        $stmtCount->execute();
        $totalRows = $stmtCount->get_result()->fetch_assoc()['total'] ?? 0;
        $totalPages = ceil($totalRows / $limit);
        $stmtCount->close();

        // 7. OBTENER LOS DATOS DE LA PÁGINA ACTUAL
        $dataQuery = $finalQuery . " ORDER BY fecha DESC, id DESC LIMIT ? OFFSET ?";
        $dataParams = array_merge($finalParams, [$limit, $offset]);
        $dataTypes = $finalTypes . 'ii';

        $stmtData = $conn->prepare($dataQuery);
        $stmtData->bind_param($dataTypes, ...$dataParams);
        $stmtData->execute();
        $transactions = $stmtData->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmtData->close();

        // 8. ENVIAR LA RESPUESTA JSON COMPLETA
        send_json_response(true, 'Datos cargados', [
            'transactions' => $transactions,
            'pagination' => ['currentPage' => $page, 'totalPages' => $totalPages, 'totalRows' => $totalRows, 'limit' => $limit],
            'totals' => ['ingresos' => $totals['totalIngresos'] ?? 0, 'gastos' => $totals['totalGastos'] ?? 0]
        ]);

    } catch (Exception $e) {
        // Si algo falla, este bloque lo capturará y enviará un error JSON limpio.
        send_json_response(false, 'Error en el servidor: ' . $e->getMessage(), null, 500);
    } 
}
 else {
        send_json_response(false, 'Acción GET no reconocida.', null, 400);
    }

} else {
    send_json_response(false, 'Método no permitido.', null, 405);
}

$conn->close();
?>