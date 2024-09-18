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
    $ruta_image_menu = "caja.php"; // Cambiado a cerrar_caja.php
    $ruta_image = "../img/chisgas_fondo_blanco.png";
    include $ruta_template;

    include '../../conexion/db_connection.php';
    include '../../model/funciones.php';

    // Obtener el mes y año seleccionados del formulario
    $mes = isset($_GET['mes']) ? $_GET['mes'] : date('m'); // Si no se selecciona, usa el mes actual
$anio = isset($_GET['anio']) ? $_GET['anio'] : date('Y'); // Si no se selecciona, usa el año actual

    $nombres_meses = [
        1 => 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
        'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
    ];
    // Llamar a la función para obtener los datos de la tabla caja filtrados por mes y año
    $resultados = seleccionar_todas_las_columnas_caja_por_mes($mes, $anio);

    // Inicializar variable para la suma del dinero final
    $total_dinero_final = 0;

    // Formulario de selección de mes y año
    echo '<div class="p_centrar">';
    echo '<form method="GET" action="">';
    echo '<label for="mes">Mes:</label>';
    echo '<select id="mes" name="mes">';
    for ($i = 1; $i <= 12; $i++) {
        $selected = ($i == $mes) ? 'selected' : '';
        echo '<option value="' . $i . '" ' . $selected . '>' . $nombres_meses[$i] . '</option>';
    }
    echo '</select>';
    echo '<label for="anio">Año:</label>';
    echo '<select id="anio" name="anio">';
    $anio_actual = date('Y');
    for ($i = 2024; $i <= $anio_actual + 10; $i++) {  // Mostrar años desde 2025 hasta 10 años más allá del año actual
        $selected = ($i == $anio) ? 'selected' : '';
        echo '<option value="' . $i . '" ' . $selected . '>' . $i . '</option>';
    }
    echo '</select>';
    echo '<button type="submit">Filtrar</button>';
    echo '</form>';
    
    

    // Mostrar los resultados en una tabla HTML
    echo '<div class="table-container">';
    echo '<table border="1" class="centered-table">';
    echo '<thead><tr><th>Fecha</th><th>Base</th><th>Dinero Final</th><th>Total Recogido</th></tr></thead>';
    echo '<tbody>';

    // Iterar sobre los resultados y mostrar cada fila en la tabla
    foreach ($resultados as $fila) {
        echo '<tr>';
        echo '<td><a href="detalle_fecha2.php?fecha=' . urlencode($fila['fecha']) . '&id=' . urlencode($fila['id']) . '">' . date('Y-m-d', strtotime($fila['fecha'])) . '</a></td>';
        echo '<td> $' . number_format($fila['base'], 0, ',', '.') . '</td>'; // Formato sin decimales, con separadores de miles
        echo '<td> $' . number_format($fila['dinero_final'], 0, ',', '.') . '</td>'; // Formato sin decimales, con separadores de miles
        echo '<td> $' . number_format($fila['total_recogido'], 0, ',', '.') . '</td>'; // Formato sin decimales, con separadores de miles
        echo '</tr>';
        // Sumar el dinero final de la fila actual al total
        $total_dinero_final += $fila['total_recogido'];
    }
    echo '</tbody>';
    echo '</table>';
    echo '</div>'; // Cierre del contenedor de la tabla

    // Mostrar el total del dinero final debajo de la tabla
    echo '<div class="total-dinero-final">';
    echo '<p>Total Dinero Final: ' . number_format($total_dinero_final, 0, ',', '.') . '</p>';
    echo '</div>'; // Cierre del contenedor del total

    // Botón para volver a cerrar_caja.php
    echo '<div class="volver-menu">';
    echo '<a href="' . $ruta_image_menu . '"><button>Volver</button></a>';
    echo '</div>'; // Cierre del contenedor del botón

    echo '</div>'; // Cierre del contenedor p_centrar

    // Incluir archivo de JavaScript y footer
    $ruta_js = "../js/main.js";
    include $ruta_footer;
} else {
    echo "El archivo de template o footer no existe.";
}
?>
