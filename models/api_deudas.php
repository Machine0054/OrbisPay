<?php
session_start();
ini_set('display_errors', 1); 
error_reporting(E_ALL);

header('Content-Type: application/json');

if (!isset($_SESSION['id'])) { // Usamos 'ID' en mayúsculas para consistencia con tu tabla T_USUARIOS
    http_response_code(401 );
    echo json_encode(['success' => false, 'message' => 'Usuario no autenticado']);
    exit;
}

// --- CONEXIÓN A LA BASE DE DATOS ---
require_once 'conexion.php'; // ¡Ajusta esta ruta si es necesario!
$conn = conectar();

// --- ENRUTADOR DE ACCIONES (ROUTER) ---
$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? $_POST['action'] ?? $_GET['action'] ?? '';
$usuario_id = $_SESSION['id'];

switch ($action) {
    case 'crear_deuda_prestamo':
        crearDeudaPrestamo($conn, $usuario_id, $input);
        break;

    case 'obtener_deudas_prestamos':
        obtenerDeudasPrestamos($conn, $usuario_id);
        break;
        case 'registrar_abono':
        registrarAbono($conn, $usuario_id, $input);
        break;
    case 'obtener_historial_abonos':
        obtenerHistorialAbonos($conn, $usuario_id, $_GET); // Usamos $_GET porque es una petición de solo lectura
        break;
    default:
        http_response_code(400 );
        echo json_encode(['success' => false, 'message' => 'Acción no válida en api_deudas']);
}

$conn->close();

// =================================================================
// --- DEFINICIÓN DE FUNCIONES ---
// =================================================================

function crearDeudaPrestamo($conn, $usuario_id, $input) {
    try {
        // --- RECOLECCIÓN Y VALIDACIÓN DE DATOS ---
        $tipo = trim($input['tipo'] ?? '');
        $descripcion = trim($input['descripcion'] ?? '');
        $acreedor_deudor = trim($input['acreedor_deudor'] ?? '');
        $monto_inicial = floatval($input['monto_inicial'] ?? 0);
        $fecha_creacion = trim($input['fecha_creacion'] ?? '');

        if (!in_array($tipo, ['Deuda', 'Préstamo'])) {
            throw new Exception('El tipo de registro no es válido.');
        }
        if (empty($descripcion) || strlen($descripcion) < 3) {
            throw new Exception('La descripción debe tener al menos 3 caracteres.');
        }
        if ($monto_inicial <= 0) {
            throw new Exception('El monto inicial debe ser mayor a cero.');
        }
        if (empty($fecha_creacion)) {
            throw new Exception('La fecha de origen es obligatoria.');
        }

        // --- INSERCIÓN EN LA BASE DE DATOS ---
        // El saldo_actual es igual al monto_inicial al crear el registro.
        $sql = "INSERT INTO T_DEUDAS_PRESTAMOS 
                    (id_usuario, tipo, descripcion, acreedor_deudor, monto_inicial, saldo_actual, fecha_creacion) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Error al preparar la consulta: " . $conn->error);
        }
        
        // "isssdds" -> i:id_usuario, s:tipo, s:descripcion, s:acreedor_deudor, d:monto_inicial, d:saldo_actual, s:fecha_creacion
        $stmt->bind_param("isssdds", $usuario_id, $tipo, $descripcion, $acreedor_deudor, $monto_inicial, $monto_inicial, $fecha_creacion);
        
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Registro creado exitosamente.',
                'id' => $conn->insert_id
            ]);
        } else {
            throw new Exception('Error al guardar el registro en la base de datos.');
        }
        $stmt->close();

    } catch (Exception $e) {
        http_response_code(400 );
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

/**
 * Obtiene todas las deudas y préstamos activos de un usuario.
 */
function obtenerDeudasPrestamos($conn, $usuario_id) {
    try {
        // Obtenemos todos los registros con estado 'Activa' para el usuario logueado.
        $sql = "SELECT 
                    id_deuda,
                    tipo,
                    descripcion,
                    acreedor_deudor,
                    monto_inicial,
                    saldo_actual,
                    estado,
                    fecha_creacion,
                    fecha_limite
                FROM T_DEUDAS_PRESTAMOS 
                WHERE id_usuario = ? AND estado = 'Activa'
                ORDER BY fecha_creacion DESC";
        
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Error al preparar la consulta: " . $conn->error);
        }
        
        $stmt->bind_param("i", $usuario_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // Separamos los resultados en dos arrays: deudas y préstamos.
        $deudas = [];
        $prestamos = [];

        foreach ($data as $item) {
            // Convertimos los valores numéricos a float para consistencia en JS.
            $item['monto_inicial'] = floatval($item['monto_inicial']);
            $item['saldo_actual'] = floatval($item['saldo_actual']);

            if ($item['tipo'] === 'Deuda') {
                $deudas[] = $item;
            } else {
                $prestamos[] = $item;
            }
        }

        echo json_encode([
            'success' => true,
            'data' => [
                'deudas' => $deudas,
                'prestamos' => $prestamos
            ]
        ]);

    } catch (Exception $e) {
        http_response_code(500 );
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function registrarAbono($conn, $usuario_id, $input) {
    // Iniciamos una transacción. Esto asegura que todas las consultas se ejecuten con éxito, o ninguna lo hará.
    $conn->begin_transaction();

    try {
        // --- RECOLECCIÓN Y VALIDACIÓN DE DATOS ---
        $id_deuda = intval($input['id_deuda'] ?? 0);
        $monto_abono = floatval($input['monto_abono'] ?? 0);
        $fecha_abono = trim($input['fecha_abono'] ?? '');

        if ($id_deuda <= 0) throw new Exception('ID de la deuda/préstamo no válido.');
        if ($monto_abono <= 0) throw new Exception('El monto del abono debe ser mayor a cero.');
        if (empty($fecha_abono)) throw new Exception('La fecha del abono es obligatoria.');

        // --- PASO 1: VERIFICAR Y OBTENER EL SALDO ACTUAL ---
        // Usamos FOR UPDATE para bloquear la fila y evitar que otro proceso la modifique mientras estamos en la transacción.
        $sql_select = "SELECT saldo_actual FROM T_DEUDAS_PRESTAMOS WHERE id_deuda = ? AND id_usuario = ? FOR UPDATE";
        $stmt_select = $conn->prepare($sql_select);
        $stmt_select->bind_param("ii", $id_deuda, $usuario_id);
        $stmt_select->execute();
        $result = $stmt_select->get_result();
        $deuda = $result->fetch_assoc();
        $stmt_select->close();

        if (!$deuda) {
            throw new Exception('La deuda/préstamo no existe o no pertenece a este usuario.');
        }

        $saldo_anterior = $deuda['saldo_actual'];
        if ($monto_abono > $saldo_anterior) {
            throw new Exception('El monto del abono no puede ser mayor que el saldo pendiente.');
        }

        // --- PASO 2: INSERTAR EL ABONO EN EL HISTORIAL ---
        $sql_insert = "INSERT INTO T_ABONOS_DEUDAS (id_deuda, fecha_abono, monto_abono) VALUES (?, ?, ?)";
        $stmt_insert = $conn->prepare($sql_insert);
        $stmt_insert->bind_param("isd", $id_deuda, $fecha_abono, $monto_abono);
        if (!$stmt_insert->execute()) {
            throw new Exception('Error al registrar el abono.');
        }
        $stmt_insert->close();

        // --- PASO 3: ACTUALIZAR EL SALDO Y EL ESTADO EN LA TABLA PRINCIPAL ---
        $nuevo_saldo = $saldo_anterior - $monto_abono;
        $nuevo_estado = ($nuevo_saldo <= 0) ? 'Pagada' : 'Activa';

        $sql_update = "UPDATE T_DEUDAS_PRESTAMOS SET saldo_actual = ?, estado = ? WHERE id_deuda = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("dsi", $nuevo_saldo, $nuevo_estado, $id_deuda);
        if (!$stmt_update->execute()) {
            throw new Exception('Error al actualizar el saldo.');
        }
        $stmt_update->close();

        // --- FINALIZAR LA TRANSACCIÓN ---
        // Si todo salió bien, confirmamos los cambios en la base de datos.
        $conn->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Abono registrado exitosamente.',
            'nuevo_saldo' => $nuevo_saldo,
            'nuevo_estado' => $nuevo_estado
        ]);

    } catch (Exception $e) {
        // Si algo falló, revertimos todos los cambios hechos durante la transacción.
        $conn->rollback();
        http_response_code(400 );
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function obtenerHistorialAbonos($conn, $usuario_id, $input) {
    try {
        $id_deuda = intval($input['id_deuda'] ?? 0);

        if ($id_deuda <= 0) {
            throw new Exception('ID de la deuda/préstamo no válido.');
        }

        // --- PASO 1: VERIFICAR QUE LA DEUDA PERTENECE AL USUARIO ---
        // Esto es una medida de seguridad crucial.
        $sql_verify = "SELECT descripcion FROM T_DEUDAS_PRESTAMOS WHERE id_deuda = ? AND id_usuario = ?";
        $stmt_verify = $conn->prepare($sql_verify);
        $stmt_verify->bind_param("ii", $id_deuda, $usuario_id);
        $stmt_verify->execute();
        $result_verify = $stmt_verify->get_result();
        $deuda = $result_verify->fetch_assoc();
        $stmt_verify->close();

        if (!$deuda) {
            throw new Exception('No tienes permiso para ver este historial.');
        }
        
        $descripcion_deuda = $deuda['descripcion'];

        // --- PASO 2: OBTENER EL HISTORIAL DE ABONOS ---
        $sql_select = "SELECT id_abono, fecha_abono, monto_abono, descripcion_abono 
                       FROM T_ABONOS_DEUDAS 
                       WHERE id_deuda = ? 
                       ORDER BY fecha_abono DESC";
        
        $stmt_select = $conn->prepare($sql_select);
        $stmt_select->bind_param("i", $id_deuda);
        $stmt_select->execute();
        $result = $stmt_select->get_result();
        $historial = $result->fetch_all(MYSQLI_ASSOC);
        $stmt_select->close();

        // Convertimos los montos a float para consistencia en JS
        foreach ($historial as &$abono) {
            $abono['monto_abono'] = floatval($abono['monto_abono']);
        }

        echo json_encode([
            'success' => true,
            'descripcion_deuda' => $descripcion_deuda, // Enviamos la descripción para el título del modal
            'historial' => $historial
        ]);

    } catch (Exception $e) {
        http_response_code(400 );
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}