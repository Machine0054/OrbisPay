<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Incluimos la conexión a la base de datos
require_once 'conexion.php';

echo "=============================================\n";
echo "INICIANDO PROCESO DE AHORROS AUTOMÁTICOS\n";
echo "Fecha y Hora: " . date('Y-m-d H:i:s') . "\n";
echo "=============================================\n\n";

$conn = conectar();
if (!$conn) {
    die("ERROR: No se pudo conectar a la base de datos.\n");
}

try {
    // --- 1. OBTENER TODAS LAS METAS ACTIVAS CON AHORRO AUTOMÁTICO ---
    // Buscamos metas que no estén completadas y tengan el ahorro automático activado.
    $sql_metas = "SELECT * FROM T_METAS 
                  WHERE ahorro_automatico = 1 AND estado_meta != 'completada'";
    
    $result_metas = $conn->query($sql_metas);

    if ($result_metas->num_rows === 0) {
        echo "No hay metas con ahorro automático para procesar. Proceso finalizado.\n";
        $conn->close();
        exit;
    }

    echo "Se encontraron " . $result_metas->num_rows . " metas para revisar.\n\n";

    // --- 2. PROCESAR CADA META INDIVIDUALMENTE ---
    $hoy_dia_semana = date('N'); // 1 (para Lunes) hasta 7 (para Domingo)
    $hoy_dia_mes = date('j');    // 1 hasta 31

    while ($meta = $result_metas->fetch_assoc()) {
        $id_meta = $meta['id_meta'];
        $nombre_meta = $meta['nombre_meta'];
        $monto_cuota = (float)$meta['monto_ahorro_programado'];
        $id_usuario = $meta['id_usuario'];
        
        $debe_procesar_hoy = false;

        // --- 3. VERIFICAR SI HOY ES DÍA DE DESCUENTO PARA ESTA META ---
        switch ($meta['frecuencia_ahorro']) {
            case 'semanal':
                if ($hoy_dia_semana == $meta['dia_semana_ahorro']) {
                    $debe_procesar_hoy = true;
                }
                break;
            case 'quincenal':
                // Si el día es el guardado, o 15 días después.
                if ($hoy_dia_mes == $meta['dia_mes_ahorro'] || $hoy_dia_mes == ($meta['dia_mes_ahorro'] + 15)) {
                    $debe_procesar_hoy = true;
                }
                break;
            case 'mensual':
                if ($hoy_dia_mes == $meta['dia_mes_ahorro']) {
                    $debe_procesar_hoy = true;
                }
                break;
        }

        if ($debe_procesar_hoy) {
            echo "Procesando meta: '$nombre_meta' (ID: $id_meta) para el usuario ID: $id_usuario...\n";

            // --- 4. EJECUTAR LA TRANSACCIÓN (GASTO + ABONO) ---
            // Usamos una transacción para asegurar que ambas operaciones (gasto y abono)
            // se completen con éxito, o ninguna lo haga.
            $conn->begin_transaction();

            try {
                // 4.1. Crear el gasto
                $descripcion_gasto = "Ahorro automático para meta: " . $conn->real_escape_string($nombre_meta);
                $categoria_gasto = "Ahorros"; // ¡LE DAMOS UN VALOR FIJO A LA CATEGORÍA!
                $fecha_gasto = date('Y-m-d');

                // Ahora la consulta INSERT incluye la columna 'categoria'
                $stmt_gasto = $conn->prepare("INSERT INTO T_GASTOS (usuario_id, monto, descripcion, fecha_gasto, categoria) VALUES (?, ?, ?, ?, ?)");

                // Y el bind_param coincide: i (id), d (monto), s (desc), s (fecha), s (cat)
                $stmt_gasto->bind_param("idsss", $id_usuario, $monto_cuota, $descripcion_gasto, $fecha_gasto, $categoria_gasto);

                $stmt_gasto->execute();
                $stmt_gasto->close();

                // 4.2. Abonar a la meta
                $stmt_abono = $conn->prepare("UPDATE T_METAS SET monto_actual = monto_actual + ? WHERE id_meta = ?");
                $stmt_abono->bind_param("di", $monto_cuota, $id_meta);
                $stmt_abono->execute();
                $stmt_abono->close();

                // Si todo fue bien, guardamos los cambios permanentemente
                $conn->commit();
                echo "   -> ¡ÉXITO! Se abonaron $" . number_format($monto_cuota) . " a la meta.\n";

            } catch (Exception $e) {
                // Si algo falló, revertimos todos los cambios de esta transacción
                $conn->rollback();
                echo "   -> ERROR al procesar la meta ID $id_meta: " . $e->getMessage() . "\n";
            }
        } else {
            // echo "Hoy no es día de procesamiento para la meta: '$nombre_meta'.\n";
        }
    }

    echo "\nProceso de ahorros automáticos completado.\n";

} catch (Exception $e) {
    die("ERROR GENERAL: " . $e->getMessage() . "\n");
} finally {
    if ($conn) {
        $conn->close();
    }
}
?>