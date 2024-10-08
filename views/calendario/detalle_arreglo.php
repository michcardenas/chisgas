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

// Verificar si la sesión contiene el grupo de usuario
if (!isset($_SESSION['grupo_usuario'])) {
    echo "No se encontró el grupo de usuario.";
    exit();
}

$grupo_usuario = $_SESSION['grupo_usuario']; // Obtener el grupo de usuario de la sesión

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

$id_prenda = isset($_GET['id']) ? htmlspecialchars($_GET['id']) : '';
$ver_arreglo = ver_arreglo($id_prenda);

// Determinar los permisos según el grupo de usuario
$puede_modificar_asignado = ($grupo_usuario == 'caja' || $grupo_usuario == 'administrador');
$puede_modificar_estado = ($grupo_usuario == 'sastre' || $grupo_usuario == 'administrador');
$puede_modificar_todo = ($grupo_usuario == 'administrador');
?>

<div class="p_centrar">
    <div class="centrar">
        <form class="form card">
            <div class="card_header">
                <h1 class="form_heading">Detalle Arreglo</h1>
            </div>

            <?php if ($ver_arreglo) { ?>
                <input type="hidden" name="prenda_id" id="prenda_id" value="<?php echo htmlspecialchars($ver_arreglo['id']); ?>">
                <input type="hidden" name="id_orden" id="id_orden" value="<?php echo htmlspecialchars($ver_arreglo['id_orden']); ?>">

                <div class="field">
                    <label for="nombre_prenda">Nombre prenda: </label>
                    <select class="input" name="nombre_prenda" id="nombre_prenda" <?php echo !$puede_modificar_todo ? 'disabled' : ''; ?>>
                        <option value="">Seleccione</option>
                        <?php
                        $nombres_prenda = ["Camisa", "Camiseta", "Blusa", "Pantalon", "Chaqueta", "Saco", "Sueter", "Falda", "Vestido", "Otro"];
                        foreach ($nombres_prenda as $nombre) {
                            echo '<option value="' . $nombre . '"';
                            if ($ver_arreglo['nombre_ropa'] == $nombre) echo ' selected';
                            echo '>' . $nombre . '</option>';
                        }
                        ?>
                    </select>
                </div>

                <div class="field">
                    <label for="prendas_numero">Numero de prendas: </label>
                    <?php
                    $prendas_numero_restado = $ver_arreglo['prendas_numero'] - $ver_arreglo['cantidad_entregada'];
                    ?>
                    <input class="input" name="prendas_numero" type="text" id="prendas_numero" value="<?php echo htmlspecialchars($prendas_numero_restado); ?>" <?php echo !$puede_modificar_todo ? 'disabled' : ''; ?>>
                </div>

                <div class="field">
                    <label for="descripcion_arreglo">Descripcion Arreglo: </label>
                    <textarea class="input" name="descripcion_arreglo" id="descripcion_arreglo" <?php echo !$puede_modificar_todo ? 'disabled' : ''; ?>><?php echo htmlspecialchars($ver_arreglo['descripcion_arreglo']); ?></textarea>
                </div>

                <div class="field">
                    <label for="valor">Valor: </label>
                    <input class="input" name="valor" type="text" id="valor_prenda" value="$ <?php echo htmlspecialchars(number_format($ver_arreglo['valor'])); ?>" <?php echo !$puede_modificar_todo ? 'disabled' : ''; ?>>
                </div>

                <div class="field">
                    <label for="Asignado">Asignado a :</label>
                    <select class="input" name="Asignado" id="Asignado" <?php echo !$puede_modificar_asignado ? 'disabled' : ''; ?>>
                        <option value="">Seleccione</option>
                        <?php
                        $usuarios = obtener_usuarios();
                        foreach ($usuarios as $usuario) {
                            $selected = ($ver_arreglo && $ver_arreglo['id_asignacion'] == $usuario['id']) ? 'selected' : '';
                            echo "<option value='{$usuario['id']}' $selected>{$usuario['login']}</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="field">
                    <label for="estado_prenda">Estado:</label>
                    <select class="input" id="estado_prenda" name="estado_prenda" <?php echo !$puede_modificar_estado ? 'disabled' : ''; ?>>
                        <option value="1" <?php echo $ver_arreglo['estado'] == 1 ? 'selected' : ''; ?>>Ingresado</option>
                        <option value="4" <?php echo $ver_arreglo['estado'] == 4 ? 'selected' : ''; ?>>En proceso</option>
                        <option value="5" <?php echo $ver_arreglo['estado'] == 5 ? 'selected' : ''; ?>>Arreglado</option>
                    </select>
                </div>
                <div class="progress-container">
                    <div id="progress-bar" class="progress-bar"></div>
                </div>

                <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
                <script>
                    // Función para actualizar la barra de progreso
                    function updateProgressBar(estado) {
                        var percent = 0;
                        var color = '#111111';

                        switch (estado) {
                            case '1': // Ingresado
                                percent = 0;
                                color = '#FF5733'; // Naranja
                                break;
                            case '4': // En proceso
                                percent = 50;
                                color = '#2196F3'; // Azul
                                break;
                            case '5': // Arreglado
                                percent = 100;
                                color = '#4caf50'; // Verde
                                break;
                            default:
                                percent = 0;
                                color = '#f44336'; // Rojo por defecto
                        }

                        $('#progress-bar').css('width', percent + '%');
                        $('#progress-bar').text(percent + '%');
                        $('#progress-bar').css('background-color', color);
                    }

                    // Obtener el estado inicial desde PHP
                    <?php
                    if (isset($ver_arreglo['estado'])) {
                        $estado_actual = $ver_arreglo['estado'];
                        echo "updateProgressBar('$estado_actual');";
                    } else {
                        echo "console.error('Estado no definido');";
                    }
                    ?>

                    // Actualizar la barra de progreso cuando cambie el estado
                    $('#estado_prenda').change(function() {
                        var estadoSeleccionado = $(this).val();
                        updateProgressBar(estadoSeleccionado);
                    });

                    var grupoUsuario = "<?php echo $grupo_usuario; ?>";
                </script>

            <?php } else { ?>
                <p>No se encontraron resultados para esta prenda.</p>
            <?php } ?>
        </form>
        <h1 class="form_heading" id="resultado_editar"></h1>

        <div class="flex">
            <button id="editar_arreglo" class="button">Editar arreglo</button>
        </div>
    </div>
</div>

<?php

$ruta_footer = '../footer.php';

if (file_exists($ruta_footer)) {
    $ruta_css = '../css/style.css';
    $ruta_image = "../img/chisgas_fondo_blanco.png";
    $ruta_js = "../js/main.js";

    include $ruta_footer;
} else {
    echo "El archivo $ruta_footer no existe.";
}

?>
