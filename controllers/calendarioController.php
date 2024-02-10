<?php
// clientesController.php

// Aquí podrías incluir el modelo
include '../model/calendarioModel.php';

// Comprobar el tipo de acción que se va a realizar (crear o buscar)
$action = $_REQUEST['action'];
if($action == 'ver_calendario') {
    $ver_calendario = ver_calendario();
    if ($ver_calendario) {
        echo json_encode(['success' => true, 'data' => $ver_calendario]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No se encontraron datos.']);
    }
    exit;
}
elseif($action == 'ver_dia') {
    $fecha = $_REQUEST['fecha'];
    $ver_dia = ver_dia($fecha);
    if ($ver_dia) {
        echo json_encode(['success' => true, 'data' => $ver_dia]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No se encontraron datos.']);
    }
    exit;
}
elseif($action == 'actualizar_prenda') {
    $estado = $_POST['estado'];
    $id = $_POST['id'];

    // Supongamos que tienes una función llamada actualizar_prenda() que actualiza la prenda en la base de datos y devuelve true si tuvo éxito.
    if (actualizar_prenda($id, $estado)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar la prenda.']);
    }
    exit;
}
elseif($action == 'editar_arreglo') {
    $id = $_POST['id'];
    $nombre_prenda = isset($_POST['nombre_prenda']) ? $_POST['nombre_prenda'] : null;
    $prendas_numero = isset($_POST['prendas_numero']) ? $_POST['prendas_numero'] : null;
    $descripcion_arreglo = isset($_POST['descripcion_arreglo']) ? $_POST['descripcion_arreglo'] : null;
    $valor = isset($_POST['valor']) ? $_POST['valor'] : null;
    $asignado = isset($_POST['asignado']) ? intval($_POST['asignado']) : null;
    $estado = isset($_POST['estado']) ? intval($_POST['estado']) : null;



    $data = editar_prenda($id, $nombre_prenda, $prendas_numero, $descripcion_arreglo, $valor, $asignado,$estado);

    if ($data) {
        echo json_encode(['success' => 'Se ha editado correctamente', 'data' => $data]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al recuperar los datos de la prenda.']);
    }
    exit;
}

elseif ($action == 'entregar') {
    $id_orden = isset($_POST['id_orden']) ? $_POST['id_orden'] : null;

    if ($id_orden !== null ) {

        $result = verificar_estado_entrega($id_orden);
       
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Puede hacer su factura']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Tiene que haber al menos una prenda arreglada para poder entregar la orden.']);
        }
        
    } else {
        echo json_encode(['success' => false, 'message' => 'Error no controlado']);
    }

    exit;
}

elseif ($action == 'entregaTotal') {
    $id_orden = isset($_POST['idOrden']) ? $_POST['idOrden'] : null;
    $forma_pago = isset($_POST['forma_pago']) ? $_POST['forma_pago'] : null;
    $nombre_usuario = isset($_POST['id_usuario']) ? $_POST['id_usuario'] : null;
  

    if ($id_orden !== null && $forma_pago !== null && $nombre_usuario !== null) {
        $result = registrarEntrega($id_orden, $nombre_usuario, $forma_pago);
       
        if ($result) {
            // Llama a la función generarFacturaPDF pasando el ID de la orden y el nombre de usuario
            $rutaPDF = generarFacturaPDF($id_orden, $nombre_usuario);
            
            if ($rutaPDF) {
                // Aquí puedes manejar el envío del PDF a WhatsApp o cualquier otra acción
                echo json_encode(['success' => true, 'message' => 'La entrega se ha registrado correctamente y la factura ha sido generada.', 'pdf' => $rutaPDF]);
            } else {
                echo json_encode(['success' => false, 'message' => 'La entrega se ha registrado, pero no se pudo generar la factura.']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'No se pudo registrar la entrega']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Faltan datos para procesar la entrega']);
    }
    

    exit;
}

?>