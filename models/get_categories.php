<?php
session_start();
header('Content-Type: application/json');
require_once 'conexion.php';

if (!isset($_SESSION['usuario'])) {
    echo json_encode(['success' => false, 'message' => 'No autenticado']);
    exit;
}

$usuario_id = $_SESSION['id'];
$tipo = $_POST['type'] ?? 'ingreso'; // Por defecto, ingresos

$conn = conectar();
$sql = "SELECT nombre, icono FROM T_CATEGORIAS_2 WHERE usuario_id = ? AND tipo = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $usuario_id, $tipo);
$stmt->execute();
$result = $stmt->get_result();

$categorias = [];
while ($row = $result->fetch_assoc()) {
    $categorias[] = $row;
}

echo json_encode(['success' => true, 'data' => $categorias]);

$stmt->close();
$conn->close();
?>