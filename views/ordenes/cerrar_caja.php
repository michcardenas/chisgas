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
$base = 0; // Variable para almacenar el valor de la caja cuando se abrió
$encargado = $_SESSION['username']; // Nombre del encargado

// Obtener los datos de la caja abierta hoy
$fecha_hoy = date('Y-m-d');
$sql_select = "SELECT * FROM caja WHERE DATE(fecha) = '$fecha_hoy'";
$result_select = mysqli_query($conn, $sql_select);
$row_select = mysqli_fetch_assoc($result_select);

if ($row_select) {
    $base = $row_select['base'];
    if (!is_null($row_select['gastos']) && !is_null($row_select['dinero_final'])) {
        $mensaje = "<div style='display: flex; justify-content: center; align-items: center; height: 100vh;'>La caja ya ha sido cerrada hoy.</div>";
    }
} else {
    $mensaje = "<div style='display: flex; justify-content: center; align-items: center; height: 100vh;'>No se encontró una caja abierta para hoy.</div>";
}



// Calcular el total de órdenes del día que están arregladas o en entrega parcial
$total_recogido = 0;
$total_efectivo = 0;
$total_nequi = 0;

### 1. Consulta para Órdenes Normales del Día ###
$sql_ordenes_dia = "
SELECT 
    COALESCE(SUM(CASE 
        WHEN o.forma_pago = 'Efectivo' THEN o.saldo - COALESCE(o.abono, 0)
        ELSE 0 
    END), 0) AS total_efectivo,
    
    COALESCE(SUM(CASE 
        WHEN o.forma_pago = 'Nequi' THEN o.saldo - COALESCE(o.abono, 0)
        ELSE 0 
    END), 0) AS total_nequi,
    
    COALESCE(SUM(o.saldo - COALESCE(o.abono, 0)), 0) AS total_recogido
FROM 
    entregas s 
    JOIN ordenes o ON s.orden_id = o.id
WHERE 
    DATE(s.fecha) = CURDATE();
";

$result_ordenes_dia = mysqli_query($conn, $sql_ordenes_dia);

if (!$result_ordenes_dia) {
    // Manejo de errores en la consulta
    $mensaje .= " Error: " . mysqli_error($conn);
} else {
    $row_ordenes_dia = mysqli_fetch_assoc($result_ordenes_dia);
    if ($row_ordenes_dia) {
        $total_recogido = $row_ordenes_dia['total_recogido'];
        $total_efectivo = $row_ordenes_dia['total_efectivo'];
        $total_nequi = $row_ordenes_dia['total_nequi'];
    }
}

### 2. Consulta para Entregas Parciales del Día ###
$sql_abonos_parciales_dia = "
SELECT 
    COALESCE(SUM(CASE 
        WHEN ep.forma_pago = 'Efectivo' THEN ep.abono
        ELSE 0 
    END), 0) AS total_abonos_efectivo,
    
    COALESCE(SUM(CASE 
        WHEN ep.forma_pago = 'Nequi' THEN ep.abono
        ELSE 0 
    END), 0) AS total_abonos_nequi,
    
    COALESCE(SUM(ep.abono), 0) AS total_abonos_parciales
FROM 
    entregas_parciales ep
WHERE 
    DATE(ep.fecha_hora) = CURDATE();
";

$result_abonos_parciales_dia = mysqli_query($conn, $sql_abonos_parciales_dia);

if (!$result_abonos_parciales_dia) {
    // Manejo de errores en la consulta
    $mensaje .= " Error: " . mysqli_error($conn);
} else {
    $row_abonos_parciales_dia = mysqli_fetch_assoc($result_abonos_parciales_dia);
    if ($row_abonos_parciales_dia) {
        // Sumar los abonos parciales a los totales correspondientes
        $total_recogido += $row_abonos_parciales_dia['total_abonos_parciales'];
        $total_efectivo += $row_abonos_parciales_dia['total_abonos_efectivo'];
        $total_nequi += $row_abonos_parciales_dia['total_abonos_nequi'];
    }
}

### 3. Consulta para Abonos de Órdenes por Fecha de Creación ###
$sql_abonos_ordenes_creacion = "
SELECT 
    COALESCE(SUM(CASE 
        WHEN o.forma_pago = 'Efectivo' THEN o.abono
        ELSE 0 
    END), 0) AS total_abonos_efectivo,
    
    COALESCE(SUM(CASE 
        WHEN o.forma_pago = 'Nequi' THEN o.abono
        ELSE 0 
    END), 0) AS total_abonos_nequi,
    
    COALESCE(SUM(o.abono), 0) AS total_abonos_ordenes
FROM 
    ordenes o
WHERE 
    DATE(o.fecha_creacion) = CURDATE();
";

$result_abonos_ordenes_creacion = mysqli_query($conn, $sql_abonos_ordenes_creacion);

if (!$result_abonos_ordenes_creacion) {
    // Manejo de errores en la consulta
    $mensaje .= " Error: " . mysqli_error($conn);
} else {
    $row_abonos_ordenes_creacion = mysqli_fetch_assoc($result_abonos_ordenes_creacion);
    if ($row_abonos_ordenes_creacion) {
        // Sumar los abonos de órdenes por fecha de creación a los totales correspondientes
        $total_recogido += $row_abonos_ordenes_creacion['total_abonos_ordenes'];
        $total_efectivo += $row_abonos_ordenes_creacion['total_abonos_efectivo'];
        $total_nequi += $row_abonos_ordenes_creacion['total_abonos_nequi'];
    }
}

$dinero_final_calculado = $base + $total_recogido; // Calculo inicial del dinero final sin los gastos

// Procesar el formulario de cierre de caja
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['dinero_final']) && empty($mensaje)) {
    // Verificar que la hora actual sea después de las 12:00 PM
    $hora_actual = date('H:i:s');
    if ($hora_actual < '12:00:00') {
        $mensaje = "La caja solo se puede cerrar después de las 12:00 PM.";
    } else {
        $dinero_final = $_POST['dinero_final'];
        $gastos = isset($_POST['gastos']) ? $_POST['gastos'] : ''; // Obtener gastos o establecer como cadena vacía si no se proporcionan

        // Calcular la suma total de los gastos
        $total_gastos = 0;
        if (!empty($gastos)) {
            $gastos_array = explode("\n", $gastos);
            foreach ($gastos_array as $gasto) {
                $total_gastos += preg_replace('/[^\d.]/', '', $gasto);
            }
        }

        $dinero_final_calculado -= $total_gastos; // Actualizar el dinero final restando los gastos

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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>
<body>
<div class="p_centrar">
    <div class="centrar">
        <div class="container">
        <div class="centrarcaja">
            <h1>Cerrar Caja</h1>
            <div class="centrarcaja1">
            <h1>Detalles:</h1>
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
                        <th>Total Efectivo:</th>
                        <td>$<?php echo number_format($total_efectivo); ?></td>
                    </tr>
                    <tr>
                        <th>Total Nequi:</th>
                        <td>$<?php echo number_format($total_nequi); ?></td>
                    </tr>
                    <tr>
                        <th>Base de Caja:</th>
                        <td>$<?php echo number_format($base, 0, ',', '.'); ?></td>
                    </tr>
                </table>
            <?php endif; ?>

            <?php if (empty($mensaje) || $mensaje == "La caja solo se puede cerrar después de las 12:00 PM.") : ?>
                <form id="cerrar_caja_form" class="card" method="post">
                    <div class="card_header"></div>

                  <!-- Botón para abrir el modal -->
<!-- Botón para abrir el modal -->
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#gastoModal">
        Agregar Gasto
        </button>

<!-- Modal -->
<div class="modal fade" id="gastoModal" tabindex="-1" aria-labelledby="gastoModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="gastoModalLabel">Agregar Gasto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="gastoForm">
                    <div class="mb-3">
                        <label for="titulo_gasto" class="form-label">Título del Gasto</label>
                        <input type="text" class="form-control" id="titulo_gasto" required>
                    </div>
                    <div class="mb-3">
                        <label for="monto_gasto" class="form-label">Monto del Gasto</label>
                        <input type="number" class="form-control" id="monto_gasto" required min="0" step="0.01">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" id="agregarGastoBtn">Agregar Gasto</button>
            </div>
        </div>
    </div>
</div>

<!-- Textarea para mostrar los gastos -->
<div class="field">
    <label for="gastos">Gastos:</label>
    <textarea class="input" id="gastos" name="gastos" placeholder="Gastos" required readonly></textarea>
</div>

<!-- Input para la suma total de gastos -->
<div class="field">
    <label for="suma_gastos">Suma Total de Gastos:</label>
    <input class="input" type="text" id="suma_gastos" name="suma_gastos" placeholder="$" readonly>
</div>

                    <div class="field">
                        <label for="dinero_final">Dinero Final en Caja</label>
                        <input class="input" type="text" id="dinero_final" name="dinero_final" value="<?php echo number_format($dinero_final_calculado, 0, ',', '.'); ?>" required readonly>
                    </div>

                    <div class="field_boton_editar">
                        <button type="submit" class="button">Cerrar Caja</button>
                        <button type="submit" formaction="facturas_dia.php" class="button">Facturas del Día</button>
                        <button type="button" class="button atras" onclick="goBack()">Volver</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/js/bootstrap.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const dineroFinalInput = document.getElementById('dinero_final');
    const sumaGastosInput = document.getElementById('suma_gastos');
    const gastosTextarea = document.getElementById('gastos');
    const gastoForm = document.getElementById('gastoForm');
    const agregarGastoBtn = document.getElementById('agregarGastoBtn');
    const gastoModal = new bootstrap.Modal(document.getElementById('gastoModal'));

    let totalGastos = 0;
    let gastos = [];

    function formatNumber(num) {
        return Math.round(num).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }

    function parseFormattedNumber(str) {
        return parseInt(str.replace(/,/g, ''), 10);
    }

    function updateGastosDisplay() {
        gastosTextarea.value = gastos.map(gasto => `${gasto.titulo}: $${formatNumber(gasto.monto)}`).join('\n');
        sumaGastosInput.value = `$${formatNumber(totalGastos)}`;
        
        // Actualizar el dinero final restando los gastos
        let dineroFinalOriginal = parseFormattedNumber(dineroFinalInput.getAttribute('data-original-value') || dineroFinalInput.value);
        let dineroFinalActualizado = dineroFinalOriginal - totalGastos;
        dineroFinalInput.value = formatNumber(dineroFinalActualizado);
    }

    function addGasto() {
        const titulo = document.getElementById('titulo_gasto').value.trim();
        const monto = parseFloat(document.getElementById('monto_gasto').value);

        if (titulo && !isNaN(monto) && monto > 0) {
            const montoRedondeado = Math.round(monto);
            gastos.push({ titulo, monto: montoRedondeado });
            totalGastos += montoRedondeado;

            updateGastosDisplay();
            gastoModal.hide();
            gastoForm.reset();
        } else {
            alert('Por favor, ingrese un título válido y un monto mayor que cero.');
        }
    }

    agregarGastoBtn.addEventListener('click', addGasto);

    gastoForm.addEventListener('submit', function(event) {
        event.preventDefault();
        addGasto();
    });
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
