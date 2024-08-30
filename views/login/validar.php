<?php
session_start();

$username = isset($_POST['username']) ? $_POST['username'] : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

include_once '../../conexion/db_connection.php';

$statement = $conn->prepare('SELECT * FROM usuarios WHERE login = ?');

if (!$statement) {
    die("Database query failed.");
}

$statement->bind_param('s', $username);
$statement->execute();
$result = $statement->get_result();

if ($result->num_rows === 0) {
    header("Location: ../login/login.php?error=1"); // Usuario no encontrado
    exit();
}

$user = $result->fetch_assoc();

// Verificar si la contraseña es correcta
if ($password === $user['contrasena']) {
    // Guardar la información del usuario en la sesión
    $_SESSION['username'] = $username;
    $_SESSION['grupo_usuario'] = $user['grupo_usuario'];
    $_SESSION['id'] = $user['id']; // Asegúrate de que esta clave es correcta

    header('Location: ../menu.php');
    exit();
} else {
    header("Location: ../login/login.php?error=2"); // Contraseña incorrecta
    exit();
}

?>
