<?php
include_once '../model/usuariosModel.php';

// Instanciar el modelo
$model = new UsuariosModel($conn);

$action = $_REQUEST['action'] ?? null;

try {
    if ($action == 'obtener_usuarios') {
        // Obtener todos los usuarios
        $usuarios = $model->obtenerUsuarios();
        echo json_encode(['success' => true, 'data' => $usuarios]);
    } elseif ($action == 'obtener_grupos_usuarios') {
        // Obtener los grupos de usuarios
        $gruposUsuarios = $model->obtenerGruposUsuarios();
        echo json_encode(['success' => true, 'data' => $gruposUsuarios]);
    } elseif ($action == 'editar_usuario') {
        $id = $_POST['id'];
        $nuevoLogin = $_POST['login'];
        $nuevoPassword = $_POST['contrasena'];
        $nuevoGrupoUsuario = $_POST['grupo_usuario'];
        
        $model->editarUsuario($id, $nuevoLogin, $nuevoPassword, $nuevoGrupoUsuario);
        echo json_encode(['success' => true, 'message' => 'Usuario actualizado con éxito.']);
    } elseif ($action == 'eliminar_usuario') {
        $id = $_POST['id'];
        $model->eliminarUsuario($id);
        echo json_encode(['success' => true, 'message' => 'Usuario eliminado con éxito.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Acción no válida.']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}




?>
