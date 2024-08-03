<?php
// Iniciar la sesión
session_start();

// Comprobar si el usuario ha iniciado sesión
if (!isset($_SESSION['username'])) {
    // Si no ha iniciado sesión, redirigir al login
    header("Location: login/login.php");
    exit();
}

// Rutas para los archivos incluidos
$ruta_template = '../template.php';
$ruta_footer = '../footer.php';

// Verificar si los archivos existen
if (file_exists($ruta_template) && file_exists($ruta_footer)) {
    // Incluir archivos de template y footer
    $ruta_css = '../css/style.css';
    $ruta_icon = '../img/aguja.png';
    $ruta_image_menu = "../menu.php";
    $ruta_image = "../img/chisgas_fondo_blanco.png";
    include $ruta_template;

    include '../../conexion/db_connection.php';
    include '../../model/funciones.php';

    // Verificar si se recibió el ID como parámetro GET
    if (isset($_GET['id'])) {
        // Obtener el ID desde el parámetro GET y asegurarse de sanitizarlo adecuadamente
        $id = htmlspecialchars($_GET['id']);

        // Llamar a la función para obtener los datos de la tabla caja para el ID seleccionado
        $resultados = seleccionar_datos_por_id($id);

        // Mostrar los resultados en una tabla HTML
        echo '<div class="p_centrar">';
        echo '<div class="table-container">';
        echo '<div style="display: flex; flex-wrap: wrap; justify-content: space-around; gap: 20px; padding: 20px;">'; // cards-container
        foreach ($resultados as $fila) {
            echo '<div style="background-color: #f9f9f9; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); padding: 15px; width: 300px; transition: transform 0.3s ease;">'; // card
            echo '<div style="font-size: 1.2em; font-weight: bold; margin-bottom: 10px; color: #333;">Fecha : ' . date('Y-m-d', strtotime($fila['fecha'])) . '</div>'; // card-date
            echo '<div style="display: flex; flex-direction: column; gap: 10px;">'; // card-content
            echo '<div style="display: flex; justify-content: space-between;"><span style="font-weight: bold; color: #666;">Base:</span> ' . number_format($fila['base'], 0, ',', '.') . '</div>'; // card-item
            echo '<div style="display: flex; justify-content: space-between;"><span style="font-weight: bold; color: #666;">Gastos:</span> ' . $fila['gastos'] . '</div>'; // card-item
            echo '<div style="display: flex; justify-content: space-between;"><span style="font-weight: bold; color: #666;">Total Recogido:</span> ' . number_format($fila['total_recogido'], 0, ',', '.') . '</div>'; // card-item
            echo '<div style="display: flex; justify-content: space-between;"><span style="font-weight: bold; color: #666;">Dinero Final:</span> ' . number_format($fila['dinero_final'], 0, ',', '.') . '</div>'; // card-item

            echo '</div>';
            echo '</div>';
        }
        echo '</div>';
        echo '</div>'; // Cierre del contenedor de la tabla
        echo '</div>'; // Cierre del contenedor p_centrar

        // Incluir archivo de JavaScript y footer si es necesario
        // $ruta_js = "../js/main.js"; // Incluir JavaScript si es necesario
        // include '../../footer.php'; // Incluir footer si es necesario

    } else {
        // Si no se proporcionó un ID válido, muestra un mensaje de error o redirecciona
        echo '<p>No se ha seleccionado ningún ID válido.</p>';
    }

    // Incluir archivo de JavaScript y footer
    // $ruta_js = "../js/main.js"; // Incluir JavaScript si es necesario
    $ruta_js = "../js/main.js";
    include $ruta_footer;
} else {
    echo "El archivo de template o footer no existe.";
}
?>
