<?php
// Iniciar la sesión
session_start();

// Incluir el archivo del modelo SastreModel y la conexión a la base de datos
include '../conexion/db_connection.php';
include '../model/sastreModel.php';

// Comprobar si el usuario ha iniciado sesión
if (!isset($_SESSION['username']) || !isset($_SESSION['id'])) {
    // Si no ha iniciado sesión, redirigir al login
    header("Location: login/login.php");
    exit();
}

// Obtener el ID del usuario desde la sesión
$id_usuario = $_SESSION['id'];

// Crear una instancia de SastreModel
$model = new SastreModel($conn);

// Obtener los arreglos del sastre filtrados por id_usuario
$sastreData = $model->obtenerArreglosSastre($id_usuario);

$ruta = 'template.php';
if (file_exists($ruta)) {
    $ruta_css = '../views/css/style.css';
    $ruta_icon = '../views/img/aguja.png';
    $ruta_cerrar_sesion = 'login/cerrar_sesion.php';
    $ruta_image_menu = 'menu.php';
    $ruta_image = "img/chisgas_fondo_blanco.png";
    include $ruta;
} else {
    echo "El archivo $ruta no existe.";
}

function convertirMinutosAHoras($minutos) {
    $horas = floor($minutos / 60);
    $minRestantes = $minutos % 60;
    
    if ($horas > 0) {
        return "{$horas}h {$minRestantes}min";
    } else {
        return "{$minRestantes}min";
    }
}
?>
<div class="p_centrar">
    <h2>Calendario de Arreglos de Prendas</h2>
    <p>Bienvenido, <?php echo htmlspecialchars($_SESSION['username']); ?></p>
    <div class="select" style="display: flex; flex-direction: column; justify-content: space-evenly; align-items: center; margin-top:10px;">
        <label for="Buscar por estado">Buscar por estado</label>
        <select class="select-container" id="estadoPrendaSelect1" style="margin:10px;">
            <option value="">Seleccione</option>
            <option value="3">Pendiente</option>
            <option value="5">Arreglado</option>
            <option value="all">Todos</option>
        </select>
        <button id="botonAtras" class="button-buscar_orden">
            <span style="margin-right: 5px;">&#8592;</span>
            <span>Atrás</span>
        </button>
    </div>
    <div id="resultados" style="display: flex; justify-content: center;"></div>
    
    <table id="calendarioArreglosTabla" border="1">
        <thead>
            <tr>
                <th>Nombre del Cliente</th>
                <th>Nombre de la Prenda</th>
                <th>Valor</th> <!-- Columna para el valor -->
            </tr>
        </thead>
        <tbody>
            <?php foreach ($sastreData as $arreglo): ?>
            <tr>
                <td><?php echo htmlspecialchars($arreglo["nombre_cliente"]); ?></td>
                <td>
                    <?php
                    $idPrenda = htmlspecialchars($arreglo["id_prenda"]);
                    $nombreRopa = htmlspecialchars($arreglo["nombre_ropa"]);
                    echo '<a href="#" onclick="verCalendario(' . $idPrenda . ')">' . $nombreRopa . '</a>';
                    ?>
                </td>
                <td>
                    <?php
                    $valor = htmlspecialchars($arreglo["valor"]); // Valor de la prenda
                    echo $valor; // Imprimir el valor en la columna correspondiente
                    ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
function verCalendario(idPrenda) {
    $.ajax({
        url: '../controllers/sastreController.php',
        type: 'GET',
        data: {
            'action': 'ver_arreglo',
            'id_prenda': idPrenda
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                // Almacena los datos de la prenda en sessionStorage
                sessionStorage.setItem('detalleArreglo', JSON.stringify(response.data));

                // Redirigir a la vista de detalle
                window.location.href = "../views/calendario/detalle_arreglo.php?id=" + idPrenda;
            } else {
                alert(response.message);
            }
        },
        error: function() {
            alert("Ocurrió un error al obtener los datos del arreglo.");
        }
    });
}


$(document).ready(function() {
    // Verificar si ya hay datos en sessionStorage
    const sastreData = sessionStorage.getItem('sastreData');
    
    if (sastreData) {
        // Si existen datos, actualiza la vista del calendario con esos datos
        const parsedData = JSON.parse(sastreData);
        actualizarVistaCalendario1(parsedData);
    }
});

</script>

<?php 
$ruta_footer = 'footer.php';

if (file_exists($ruta_footer)) {
    $ruta_js = "js/main.js";
    include $ruta_footer;
} else {
    echo "El archivo $ruta_footer no existe.";
}
?>
