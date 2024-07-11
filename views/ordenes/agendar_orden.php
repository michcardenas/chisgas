<?php
// Iniciar la sesión
session_start();

// Comprobar si el usuario ha iniciado sesión
if (!isset($_SESSION['username'])) {
    // Si no ha iniciado sesión, redirigir al login
    header("Location: login/login.php");
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
$total_prendas = 0;
$valor_total = 0;

if(isset($_SESSION['cliente_consultar'])) {
  $cliente_consultar = $_SESSION['cliente_consultar'];
  
  foreach($cliente_consultar as $cliente) {
    $cliente_nombre = $cliente['nombre'];
    $telefono = $cliente['telefono'];
    $cliente_id = $cliente['cliente_id'];
    $nombre_ropa = $cliente['nombre_ropa'];
    $descripcion_arreglo = $cliente['descripcion_arreglo'];
    $tiempo_estimado = $cliente['tiempo_estimado'];
    $valor = $cliente['valor'];
    $prendas_numero = $cliente['prendas_numero'];
    $prenda_id = $cliente['prenda_id'];
    $total_prendas += $cliente['prendas_numero'];
    $valor_total += $cliente['valor'];
  
  }
}



?>

<div class="p_centrar">

<div class="centrar">


  <form class="form card" >
    <div class="card_header">
      <h1 class="form_heading">Detalle cliente</h1>
      <input type="hidden" name="cliente_id" id="cliente_id" value="<?php echo isset($_GET['id']) ? htmlspecialchars($_GET['id']) : ''; ?>">

    </div>
    <div class="field">
      <label for="nombre_cliente">Nombre</label>
      <input class="input readonly" readonly name="nombre_cliente" type="text" placeholder="nombre" id="nombre_cliente" value="<?php echo isset($cliente_nombre) ? htmlspecialchars($cliente_nombre) : ''; ?>">
    </div>
    <div class="field">
      <label for="telefono_cliente">Telefono</label>
      <input class="input" readonly name="telefono_cliente" type="number" placeholder="telefono" id="telefono_cliente" value="<?php echo isset($telefono) ? htmlspecialchars($telefono) : ''; ?>">
    </div>

    <div class="content_loader"><div class="loader"></div></div>
    <h1 class="form_heading" id="resultado_editar"></h1>

  </form>

  <table>
    
        <tbody>
        <?php
if (isset($_SESSION['cliente_consultar'])) {
    $cliente_consultar = $_SESSION['cliente_consultar'];
    foreach ($cliente_consultar as $cliente) {
        $imagen_card = '';
        if ($cliente['nombre_ropa'] == 'Pantalon') {
            $imagen_card = '<img src="../img/pantalon.png" alt="pantalon" class="img_card">';
        } elseif ($cliente['nombre_ropa'] == 'Camisa') {
            $imagen_card = '<img src="../img/camisa.png" alt="camisa" class="img_card">';
        } elseif ($cliente['nombre_ropa'] == 'Chaqueta') {
            $imagen_card = '<img src="../img/chaqueta.png" alt="chaqueta" class="img_card">';
        } elseif ($cliente['nombre_ropa'] == 'Saco') {
            $imagen_card = '<img src="../img/saco.png" alt="saco" class="img_card">';
        } elseif ($cliente['nombre_ropa'] == 'Falda') {
            $imagen_card = '<img src="../img/falda.png" alt="falda" class="img_card">';
        } elseif ($cliente['nombre_ropa'] == 'Vestido') {
            $imagen_card = '<img src="../img/vestido.png" alt="vestido" class="img_card">';
        } elseif ($cliente['nombre_ropa'] == 'Otro') {
            $imagen_card = '<img src="../img/otro.png" alt="otro" class="img_card">';
        }elseif ($cliente['nombre_ropa'] == 'Sueter') {
            $imagen_card = '<img src="../img/sueter.png" alt="sueter" class="img_card">';
        }elseif ($cliente['nombre_ropa'] == 'Camiseta') {
            $imagen_card = '<img src="../img/camiseta.png" alt="camiseta" class="img_card">';
        }elseif ($cliente['nombre_ropa'] == 'Blusa') {
            $imagen_card = '<img src="../img/blusa.png" alt="blusa" class="img_card">';
        } else {
            $imagen_card = '<img src="../img/otro.png" alt="otro" class="img_card">';
        }

        echo '<div class="card">';
        echo '  <div class="card-img">' . $imagen_card . '</div>';
        echo '  <div class="card-title">' . htmlspecialchars($cliente['nombre_ropa']) . ' <br> Cantidad: <b>' . htmlspecialchars($cliente['prendas_numero']) . ' </b></div>';
        echo '  <div class="card-subtitle">' . htmlspecialchars($cliente['descripcion_arreglo']) . '</div>';
        echo '  <hr class="card-divider">';
        echo '    <div class="card-price">';
        echo '      <span>$</span>';
        echo '      <input type="text" class="input valor_actualizado"   style="width: 70%; text-align: center;border: ' . (htmlspecialchars($cliente['valor']) == 0 ? '1px solid red' : '1px solid #ccc') . ';" value="' . number_format(htmlspecialchars($cliente['valor'])) . '">';
        echo '<div class="loader_caset"></div>';
        echo '    </div>';
        echo '  <div class="card-footer">';
       
        echo '    <button  class="card-btn actualizar_valor" data-id="' . htmlspecialchars($cliente['prenda_id']) . '" data-cliente_id="' . htmlspecialchars($cliente['cliente_id']) . '">';
        echo '      💲';  
        echo '    </button>';
        echo '    <button class="card-btn btn-delete" data-id="' . htmlspecialchars($cliente['prenda_id']) . '" data-cliente_id="' . htmlspecialchars($cliente['cliente_id']) . '">';
        echo '      <img class="icon-trash" src="../img/basura.png">';  
        echo '    </button>';
        echo '    <button class="card-btn btn-edit" data-id="' . htmlspecialchars($cliente['prenda_id']) . '" data-cliente_id="' . htmlspecialchars($cliente['cliente_id']) . '">';
        echo '      <img class="icon-trash" src="../img/lapiz.png">';
        echo '    </button>';
        echo '</div>';
        echo '</div>';
        
    }
}
?> 

<h1 class="form_heading">Ahora vamos agendar!</h1>
<p>Sigue bajando 🢃</p>

</tbody>
</table>
<tr>

<form id="calendario_form" style="display: flex; justify-content: space-between;" onsubmit="guardarDatosYConsultar(event)">
    <a href="../calendario/calendario.php" class="button" style="display: flex; align-items: center;">
        <span style="margin-right: 5px;">&#128197;</span>
        <span>Consultar Calendario</span>
    </a>
</form>


<form id="orden_form" class="card">
    <div class="card_header"></div>

    <!-- Campos de formulario para la orden -->
    <div class="field">
        <label for="fecha_entrega">Ingresa Fecha de entrega: </label>
        <input class="input" name="fecha_entrega" type="date" id="fecha_entrega">
    </div>

    <div class="field">
        <label for="franja_horaria">Franja Horaria</label>
        <select class="input" name="franja_horaria" id="franja_horaria">
            <option value="PM">PM</option>
            <option value="AM">AM</option>
        </select>
    </div>

    <div class="field">
    <label for="total_prendas">Total de Prendas</label>
    <input class="input" name="total_prendas" type="number" id="total_prendas" value="<?php echo $total_prendas; ?>" readonly>
</div>


    <div class="field">
        <label for="valor_total">Valor Total</label>
        <input readonly class="input input_readonly" name="valor_total" type="text" id="valor_total" value="$ <?php echo number_format($valor_total); ?>">
    </div>

    <div class="field">
        <label for="abono">Abono</label>
        <input class="input" name="abono" type="text" id="abono">
    </div>

    <div class="field">
        <label for="forma_pago">Forma de Pago</label>
        <select class="input" name="forma_pago" id="forma_pago">
            <option value="efectivo">Efectivo</option>
            <option value="nequi">Nequi</option>
        </select>
    </div>

    <div class="field">
        <label for="saldo">Saldo</label>
        <input readonly class="input input_readonly" name="saldo" type="text" id="saldo">
    </div>


<div class="field_boton_editar">
    <button value="generar_orden" id="generar_orden" class="button">Generar Orden &#10133;</button>
    <button onclick="goBack()" class="button atras">Atras</button>
</div>

<script>
// Función para guardar los datos y consultar el calendario
function guardarDatosYConsultar(event) {
            event.preventDefault(); // Prevenir el comportamiento predeterminado del formulario

            const totalPrendas = document.getElementById('total_prendas').value;
            const formData = {
                fecha_entrega: document.getElementById('fecha_entrega').value,
                franja_horaria: document.getElementById('franja_horaria').value,
                total_prendas: totalPrendas,
                abono: document.getElementById('abono').value,
                forma_pago: document.getElementById('forma_pago').value
                // Agrega más campos según sea necesario
            };

            // Almacena los datos en localStorage usando el número de prendas como clave
            localStorage.setItem(`ordenData_${totalPrendas}`, JSON.stringify(formData));
            
            // Establece una bandera en localStorage para mostrar el botón "Volver"
            localStorage.setItem('mostrarVolver', 'true');

            // Redirigir al calendario
            window.location.href = '../calendario/calendario_vista.php';
        }

        // Cargar datos almacenados al cargar la página
        document.addEventListener('DOMContentLoaded', () => {
            const totalPrendas = document.getElementById('total_prendas').value;
            const storedData = localStorage.getItem(`ordenData_${totalPrendas}`);
            if (storedData) {
                const data = JSON.parse(storedData);
                document.getElementById('fecha_entrega').value = data.fecha_entrega;
                document.getElementById('franja_horaria').value = data.franja_horaria;
                document.getElementById('abono').value = data.abono;
                document.getElementById('forma_pago').value = data.forma_pago;
                // Carga más campos según sea necesario
            }

            // Verificar si la bandera está establecida y mostrar el botón "Volver" si es necesario
            if (localStorage.getItem('mostrarVolver') === 'true') {
                document.getElementById('volver_form').style.display = 'inline';
                localStorage.removeItem('mostrarVolver'); // Limpiar la bandera después de usar
            }
        });

        // Función para limpiar datos de la orden actual del localStorage si es necesario
        function limpiarDatos() {
            const totalPrendas = document.getElementById('total_prendas').value;
            localStorage.removeItem(`ordenData_${totalPrendas}`);
        }

        // Función para actualizar el saldo
        function actualizarSaldo() {
            const valorTotal = parseFloat(document.getElementById('valor_total').value.replace('$', '').replace(',', ''));
            const abono = parseFloat(document.getElementById('abono').value);
            if (!isNaN(valorTotal) && !isNaN(abono)) {
                const saldo = valorTotal - abono;
                document.getElementById('saldo').value = `$ ${saldo.toLocaleString()}`;
            }
        }

        // Evento cuando se carga la página para escuchar cambios en el abono y actualizar el saldo
        document.addEventListener('DOMContentLoaded', () => {
            const abonoInput = document.getElementById('abono');
            if (abonoInput) {
                abonoInput.addEventListener('input', actualizarSaldo);
            }
        });
    </script>
</script>

</form>

</div>

</div>
<?php 
$ruta_footer = '../footer.php';

if (file_exists($ruta)) {
   
    $ruta_js = "../js/main.js";

    include $ruta_footer;
} else {
    echo "El archivo $ruta no existe.";
}

?>