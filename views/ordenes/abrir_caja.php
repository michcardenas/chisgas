<?php
// Iniciar la sesión
session_start();
date_default_timezone_set('America/Bogota');

// Comprobar si el usuario ha iniciado sesión
if (!isset($_SESSION['username'])) {
    // Si no ha iniciado sesión, redirigir al login
    header("Location: ../login/login.php");
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

// Incluir conexión a la base de datos
include '../../conexion/db_connection.php';
include '../../model/funciones.php';

$mensaje = ''; // Variable para almacenar el mensaje de éxito o error
$base = ''; // Variable para almacenar el valor de la base de caja predeterminada

// Verificar si ya se abrió una caja hoy
$fecha_hoy = date('Y-m-d');
$sql_check = "SELECT * FROM caja WHERE DATE(fecha) = '$fecha_hoy'";
$result_check = mysqli_query($conn, $sql_check);

if (mysqli_num_rows($result_check) > 0) {
    $mensaje = "Ya se ha abierto una caja hoy.";
} else {
    // Obtener el valor del dinero final del día anterior
    $sql_select = "SELECT dinero_final FROM caja WHERE DATE(fecha) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)";
    $result_select = mysqli_query($conn, $sql_select);
    $row_select = mysqli_fetch_assoc($result_select);

    if ($row_select) {
        $base = number_format($row_select['dinero_final'], 0, ',', '.');
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['base'])) {
        // Obtener la base de caja sin formato
        $base = $_POST['base'];
        // Remover cualquier formato de miles para obtener el valor sin formato
        $base = preg_replace('/[^\d.]/', '', $base); // Esto elimina todo excepto dígitos y el punto decimal si lo hubiera

        $fecha = date('Y-m-d H:i:s');

        // Insertar los datos en la tabla caja
        $sql = "INSERT INTO caja (fecha, base) VALUES ('$fecha', '$base')";
        if (mysqli_query($conn, $sql)) {
            $mensaje = "Caja iniciada con éxito con una base de $base el $fecha";
            // Redirigir a caja.php después de la inserción exitosa
            header("Location: caja.php");
            exit();
        } else {
            $mensaje = "Error: " . mysqli_error($conn);
        }

        // Cerrar la conexión a la base de datos
        mysqli_close($conn);
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar Caja</title>
    <link rel="stylesheet" href="<?php echo $ruta_css; ?>">
</head>
<body>
<div class="p_centrar">
    <div class="centrar">
        <div class="container">
            <h1 class="form_heading">Iniciar Caja</h1>

            <?php if (!empty($mensaje)) : ?>
                <p><?php echo $mensaje; ?></p>
            <?php endif; ?>

            <?php if (empty($mensaje)) : ?>
                <form id="caja_form" class="card" method="post">
                    <div class="card_header"></div>

                    <div class="field">
                        <label for="base">Base de Caja:</label>
                        <input class="input" type="text" id="base" name="base" placeholder="$" value="<?php echo isset($_POST['base']) ? number_format($_POST['base'], 0, ',') : $base; ?>" required>
                    </div>

                    <div class="field_boton_editar">
                        <button type="submit" class="button">Iniciar</button>
                        <button type="button" class="button atras" onclick="goBack()">Volver</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    // Función para retroceder en la historia del navegador
    function goBack() {
        window.history.back();
    }

    // Función para formatear el número con separadores de miles mientras se escribe
    document.addEventListener('DOMContentLoaded', function() {
        const baseInput = document.getElementById('base');

        if (dineroFinalInput) { // Verificar si el elemento existe antes de añadir el event listener
        function formatNumber(num) {
            return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        }


        baseInput.addEventListener('input', function(e) {
            let val = this.value.replace(/[^\d]/g, '');
            this.value = formatNumber(val);
        });
    }
    });
</script>

<?php 
$ruta_footer = '../footer.php';
if (file_exists($ruta_footer)) {
    $ruta_js = "../js/main.js";
    include $ruta_footer;
} else {
    echo "El archivo $ruta_footer no existe.";
}
?>
</body>
</html>
