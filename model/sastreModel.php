<?php

include_once '../conexion/db_connection.php'; // Incluir la conexión a la base de datos

class SastreModel {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    // Obtener los arreglos del sastre filtrados por id_asignacion
    public function obtenerArreglosSastre($id_usuario) {
        $sql = "
 SELECT 
    p.id_asignacion, 
    p.id AS id_prenda,
    p.nombre_ropa,
    p.id_cliente,
    p.valor,
    c.nombre AS nombre_cliente
FROM 
    prendas p
JOIN 
    clientes c ON p.id_cliente = c.id
WHERE 
    p.id_asignacion = ?
ORDER BY 
    p.nombre_ropa ASC;

        ";

        // Preparar la consulta
        $stmt = $this->conn->prepare($sql);
        if ($stmt === false) {
            throw new Exception("Error en la preparación de la consulta: " . $this->conn->error);
        }

        // Vincular el parámetro
        $stmt->bind_param('i', $id_usuario);

        // Ejecutar la consulta
        $stmt->execute();

        // Obtener el resultado
        $result = $stmt->get_result();

        // Verificar que la consulta se haya ejecutado correctamente
        if ($result === false) {
            throw new Exception("Error en la consulta: " . $stmt->error);
        }

        // Obtener todos los resultados como un array asociativo
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}

function ver_arreglo($idPrenda) {
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
            u.login,
            COALESCE(ep.cantidad_entregada, 0) AS cantidad_entregada
        FROM 
            prendas p
        LEFT JOIN 
            usuarios u ON u.id = p.id_asignacion
        LEFT JOIN 
            entregas_parciales ep ON ep.id_prenda = p.id
        WHERE 
            p.id = ?";

    // Preparar la consulta
    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        echo "Error en la preparación de la consulta: " . $conn->error . "<br>";
        return false;
    }

    // Vincular el parámetro
    $stmt->bind_param("i", $idPrenda);

    // Ejecutar la consulta
    $stmt->execute();

    // Obtener los resultados
    $result = $stmt->get_result();

    // Verificar si la consulta devuelve resultados
    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();
        return $data;
    } else {
        return false;
    }

    // Cerrar el statement
    $stmt->close();
}


function ver_calendario_estado_prenda($estado) {
    global $conn;  // Asegúrate de que tu conexión se llama $conn

    // Validar si el estado proporcionado está en la lista permitida
    $validStates = ['3', '4', '5', '6', 'all'];
    if (!in_array($estado, $validStates)) {
        return false;  // O podrías lanzar un error si lo prefieres
    }

    // Preparar la consulta basada en el estado proporcionado
    if ($estado == 'all') {
        // Seleccionar todas las prendas en los estados permitidos
        $query = "
        SELECT 
            p.id_asignacion, 
            p.id AS id_prenda,
            p.nombre_ropa,
            p.id_cliente,
            p.valor,
            c.nombre AS nombre_cliente
        FROM 
            prendas p
        JOIN 
            clientes c ON p.id_cliente = c.id
        WHERE 
            p.estado IN ('3', '4', '5', '6')
        ORDER BY 
            p.nombre_ropa ASC;
        ";
    } elseif ($estado == '5') {
        // Seleccionar sólo las prendas en estado 6
        $query = "
        SELECT 
            p.id_asignacion, 
            p.id AS id_prenda,
            p.nombre_ropa,
            p.id_cliente,
            p.valor,
            c.nombre AS nombre_cliente
        FROM 
            prendas p
        JOIN 
            clientes c ON p.id_cliente = c.id
        WHERE 
            p.estado = '5'
        ORDER BY 
            p.nombre_ropa ASC;
        ";
    } elseif ($estado == '3') {
        // Seleccionar prendas en uno de los estados específicos (3, 4, 0)
        $query = "
          SELECT 
        p.id_asignacion, 
        p.id AS id_prenda,
        p.nombre_ropa,
        p.id_cliente,
        p.valor,
        c.nombre AS nombre_cliente
    FROM 
        prendas p
    JOIN 
        clientes c ON p.id_cliente = c.id
    WHERE 
        p.estado IN ('0', '3', '4')
    ORDER BY 
        p.nombre_ropa ASC;
        ";
    } else {
        // Manejo para estados no válidos o casos de error
        return false;  // O podrías lanzar un error si lo prefieres
    }

    $stmt = $conn->prepare($query);

    // Ejecutar la consulta
    $stmt->execute();
    $result = $stmt->get_result();

    // Verificar si la consulta devuelve resultados
    if ($result->num_rows > 0) {
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        return $data;
    } else {
        return false;  // O podrías devolver un array vacío dependiendo de lo que necesites
    }
}

?>