<?php
// $servidor = "localhost";
// $usuario = "root";
// $contraseña = "";  
// $basedatos = "chisgas";
// $puerto = 3306; 

$servidor = "localhost";
$usuario = "c2621289_chsigas";
$contraseña = "65noPEtuma";  
$basedatos = "c2621289_chsigas";
$puerto = 3306; 

$conn = new mysqli($servidor, $usuario, $contraseña, $basedatos, $puerto);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}
?>
