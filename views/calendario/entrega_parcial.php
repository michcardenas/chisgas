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

$id_orden = isset($_GET['idOrden']) ? htmlspecialchars($_GET['idOrden']) : '';
// Este es tu código para obtener los datos
$arreglos_prendas = prendas_por_entregar($id_orden);




?>
<div class="p_centrar">
<div class="centrar">


    
    <?php
    // Asumimos que $arreglos_prendas tiene al menos un resultado
    if (isset($arreglos_prendas[0])) {
        $primer_resultado = $arreglos_prendas[0];
    }
    ?>
       
       <table>
    <thead>
        <tr>
            <th>Prenda</th>
            <th># prendas a entregar</th>
            <th>Valor </th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($arreglos_prendas as $index => $prenda): ?>

        <tr>
            <td>
                <p href="detalle_arreglo.php?id=<?php echo htmlspecialchars($prenda['id']); ?>">
                    <?php echo htmlspecialchars($prenda['nombre_ropa']); ?>
        </p>
            </td>
            <td><input class="input_file" type="number"  name="prendas_numero[<?php echo $prenda['id']; ?>]" placeholder="  <?php echo htmlspecialchars($prenda['prendas_numero']); ?> " min="1" />
 </td>
            
            <td><?php echo "$" . number_format($prenda['valor'], 0, ',', '.'); ?></td>
           
            <input type="hidden" name="id_usuario" id="prendas_numero_real" value=" <?php echo htmlspecialchars($prenda['prendas_numero']); ?>">



        </tr>
        <?php endforeach; ?>

    </tbody>
</table>

<div class="container_rosa">
  <div class="card cart">
    <label class="title">Tiquete  <?php echo "#" . number_format($prenda['id'], 0, ',', '.'); ?></label>
    <div class="steps">
      <div class="step">
        <div>
          <div class="field">
            <label for="nombre_cliente">Nombre</label>
            <p><?php echo htmlspecialchars($primer_resultado['nombre_cliente']); ?> </p>
        </div>
        <div class="field">
            <label for="nombre_cliente">Numero:</label>
            <p><?php echo htmlspecialchars($primer_resultado['telefono_cliente']); ?> </p>
        </div>
        </div>
        <hr>
        <div>
          <span>Metodo de pago: </span>
          <div class="field">

      <select class="input" name="forma_pago" id="forma_pago">
          <option value="efectivo">Efectivo</option>
          <option value="nequi">Nequi</option>
          <option value="daviplata">Daviplata</option>
      </select>
  </div>
        </div>
        <hr>
        <input type="hidden" name="id_orden" id="id_orden" value="<?php echo $id_orden;?>">
        <input type="hidden" name="id_usuario" id="id_usuario" value="<?php echo $_SESSION['username'];?>">
        <input type="hidden" name="telefono_cliente" id="telefono_cliente" value="<?php echo htmlspecialchars($primer_resultado['telefono_cliente']);?>">

        <hr>
        <div class="payments">
          <span>Detalles Pago</span>
          
          <div class="details">
          <br>
            <span>Subtotal:</span>
            <label id="valor_total2" class="price"><?php echo "$" . number_format($prenda['valor_total'], 0, ',', '.'); ?></label>     
            <span>Abono:</span>
            <input  class="input"  value="<?php echo "$" . number_format($prenda['abono'], 0, ',', '.'); ?>" name="abono" type="text"  id="abono2">

          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="card checkout">
   
    <label for="total">Total</label>  <h1 class="price" id="saldo2"><?php echo "$" . number_format($prenda['saldo'], 0, ',', '.'); ?></h1>
    <button id="entrega_parcial_entregar"  class="button">Entregar  &#9203;</button>

  </div>

</div>



</div>
</div>


<?php

$ruta_footer = '../footer.php';

if (file_exists($ruta)) {
    $ruta_css = '../css/style.css';
    $ruta_image = "../img/chisgas_fondo_blanco.png";
    $ruta_js = "../js/main.js";

    include $ruta_footer;
} else {
    echo "El archivo $ruta no existe.";
}

?>
<script  >
    $(document).ready(function(){

        let changingValue = false; // Asegúrate de definir esto fuera de tu función de evento para que tenga un ámbito global

$('#abono2').on('change', function()  {
    if (changingValue) return;

    changingValue = true;

    let value = $(this).val().replace(/[^0-9]/g, '');
    let numericValue = parseInt(value, 10); // Usamos parseInt para asegurarnos de trabajar sin decimales

    // Obtener el valor de valor_total desde el label y limpiarlo para cálculo
    let valorTotalText = $('#valor_total2').text().replace(/[^0-9]/g, '');
    let valorTotal = parseInt(valorTotalText, 10);

    let saldo = valorTotal - numericValue; // Cálculo del saldo

    // Actualizar el saldo en el formato deseado
    $('#saldo2').text("" + saldo.toLocaleString('es-CO', { style: 'currency', currency: 'COP', maximumFractionDigits: 0 }).replace('COP', ''));

    // Volver a formatear el valor introducido al formato de moneda
    $(this).val("" + numericValue.toLocaleString('es-CO', { style: 'currency', currency: 'COP', maximumFractionDigits: 0 }).replace('COP', ''));

    changingValue = false;
});

$("#entrega_parcial_entregar").click(function(e){
    e.preventDefault();

    var id_orden = $("#id_orden").val();
    var id_usuario = $("#id_usuario").val();
    var telefono_cliente = $("#telefono_cliente").val();
    var abono = $("#abono2").val().replace(/[^0-9]/g, ''); // Limpiar el valor para enviar solo números
    var saldo = $("#saldo2").text().replace(/[^0-9]/g, ''); // Limpiar el valor para enviar solo números
    var forma_pago = $("#forma_pago").val();

    var prendas_datos = [];
    var validacionCorrecta = true; // Asunción inicial de que todas las validaciones son correctas

    $(".input_file").each(function() {
        var prenda_id = $(this).attr('name').match(/\[(\d+)\]/)[1];
        var prenda_numero_entregar_input = $(this).val().trim();
        var prenda_numero_entregar = prenda_numero_entregar_input ? parseInt(prenda_numero_entregar_input, 10) : NaN;
        var prenda_numero_real = parseInt($(this).attr('placeholder').trim(), 10);
        var nombre_prenda = $(this).closest('tr').find('td:first').text().trim(); // Asumiendo que el nombre de la prenda está en el primer <td>

        // Verificar si el input está vacío o si el número es NaN
        if (prenda_numero_entregar_input === "" || isNaN(prenda_numero_entregar)) {
            alert(`Debe ingresar el número a entregar o 0 para las prendas o la  prenda ${nombre_prenda}.`);
            validacionCorrecta = false;
            return false; // Salir del bucle .each
        }

        if (prenda_numero_entregar > prenda_numero_real) {
            alert(`En la prenda ${nombre_prenda}, estás intentando entregar un número mayor de prendas al recibido. Puede ser menor o igual.`);
            validacionCorrecta = false;
            return false; // Salir del bucle .each
        }

        prendas_datos.push({
            prenda_id: prenda_id,
            prenda_numero_entregar: prenda_numero_entregar,
            prenda_numero_real: prenda_numero_real
        });
    });

    if (!validacionCorrecta) {
        return; // Detener la ejecución si la validación falló
    }

    console.log(id_orden, id_usuario, telefono_cliente, abono, saldo, forma_pago, prendas_datos); 
    $.ajax({
        type: "POST",
        url: '../../controllers/calendarioController.php',
        data: JSON.stringify({
            action: "entrega_parcial_en",
            id_orden: id_orden,
            id_usuario: id_usuario,
            telefono_cliente: telefono_cliente,
            abono: abono,
            saldo: saldo,
            forma_pago: forma_pago,
            prendas_datos: prendas_datos
        }),
        contentType: "application/json; charset=utf-8",
        dataType: "json",
        success: function(response) {
            if (response.success) {
                alert(response.message);
                // Código para manejar una respuesta exitosa
            } else {
                // Código para manejar errores
                alert(response.message);
            }
        },
        error: function(xhr, status, error) {
            // Si hay un error en la solicitud AJAX
            console.error("Error en AJAX:", status, error);
            alert("Error al actualizar la prenda.");
        }
    });
});



});
</script>