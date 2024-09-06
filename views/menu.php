<?php
// Iniciar la sesión
session_start();

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
$id_usuario = $_SESSION['id']; // Obtener el grupo de usuario de la sesión

$ruta = 'template.php';

if (file_exists($ruta)) {
    $ruta_css = '../views/css/style.css';
    $ruta_icon = '../views/img/aguja.png';
    $ruta_cerrar_sesion ='login/cerrar_sesion.php';
    $ruta_image_menu ='';
    $ruta_image = "img/chisgas_fondo_blanco.png";
    include $ruta;
} else {
    echo "El archivo $ruta no existe.";
}
?>

<div class="centrar_botones_menu">
    <!-- Mostrar el botón "Ordenes" solo si el usuario es "Administrador" o "Caja" -->
    <?php if ($grupo_usuario == 'administrador' || $grupo_usuario == 'caja') { ?>
    <a style="margin-top: 8rem;" href="ordenes/ordenes.php">
        <button class="button2">
            Ordenes
            <img src="img/factura.png" alt="Icono de Ordenes" class="button-image">
        </button>
    </a>
    <?php } ?>

    <!-- Mostrar el botón "Calendario" para todos -->
    <a id='calendario'>
        <button class="button2">
            Calendario
            <img src="img/calendario.png" alt="Icono de Calendario" class="button-image">
        </button>
    </a>

    <!-- Mostrar el botón "Caja" solo para "Administrador" y "Caja" -->
    <?php if ($grupo_usuario == 'administrador' || $grupo_usuario == 'caja') { ?>
    <a href='ordenes/caja.php'>
        <button class="button2">
            Caja
            <img src="img/cajero-automatico.png" alt="Icono de Caja" class="button-image">
        </button>
    </a>
    <?php } ?>

    <!-- Mostrar el botón "Usuarios" solo para "Administrador" -->
    <?php if ($grupo_usuario == 'administrador') { ?>
    <a id='usuarios'>
        <button class="button2">
            Usuarios
            <img src="img/usuario.png" alt="Icono de Usuarios" class="button-image">
        </button>
    </a>
    <?php } ?>

     <!-- Mostrar el botón "Usuarios" solo para "Administrador" -->
     <?php if ($grupo_usuario == 'administrador' || $grupo_usuario == 'sastre') { ?>
    <a id='sastre'>
        <button class="button2">
            Sastre Auxiliar
            <img src="img/usuario.png" alt="Icono de Usuarios" class="button-image">
        </button>
    </a>
    <?php } ?>
</div>

<?php 
$ruta_footer = 'footer.php';

if (file_exists($ruta_footer)) {
    $ruta_js = "js/main.js";
    include $ruta_footer;
} else {
    echo "El archivo $ruta_footer no existe.";
}
?>
