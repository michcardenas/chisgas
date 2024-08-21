<?php
    include_once '../conexion/db_connection.php';
    include_once '../model/usuariosModel.php';


    $action = $_REQUEST['action'] ?? null;

if ($action == 'obtener_usuarios') {
    $obtener_usuarios = obtener_usuarios();
    if ($obtener_usuarios) {
        echo json_encode(['success' => true, 'data' => $obtener_usuarios]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No se encontraron datos.']);
    }
    exit;
}
?>
