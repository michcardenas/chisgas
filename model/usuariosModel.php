<?php
include_once '../conexion/db_connection.php'; // Incluir la conexión a la base de datos

function obtenerUsuarios() {
        $sql = "SELECT id, nombre FROM usuarios";
        $result = $this->conn->query($sql);

        // Verificar que la consulta se haya ejecutado correctamente
        if ($result === false) {
            throw new Exception("Error en la consulta: " . $this->conn->error);
        }

        return $result;
    }
?>


