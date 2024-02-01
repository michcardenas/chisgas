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

$id_orden = isset($_GET['id_orden']) ? htmlspecialchars($_GET['id_orden']) : '';
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
            <th>Asignado</th>
            <th>Valor </th>
        </tr>
    </thead>
    <tbody>
        <?php 
        foreach ($arreglos_prendas as $prenda) {
        
        ?>
        <tr>
            <td>
                <p href="detalle_arreglo.php?id=<?php echo htmlspecialchars($prenda['id']); ?>">
                    <?php echo htmlspecialchars($prenda['nombre_ropa']); ?>
        </p>
            </td>
            <td><?php echo htmlspecialchars($prenda['login']); ?>  </td>
            
            <td><?php echo "$" . number_format($prenda['valor'], 0, ',', '.'); ?></td>



        </tr>
        <?php 
        } 
        ?>
    </tbody>
</table>
<div class="container_rosa">
  <div class="card cart">
    <label class="title">Factura  <?php echo "#" . number_format($prenda['id'], 0, ',', '.'); ?></label>
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

        <hr>
        <div class="payments">
          <span>Detalles Pago</span>
          
          <div class="details">
          <br>
            <span>Subtotal:</span>
            <label class="price"><?php echo "$" . number_format($prenda['valor_total'], 0, ',', '.'); ?></label>     
            <span>Abono:</span>
            <label class="price"><?php echo "$" . number_format($prenda['abono'], 0, ',', '.'); ?></label> 
  
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="card checkout">
   
    <label for="total">Total</label>  <h1 class="price"><?php echo "$" . number_format($prenda['saldo'], 0, ',', '.'); ?></h1>
    
  </div>
</div>



        
 
<div class=" flex">
<button id="entrega_total"  class="button">Entrega total &#128722;</button>
<button id="entrega_parcial"  class="button">Entrega parcial  &#9203;</button>

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