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
    $ruta_image_menu = "cerrar_caja.php";
    $ruta_image = "../img/chisgas_fondo_blanco.png";
    include $ruta_template;

    // Construir la URL base para las facturas
    $base_url = 'http://' . $_SERVER['HTTP_HOST'] . '/chisgas/facturas/';
    $dir = $_SERVER['DOCUMENT_ROOT'] . '/chisgas/facturas/';

    // Incluir archivo de estilos CSS para centrar la tabla
    echo '<link rel="stylesheet" type="text/css" href="' . $ruta_css . '">';

    // Iniciar contenedor para centrar el contenido
    echo '<div class="p_centrar">';
    echo '<div class="table-container">';
    echo '<table border="1" class="centered-table">';
    echo '<thead><tr><th>Fecha</th><th>Factura</th><th>Descargar</th></tr></thead>';
    echo '<tbody>';

    // Array para almacenar detalles de facturas
    $facturas = array();

    // Obtener la fecha de hoy en formato Y-m-d para comparar
    $today = date("Y-m-d");

    // Abre el directorio
    if ($handle = opendir($dir)) {
        // Itera sobre cada archivo en el directorio
        while (false !== ($file = readdir($handle))) {
            // Excluye directorios y archivos ocultos
            if ($file != "." && $file != "..") {
                // Obtiene la fecha de modificación (o creación)
                $modification_date = date("Y-m-d", filemtime($dir . $file));

                // Comprueba si la factura fue creada hoy
                if ($modification_date == $today) {
                    // Almacena detalles de factura en el array
                    $modification_time = date("Y-m-d H:i:s", filemtime($dir . $file));
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

    // Cierra la tabla HTML
    echo '</tbody>';
    echo '</table>';

    // Cerrar contenedor de la tabla
    echo '</div>'; // Cierre del contenedor table-container

    // Botón para volver a cerrar_caja.php
    echo '<div class="volver-menu">';
    echo '<a href="' . $ruta_image_menu . '"><button>Volver a Cerrar Caja</button></a>';
    echo '</div>'; // Cierre del contenedor volver-menu
    
    echo '</div>'; // Cierre del contenedor p_centrar

    // Incluir archivo de JavaScript y footer
    $ruta_js = "../js/main.js";
    include $ruta_footer;
} else {
    echo "El archivo de template o footer no existe.";
}
?>
