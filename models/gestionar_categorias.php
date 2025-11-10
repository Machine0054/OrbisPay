<?php

session_start();
header('Content-Type: application/json');
require_once 'conexion.php';
if (!isset($_SESSION['id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(401 );
    echo json_encode(['success' => false, 'message' => 'Acceso no autorizado.']);
    exit;
}

$conn = conectar();
$usuario_id = $_SESSION['id'];
$action = $_POST['action'] ?? '';

switch ($action) {
    case 'obtener_todas':
        handleObtenerTodas($conn, $usuario_id);
        break;

    case 'preparar_eliminacion':
        handlePrepararEliminacion($conn, $usuario_id);
        break;

    case 'ejecutar_eliminacion':
        handleEjecutarEliminacion($conn, $usuario_id);
        break;
    case 'ejecutar_edicion':
        handleEjecutarEdicion($conn, $usuario_id);
        break;
    case 'ejecutar_eliminacion_simple': // <-- NUEVA ACCIÓN
        handleEliminacionSimple($conn, $usuario_id);
        break;

    default:
        http_response_code(400 );
        echo json_encode(['success' => false, 'message' => 'Acción no válida o no especificada.']);
        break;
}

if ($conn) $conn->close();
function handleObtenerTodas($conn, $usuario_id) {
    // (Aquí va el código de 'obtener_categorias_ingreso.php')
    $sql = "SELECT id, nombre_categoria, icono FROM T_CATEGORIAS_INGRESOS_USUARIO WHERE id_usuario = ? ORDER BY nombre_categoria ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $categorias = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    echo json_encode(['success' => true, 'data' => $categorias]);
    $stmt->close();
}
function handlePrepararEliminacion($conn, $usuario_id) {
    // (Aquí va el código de 'preparar_eliminacion_categoria.php')
    $category_id = intval($_POST['category_id'] ?? 0);
    if ($category_id <= 0) { /* ... manejo de error ... */ exit; }

    $sql_count = "SELECT COUNT(*) as income_count FROM T_INGRESOS WHERE id_categoria_ingreso = ? AND usuario_id = ?";
    $stmt_count = $conn->prepare($sql_count);
    $stmt_count->bind_param("ii", $category_id, $usuario_id);
    $stmt_count->execute();
    $income_count = $stmt_count->get_result()->fetch_assoc()['income_count'];
    $stmt_count->close();

    $reassign_options = [];
    if ($income_count > 0) {
        $sql_options = "SELECT id, nombre_categoria FROM T_CATEGORIAS_INGRESOS_USUARIO WHERE id_usuario = ? AND id != ?";
        $stmt_options = $conn->prepare($sql_options);
        $stmt_options->bind_param("ii", $usuario_id, $category_id);
        $stmt_options->execute();
        $reassign_options = $stmt_options->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt_options->close();
    }

    echo json_encode([
        'success' => true,
        'income_count' => intval($income_count),
        'reassign_options' => $reassign_options
    ]);
}
function handleEjecutarEliminacion($conn, $usuario_id) {
    $category_to_delete = intval($_POST['category_to_delete'] ?? 0);
    $reassign_to_id = intval($_POST['reassign_to'] ?? 0);
    if ($category_to_delete <= 0 || $reassign_to_id <= 0) {
        http_response_code(400 );
        echo json_encode(['success' => false, 'message' => 'IDs de categoría inválidos.']);
        exit;
    }

    $conn->begin_transaction();
    try {
        $sql_update = "UPDATE T_INGRESOS SET id_categoria_ingreso = ? WHERE id_categoria_ingreso = ? AND usuario_id = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("iii", $reassign_to_id, $category_to_delete, $usuario_id);
        $stmt_update->execute();
        $stmt_update->close();
        $sql_delete = "DELETE FROM T_CATEGORIAS_INGRESOS_USUARIO WHERE id = ? AND id_usuario = ?";
        $stmt_delete = $conn->prepare($sql_delete);
        $stmt_delete->bind_param("ii", $category_to_delete, $usuario_id);
        $stmt_delete->execute();
        $stmt_delete->close();
        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Categoría eliminada y reasignada.']);

    } catch (Exception $e) {
        $conn->rollback();
        error_log("Error en la transacción de eliminación: " . $e->getMessage());
        http_response_code(500 );
        echo json_encode(['success' => false, 'message' => 'No se pudo completar la operación. Se han revertido los cambios.']);
    }
}
function handleEjecutarEdicion($conn, $usuario_id) {
    $category_id = intval($_POST['category_id'] ?? 0);
    $new_name = trim($_POST['new_name'] ?? '');

    if ($category_id <= 0 || empty($new_name) || strlen($new_name) < 3) {
        http_response_code(400 );
        echo json_encode(['success' => false, 'message' => 'Datos inválidos. Asegúrate de que el nombre tenga al menos 3 caracteres.']);
        exit;
    }

    try {
        $sql_check = "SELECT id FROM T_CATEGORIAS_INGRESOS_USUARIO WHERE nombre_categoria = ? AND id_usuario = ? AND id != ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("sii", $new_name, $usuario_id, $category_id);
        $stmt_check->execute();
        if ($stmt_check->get_result()->num_rows > 0) {
            http_response_code(409 ); 
            echo json_encode(['success' => false, 'message' => 'Ya tienes otra categoría con ese nombre.']);
            $stmt_check->close();
            return;
        }
        $stmt_check->close();
        $sql_update = "UPDATE T_CATEGORIAS_INGRESOS_USUARIO SET nombre_categoria = ? WHERE id = ? AND id_usuario = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("sii", $new_name, $category_id, $usuario_id);
        
        if ($stmt_update->execute()) {
            echo json_encode(['success' => true, 'message' => 'Categoría actualizada correctamente.']);
        } else {
            throw new Exception("Error al ejecutar la actualización.");
        }
        $stmt_update->close();

    } catch (Exception $e) {
        error_log("Error en handleEjecutarEdicion: " . $e->getMessage());
        http_response_code(500 );
        echo json_encode(['success' => false, 'message' => 'Ocurrió un error en el servidor al actualizar la categoría.']);
    }
}

function handleEliminacionSimple($conn, $usuario_id) {
    $category_id = intval($_POST['category_id'] ?? 0);

    if ($category_id <= 0) {
        http_response_code(400 );
        echo json_encode(['success' => false, 'message' => 'ID de categoría inválido.']);
        exit;
    }
    try {
        $sql = "DELETE FROM T_CATEGORIAS_INGRESOS_USUARIO WHERE id = ? AND id_usuario = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $category_id, $usuario_id);
        
        if ($stmt->execute() && $stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'Categoría eliminada correctamente.']);
        } else {
            throw new Exception("No se pudo eliminar la categoría o no se encontró.");
        }
        $stmt->close();

    } catch (Exception $e) {
        error_log("Error en handleEliminacionSimple: " . $e->getMessage());
        http_response_code(500 );
        echo json_encode(['success' => false, 'message' => 'Ocurrió un error en el servidor.']);
    }
}


?>