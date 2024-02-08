<?php
include_once '../conexion/db_connection.php';
function ver_calendario() {
    global $conn;  // Asegúrate de que tu conexión se llama $conn

    $query = "
        SELECT 
            o.fecha_entrega,
            COUNT(DISTINCT p.id_cliente) AS numero_clientes,
            SUM(p.tiempo_estimado) AS tiempo_estimado_total
        FROM 
            ordenes o
        JOIN 
            prendas p ON o.id = p.id_orden
        GROUP BY 
            o.fecha_entrega
        ORDER BY 
            o.fecha_entrega
    ";

    $result = $conn->query($query);

    // Verificar si la consulta devuelve resultados
    if ($result->num_rows > 0) {
        $data = [];
        while($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        return $data;
    } else {
        return false;  // O podrías devolver un array vacío dependiendo de lo que necesites
    }
}
function ver_dia($fecha) {
    global $conn;  // Asegúrate de que tu conexión se llama $conn

    $query = "
        SELECT 
            o.id AS id_orden,
            c.nombre AS nombre_cliente,
            o.total_prendas AS total_prendas_por_orden
        FROM 
            ordenes o
        LEFT JOIN 
            prendas p ON p.id_orden = o.id
        LEFT JOIN 
            clientes c ON c.id = p.id_cliente 
        WHERE 
            o.fecha_entrega = ?
        GROUP BY 
            o.id, c.nombre, o.total_prendas;
    ";

    // Preparar la consulta y vincular el parámetro
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $fecha); // "s" significa que es una cadena (string)

    // Ejecutar la consulta
    $stmt->execute();

    // Obtener los resultados
    $result = $stmt->get_result();

    // Verificar si la consulta devuelve resultados
    if ($result->num_rows > 0) {
        $data = [];
        while($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        return $data;
    } else {
        return false;  // O podrías devolver un array vacío dependiendo de lo que necesites
    }

    // Cerrar el statement y la conexión
    $stmt->close();
}

function actualizar_prenda($prenda_id, $estado) {
    global $conn;  // Asegúrate de que tu conexión se llama $conn

    // Query de actualización
    $query = "
        UPDATE prendas 
        SET estado = ?
        WHERE id = ?
    ";

    // Preparar la consulta y vincular los parámetros
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $estado, $prenda_id);  // Dos "i" porque ambos son enteros

    // Ejecutar la consulta
    $result = $stmt->execute();

    // Cerrar el statement y verificar si la actualización tuvo éxito
    $stmt->close();

    // $result será TRUE si la consulta se ejecutó correctamente, y FALSE si hubo un error
    return $result;
}

function editar_prenda($prenda_id, $nombre_prenda, $prendas_numero, $descripcion_arreglo, $valor, $asignado, $estado) {
    global $conn;  // Asegúrate de que tu conexión se llama $conn

    $query = "
    UPDATE prendas
    SET nombre_ropa = ?, prendas_numero = ?, descripcion_arreglo = ?, valor = ?, id_asignacion = ?, estado = ?
    WHERE id = ?
    ";

    // Preparar la consulta y vincular los parámetros
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sisisii", $nombre_prenda, $prendas_numero, $descripcion_arreglo, $valor, $asignado, $estado, $prenda_id);

    // Ejecutar la consulta
    if ($stmt->execute()) {
        $stmt->close();
        return true;  // Devuelve true si la actualización fue exitosa
    } else {
        $stmt->close();
        return false;  // Devuelve false si hay un error
    }
}
function verificar_estado_entrega($id_orden) {
    global $conn;  // Asegúrate de que tu conexión se llama $conn

    // Consulta para verificar si hay al menos una prenda con estado 5 en la orden dada
    $query = "
    SELECT COUNT(*)
    FROM prendas
    WHERE id_orden = ? AND estado = 5
    ";

    // Preparar la consulta y vincular los parámetros
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id_orden);

    // Ejecutar la consulta
    if ($stmt->execute()) {
        // Vincular el resultado a una variable
        $stmt->bind_result($count);
        $stmt->fetch();

        // Cerrar el statement
        $stmt->close();

        // Verificar si hay al menos una prenda arreglada
        return $count > 0;
    } else {
        // Cerrar el statement en caso de error
        $stmt->close();
        return false;
    }
}


function editar_estado_prenda($prenda_id, $estado) {
    global $conn; // Asegúrate de que tu conexión se llama $conn

    $query = "
        UPDATE prendas
        SET estado = ?
        WHERE id = ?
    ";

    // Preparar la consulta y vincular los parámetros
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $estado, $prenda_id);

    // Ejecutar la consulta
    if ($stmt->execute()) {
        $stmt->close();
        return true;  // Devuelve true si la actualización fue exitosa
    } else {
        $stmt->close();
        return false;  // Devuelve false si hay un error
    }
}
function registrarEntrega($id_orden, $nombre_usuario, $forma_pago) {
    global $conn;

    // Buscar el id_usuario basado en el nombre_usuario
    $queryUsuario = "SELECT id FROM usuarios WHERE login = ?";
    $stmtUsuario = $conn->prepare($queryUsuario);
    if (!$stmtUsuario) {
        echo "Error preparando la consulta: " . $conn->error;
        return false;
    }
    $stmtUsuario->bind_param("s", $nombre_usuario);
    if (!$stmtUsuario->execute()) {
        echo "Error ejecutando la consulta: " . $stmtUsuario->error;
        $stmtUsuario->close();
        return false;
    }
    $resultadoUsuario = $stmtUsuario->get_result();
    if ($resultadoUsuario->num_rows == 0) {
        echo "Usuario no encontrado.";
        $stmtUsuario->close();
        return false; // No se encontró el usuario
    }
    $filaUsuario = $resultadoUsuario->fetch_assoc();
    $id_usuario = $filaUsuario['id'];
    $stmtUsuario->close();

    // Primero, actualizamos el estado de la orden a entregado (estado 6)
    $queryOrdenes = "
        UPDATE ordenes
        SET estado = 6, forma_pago = ?
        WHERE id = ?
    ";
    
    $stmtOrdenes = $conn->prepare($queryOrdenes);
    if (!$stmtOrdenes) {
        echo "Error preparando la consulta: " . $conn->error;
        return false;
    }
    $stmtOrdenes->bind_param("si", $forma_pago, $id_orden);
    if (!$stmtOrdenes->execute()) {
        echo "Error ejecutando la consulta: " . $stmtOrdenes->error;
        $stmtOrdenes->close();
        return false; // Devuelve false si hay un error actualizando la orden
    }
    $stmtOrdenes->close();
    
    // Ahora, registramos la entrega en la nueva tabla
    $queryEntregas = "
        INSERT INTO entregas (orden_id, usuario_id, fecha, hora)
        VALUES (?, ?, CURDATE(), CURTIME())
    ";

    $stmtEntregas = $conn->prepare($queryEntregas);
    if (!$stmtEntregas) {
        echo "Error preparando la consulta: " . $conn->error;
        return false;
    }
    $stmtEntregas->bind_param("ii", $id_orden, $id_usuario);
    if (!$stmtEntregas->execute()) {
        echo "Error ejecutando la consulta: " . $stmtEntregas->error;
        $stmtEntregas->close();
        return false; // Devuelve false si hay un error en el registro de la entrega
    }
    $stmtEntregas->close();
    return true; // Devuelve true si todo salió bien
}




?>