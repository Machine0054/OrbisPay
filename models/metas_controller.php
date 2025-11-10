<?php

session_start();
ini_set('display_errors', 0); // No mostrar errores en el output
error_reporting(E_ALL);

set_error_handler(function ($severity, $message, $file, $line) {
    http_response_code(500 ); // Error interno del servidor
    echo json_encode([
        'success' => false,
        'message' => "Error interno del servidor: $message en $file línea $line"
    ]);
    exit;
});

header('Content-Type: application/json');

if (!isset($_SESSION['id'])) {
    http_response_code(401 ); // No autorizado
    echo json_encode([
        'success' => false,
        'message' => 'Usuario no autenticado'
    ]);
    exit;
}

// --- CONEXIÓN A LA BASE DE DATOS ---
require_once 'conexion.php'; // ¡Ajusta esta ruta si es necesario!
$conn = conectar();

// --- ENRUTADOR DE ACCIONES (ROUTER) ---
// Determina la acción a realizar. Primero busca en el cuerpo de la solicitud (para JSON) y luego en POST/GET.
$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? $_POST['action'] ?? $_GET['action'] ?? '';
$usuario_id = $_SESSION['id'];

switch ($action) {
    case 'crear_meta':
        crearMeta($conn, $usuario_id, $input);
        break;
     case 'obtener_metas':
        obtenerMetas($conn, $usuario_id);
        break;
    case 'abonar_meta':
        abonarMeta($conn, $usuario_id, $input);
        break;
    case 'eliminar_meta':
        eliminarMeta($conn, $usuario_id, $input);
        break;
    case 'editar_meta':
        editarMeta($conn, $usuario_id, $input);
        break;
    default:
        http_response_code(400 ); // Solicitud incorrecta
        echo json_encode([
            'success' => false,
            'message' => 'Acción no válida'
        ]);
}

$conn->close();
function crearMeta($conn, $usuario_id, $input) {
    try {
        // --- RECOLECCIÓN Y VALIDACIÓN DE DATOS ---
        $nombre_meta = trim($input['nombre_meta'] ?? '');
        $monto_objetivo = floatval($input['monto_objetivo'] ?? 0);
        $fecha_limite = trim($input['fecha_limite'] ?? '');

        if (empty($nombre_meta) || strlen($nombre_meta) < 3) {
            throw new Exception('El nombre de la meta debe tener al menos 3 caracteres.');
        }
        if ($monto_objetivo <= 0) {
            throw new Exception('El monto objetivo debe ser un número mayor a 0.');
        }
        
        // Validación de fecha opcional
        $fecha_limite_sql = null;
        if (!empty($fecha_limite)) {
            $dateTime = DateTime::createFromFormat('Y-m-d', $fecha_limite);
            if (!$dateTime || $dateTime->format('Y-m-d') !== $fecha_limite) {
                throw new Exception('El formato de la fecha límite es inválido. Use AAAA-MM-DD.');
            }
            $fecha_limite_sql = $fecha_limite;
        }

        // --- INSERCIÓN EN LA BASE DE DATOS ---
        $sql = "INSERT INTO T_METAS (id_usuario, nombre_meta, monto_objetivo, fecha_limite) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Error al preparar la consulta: " . $conn->error);
        }
        
        $stmt->bind_param("isds", $usuario_id, $nombre_meta, $monto_objetivo, $fecha_limite_sql);
        
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => '¡Meta creada con éxito!',
                'id_meta' => $conn->insert_id // Devolvemos el ID de la nueva meta
            ]);
        } else {
            throw new Exception('Error al guardar la meta en la base de datos.');
        }
        $stmt->close();

    } catch (Exception $e) {
        http_response_code(400 ); // Error de cliente (datos inválidos)
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}

function obtenerMetas($conn, $usuario_id) {
    try {
        $sql = "SELECT id_meta, nombre_meta, monto_objetivo, monto_actual, fecha_limite, estado_meta, imagen_url 
                FROM T_METAS 
                WHERE id_usuario = ? 
                ORDER BY fecha_creacion DESC";
        
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Error al preparar la consulta: " . $conn->error);
        }
        
        $stmt->bind_param("i", $usuario_id);
        
        $stmt->execute();
        
        // Obtenemos el resultado de la consulta.
        $result = $stmt->get_result();
        
        // Creamos un array para almacenar todas las metas.
        $metas = [];
        
        // Recorremos cada fila del resultado y la añadimos a nuestro array.
        while ($row = $result->fetch_assoc()) {
            // Convertimos los valores numéricos a tipos correctos (float) para consistencia.
            $row['monto_objetivo'] = (float)$row['monto_objetivo'];
            $row['monto_actual'] = (float)$row['monto_actual'];
            $metas[] = $row;
        }
        
        // --- ENVÍO DE LA RESPUESTA ---
        // Devolvemos una respuesta JSON con éxito y el array de metas.
        echo json_encode([
            'success' => true,
            'data' => $metas
        ]);
        
        $stmt->close();

    } catch (Exception $e) {
        http_response_code(500 ); // Error del servidor
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}

function abonarMeta($conn, $usuario_id, $input) {
    try {
        // --- RECOLECCIÓN Y VALIDACIÓN DE DATOS ---
        $id_meta = intval($input['id_meta'] ?? 0);
        $monto_abono = floatval($input['monto_abono'] ?? 0);

        if ($id_meta <= 0) {
            throw new Exception('ID de la meta no válido.');
        }
        if ($monto_abono <= 0) {
            throw new Exception('El monto a abonar debe ser mayor a 0.');
        }

        // --- ACTUALIZACIÓN EN LA BASE DE DATOS ---
        // Usamos 'monto_actual = monto_actual + ?' para evitar condiciones de carrera.
        // Es la forma más segura de hacer una actualización incremental.
        // También nos aseguramos de que la meta pertenezca al usuario logueado (WHERE id_usuario = ?).
        $sql = "UPDATE T_METAS 
                SET monto_actual = monto_actual + ? 
                WHERE id_meta = ? AND id_usuario = ?";
        
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Error al preparar la consulta: " . $conn->error);
        }
        
        // Vinculamos los parámetros: d = double (monto), i = integer (id_meta), i = integer (id_usuario)
        $stmt->bind_param("dii", $monto_abono, $id_meta, $usuario_id);
        
        if ($stmt->execute()) {
            // Verificamos si alguna fila fue realmente afectada.
            // Si no se afectó ninguna fila, significa que la meta no existía o no pertenecía al usuario.
            if ($stmt->affected_rows > 0) {
                echo json_encode([
                    'success' => true,
                    'message' => '¡Abono realizado con éxito!'
                ]);
            } else {
                throw new Exception('La meta no fue encontrada o no tienes permiso para modificarla.');
            }
        } else {
            throw new Exception('Error al realizar el abono en la base de datos.');
        }
        $stmt->close();

    } catch (Exception $e) {
        http_response_code(400 ); // Error de cliente (datos inválidos o no encontrados)
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}

function eliminarMeta($conn, $usuario_id, $input) {
    try {
        $id_meta = intval($input['id_meta'] ?? 0);

        if ($id_meta <= 0) {
            throw new Exception('ID de la meta no válido.');
        }

        // Preparamos la consulta DELETE.
        // Es crucial incluir 'AND id_usuario = ?' para que un usuario no pueda borrar las metas de otro.
        $sql = "DELETE FROM T_METAS WHERE id_meta = ? AND id_usuario = ?";
        
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Error al preparar la consulta: " . $conn->error);
        }
        
        $stmt->bind_param("ii", $id_meta, $usuario_id);
        
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Meta eliminada con éxito.'
                ]);
            } else {
                throw new Exception('La meta no fue encontrada o no tienes permiso para eliminarla.');
            }
        } else {
            throw new Exception('Error al eliminar la meta de la base de datos.');
        }
        $stmt->close();

    } catch (Exception $e) {
        http_response_code(400 );
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}

function editarMeta($conn, $usuario_id, $input) {
    try {
        // --- RECOLECCIÓN Y VALIDACIÓN DE DATOS ---
        $id_meta = intval($input['id_meta'] ?? 0);
        $nombre_meta = trim($input['nombre_meta'] ?? '');
        $monto_objetivo = floatval($input['monto_objetivo'] ?? 0);
        $fecha_limite = trim($input['fecha_limite'] ?? '');

        if ($id_meta <= 0) {
            throw new Exception('ID de la meta no válido.');
        }
        if (empty($nombre_meta) || strlen($nombre_meta) < 3) {
            throw new Exception('El nombre de la meta debe tener al menos 3 caracteres.');
        }
        if ($monto_objetivo <= 0) {
            throw new Exception('El monto objetivo debe ser un número mayor a 0.');
        }
        
        $fecha_limite_sql = !empty($fecha_limite) ? $fecha_limite : null;

        // --- ACTUALIZACIÓN EN LA BASE DE DATOS ---
        $sql = "UPDATE T_METAS 
                SET nombre_meta = ?, monto_objetivo = ?, fecha_limite = ? 
                WHERE id_meta = ? AND id_usuario = ?";
        
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Error al preparar la consulta: " . $conn->error);
        }
        
        $stmt->bind_param("sdsii", $nombre_meta, $monto_objetivo, $fecha_limite_sql, $id_meta, $usuario_id);
        
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                echo json_encode(['success' => true, 'message' => 'Meta actualizada con éxito.']);
            } else {
                // Si no se afectaron filas, puede ser porque no se cambió ningún dato
                // o porque la meta no pertenece al usuario.
                echo json_encode(['success' => true, 'message' => 'No se realizaron cambios o la meta no fue encontrada.']);
            }
        } else {
            throw new Exception('Error al actualizar la meta.');
        }
        $stmt->close();

    } catch (Exception $e) {
        http_response_code(400 );
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
?>