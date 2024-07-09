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
        echo '<table border="1" class="centered-table">';
        echo '<thead><tr><th>ID</th><th>Fecha</th><th>Base</th><th>Dinero Final</th><th>Total Recogido</th><th>Usuarios Día</th><th>Gastos</th><th>Entregas</th><th>Abonos</th></tr></thead>';
        echo '<tbody>';

        // Iterar sobre los resultados y mostrar cada fila en la tabla
        foreach ($resultados as $fila) {
            echo '<tr>';
            echo '<td>' . $fila['id'] . '</td>';
            echo '<td>' . date('Y-m-d', strtotime($fila['fecha'])) . '</td>'; // Mostrar solo el día en formato YYYY-MM-DD
            echo '<td>' . number_format($fila['base'], 0, ',', '.') . '</td>'; // Formato sin decimales, con separadores de miles
            echo '<td>' . number_format($fila['dinero_final'], 0, ',', '.') . '</td>'; // Formato sin decimales, con separadores de miles
            echo '<td>' . number_format($fila['total_recogido'], 0, ',', '.') . '</td>'; // Formato sin decimales, con separadores de miles
            echo '<td>' . $fila['total_entregas'] . '</td>'; // Este campo ya debería ser un entero según la consulta
            echo '<td>' . $fila['gastos'] . '</td>';
            echo '<td>' . $fila['nombre_cliente'] . '</td>';
            echo '<td>' . $fila['nombres_abonos'] . '</td>';
            echo '</tr>';
        }
        echo '</tbody>';
        echo '</table>';
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
