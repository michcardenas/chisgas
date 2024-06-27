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
$base = ''; // Variable para almacenar el valor de la caja cuando se abrió
$encargado = $_SESSION['username']; // Nombre del encargado

// Obtener los datos de la caja abierta hoy
$fecha_hoy = date('Y-m-d');
$sql_select = "SELECT * FROM caja WHERE DATE(fecha) = '$fecha_hoy'";
$result_select = mysqli_query($conn, $sql_select);
$row_select = mysqli_fetch_assoc($result_select);

if ($row_select) {
    $base = number_format($row_select['base'], 0, ',', '.');
    if (!is_null($row_select['gastos']) && !is_null($row_select['dinero_final'])) {
        $mensaje = "La caja ya ha sido cerrada hoy.";
    }
} else {
    $mensaje = "No se encontró una caja abierta para hoy.";
}

// Calcular el total de órdenes del día que están arregladas o en entrega parcial
$total_recogido = 0;

$sql_ordenes_dia = "SELECT SUM(valor_total) AS total 
                    FROM ordenes 
                    WHERE DATE(fecha_entrega) = '$fecha_hoy' 
                    AND (estado = '6' OR estado = '7')";

$result_ordenes_dia = mysqli_query($conn, $sql_ordenes_dia);

if (!$result_ordenes_dia) {
    // Manejo de errores en la consulta
    $mensaje .= " Error: " . mysqli_error($conn);
} else {
    $row_ordenes_dia = mysqli_fetch_assoc($result_ordenes_dia);
    if ($row_ordenes_dia['total']) {
        $total_recogido = $row_ordenes_dia['total'];
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['gastos']) && isset($_POST['dinero_final']) && empty($mensaje)) {
    // Verificar que la hora actual sea después de las 12:00 PM
    $hora_actual = date('H:i:s');
    if ($hora_actual < '12:00:00') {
        $mensaje = "La caja solo se puede cerrar después de las 12:00 PM.";
    } else {
        $gastos = $_POST['gastos'];
        $dinero_final = preg_replace('/[^\d.]/', '', $_POST['dinero_final']); // Remover formato de miles
        $total_gastos = 0;

        // Calcular la suma total de los gastos
        $gastos_array = explode("\n", $gastos);
        foreach ($gastos_array as $gasto) {
            $total_gastos += preg_replace('/[^\d.]/', '', $gasto);
        }

        $dinero_final_calculado = $dinero_final - $total_gastos;

        $fecha = date('Y-m-d H:i:s');

        if ($row_select) {
            $id = $row_select['id'];
            // Actualizar los datos en la tabla caja
            $sql_update = "UPDATE caja SET gastos='$gastos', dinero_final='$dinero_final_calculado', total_recogido='$total_recogido' WHERE id='$id'";
            if (mysqli_query($conn, $sql_update)) {
                $mensaje = "Caja cerrada con éxito el $fecha";
            } else {
                $mensaje = "Error: " . mysqli_error($conn);
            }
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
    <title>Cerrar Caja</title>
    <link rel="stylesheet" href="<?php echo $ruta_css; ?>">
</head>
<body>
<div class="p_centrar">
    <div class="centrar">
        <div class="container">
            <h1 class="form_heading">Cerrar Caja</h1>

            <?php if (!empty($mensaje)) : ?>
                <p><?php echo $mensaje; ?></p>
            <?php endif; ?>

            <?php if ($row_select && empty($mensaje)) : ?>
                <table class="card">
                    <tr>
                        <th>Encargado:</th>
                        <td><?php echo $encargado; ?></td>
                    </tr>
                    <tr>
                        <th>Fecha:</th>
                        <td><?php echo date('Y-m-d'); ?></td>
                    </tr>
                    <tr>
                        <th>Total Recogido del Día:</th>
                        <td>$<?php echo number_format($total_recogido); ?></td>
                    </tr>
                    <tr>
                        <th>Base de Caja:</th>
                        <td>$<?php echo $base; ?></td>
                    </tr>
                </table>
            <?php endif; ?>

            <?php if (empty($mensaje) || $mensaje == "La caja solo se puede cerrar después de las 12:00 PM.") : ?>
            <form id="cerrar_caja_form" class="card" method="post">
                <div class="card_header"></div>

                <div class="field">
                    <label for="gasto">Agregar Gasto:</label>
                    <input class="input" type="text" id="gasto" name="gasto" placeholder="Descripción del gasto">
                    <button type="button" id="add_gasto_button" class="button">Agregar Gasto</button>
                </div>

                <div class="field">
                    <label for="gastos">Gastos:</label>
                    <textarea class="input" id="gastos" name="gastos" placeholder="Gastos" required readonly></textarea>
                </div>

                <div class="field">
                    <label for="suma_gastos">Suma Total de Gastos:</label>
                    <input class="input" type="text" id="suma_gastos" name="suma_gastos" placeholder="$" readonly>
                </div>

                <div class="field">
                    <label for="dinero_final">Dinero Final en Caja:</label>
                    <input class="input" type="text" id="dinero_final" name="dinero_final" placeholder="$" required>
                </div>

                <div class="field_boton_editar">
                    <button type="submit" class="button">Cerrar Caja</button>
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
        const dineroFinalInput = document.getElementById('dinero_final');
        const sumaGastosInput = document.getElementById('suma_gastos');
        const gastosTextarea = document.getElementById('gastos');
        const gastoInput = document.getElementById('gasto');
        const addGastoButton = document.getElementById('add_gasto_button');

        function formatNumber(num) {
            return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        }

        if (dineroFinalInput) { // Verificar si el elemento existe antes de añadir el event listener
            dineroFinalInput.addEventListener('input', function(e) {
                let val = this.value.replace(/[^\d]/g, '');
                this.value = formatNumber(val);
            });
        }

        // Función para actualizar la suma total de gastos
        function actualizarGastos() {
            const gastos = gastosTextarea.value.split('\n');
            let totalGastos = 0;
            gastos.forEach(gasto => {
                totalGastos += parseFloat(gasto.replace(/[^\d.]/g, '')) || 0;
            });
            sumaGastosInput.value = formatNumber(totalGastos);
        }

        // Función para agregar un gasto a la lista de gastos
        addGastoButton.addEventListener('click', function() {
            const gasto = gastoInput.value.trim();
            if (gasto) {
                const currentGastos = gastosTextarea.value;
                gastosTextarea.value = currentGastos ? currentGastos + '\n' + gasto : gasto;
                gastoInput.value = '';
                actualizarGastos();
            }
        });

        // Actualizar la suma de los gastos cuando se modifique
        gastosTextarea.addEventListener('input', actualizarGastos);
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
