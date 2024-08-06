<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?php echo $ruta_css; ?>">
    <link rel="icon" type="image/png" href="<?php echo $ruta_icon; ?>">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <title><?php echo $title ?? 'Chisgas'; ?></title>
    <style>    

    
.user-name {
        margin: 0 15px;  /* Reducido de 20px a 15px */
        font-weight: bold;
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 8px 12px;  /* Reducido de 10px 15px a 8px 12px */
        background-color: white;
        border-radius: 6px;  /* Reducido de 8px a 6px */
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    
    .user-name span {
        margin: 1px 0;  /* Reducido de 2px a 1px */
        text-align: center;
    }
    
    .nav-link, .logout-link {
        position: relative;
        overflow: hidden;
        height: 2.5rem;
        padding: 0 1.5rem;
        border-radius: 1.25rem;
        background: transparent;
        color: #3d3a4e;
        border: 2px solid #3d3a4e;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.9rem;
        font-weight: bold;
        transition: all 0.3s ease;
    }
    
    .nav-link:hover, .logout-link:hover {
        background-color: rgba(61, 58, 78, 0.1);
        color: #2a2839;
    }
    </style>
</head>
<body>
<?php
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    if ($ruta_cerrar_sesion == '') {
        $ruta_cerrar_sesion = '../login/cerrar_sesion.php';
    } else {
        $ruta_cerrar_sesion = 'login/cerrar_sesion.php';
    }

    if (isset($_SESSION['username']) && $_SESSION['username'] == true) {
        $saludo = (isset($_SESSION['grupo_usuario']) && $_SESSION['grupo_usuario'] == 'administrador') 
            ? '<span>Bienvenido</span><span>Administrador:</span>' 
            : '<span>Bienvenido,</span>';
        
        echo '<nav class="image-nav">
            <a href="'.$ruta_image_menu.'">
                <img src="'.$ruta_image.'" alt="Chisgas logo" class="center-image">
            </a>
            <div class="nav-right">
                <div class="user-name">
                    ' . $saludo . '
                    <span>' . htmlspecialchars($_SESSION['username']) . '</span>
                </div>
                <a href="ruta_a_usuarios.php" class="nav-link">Usuarios</a>
                <a href="' . $ruta_cerrar_sesion . '" class="nav-link">Cerrar sesión</a>
            </div>
        </nav>';
    }
    ?>
    <!-- Aquí puedes añadir el contenido principal de tu página -->
    <!-- Aquí puedes añadir el contenido principal de tu página -->
</body>
</html>