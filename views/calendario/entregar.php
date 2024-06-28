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
$arreglos_prendas = prendas_por_entregar($id_orden);
$entregas_parciales = entregas_parciales_datos($id_orden);
$entregas_total = total_entrega($id_orden);

// Asegúrate de que $entregas_parciales sea un array
if (is_array($entregas_parciales)) {
    $total_abonos = array_sum(array_column($entregas_parciales, 'abono'));
    $cantidades_por_prenda = [];
    foreach ($entregas_parciales as $entrega) {
        $id_prenda = $entrega['id_prenda'];
        if (!isset($cantidades_por_prenda[$id_prenda])) {
            $cantidades_por_prenda[$id_prenda] = 0;
        }
        $cantidades_por_prenda[$id_prenda] += $entrega['cantidad_entregada'];
    }
} else {
    $total_abonos = 0;
}

// Calcular el subtotal de todas las prendas
$subtotal = 0;
foreach ($arreglos_prendas as $prenda) {
    $subtotal += $prenda['valor'];
}
?>

<div class="p_centrar">
    <div class="centrar">
        <?php if (isset($arreglos_prendas[0])): ?>
            <?php $primer_resultado = $arreglos_prendas[0]; ?>
        <?php endif; ?>
        
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
                    <?php
                    // Calcular la cantidad ajustada de prendas
                    $cantidad_original = $prenda['prendas_numero'];
                    $cantidad_entregada = $cantidades_por_prenda[$prenda['id']] ?? 0;
                    $cantidad_ajustada = max(0, $cantidad_original - $cantidad_entregada);
                    
                    // Si la cantidad ajustada es 0, no generamos la fila
                    if ($cantidad_ajustada > 0):
                    ?>
                    <tr>
                        <td>
                            <p href="detalle_arreglo.php?id=<?php echo htmlspecialchars($prenda['id']); ?>">
                                <?php echo htmlspecialchars($prenda['nombre_ropa']); ?>
                            </p>
                        </td>
                        <td>
                            <input readonly class="input_file" type="number" name="prendas_numero[<?php echo $prenda['id']; ?>]" placeholder="<?php echo $cantidad_ajustada; ?>" min="1" />
                        </td>
                        <td><?php echo "$" . number_format($prenda['valor'], 0, ',', '.'); ?></td>
                        <input type="hidden" name="id_usuario" id="prendas_numero_real" value="<?php echo htmlspecialchars($prenda['prendas_numero']); ?>">
                    </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="container_rosa">
            <div class="card cart">
                <label class="title">Tiquete <?php echo "#" . number_format($prenda['id'], 0, ',', '.'); ?></label>
                <div class="steps">
                    <div class="step">
                        <div>
                            <div class="field">
                                <label for="nombre_cliente">Nombre</label>
                                <p><?php echo htmlspecialchars($primer_resultado['nombre_cliente']); ?></p>
                            </div>
                            <div class="field">
                                <label for="nombre_cliente">Numero:</label>
                                <p><?php echo htmlspecialchars($primer_resultado['telefono_cliente']); ?></p>
                            </div>
                        </div>
                        <hr>
                        <div>
                            <span>Metodo de pago: </span>
                            <div class="field">
                                <select class="input" name="forma_pago" id="forma_pago">
                                    <option value="efectivo">Efectivo</option>
                                    <option value="nequi">Nequi</option>
                                </select>
                            </div>
                        </div>
                        <hr>
                        <input type="hidden" name="id_orden" id="id_orden" value="<?php echo $id_orden; ?>">
                        <input type="hidden" name="valor_total22" id="valor_total22" value="<?php echo $prenda['valor_total'] - $total_abonos - $prenda['abono']; ?>">
                        <input type="hidden" name="valor_total1" id="valor_total1" value="<?php echo $prenda['valor_total']; ?>">
                        <input type="hidden" name="abonos_totales" id="abonos_totales" value="<?php echo number_format($prenda['abono'] + $total_abonos); ?>">
                        <input type="hidden" name="id_usuario" id="id_usuario" value="<?php echo $_SESSION['username']; ?>">
                        <input type="hidden" name="telefono_cliente" id="telefono_cliente" value="<?php echo htmlspecialchars($primer_resultado['telefono_cliente']); ?>">
                        <hr>
                        <div class="payments">
                            <span>Detalles Pago</span>
                            <br>
                            <div class="details">
                                <span>Subtotal:</span>
                                <label id="valor_total2" class="price"><?php echo "$" . number_format($subtotal, 0, ',', '.'); ?></label>
                                <?php if ($prenda['abono'] > 0): ?>
                                    <span>Abonos anteriores:</span>
                                    <input readonly class="input" value="<?php echo "$" . number_format($prenda['abono'] + $total_abonos, 0, ',', '.'); ?>" type="text">
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card checkout">
                <label for="total">Saldo</label>
                <h1 class="price"><?php echo "$" . number_format($prenda['valor_total'] - $total_abonos - $prenda['abono'], 0, ',', '.'); ?></h1>
            </div>
        </div>

        <div class="flex">
            <?php if ($entregas_total): ?>
                <button id="entrega_total" class="button">Entrega total &#128722;</button>
            <?php endif; ?>
            <?php if (count($arreglos_prendas) > 1 || $arreglos_prendas[0]['prendas_numero'] > 1): ?>
                <button id="entrega_parcial" class="button">Entrega parcial o abonos &#9203;</button>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$ruta_footer = '../footer.php';
if (file_exists($ruta_footer)) {
    $ruta_js = "../js/main.js";
    include $ruta_footer;
} else {
    echo "El archivo $ruta_footer no existe.";
}
?>


