<?php
include_once '../conexion/db_connection.php';
include_once '../TCPDF/tcpdf.php';
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

function generarFacturaPDF($id_orden, $nombre_usuario) {
    global $conn; // Asume la conexión a la base de datos

    // Define el query para obtener los datos de la factura
    $query = "SELECT o.id, o.fecha_creacion, o.fecha_entrega, o.total_prendas, o.valor_total, o.abono, o.saldo, c.nombre, c.telefono, p.nombre_ropa, p.descripcion_arreglo,p.valor FROM ordenes o JOIN prendas p on p.id_orden=o.id JOIN clientes c on p.id_cliente=c.id WHERE o.id=?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $id_orden);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $fechayhora = date("Y-m-d H:i:s");
    
    if ($resultado->num_rows > 0) {
        $datosFactura = $resultado->fetch_assoc();
        $nombre_cliente = $datosFactura['nombre'];
        $telefono_cliente = $datosFactura['telefono'];
        $abono = $datosFactura['abono'];
        $saldo = $datosFactura['saldo'];
        $valor_total = $datosFactura['valor_total'];
        $subtotalFormateado = "$" . number_format($valor_total, 0, ',', '.');
        $abonoFormateado = "$" . number_format($abono, 0, ',', '.');
        $totalFormateado = "$" . number_format($saldo, 0, ',', '.');
  // Suponiendo que TCPDF ya está incluido e inicializado
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$resultado->data_seek(0);

// Recoger todos los datos en un arreglo primero
$datosPrendas = [];
while ($fila = $resultado->fetch_assoc()) {
    $datosPrendas[] = $fila;
}

// Luego, generas las filas de la tabla
$filasTabla = '';
foreach ($datosPrendas as $prenda) {
    $filasTabla .= "<tr>
                        <td>{$prenda['nombre_ropa']}</td>
                        <td>{$prenda['descripcion_arreglo']}</td>
                        <td>$" . number_format($prenda['valor'], 0, ',', '.') . "</td>
                        </tr>";
}
// Configura el documento
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Tu Empresa');
$pdf->SetTitle('Factura');
$pdf->SetSubject('Factura Detallada');
$pdf->SetMargins(20, 20, 20);
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
$pdf->AddPage();

// Preparar el HTML de la factura
$html = <<<EOD
<style>
    .factura-header, .factura-cliente, .factura-items, .factura-totales, .factura-footer {
        font-family: 'Helvetica', 'sans-serif';
    }
    .factura-header {
        text-align: right;
        margin-bottom: 20px;
    }
    .factura-cliente {
        margin-bottom: 20px;
    }
    .factura-items th, .factura-items td {
        border-bottom: 1px solid #000;
        padding: 5px;
    }
    .factura-totales {
        margin-top: 20px;
        text-align: right;
    }
    .factura-totales th, .factura-totales td {
        padding: 5px;
    }
    .factura-footer {
        font-size: 10px;
        text-align: center;
        margin-top: 30px;
    }
    .tabla-items {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }
    .tabla-items th, .tabla-items td {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: left;
    }
    .tabla-items th {
        background-color: #f2f2f2;
        color: #333;
        font-weight: bold;
    }
    .tabla-items tr:nth-child(even){background-color: #f9f9f9;}
    .tabla-items tr:hover {background-color: #f1f1f1;}
</style>
<div class="factura-header" style="text-align: right;">
    <img src="../views/img/logo_negro.png" alt="Logo Empresa" style="width:100px; height:auto; float:right; margin-bottom: 20px;">
    <p>NIT: 1032455582-5</p>
    <h2>Orden de arreglo N°:$id_orden</h2>
    <p>Fecha: $fechayhora</p>
    <p>Sastreria Chisgas</p>
</div>


<div class="factura-cliente">
    <p><strong>Datos cliente</strong><br>
    {$nombre_cliente}<br>
    {$telefono_cliente}<br>
</div>

<div class="factura-items">
<table class="tabla-items">
    <thead>
        <tr>
            <th style="font-weight: bold; background-color: #f2f2f2; color: #333; border: 1px solid #ddd; padding: 8px; text-align: left;"> Prenda</th>
            <th>Descripcion</th>
            <th>Valor</th>
        </tr>
    </thead>
    <tbody>
        $filasTabla
    </tbody>
</table>
</div>

<div class="factura-totales">
<table>
<tr>
    <th>Sub-total</th>
    <td>{$subtotalFormateado}</td>
</tr>
<tr>
    <th>Abono</th>
    <td>{$abonoFormateado}</td>
</tr>
<tr>
    <th>Total</th>
    <td>{$totalFormateado}</td>
</tr>
</table>
</div>

<div class="condiciones-servicio">
    <p><strong>Condiciones de Servicio para Ajustes y Arreglos de Ropa:</strong></p>
    <ul>
        <li>La responsabilidad sobre los arreglos finaliza 30 días después de la fecha de entrega acordada.</li>
        <li>La ropa para arreglo debe entregarse limpia. No se aceptarán prendas sucias.</li>
        <li>Los arreglos están garantizados por 15 días siguientes a la fecha de entrega. Cualquier inconformidad debe ser notificada dentro de este período.</li>
        <li>Después de 15 días de garantía, no se aceptarán reclamos por ajustes en prendas que no se adecuen a cambios en la medida corporal del cliente.</li>
    </ul>
    <p>Las presentes condiciones están sujetas a aceptación por parte del cliente antes del servicio.</p>
</div>

EOD;

// Imprime el HTML en el PDF
$pdf->writeHTML($html, true, false, true, false, '');
        
        // Define el nombre del archivo y la ruta de guardado
        $nombreArchivo = 'factura_' . $id_orden . '.pdf';
        $rutaGuardado = __DIR__ . '/../facturas/' . $nombreArchivo;
        
        // Guardar el PDF en el servidor
        $pdf->Output($rutaGuardado, 'F');
        
        return $rutaGuardado;
        // Retorna el nombre del archivo para su uso posterior
    } else {
        return false; // No se encontraron datos
    }
}
function entregaParcial($id_orden, $nombre_usuario) {
    global $conn; // Asegúrate de que tu conexión se llama $conn

    // Este es el query que quieres ejecutar para obtener los datos de la orden
    $query = "SELECT o.id, o.fecha_creacion, o.fecha_entrega, o.total_prendas, o.valor_total, 
                     o.abono, o.saldo, c.nombre, c.telefono, p.nombre_ropa, 
                     p.descripcion_arreglo, p.valor, p.prendas_numero 
              FROM ordenes o 
              JOIN prendas p on p.id_orden=o.id 
              JOIN clientes c on p.id_cliente=c.id 
              WHERE o.id=? "; // Asegúrate de definir el estado correcto

    // Preparar la consulta y vincular los parámetros
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id_orden);

    // Ejecutar la consulta y obtener los resultados
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            // Aquí puedes recoger los datos que quieres enviar de vuelta al AJAX
            $datos = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            return $datos; // Devuelve los datos de la orden si hay al menos una prenda arreglada
        } else {
            $stmt->close();
            return false; // Devuelve false si no hay prendas arregladas
        }
    } else {
        $stmt->close();
        return false; // Devuelve false si hay un error en la consulta
    }
}




?>