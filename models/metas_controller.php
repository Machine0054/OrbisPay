<?php

session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
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
require_once 'conexion.php'; 
$conn = conectar();

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? $_POST['action'] ?? $_GET['action'] ?? '';
$usuario_id = $_SESSION['id'];
$nombre_usuario = $_SESSION['nombre'];

switch ($action) {
    case 'crear_meta':
        crearMeta($conn, $usuario_id, $input);
        break;
     case 'obtener_metas':
        obtenerMetas($conn, $usuario_id);
        break;
    case 'abonar_meta':
        abonarMeta($conn, $usuario_id, $input, $nombre_usuario);
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
    $conn->begin_transaction();

    try {
        $nombre_meta = trim($input['nombre_meta'] ?? '');
        $monto_objetivo = floatval($input['monto_objetivo'] ?? 0);
        $fecha_limite = trim($input['fecha_limite'] ?? '');

        if (empty($nombre_meta) || $monto_objetivo <= 0 || empty($fecha_limite)) {
            throw new Exception('Nombre, Monto Objetivo y Fecha Límite son obligatorios.');
        }
        
        $ahorro_automatico = filter_var($input['ahorro_automatico'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $frecuencia_ahorro = null;
        $monto_ahorro_programado = null;
        $dia_semana_ahorro = null;
        $dia_mes_ahorro = null;
        $dia_mes_ahorro_2 = null;

         if ($ahorro_automatico) {
            $frecuencia_ahorro = $input['frecuencia_ahorro'] ?? '';
            $monto_ahorro_programado = floatval($input['monto_ahorro_programado'] ?? 0);

            if (!in_array($frecuencia_ahorro, ['diario', 'semanal', 'quincenal', 'mensual']) || $monto_ahorro_programado <= 0) {
                throw new Exception('Para el ahorro automático, la frecuencia y el monto por cuota son obligatorios.');
            }

            if ($frecuencia_ahorro === 'semanal') {
                $dia_semana_ahorro = intval($input['dia_seleccionado'] ?? 0);
                if ($dia_semana_ahorro < 1 || $dia_semana_ahorro > 7) throw new Exception('Día de la semana no válido.');
            
            } elseif ($frecuencia_ahorro === 'quincenal') {
    
                $dias_quincena = explode(',', $input['dia_mes_ahorro'] ?? '');
                
                if (count($dias_quincena) !== 2) {
                    throw new Exception('Error de datos quincenales. Se esperaban dos días (ej: "3,18") pero se recibió: ' . ($input['dia_mes_ahorro'] ?? 'nada'));
                }
                
                $dia_mes_ahorro = intval(trim($dias_quincena[0]));
                $dia_mes_ahorro_2 = intval(trim($dias_quincena[1]));

                if ($dia_mes_ahorro < 1 || $dia_mes_ahorro > 31 || $dia_mes_ahorro_2 < 1 || $dia_mes_ahorro_2 > 31) {
                    throw new Exception('Los días seleccionados para la quincena no son válidos.');
                }
            } elseif ($frecuencia_ahorro === 'mensual') {
                            $dia_mes_ahorro = intval($input['dia_seleccionado'] ?? 0);
                            if ($dia_mes_ahorro < 1 || $dia_mes_ahorro > 28) throw new Exception('El día del mes debe estar entre 1 y 28.');
                        }
                    
                    }

        $sql = "INSERT INTO T_METAS (id_usuario, nombre_meta, monto_objetivo, fecha_limite, ahorro_automatico, frecuencia_ahorro, monto_ahorro_programado, dia_semana_ahorro, dia_mes_ahorro, dia_mes_ahorro_2) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isdsisdiii", $usuario_id, $nombre_meta, $monto_objetivo, $fecha_limite, $ahorro_automatico, $frecuencia_ahorro, $monto_ahorro_programado, $dia_semana_ahorro, $dia_mes_ahorro, $dia_mes_ahorro_2);
        
        if (!$stmt->execute()) throw new Exception('Error al guardar la meta en la base de datos.');
        
        $id_nueva_meta = $conn->insert_id;
        $stmt->close();
        $primer_descuento_realizado = false;
        $meta_completada_al_crear = false;

        if ($ahorro_automatico) {
            $hoy_dia_semana = date('N');
            $hoy_dia_mes = date('j');
            $debe_procesar_hoy = false;

            switch ($frecuencia_ahorro) {
                case 'diario': $debe_procesar_hoy = true; break;
                case 'semanal': if ($hoy_dia_semana == $dia_semana_ahorro) $debe_procesar_hoy = true; break;
                case 'quincenal': if ($hoy_dia_mes == $dia_mes_ahorro) $debe_procesar_hoy = true; break;
                case 'mensual': if ($hoy_dia_mes == $dia_mes_ahorro) $debe_procesar_hoy = true; break;
            }

            if ($debe_procesar_hoy) {
                // 1. Verificar saldo disponible
                $stmt_ingresos = $conn->prepare("SELECT SUM(monto) as total FROM T_INGRESOS WHERE usuario_id = ?");
                $stmt_ingresos->bind_param('i', $usuario_id);
                $stmt_ingresos->execute();
                $total_ingresos = $stmt_ingresos->get_result()->fetch_assoc()['total'] ?? 0;
                $stmt_ingresos->close();

                $stmt_gastos = $conn->prepare("SELECT SUM(monto) as total FROM T_GASTOS WHERE usuario_id = ?");
                $stmt_gastos->bind_param('i', $usuario_id);
                $stmt_gastos->execute();
                $total_gastos = $stmt_gastos->get_result()->fetch_assoc()['total'] ?? 0;
                $stmt_gastos->close();

                $saldo_disponible = $total_ingresos - $total_gastos;

                if ($saldo_disponible >= $monto_ahorro_programado) {
                    // 2. Realizar el primer abono
                    $descripcion_gasto = "Primer ahorro para meta: " . $conn->real_escape_string($nombre_meta);
                    $stmt_gasto = $conn->prepare("INSERT INTO T_GASTOS (usuario_id, monto, descripcion, fecha_gasto, categoria) VALUES (?, ?, ?, CURDATE(), 'Ahorros')");
                    $stmt_gasto->bind_param("ids", $usuario_id, $monto_ahorro_programado, $descripcion_gasto);
                    $stmt_gasto->execute();
                    $stmt_gasto->close();

                    $stmt_abono = $conn->prepare("UPDATE T_METAS SET monto_actual = monto_actual + ? WHERE id_meta = ?");
                    $stmt_abono->bind_param("di", $monto_ahorro_programado, $id_nueva_meta);
                    $stmt_abono->execute();
                    $stmt_abono->close();
                    
                    $primer_descuento_realizado = true;
                    if ($monto_ahorro_programado >= $monto_objetivo) {
                        $meta_completada_al_crear = true;
                        $sql_complete = "UPDATE T_METAS SET estado_meta = 'completada', ahorro_automatico = 0, fecha_completada = CURDATE() WHERE id_meta = ?";
                        $stmt_complete = $conn->prepare($sql_complete);
                        $stmt_complete->bind_param("i", $id_nueva_meta);
                        $stmt_complete->execute();
                        $stmt_complete->close();
                    }
                }
            }
        }
        
        $conn->commit();
        if ($meta_completada_al_crear) {
            echo json_encode([
                'success' => true,
                'message' => "¡Increíble! Has creado y completado tu meta '" . $nombre_meta . "' en un solo paso.",
                'meta_completada' => true, // Le decimos al frontend que active la celebración
                'nombre_usuario' => $_SESSION['usuario']
            ]);
        } else {
            $mensaje_final = '¡Meta creada con éxito!';
            if ($primer_descuento_realizado) {
                $mensaje_final .= ' ¡Hemos realizado tu primer ahorro de $' . number_format($monto_ahorro_programado) . '!';
            }
            echo json_encode([
                'success' => true,
                'message' => $mensaje_final,
                'meta_completada' => false // No se completó, es un abono normal
            ]);
        }

    } catch (Exception $e) {
        $conn->rollback();
        http_response_code(400 );
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function obtenerMetas($conn, $usuario_id) {
    try {
        $sql = "SELECT id_meta, nombre_meta, monto_objetivo, monto_actual, fecha_limite, estado_meta, imagen_url, fecha_completada 
                FROM T_METAS 
                WHERE id_usuario = ? 
                ORDER BY estado_meta ASC, fecha_creacion DESC"; // Ordenamos para que las completadas salgan al final
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $usuario_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $metas = [];
        
        while ($row = $result->fetch_assoc()) {
            $row['monto_objetivo'] = (float)$row['monto_objetivo'];
            $row['monto_actual'] = (float)$row['monto_actual'];
            $metas[] = $row;
        }
        
        echo json_encode(['success' => true, 'data' => $metas]);
        $stmt->close();

    } catch (Exception $e) {
        http_response_code(500 );
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function abonarMeta($conn, $usuario_id, $input, $nombre_usuario) {
    $conn->begin_transaction();

    try {
        $id_meta = intval($input['id_meta'] ?? 0);
        $monto_abono_usuario = floatval($input['monto_abono'] ?? 0);

        if ($id_meta <= 0 || $monto_abono_usuario <= 0) {
            throw new Exception('El monto a abonar debe ser mayor que cero.');
        }

        $stmt_meta = $conn->prepare("SELECT nombre_meta, monto_objetivo, monto_actual, estado_meta FROM T_METAS WHERE id_meta = ? AND id_usuario = ? FOR UPDATE");
        $stmt_meta->bind_param("ii", $id_meta, $usuario_id);
        $stmt_meta->execute();
        $meta = $stmt_meta->get_result()->fetch_assoc();
        $stmt_meta->close();

        if (!$meta) {
            throw new Exception('La meta no existe o no te pertenece.');
        }
        if ($meta['estado_meta'] === 'completada') {
            throw new Exception('Esta meta ya ha sido completada. No se pueden realizar más abonos.');
        }

        $monto_objetivo = floatval($meta['monto_objetivo']);
        $monto_actual = floatval($meta['monto_actual']);
        $monto_necesario = $monto_objetivo - $monto_actual;

        $monto_abono_final = min($monto_abono_usuario, $monto_necesario);

        if ($monto_abono_final <= 0) {
            throw new Exception('La meta ya está completa. No se necesita ningún abono.');
        }

        $sql_update = "UPDATE T_METAS SET monto_actual = monto_actual + ? WHERE id_meta = ? AND id_usuario = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("dii", $monto_abono_final, $id_meta, $usuario_id);

        if (!$stmt_update->execute()) {
            throw new Exception('Error al actualizar el monto de la meta.');
        }
        $stmt_update->close();

        $nuevo_monto_actual = $monto_actual + $monto_abono_final;

        $response = [
            'success' => true,
            'message' => '¡Abono de $' . number_format($monto_abono_final) . ' realizado con éxito!',
            'meta_completada' => false,
            'nombre_usuario' => $nombre_usuario
        ];

        if ($nuevo_monto_actual >= $monto_objetivo) {
            $fecha_hoy = date('Y-m-d');
            $sql_complete = "UPDATE T_METAS SET estado_meta = 'completada', ahorro_automatico = 0, fecha_completada = ? WHERE id_meta = ?";
            $stmt_complete = $conn->prepare($sql_complete);
            $stmt_complete->bind_param("si", $fecha_hoy, $id_meta);
            $stmt_complete->execute();
            $stmt_complete->close();

            $response['message'] = "¡Felicitaciones! Con este abono de $" . number_format($monto_abono_final) . " has completado tu meta '" . htmlspecialchars($meta['nombre_meta']) . "'.";
            $response['meta_completada'] = true;
        }

        $conn->commit();
        echo json_encode($response);

    } catch (Exception $e) {
        $conn->rollback();
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
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