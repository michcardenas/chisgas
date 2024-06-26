<?php
// Iniciar la sesión
session_start();

// Comprobar si el usuario ha iniciado sesión
if (!isset($_SESSION['username'])) {
    // Si no ha iniciado sesión, redirigir al login
    header("Location: ../login/login.php");
    exit();
}

// Aquí puedes agregar la lógica para cerrar la caja
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Cerrar Caja</title>
    <style>
        /* Aquí puedes incluir tus estilos CSS existentes o vincular tu archivo CSS externo */
        <?php include '../css/style.css'; ?>
    </style>
</head>
<body>
    <p>Cerrar caja - Lógica pendiente de implementar</p>
</body>
</html>
