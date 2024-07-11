<?php
// Iniciar la sesión
session_start();

// Comprobar si el usuario ha iniciado sesión
if (!isset($_SESSION['username'])) {
    // Si no ha iniciado sesión, redirigir al login
    header("Location: login/login.php");
    exit();
}

$ruta = '../template.php';

if (file_exists($ruta)) {
    $ruta_css = '../css/style.css';
    $ruta_icon = '../img/aguja.png';
    $ruta_image_menu = "../menu.php";
    $ruta_image = "../img/chisgas_fondo_blanco.png";
    include $ruta;
} else {
    echo "El archivo $ruta no existe.";
}
?>

<div class="centrar_botones_menu">
    <!-- Botón para abrir caja -->
        <a href="../ordenes/abrir_caja.php">
        <button type="submit" name="accion" value="abrir" class="button2">Abrir caja &#x1F6E0;</button>
        </a>

    <!-- Botón para cerrar caja -->
    <a href="../ordenes/cerrar_caja.php">
        <button type="submit" name="accion" value="cerrar" class="button2">Cerrar caja &#x1F6AC;</button>
    </a>

    <a href="../ordenes/estadisticas.php">
    <button type="submit" name="accion" value="estadisticas" class="button2">Estadisticas &#x1F4C8;</button>
</a>

<a href="../ordenes/facturas.php">
    <button type="submit" name="accion" value="facturas" class="button2">Facturas &#x1F4C4;</button>
</a>

</div>



<?php 
$ruta_footer = '../footer.php';
if (file_exists($ruta_footer)) {
    $ruta_js = "../js/main.js";
    include $ruta_footer;
} else {
    echo "El archivo $ruta_footer no existe.";
}
?>
