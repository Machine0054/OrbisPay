<?php
 
require 'config.php';

function conectar() {
  $serverName = SERVER;
  $username = USER;
  $password = PASS;
  $dbName = DB;

  // Crear una nueva conexión MySQLi
  $conn = new mysqli( $serverName, $username, $password, $dbName );

  // Verificar si hay errores de conexión
  if ( $conn->connect_error ) {
    die( "Error de conexión: " . $conn->connect_error );
  }
   mysqli_set_charset($conn, "utf8mb4");
  // Establecer el conjunto de caracteres a UTF-8 (opcional)
  $conn->set_charset( "utf8" );

  return $conn;
}

function EjecutarConsulta( $str ) {
  // Establecer la conexión a la base de datos
  $conn = conectar();

  // Verificar la conexión
  if ( $conn->connect_error ) {
    die( "Error de conexión: " . $conn->connect_error );
  }

  // Ejecutar la consulta SQL
  if ( $conn->query( $str ) === TRUE ) {
    $conn->close();
    return true; // Éxito en la inserción
  } else {
    echo "Error al ejecutar la consulta: " . $conn->error;
    $conn->close();
    return false; // Error en la inserción
  }
}

function GenerarArray($str) {
    try {
        $conn = conectar();

        if ($conn->connect_error) {
            throw new Exception("Error de conexión: " . $conn->connect_error);
        }

        $result = $conn->query($str);

        if (!$result) {
            throw new Exception("Error al ejecutar la consulta: " . $conn->error);
        }

        $matriz = array();

        while ($row = $result->fetch_assoc()) {
            $matriz[] = $row;
        }

        $result->free();
        $conn->close();

        return $matriz;

    } catch (Exception $e) {
        // Propagamos el error hacia arriba
        throw $e;
    }
}

function insertReturnId($table, $fields) {
	try {
			//code...
		$conn = conectar();
    
    if ($conn === false) {
        throw new Exception("Error en la conexión a la base de datos");
    }

    // Elimina las claves 'id', 'op' y 'link'
	if(isset($fields['id'])){
		unset($fields['id']);
	}
	if(isset($fields['op'])){
		unset($fields['op']);
	}
	if(isset($fields['link'])){
		unset($fields['link']);
	}
    //unset($fields['id'], $fields['op'], $fields['link']);

    // Construye la consulta de inserción
    $fieldNames = implode(', ', array_keys($fields));
    $fieldValues = implode(', ', array_fill(0, count($fields), '?'));

    $insertQuery = "INSERT INTO $table ($fieldNames) VALUES ($fieldValues)";
	//echo $insertQuery;
    $stmt = $conn->prepare($insertQuery);

    if ($stmt === false) {
        throw new Exception("Error al preparar la consulta de inserción: " . $conn->error);
    }

    // Une los valores de los campos y define los tipos
    $params = array_map(function($value) {
        return $value ?? null;
    }, array_values($fields));
    
    $types = '';
    foreach ($params as $param) {
        $types .= is_int($param) ? 'i' : (is_double($param) ? 'd' : 's');
    }

    $stmt->bind_param($types, ...$params);

    if (!$stmt->execute()) {
        throw new Exception("La consulta de inserción falló: " . $stmt->error);
    }

    $lastInsertId = $conn->insert_id;
    $stmt->close();
    $conn->close();

    return $lastInsertId;
		} catch (\Throwable $th) {
			//throw $th;
		 print_r($th);
		}
    
}

function update( $conn, $tabla, $datos, $condicion ) {
  try {
    unset( $datos[ 'id' ] ); // Elimina la clave 'id'
    unset( $datos[ 'op' ] ); // Elimina la clave 'op'
    unset( $datos[ 'link' ] ); // Elimina la clave 'link'

     $sql = "UPDATE $tabla SET ";

    $updateColumns = array();
    $updateValues = array();

    foreach ( $datos as $campo => $valor ) {
      $updateColumns[] = "$campo = ?";
      $updateValues[] = $valor; // Agrega directamente el valor al array
    }

    $sql .= implode( ', ', $updateColumns );
   /*if($tabla=='t_terceros_direcciones') {
         $sql .= " WHERE tercero_id = ?";
    }
	  else {
	  $sql .= " WHERE id = ?"; }*/
    $sql .= " WHERE id = ?";
    // Preparar la consulta
    $stmt = $conn->prepare( $sql );

    if ( $stmt === false ) {
      throw new Exception( "Error al preparar la consulta: " . $conn->error );
    }

    // Construir array de tipos para bind_param
    $types = str_repeat( 's', count( $updateValues ) + 1 ); // 's' indica cadena (string)
    $updateValues[] = $condicion; // Añadir la condición al final del array

    // Bind parameters
    $stmt->bind_param( $types, ... $updateValues );

    // Ejecutar la consulta
    if ( $stmt->execute() ) {
      // Cerrar la declaración
      $stmt->close();
      return true; // Actualización exitosa
    } else {
      throw new Exception( "Error al ejecutar la consulta: " . $stmt->error );
    }
  } catch ( Exception $e ) {
    echo "Error: " . $e->getMessage();
    return false; // Devolver false en caso de error
  }
}

function eliminarRegistro($id, $tabla) {
  try {
    // Conectar a la base de datos
    $conn = conectar();

    // Verificar la conexión
    if ($conn === false) {
        throw new Exception("Error en la conexión a la base de datos");
    }

    // Consultar para eliminar el registro
    $deleteQuery = "DELETE FROM $tabla WHERE id = ?";

    // Preparar la consulta
    $stmt = $conn->prepare($deleteQuery);

    if ($stmt === false) {
        throw new Exception("Error al preparar la consulta de eliminación: " . $conn->error);
    }

    // Bind del parámetro (el ID del registro)
    $stmt->bind_param("i", $id); // 'i' para entero

    // Ejecutar la consulta
    if (!$stmt->execute()) {
        throw new Exception("Error al ejecutar la consulta de eliminación: " . $stmt->error);
    }

    // Cerrar la declaración y la conexión
    $stmt->close();
    $conn->close();

    return true; // Eliminación exitosa
  } catch (Exception $e) {
    echo "Error: " . $e->getMessage();
    return false; // Devolver false en caso de error
  }
}


?>