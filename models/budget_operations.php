<?php
session_start();

// Configuración de la base de datos
$host = '5.189.164.100';
$dbname = 'FINANZAS';
$username = 'devuser';
$password = 'Inicio01$%.';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die(json_encode(['success' => false, 'message' => 'Error de conexión: ' . $e->getMessage()]));
}

// Verificar que el usuario esté logueado
if (!isset($_SESSION['id'])) {
    die(json_encode(['success' => false, 'message' => 'Usuario no autenticado']));
}

$user_id = $_SESSION['id'];
$action = $_REQUEST['action'] ?? '';

header('Content-Type: application/json');

switch ($action) {
    case 'add_budget':
        addBudget($pdo, $user_id);
        break;
    case 'get_budgets':
        getBudgetsWithExpenses($pdo, $user_id);
        break;
    case 'get_expenses_by_category':
        getExpensesByCategory($pdo, $user_id);
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Acción no válida']);
}

function addBudget($pdo, $user_id) {
    try {
        // Validar datos de entrada
        $category = trim($_POST['category'] ?? '');
        $amount = floatval($_POST['amount'] ?? 0);
        $period = trim($_POST['period'] ?? '');
        $start_date = trim($_POST['start_date'] ?? '');
        $end_date = trim($_POST['end_date'] ?? '');
        
        // Validaciones
        if (empty($category) || empty($period) || empty($start_date) || empty($end_date)) {
            throw new Exception('Todos los campos son obligatorios');
        }
        
        if ($amount <= 0) {
            throw new Exception('El monto debe ser mayor a 0');
        }
        
        if (strtotime($start_date) >= strtotime($end_date)) {
            throw new Exception('La fecha de fin debe ser posterior a la fecha de inicio');
        }
        
        // Verificar categorías válidas
        $valid_categories = ['alimentacion', 'transporte', 'servicios', 'entretenimiento', 'salud', 'otros'];
        if (!in_array($category, $valid_categories)) {
            throw new Exception('Categoría no válida');
        }
        
        // Verificar períodos válidos
        $valid_periods = ['diario', 'semanal', 'quincenal', 'mensual', 'anual'];
        if (!in_array($period, $valid_periods)) {
            throw new Exception('Período no válido');
        }
        
        // Verificar si ya existe un presupuesto para esta categoría en el mismo período
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM budgets 
            WHERE user_id = ? AND category = ? 
            AND ((start_date <= ? AND end_date >= ?) OR (start_date <= ? AND end_date >= ?))
        ");
        $stmt->execute([$user_id, $category, $start_date, $start_date, $end_date, $end_date]);
        
        if ($stmt->fetchColumn() > 0) {
            throw new Exception('Ya existe un presupuesto para esta categoría en el período seleccionado');
        }
        
        // Insertar el presupuesto
        $stmt = $pdo->prepare("
            INSERT INTO budgets (user_id, category, amount, period, start_date, end_date, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$user_id, $category, $amount, $period, $start_date, $end_date]);
        
        echo json_encode(['success' => true, 'message' => 'Presupuesto agregado exitosamente']);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function getBudgetsWithExpenses($pdo, $user_id) {
    try {
        // Obtener presupuestos
        $stmt = $pdo->prepare("
            SELECT id, category, amount, period, start_date, end_date, created_at
            FROM budgets 
            WHERE user_id = ? 
            ORDER BY created_at DESC
        ");
        $stmt->execute([$user_id]);
        $budgets = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Para cada presupuesto, calcular los gastos correspondientes
        foreach ($budgets as &$budget) {
            $expenses = calculateExpensesForBudget($pdo, $user_id, $budget);
            $budget['gastos'] = $expenses['total'];
            $budget['gastos_detalle'] = $expenses['detalle'];
            $budget['porcentaje_usado'] = $budget['amount'] > 0 ? ($expenses['total'] / $budget['amount']) * 100 : 0;
            $budget['restante'] = $budget['amount'] - $expenses['total'];
            
            // Mantener compatibilidad con frontend (mapear nombres de columnas)
            $budget['categoria'] = $budget['category'];
            $budget['monto'] = $budget['amount'];
            $budget['fecha_inicio'] = $budget['start_date'];
            $budget['fecha_fin'] = $budget['end_date'];
            $budget['fecha_creacion'] = $budget['created_at'];
        }
        
        // Calcular totales generales
        $totals = calculateTotals($budgets);
        
        echo json_encode([
            'success' => true, 
            'budgets' => $budgets,
            'totals' => $totals
        ]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function calculateExpensesForBudget($pdo, $user_id, $budget) {
    try {
        // Calcular gastos para el período del presupuesto
        $stmt = $pdo->prepare("
            SELECT SUM(monto) as total, COUNT(*) as cantidad
            FROM T_GASTOS 
            WHERE usuario_id = ? 
            AND categoria = ? 
            AND fecha_gasto BETWEEN ? AND ?
        ");
        $stmt->execute([
            $user_id, 
            $budget['category'], 
            $budget['start_date'], 
            $budget['end_date']
        ]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $total = floatval($result['total'] ?? 0);
        
        // Obtener detalle de gastos
        $stmt = $pdo->prepare("
            SELECT descripcion, monto, fecha_gasto
            FROM T_GASTOS 
            WHERE usuario_id = ? 
            AND categoria = ? 
            AND fecha_gasto BETWEEN ? AND ?
            ORDER BY fecha_gasto DESC
        ");
        $stmt->execute([
            $user_id, 
            $budget['category'], 
            $budget['start_date'], 
            $budget['end_date']
        ]);
        
        $detalle = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'total' => $total,
            'detalle' => $detalle
        ];
        
    } catch (Exception $e) {
        return [
            'total' => 0,
            'detalle' => []
        ];
    }
}

function getExpensesByCategory($pdo, $user_id) {
    try {
        $start_date = $_GET['start_date'] ?? date('Y-m-01'); // Primer día del mes actual
        $end_date = $_GET['end_date'] ?? date('Y-m-t'); // Último día del mes actual
        
        // Obtener gastos agrupados por categoría
        $stmt = $pdo->prepare("
            SELECT 
                categoria,
                SUM(monto) as total_gastado,
                COUNT(*) as cantidad_gastos,
                AVG(monto) as promedio_gasto
            FROM T_GASTOS 
            WHERE usuario_id = ? 
            AND fecha_gasto BETWEEN ? AND ?
            GROUP BY categoria
            ORDER BY total_gastado DESC
        ");
        $stmt->execute([$user_id, $start_date, $end_date]);
        $expenses = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Obtener total general
        $stmt = $pdo->prepare("
            SELECT SUM(monto) as total_general
            FROM T_GASTOS 
            WHERE usuario_id = ? 
            AND fecha_gasto BETWEEN ? AND ?
        ");
        $stmt->execute([$user_id, $start_date, $end_date]);
        $total_general = floatval($stmt->fetchColumn());
        
        echo json_encode([
            'success' => true, 
            'expenses' => $expenses,
            'total_general' => $total_general,
            'period' => [
                'start_date' => $start_date,
                'end_date' => $end_date
            ]
        ]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function calculateTotals($budgets) {
    $total_presupuesto = 0;
    $total_gastado = 0;
    
    foreach ($budgets as $budget) {
        $total_presupuesto += floatval($budget['amount']); // Usar 'amount' en lugar de 'monto'
        $total_gastado += floatval($budget['gastos']);
    }
    
    $total_restante = $total_presupuesto - $total_gastado;
    $porcentaje_usado = $total_presupuesto > 0 ? ($total_gastado / $total_presupuesto) * 100 : 0;
    
    return [
        'total_presupuesto' => $total_presupuesto,
        'total_gastado' => $total_gastado,
        'total_restante' => $total_restante,
        'porcentaje_usado' => $porcentaje_usado
    ];
}

// Función auxiliar para validar fechas
function validateDate($date, $format = 'Y-m-d') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

// Función auxiliar para formatear moneda
function formatCurrency($amount) {
    return number_format($amount, 0, ',', '.');
}
?>