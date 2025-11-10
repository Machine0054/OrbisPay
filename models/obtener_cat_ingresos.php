<?php
session_start( );
header('Content-Type: application/json');
require_once 'conexion.php';

if (!isset($_SESSION['id'])) {
    http_response_code(401 );
    echo json_encode([]); // Devuelve un array vacío si no hay sesión
    exit;
}

$conn = conectar();
$usuario_id = $_SESSION['id'];

// Solo traemos las categorías de tipo 'ingreso'
$stmt = $conn->prepare("SELECT nombre_categoria FROM T_CATEGORIAS_INGRESOS_USUARIO WHERE id_usuario = ? ORDER BY nombre_categoria ASC");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();

$categorias = [];
while ($row = $result->fetch_assoc()) {
    // Tagify espera un array de strings o de objetos. Un array de strings es más simple.
    $categorias[] = $row['nombre_categoria'];
}

echo json_encode($categorias);

$stmt->close();
$conn->close();
?>