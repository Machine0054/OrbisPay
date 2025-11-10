<?php
session_start();
header('Content-Type: application/json');

// --- Seguridad y Conexión ---
if (!isset($_SESSION['id'])) {
    http_response_code(401 );
    echo json_encode(['success' => false, 'message' => 'Usuario no autenticado']);
    exit;
}
require_once 'conexion.php';
$conn = conectar();
$usuario_id = $_SESSION['id'];
$action = $_GET['action'] ?? $_POST['action'] ?? '';

// --- Enrutador de Acciones ---
switch ($action) {
    case 'obtener_notificaciones':
        obtenerNotificaciones($conn, $usuario_id);
        break;
    case 'marcar_leidas':
        marcarLeidas($conn, $usuario_id);
        break;
    default:
        http_response_code(400 );
        echo json_encode(['success' => false, 'message' => 'Acción no válida']);
}
$conn->close();


// --- Funciones ---

function obtenerNotificaciones($conn, $usuario_id) {
    try {
        // Obtenemos las últimas 20 notificaciones para el usuario
        $sql = "SELECT * FROM T_NOTIFICACIONES WHERE id_usuario = ? ORDER BY fecha_creacion DESC LIMIT 20";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $usuario_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $notificaciones = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // Contamos cuántas de ellas no están leídas
        $sql_count = "SELECT COUNT(*) as unread_count FROM T_NOTIFICACIONES WHERE id_usuario = ? AND leida = FALSE";
        $stmt_count = $conn->prepare($sql_count);
        $stmt_count->bind_param("i", $usuario_id);
        $stmt_count->execute();
        $unread_count = $stmt_count->get_result()->fetch_assoc()['unread_count'] ?? 0;
        $stmt_count->close();

        echo json_encode([
            'success' => true,
            'data' => $notificaciones,
            'unread_count' => (int)$unread_count
        ]);

    } catch (Exception $e) {
        http_response_code(500 );
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function marcarLeidas($conn, $usuario_id) {
    try {
        // Marcamos todas las notificaciones del usuario como leídas
        $sql = "UPDATE T_NOTIFICACIONES SET leida = TRUE WHERE id_usuario = ? AND leida = FALSE";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $usuario_id);
        $stmt->execute();
        
        // No necesitamos devolver un error si no se afecta ninguna fila.
        // Simplemente confirmamos que la operación se intentó.
        echo json_encode(['success' => true, 'message' => 'Notificaciones marcadas como leídas.']);
        $stmt->close();

    } catch (Exception $e) {
        http_response_code(500 );
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
?>