<?php
// Iniciar la sesión
session_start();

// Comprobar si el usuario ha iniciado sesión
if (!isset($_SESSION['username'])) {
    // Si no ha iniciado sesión, redirigir al login
    header("Location: login/login.php");
    exit();
}

date_default_timezone_set('America/Bogota');

// Obtener el año y el mes del formulario, si se han enviado
$selected_year = isset($_POST['year']) ? $_POST['year'] : date('Y');
$selected_month = isset($_POST['month']) ? $_POST['month'] : date('m');

// Rutas para los archivos incluidos
$ruta_template = '../template.php';
$ruta_footer = '../footer.php';

// Verificar si los archivos existen
if (file_exists($ruta_template) && file_exists($ruta_footer)) {
    // Incluir archivos de template y footer
    $ruta_css = '../css/style.css';
    $ruta_icon = '../img/aguja.png';
    $ruta_image_menu = "caja.php";
    $ruta_image = "../img/chisgas_fondo_blanco.png";
    include $ruta_template;
    
    $base_url = 'https://sastreriachisgas.shop/facturas/';
    $dir = $_SERVER['DOCUMENT_ROOT'] . '/facturas';
    // Construir la URL base para las facturas
    // $base_url = 'http://' . $_SERVER['HTTP_HOST'] . '/chisgas/facturas/';
    // $dir = $_SERVER['DOCUMENT_ROOT'] . '/chisgas/facturas/';

    // Incluir archivo de estilos CSS para centrar la tabla
    echo '<link rel="stylesheet" type="text/css" href="' . $ruta_css . '">';

    // Array de nombres de meses en español
    $meses = array(
        '01' => 'Enero',
        '02' => 'Febrero',
        '03' => 'Marzo',
        '04' => 'Abril',
        '05' => 'Mayo',
        '06' => 'Junio',
        '07' => 'Julio',
        '08' => 'Agosto',
        '09' => 'Septiembre',
        '10' => 'Octubre',
        '11' => 'Noviembre',
        '12' => 'Diciembre'
    );

    // Agregar formulario de selección
    echo '<div class="p_centrar">';
    echo '<div class="filter-form">';
    echo '<form method="post" action="">';
    echo '<label for="year">Año:</label>';
    echo '<select name="year" id="year">';
    for ($i = 2020; $i <= date('Y'); $i++) {
        $selected = ($i == $selected_year) ? 'selected' : '';
        echo '<option value="' . $i . '" ' . $selected . '>' . $i . '</option>';
    }
    echo '</select>';

    echo '<label for="month">Mes:</label>';
    echo '<select name="month" id="month">';
    foreach ($meses as $num => $nombre) {
        $selected = ($num == $selected_month) ? 'selected' : '';
        echo '<option value="' . $num . '" ' . $selected . '>' . $nombre . '</option>';
    }
    echo '</select>';

    echo '<button type="submit">Filtrar</button>';
    echo '</form>';
    echo '</div>'; // Cierre de filter-form

    // Iniciar contenedor para centrar el contenido
    echo '<div class="table-container">';
    echo '<table border="1" class="centered-table">';
    echo '<thead><tr><th>Fecha</th><th>Factura</th><th>Descargar</th></tr></thead>';
    echo '<tbody>';

    // Verificar si el directorio existe
    if (is_dir($dir)) {
        // Abre el directorio
        if ($handle = opendir($dir)) {
            // Array para almacenar detalles de facturas
            $facturas = array();

            // Itera sobre cada archivo en el directorio
            while (false !== ($file = readdir($handle))) {
                // Excluye directorios y archivos ocultos
                if ($file != "." && $file != "..") {
                    // Obtiene la fecha y hora de modificación (o creación)
                    $modification_time = date("Y-m-d H:i:s", filemtime($dir . '/' . $file));
                    $file_year = date("Y", filemtime($dir . '/' . $file));
                    $file_month = date("m", filemtime($dir . '/' . $file));

                    // Filtra por año y mes seleccionados
                    if ($file_year == $selected_year && $file_month == $selected_month) {
                        // Almacena detalles de factura en el array
                        $facturas[] = array(
                            'fecha' => $modification_time,
                            'factura' => $file
                        );
                    }
                }
            }
            closedir($handle);

            // Ordena las facturas por fecha ascendente
            usort($facturas, function($a, $b) {
                return strtotime($a['fecha']) - strtotime($b['fecha']);
            });

            // Imprime las filas ordenadas
            foreach ($facturas as $factura) {
                echo '<tr>';
                echo '<td>' . $factura['fecha'] . '</td>';
                echo '<td>' . $factura['factura'] . '</td>';
                echo '<td><a href="' . $base_url . $factura['factura'] . '" download>Descargar</a></td>';
                echo '</tr>';
            }
        } else {
            echo '<tr><td colspan="3">No se pudo abrir el directorio de facturas.</td></tr>';
        }
    } else {
        echo '<tr><td colspan="3">El directorio de facturas no existe.</td></tr>';
    }

    // Cierra la tabla HTML
    echo '</tbody>';
    echo '</table>';

    // Botón para volver a cerrar_caja.php
    echo '<div class="volver-menu">';
    echo '<a href="' . $ruta_image_menu . '"><button>Atrás</button></a>';
    echo '</div>';
    echo '</div>'; // Cierre de table-container
    echo '</div>'; // Cierre de p_centrar
}
?>
