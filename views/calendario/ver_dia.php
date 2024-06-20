<?php
// Iniciar la sesión
session_start();
include '../../model/funciones.php';

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
    $ruta_image_menu = '../menu.php';
    $ruta_image = "../img/chisgas_fondo_blanco.png";

    include $ruta;
} else {
    echo "El archivo $ruta no existe.";
}

if (isset($_GET['fecha_entrega'])) {
    $fecha_entrega = $_GET['fecha_entrega'];

    // Ahora puedes usar la variable $fecha_entrega en tus operaciones.
    echo $fecha_entrega; // Esto imprimirá: 2023-09-25
} else {
    // Aquí manejas el caso en que no se envió la fecha.
    echo "Fecha no proporcionada.";
}
$ordenes_del_dia = obtener_ordenes_del_dia($fecha_entrega);
echo '
<div class="p_centrar">
<div class="centrar">';
echo '<table border="1">'; // Uso border="1" solo para visualizar la tabla, puedes removerlo o estilizarlo como quieras
echo '<thead>';
echo '<tr>';
echo '<th>Nombre Cliente</th>';
echo '<th>Número de Prendas</th>';
echo '<th>Estado General</th>'; // Agregamos esta columna para mostrar el estado general
echo '</tr>';
echo '</thead>';
echo '<tbody>';

foreach ($ordenes_del_dia as $orden) {
<<<<<<< Updated upstream
    echo '<tr>';
=======
    $resultado = obtenerPorcentajeYClase($orden["id_orden"]);
    $porcentajeOrden = $resultado['porcentajeOrden'];
    $progressBarClass = $resultado['progressBarClass'];
>>>>>>> Stashed changes

    // Si el nombre del cliente es NULL o vacío, muestra un mensaje predeterminado o lo que quieras mostrar
    $nombre_cliente = $orden["nombre_cliente"] ? $orden["nombre_cliente"] : "Cliente Desconocido";
    echo '<td>';
    echo '<a href="ver_arreglos.php?id_orden=' . $orden["id_orden"] . '">' . htmlspecialchars($nombre_cliente) . '</a>';
    echo '</td>';

    echo '<td>' . htmlspecialchars($orden["total_prendas_por_orden"]) . '</td>';
<<<<<<< Updated upstream
    echo '<td>' . htmlspecialchars($orden["estado_general"]) . '</td>'; // Mostramos el estado general
=======
    
    // Aquí añadimos la barra de progreso con la clase dinámica
    echo '<td>';
    echo '<div class="progress-container">';
    echo '<div class="progress-bar ' . htmlspecialchars($progressBarClass) . '" style="width:' . htmlspecialchars($porcentajeOrden) . '%;"></div>';
    echo '<span>' . htmlspecialchars($porcentajeOrden) . '%</span>';
    echo '</div>';
    echo '</td>';
    
>>>>>>> Stashed changes
    echo '</tr>';
}

echo '</tbody>';
echo '</table>';
echo '</div>';
echo '</div>';
?>

<style>
.progress-container {
    position: relative;
    width: 100%;
    height: 20px;
    background-color: #f3f3f3;
    border-radius: 5px;
    overflow: hidden;
}

.progress-bar {
    height: 100%;
    text-align: center;
    line-height: 20px;
    color: white;
    border-radius: 5px 0 0 5px;
}

/* Different color classes for the progress bar */
.progress-bar-red {
    background-color: #f44336; /* Red */
}

.progress-bar-orange {
    background-color: #ff9800; /* Orange */
}

.progress-bar-green {
    background-color: #4caf50; /* Green */
}

.progress-container span {
    position: absolute;
    width: 100%;
    text-align: center;
    top: 0;
    left: 0;
    line-height: 20px;
    color: #000;
}
</style>


<?php

$ruta_footer = '../footer.php';

if (file_exists($ruta)) {
    $ruta_css = '../css/style.css';
    $ruta_image = "../img/chisgas_fondo_blanco.png";
    $ruta_js = "../js/main.js";

    include $ruta_footer;
} else {
    echo "El archivo $ruta no existe.";
}

?>