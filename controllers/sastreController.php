<?php
include_once '../model/sastreModel.php';
include_once '../conexion/db_connection.php';

// Iniciar la sesión
session_start();

// Instanciar el modelo con la conexión de base de datos
$model = new SastreModel($conn);

// Obtener la acción desde la solicitud
$action = $_REQUEST['action'] ?? null;

try {
    // Verificar la acción
    if ($action === 'obtener_arreglos_sastre') {
        // Obtener el ID del usuario desde la sesión
        $id_usuario = $_SESSION['id'] ?? null;

        if ($id_usuario) {
            // Obtener los arreglos del sastre
            $arreglosSastre = $model->obtenerArreglosSastre($id_usuario);
            if ($arreglosSastre) {
                // Respuesta exitosa
                echo json_encode(['success' => true, 'data' => $arreglosSastre]);
            } else {
                // No se encontraron arreglos
                echo json_encode(['success' => false, 'message' => 'No se encontraron arreglos para este usuario.']);
            }
        } else {
            // Error si no hay usuario en la sesión
            echo json_encode(['success' => false, 'message' => 'ID de usuario no disponible.']);
        }

    } elseif ($action === 'ver_arreglo') {
        // Obtener el ID de la prenda desde la solicitud
        $idPrenda = $_REQUEST['id_prenda'] ?? null;

        if ($idPrenda) {
            // Obtener detalles del arreglo de la prenda
            $detalleArreglo = ver_arreglo($idPrenda);
            if ($detalleArreglo) {
                // Respuesta exitosa con detalles
                echo json_encode(['success' => true, 'data' => $detalleArreglo]);
            } else {
                // No se encontraron detalles de la prenda
                echo json_encode(['success' => false, 'message' => 'No se encontraron detalles para esta prenda.']);
            }
        } else {
            // Error si no se proporciona el ID de la prenda
            echo json_encode(['success' => false, 'message' => 'ID de prenda no proporcionado.']);
        }

    } elseif ($action === 'ver_calendario_estado_prenda') {
        // Obtener el estado desde la solicitud
        $estado = $_REQUEST['estado'] ?? null;

        if ($estado) {
            // Obtener el calendario de acuerdo al estado de la prenda
            $calendarioEstado = ver_calendario_estado_prenda($estado);
            if ($calendarioEstado) {
                // Respuesta exitosa con los datos del calendario
                echo json_encode(['success' => true, 'data' => $calendarioEstado]);
            } else {
                // No se encontraron registros para el estado especificado
                echo json_encode(['success' => false, 'message' => 'No se encontraron datos para este estado.']);
            }
        } else {
            // Error si no se proporciona el estado
            echo json_encode(['success' => false, 'message' => 'Estado no proporcionado.']);
        }

    } else {
        // Respuesta para una acción no válida
        echo json_encode(['success' => false, 'message' => 'Acción no válida.']);
    }

} catch (Exception $e) {
    // Manejar excepciones
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
