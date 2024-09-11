<?php
include_once '../conexion/db_connection.php';
// Crear una conexión a la base de datos
$conexion = new mysqli($servidor, $usuario, $contraseña, $basedatos);

// Verificar la conexión
if ($conexion->connect_error) {
    die(json_encode(["error" => "Conexión fallida: " . $conexion->connect_error]));
}

// Configurar el tipo de contenido de la respuesta
header('Content-Type: application/json; charset=utf-8');

// Función para convertir el estado a texto
function obtenerEstadoTexto($estado, $entregas) {
    if ($entregas) {
        return 'Entregado';
    }

    switch ($estado) {
        case 5:
            return 'Arreglado';
        case 3:
            return 'Ingresado';
        case 4:
            return 'En Proceso';
        default:
            return 'Desconocido';
    }
}

// Inicializar variables
$response = [];
$telefono = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $telefono = isset($_POST['telefono']) ? $conexion->real_escape_string($_POST['telefono']) : '';

    if (!empty($telefono)) {
        // Buscar el cliente por número de teléfono
        $sql_cliente = "SELECT id FROM clientes WHERE telefono = '$telefono'";
        $result_cliente = $conexion->query($sql_cliente);

        if ($result_cliente->num_rows == 0) {
            $response = ["mensaje" => 'No se encontró cliente para este número de teléfono', "prendas" => []];
        } else {
            // Obtener el ID del cliente
            $cliente = $result_cliente->fetch_assoc();
            $cliente_id = $cliente['id'];

            // Buscar todas las prendas asociadas al cliente y ordenarlas por fecha de entrega más reciente
            $sql_prendas = "
                SELECT p.nombre_ropa, p.descripcion_arreglo, p.estado, o.id AS id_orden, o.fecha_entrega, e.id AS id_entrega
                FROM prendas p
                JOIN ordenes o ON p.id_orden = o.id
                LEFT JOIN entregas e ON o.id = e.id
                WHERE p.id_cliente = $cliente_id
                ORDER BY o.fecha_entrega DESC
            ";
            $result_prendas = $conexion->query($sql_prendas);

            $prendas = [];
            while ($prenda = $result_prendas->fetch_assoc()) {
                $id_entrega = $prenda['id_entrega'] ? true : false;
                $prenda['estado'] = obtenerEstadoTexto($prenda['estado'], $id_entrega);
                $prendas[] = $prenda;
            }

            $response = ["mensaje" => "Aquí están tus prendas ordenadas por la fecha de entrega más reciente", "prendas" => $prendas];
        }
    } else {
        $response = ["mensaje" => 'Número de teléfono es requerido', "prendas" => []];
    }
}

// Cerrar la conexión
$conexion->close();

// Enviar respuesta en formato JSON
echo json_encode($response);
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consulta de Prendas</title>
</head>
<body>
    <h1>Consulta de Prendas por Teléfono</h1>
    <form method="post" action="">
        <label for="telefono">Número de Teléfono:</label>
        <input type="text" id="telefono" name="telefono" required>
        <button type="submit">Consultar</button>
    </form>
</body>
</html>
