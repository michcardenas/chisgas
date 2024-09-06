<?php
include_once '../model/sastreModel.php';
include_once '../conexion/db_connection.php';

// Iniciar la sesión
session_start();

// Instanciar el modelo
$model = new SastreModel($conn);

// Obtener la acción desde la solicitud
$action = $_REQUEST['action'] ?? null;

try {
    if ($action === 'obtener_arreglos_sastre') {
        // Obtener el ID del usuario desde la sesión
        $id_usuario = $_SESSION['id'] ?? null;

        if ($id_usuario) {
            // Obtener todos los arreglos del sastre para el usuario
            $arreglosSastre = $model->obtenerArreglosSastre($id_usuario);
            if ($arreglosSastre) {
                // Respuesta exitosa con los datos obtenidos
                echo json_encode(['success' => true, 'data' => $arreglosSastre]);
            } else {
                // Si no se encontraron arreglos
                echo json_encode(['success' => false, 'message' => 'No se encontraron arreglos para este usuario.']);
            }
        } else {
            // Error si no se encuentra el ID del usuario en la sesión
            echo json_encode(['success' => false, 'message' => 'ID de usuario no disponible.']);
        }
    } elseif ($action === 'ver_arreglo') {
        // Obtener el ID de la prenda desde la solicitud
        $idPrenda = $_REQUEST['id_prenda'] ?? null;

        if ($idPrenda) {
            // Obtener los detalles del arreglo de la prenda
            $detalleArreglo = ver_arreglo($idPrenda);
            if ($detalleArreglo) {
                // Respuesta exitosa con los detalles obtenidos
                echo json_encode(['success' => true, 'data' => $detalleArreglo]);
            } else {
                // Si no se encontró la prenda
                echo json_encode(['success' => false, 'message' => 'No se encontraron detalles para esta prenda.']);
            }
        } else {
            // Error si no se proporciona el ID de la prenda
            echo json_encode(['success' => false, 'message' => 'ID de prenda no proporcionado.']);
        }
    } else {
        // Respuesta para una acción no válida
        echo json_encode(['success' => false, 'message' => 'Acción no válida.']);
    }
} catch (Exception $e) {
    // Manejo de excepciones, devuelve el mensaje de error
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
