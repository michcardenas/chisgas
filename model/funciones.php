<?php

include_once '../../conexion/db_connection.php';
function obtener_ordenes_del_dia($fecha_entrega) {
    global $conn;  // Asegúrate de que tu conexión se llama $conn

    $query = "
        SELECT
            o.estado AS estado_orden,
            o.id AS id_orden,
            c.nombre AS nombre_cliente,
            o.total_prendas AS total_prendas_por_orden,
            (
                CASE
                    WHEN SUM(CASE WHEN p.estado = 4 THEN 1 ELSE 0 END) > 0 THEN 'En Proceso'
                    WHEN SUM(CASE WHEN p.estado = 5 THEN 1 ELSE 0 END) = COUNT(p.id) THEN 'Arreglado'
                    WHEN SUM(CASE WHEN p.estado = 6 THEN 1 ELSE 0 END) = COUNT(p.id) THEN 'Entregadas'
                    ELSE 'Ingresada'
                END
            ) AS estado_general
        FROM
            ordenes o
        LEFT JOIN
            prendas p ON o.id = p.id_orden
        LEFT JOIN
            clientes c ON c.id = p.id_cliente
        WHERE
            o.fecha_entrega = ?
        GROUP BY
            o.id, c.nombre, o.total_prendas, o.estado;
    ";

    // Preparar la consulta y vincular el parámetro
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $fecha_entrega); // "s" significa que es una cadena (string)

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
function prendas_por_orden_con_cliente($id_orden) {
    global $conn;  // Asegúrate de que tu conexión se llama $conn

    $query = "
                SELECT 
                p.nombre_ropa,
                p.tiempo_estimado,
                p.estado,
                p.id,
                c.nombre AS nombre_cliente,
                c.telefono AS telefono_cliente,
                u.login
            FROM 
                prendas p
            LEFT JOIN 
                clientes c ON c.id = p.id_cliente
            LEFT JOIN 
                usuarios u ON  p.id_asignacion = u.id
            WHERE 
                p.id_orden = ?
    ";

    // Preparar la consulta y vincular el parámetro
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id_orden); // "i" significa que es un entero (integer)

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
function prendas_por_entregar($id_orden) {
    global $conn;  // Asegúrate de que tu conexión se llama $conn

    $query = "
        SELECT 
            p.nombre_ropa,
            p.tiempo_estimado,
            p.estado,
            p.id,
            p.prendas_numero,
            c.nombre AS nombre_cliente,
            c.telefono AS telefono_cliente,
            u.login,
            p.valor,
            o.valor_total,
            o.abono,
            o.total_prendas
        FROM 
            prendas p
        LEFT JOIN 
            clientes c ON c.id = p.id_cliente
        LEFT JOIN 
            usuarios u ON p.id_asignacion = u.id
        LEFT JOIN 
            ordenes o ON o.id = p.id_orden
        LEFT JOIN 
            entregas_parciales ep ON ep.id_prenda = p.id AND ep.id_orden = o.id
        WHERE 
            p.id_orden = ?
            AND p.estado = 5
        GROUP BY 
            p.id
    ";

    // Preparar la consulta y vincular el parámetro
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id_orden); // "i" significa que es un entero (integer)

    // Ejecutar la consulta
    $stmt->execute();

    // Obtener los resultados
    $result = $stmt->get_result();

    // Verificar si la consulta devuelve resultados
    if ($result->num_rows > 0) {
        $data = [];
        while($row = $result->fetch_assoc()) {
            // Ajustar las cantidades según las entregas parciales
            $row['prendas_numero']; // Restar cantidad entregada
            $data[] = $row;
        }
        return $data;
    } else {
        return false;  // O podrías devolver un array vacío dependiendo de lo que necesites
    }

    // Cerrar el statement y la conexión
    $stmt->close();
}

function entregas_parciales_datos($id_orden) {
    global $conn;  // Asegúrate de que tu conexión se llama $conn

    $query = "
                SELECT 
              *
            FROM 
                entregas_parciales
         
            WHERE 
                id_orden = ?
               
    ";

    // Preparar la consulta y vincular el parámetro
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id_orden); // "i" significa que es un entero (integer)

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
// estados
// 3 ingresado
// 4 en proceso 
// 5 arreglada
function total_entrega($id_orden) {
    global $conn;  // se sabe si todo esta arreglado o no 

    // Primero, obtener el total de prendas para la orden
    $queryTotal = "SELECT COUNT(*) as totalPrendas FROM prendas WHERE id_orden = ?";
    $stmtTotal = $conn->prepare($queryTotal);
    $stmtTotal->bind_param("i", $id_orden);
    $stmtTotal->execute();
    $resultTotal = $stmtTotal->get_result()->fetch_assoc();
    $totalPrendas = $resultTotal['totalPrendas'];
    $stmtTotal->close();

    // Luego, contar cuántas de esas prendas están en estado 5
    $queryEstado = "SELECT COUNT(*) as prendasEnEstado5 FROM prendas WHERE id_orden = ? AND estado = 5";
    $stmtEstado = $conn->prepare($queryEstado);
    $stmtEstado->bind_param("i", $id_orden);
    $stmtEstado->execute();
    $resultEstado = $stmtEstado->get_result()->fetch_assoc();
    $prendasEnEstado5 = $resultEstado['prendasEnEstado5'];
    $stmtEstado->close();

    // Comparar si el total de prendas coincide con las prendas en estado 5
    if ($totalPrendas == $prendasEnEstado5) {
        return true; // Todas las prendas están en estado 5
    } else {
        return false; // Hay prendas que no están en estado 5
    }
}

function ver_arreglo($prenda_id) {
    global $conn;  // Asegúrate de que tu conexión se llama $conn

    $query = "
        SELECT 
            p.nombre_ropa,
            p.tiempo_estimado,
            p.estado,
            p.id,
            p.valor,
            p.prendas_numero,
            p.descripcion_arreglo,
            p.id_orden,
            p.id_asignacion,
            u.login
        FROM 
            prendas p
        LEFT JOIN 
            usuarios u ON u.id = p.id_asignacion
        WHERE 
            p.id = ?
    ";

    // Preparar la consulta y vincular el parámetro
    $stmt = $conn->prepare($query); // LINE 116
    $stmt->bind_param("i", $prenda_id); // "i" significa que es un entero (integer)

    // Ejecutar la consulta
    $stmt->execute();

    // Obtener los resultados
    $result = $stmt->get_result();

    // Verificar si la consulta devuelve resultados
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();  // Ya que es un solo registro, simplemente devuelve el primer registro
    } else {
        return false;  // O podrías devolver un array vacío dependiendo de lo que necesites
    }

    // Cerrar el statement y la conexión
    $stmt->close();
}

function obtener_usuarios() {
    global $conn;  // Asegúrate de que tu conexión se llama $conn

    $query = "
        SELECT 
            id,
            login
        FROM 
            usuarios
    ";

    // Preparar y ejecutar la consulta
    $result = $conn->query($query);

    // Verificar si la consulta devuelve resultados
    if ($result->num_rows > 0) {
        $usuarios = [];
        while($row = $result->fetch_assoc()) {
            $usuarios[] = $row;
        }
        return $usuarios;
    } else {
        return [];  // Devuelve un array vacío si no hay resultados
    }

    // Cerrar la conexión
    $conn->close();
}
function obtener_info_ordenes($cliente_id) {
    global $conn;  // Asegúrate de que tu conexión se llama $conn

    $query = "
    SELECT 
    o.id, 
    o.fecha_creacion, 
    o.fecha_entrega, 
    o.franja_horaria, 
    o.total_prendas, 
    o.valor_total, 
    o.abono, 
    o.saldo,
    CASE 
        WHEN SUM(CASE WHEN p.estado = 5 THEN 1 ELSE 0 END) = COUNT(p.id) THEN 'Arreglada'
        WHEN SUM(CASE WHEN p.estado = 4 THEN 1 ELSE 0 END) > 0 THEN 'En proceso'
        WHEN SUM(CASE WHEN p.estado = 3 THEN 1 ELSE 0 END) > 0 THEN 'En proceso'
        WHEN SUM(CASE WHEN p.estado = 1 THEN 1 ELSE 0 END) = COUNT(p.id) THEN 'Ingresada'
        ELSE 'Estado mixto'
    END AS estado
FROM 
    ordenes o 
LEFT JOIN 
    prendas p ON p.id_orden = o.id
WHERE 
    p.id_cliente = ?
GROUP BY 
    o.id, 
    o.fecha_creacion, 
    o.fecha_entrega, 
    o.franja_horaria, 
    o.total_prendas, 
    o.valor_total, 
    o.abono, 
    o.saldo;

";



    // Preparar la consulta y vincular el parámetro
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $cliente_id); // "i" significa que es un entero (integer)

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

function calcularPorcentaje($estado) {
    switch ($estado) {
        case 1:
        case 3:
            return 0; // 0% para Ingresado
        case 4:
            return 50; // 50% para En proceso
        case 5:
            return 100; // 100% para Arreglado
        default:
            return 0; // Estado desconocido se trata como 0%
    }
}

function obtenerPorcentajeYClase($id_orden) {
    $arreglos_prendas = prendas_por_orden_con_cliente($id_orden);
    $totalPrendas = count($arreglos_prendas);
    $totalPorcentaje = 0;

    foreach ($arreglos_prendas as $prenda) {
        $totalPorcentaje += calcularPorcentaje($prenda['estado']);
    }

    $porcentajeOrden = 0;
    if ($totalPrendas > 0) {
        $porcentajeOrden = $totalPorcentaje / $totalPrendas;
    }

    // Determine the class for the progress bar based on the percentage
    $progressBarClass = '';
    if ($porcentajeOrden == 100) {
        $progressBarClass = 'progress-bar-green';
    } elseif ($porcentajeOrden > 0 && $porcentajeOrden < 100) {
        $progressBarClass = 'progress-bar-orange';
    } else {
        $progressBarClass = 'progress-bar-red';
    }

    return [
        'porcentajeOrden' => $porcentajeOrden,
        'progressBarClass' => $progressBarClass
    ];
}

function obtenerEstadoGeneral($estadoOrden) {
    switch ($estadoOrden) {
        case 6:
            return 'Entregado ✔';
        case 7:
            return 'Entrega parcial 📦';
        default:
            return ''; // No agregamos nada si no es 6 ni 7
    }
}

// Asumiendo que $conn es tu conexión a la base de datos

function seleccionar_todas_las_columnas_caja_por_mes($mes, $anio) {
    global $conn;

    $sql = "SELECT 
                c.*, 
                COUNT(e.id) AS total_entregas
            FROM 
                caja c
            LEFT JOIN 
                entregas e ON DATE(c.fecha) = DATE(e.fecha) -- Comparación de las fechas sin la hora
                          AND c.fecha >= e.fecha            -- Asegura que las fechas coincidan
            WHERE 
                (? IS NULL OR MONTH(c.fecha) = ?) AND
                (? IS NULL OR YEAR(c.fecha) = ?)
            GROUP BY 
                c.fecha
            ORDER BY 
                c.fecha";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiii", $mes, $mes, $anio, $anio);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

function seleccionar_datos_por_id($id) {
    global $conn; // Asegúrate de que tu conexión se llama $conn

    // Query para seleccionar los datos de la tabla caja para el ID especificado
    $query = "
SELECT 
        c.*, 
        COUNT(DISTINCT e.id) AS total_entregas,
        cl.nombre AS nombre_cliente,
        GROUP_CONCAT(DISTINCT cl.nombre SEPARATOR ', ') AS nombres_abonos
    FROM 
        caja c
    LEFT JOIN 
        entregas e ON DATE(c.fecha) = DATE(e.fecha)
    LEFT JOIN 
        prendas p ON e.orden_id = p.id_orden
    LEFT JOIN 
        clientes cl ON p.id_cliente = cl.id
    LEFT JOIN 
        ordenes o ON DATE(c.fecha) = DATE(o.fecha_entrega)
    LEFT JOIN 
        usuarios u ON e.usuario_id = u.id
        WHERE
        c.id = ?
    GROUP BY 
        c.id, cl.nombre, c.fecha
    ORDER BY 
            c.fecha;

    ";

    // Preparar la consulta
    $stmt = $conn->prepare($query);

    if (!$stmt) {
        // Si hay un error en la preparación de la consulta, muestra el mensaje de error
        echo "Error en la preparación de la consulta: " . $conn->error;
        return false;
    }

    // Vincular el parámetro de ID y ejecutar la consulta
    $stmt->bind_param("i", $id);
    $result = $stmt->execute();

    if (!$result) {
        // Si hay un error al ejecutar la consulta, muestra el mensaje de error
        echo "Error al ejecutar la consulta: " . $stmt->error;
        return false;
    }

    // Obtener los resultados
    $resultados = $stmt->get_result();

    // Verificar si la consulta devuelve resultados
    if ($resultados->num_rows > 0) {
        $data = [];
        while ($row = $resultados->fetch_assoc()) {
            $data[] = $row;
        }
        return $data;
    } else {
        return []; // Devolver un array vacío si no hay resultados encontrados
    }

    // Cerrar el statement (liberar los recursos)
    $stmt->close();
}
?>

