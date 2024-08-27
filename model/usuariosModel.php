<?php
include_once '../conexion/db_connection.php'; // Incluir la conexión a la base de datos

class UsuariosModel {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    // Obtener todos los usuarios
    public function obtenerUsuarios() {
        $sql = "SELECT * FROM `usuarios` WHERE activo = 1";
        $result = $this->conn->query($sql);

        // Verificar que la consulta se haya ejecutado correctamente
        if ($result === false) {
            throw new Exception("Error en la consulta: " . $this->conn->error);
        }

        // Obtener todos los resultados como un array asociativo
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function obtenerGruposUsuarios() {
        $sql = "SELECT DISTINCT grupo_usuario FROM usuarios";
        $stmt = $this->conn->query($sql);
        $gruposUsuarios = [];
        if ($stmt->num_rows > 0) {
            while ($row = $stmt->fetch_assoc()) {
                $gruposUsuarios[] = $row;
            }
        }
        return $gruposUsuarios;
    }
    

    // Editar un usuario por ID
    public function editarUsuario($id, $nuevoLogin, $nuevaContrasena, $nuevoGrupoUsuario) {
        // Preparar la consulta para actualizar los campos login, contrasena y grupo_usuario
        $stmt = $this->conn->prepare("UPDATE `usuarios` SET `login` = ?, `contrasena` = ?, `grupo_usuario` = ? WHERE `id` = ?");
        if ($stmt === false) {
            throw new Exception("Error en la preparación de la consulta: " . $this->conn->error);
        }
    
        // Vincular los parámetros y ejecutar la consulta
        $stmt->bind_param('sssi', $nuevoLogin, $nuevaContrasena, $nuevoGrupoUsuario, $id);
        $stmt->execute();
    
        // Verificar si la consulta fue exitosa
        if ($stmt->affected_rows === 0) {
            throw new Exception("No se encontró el usuario con el ID: " . $id);
        }
    
        $stmt->close();
    }
    

// Marcar un usuario como inactivo por ID
public function eliminarUsuario($id) {
    // Usar una consulta preparada para evitar inyecciones SQL
    $stmt = $this->conn->prepare("UPDATE `usuarios` SET `activo` = 0 WHERE `id` = ?");
    if ($stmt === false) {
        throw new Exception("Error en la preparación de la consulta: " . $this->conn->error);
    }

    // Vincular los parámetros y ejecutar la consulta
    $stmt->bind_param('i', $id);
    $stmt->execute();

    // Verificar si la consulta fue exitosa
    if ($stmt->affected_rows === 0) {
        throw new Exception("No se encontró el usuario con el ID: " . $id);
    }

    $stmt->close();
}
}
?>
