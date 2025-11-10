<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
header('Content-Type: application/json');


// --- 2. VALIDACIÓN DE SEGURIDAD Y CONEXIÓN A BD ---

// Verificar si el usuario está autenticado
if (!isset($_SESSION['id'])) {
    http_response_code(401 ); // No autorizado
    echo json_encode(['success' => false, 'message' => 'Acceso no autorizado. Por favor, inicia sesión.']);
    exit;
}

// Incluir la conexión a la base de datos (ajusta la ruta si es necesario)
// __DIR__ hace la ruta más segura y confiable.
require_once 'conexion.php';
$conn = conectar();

// Verificar si la conexión fue exitosa
if (!$conn) {
    http_response_code(500 ); // Error interno del servidor
    echo json_encode(['success' => false, 'message' => 'Error fatal: No se pudo conectar a la base de datos.']);
    exit;
}

$usuario_id = $_SESSION['id'];


// --- 3. ENRUTADOR DE ACCIONES (ROUTER) ---

// Determinar la acción a realizar (compatible con GET, POST JSON y POST formulario)
$action = '';
if (isset($_GET['action'])) {
    $action = $_GET['action'];
} else {
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? '';
}


switch ($action) {
    case 'obtener_todas':
        obtenerTodasLasCategorias($conn, $usuario_id);
        break;

    case 'crear_categoria_usuario':
        crearCategoriaUsuario($conn, $usuario_id, $input);
        break;

    default:
        http_response_code(400 ); // Solicitud incorrecta
        echo json_encode(['success' => false, 'message' => 'Acción no reconocida en el controlador de categorías.']);
        break;
}

$conn->close();
exit; // Terminar el script de forma limpia.


// ==================================================
// --- 4. DEFINICIÓN DE FUNCIONES ---
// ==================================================

/**
 * Obtiene las categorías globales y las personalizadas del usuario y las devuelve como una sola lista.
 */
function obtenerTodasLasCategorias($conn, $usuario_id) {
    try {
        // a) Obtener categorías globales/predeterminadas desde T_CATEGORIAS
        // Asumimos que la tabla T_CATEGORIAS tiene columnas 'id', 'nombre' y 'icono'
        $sql_global = "SELECT id, nombre, icono_svg FROM T_CATEGORIAS ORDER BY nombre ASC";
        $result_global = $conn->query($sql_global);
        $categorias_globales = $result_global->fetch_all(MYSQLI_ASSOC);

        // b) Obtener categorías personalizadas del usuario desde T_CATEGORIAS_USUARIO
        // Asumimos que la tabla T_CATEGORIAS_USUARIO tiene 'id', 'nombre_categoria' y 'icono'
        $sql_usuario = "SELECT id, nombre_categoria AS nombre, icono FROM T_CATEGORIAS_USUARIO WHERE id_usuario = ? ORDER BY nombre_categoria ASC";
        $stmt_usuario = $conn->prepare($sql_usuario);   
        $stmt_usuario->bind_param("i", $usuario_id);
        $stmt_usuario->execute();
        $result_usuario = $stmt_usuario->get_result();
        $categorias_usuario = $result_usuario->fetch_all(MYSQLI_ASSOC);
        $stmt_usuario->close();

        // c) Unir los dos arrays en una sola lista
        $todas_las_categorias = array_merge($categorias_globales, $categorias_usuario);

        // d) Enviar la respuesta JSON
        echo json_encode(['success' => true, 'data' => $todas_las_categorias]);

    } catch (Exception $e) {
        http_response_code(500 );
        echo json_encode(['success' => false, 'message' => 'Error del servidor al obtener categorías: ' . $e->getMessage()]);
    }
}


/**
 * Crea una nueva categoría personalizada para el usuario en la tabla T_CATEGORIAS_USUARIO.
 */
// En categorias_controller.php

function crearCategoriaUsuario($conn, $usuario_id, $input) {
    try {
        // 1. Validar que $input no sea nulo (si el JSON está mal formado)
        if ($input === null) {
            throw new Exception('Error al decodificar los datos de entrada (JSON inválido).');
        }

        // 2. Recolectar y limpiar los datos
        $nombre_categoria = trim($input['nombre_categoria'] ?? '');
        $icono = trim($input['icono'] ?? '');

        // 3. Validaciones robustas
        if (empty($nombre_categoria)) {
            throw new Exception('El nombre de la categoría no puede estar vacío.');
        }
        if (empty($icono)) {
            throw new Exception('Por favor, selecciona un ícono.');
        }
        if (strlen($nombre_categoria) > 50) {
            throw new Exception('El nombre es demasiado largo (máx. 50 caracteres).');
        }

        // 4. Preparar la consulta SQL
        $sql_insert = "INSERT INTO T_CATEGORIAS_USUARIO (id_usuario, nombre_categoria, icono) VALUES (?, ?, ?)";
        $stmt_insert = $conn->prepare($sql_insert);

        // ¡Punto de fallo común! Si la preparación falla, $stmt_insert será `false`.
        if ($stmt_insert === false) {
            // Lanza un error más específico
            throw new Exception('Error al preparar la consulta SQL: ' . $conn->error);
        }

        // 5. Vincular parámetros
        // El tipo es "iss" (integer, string, string)
        $stmt_insert->bind_param("iss", $usuario_id, $nombre_categoria, $icono);

        // 6. Ejecutar y verificar
        if ($stmt_insert->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Categoría creada con éxito.',
                'new_id' => $conn->insert_id
            ]);
        } else {
            // Si la ejecución falla
            throw new Exception('No se pudo guardar la categoría en la base de datos: ' . $stmt_insert->error);
        }
        $stmt_insert->close();

    } catch (Exception $e) {
        // Captura cualquier excepción y la devuelve como un JSON de error
        http_response_code(400 ); // Error de cliente (datos inválidos) o 500 si es del servidor
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}


?>