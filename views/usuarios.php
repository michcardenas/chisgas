<?php
// Iniciar la sesión y verificar el login
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login/login.php");
    exit();
}

// Incluir el template
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


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tabla de Usuarios</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #f0f0f0;
        }
        .container {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        table {
            border-collapse: collapse;
            width: 100%;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .actions {
            display: flex;
            justify-content: space-around;
        }
        .actions a {
            text-decoration: none;
            color: #333;
            padding: 5px 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .actions a:hover {
            background-color: #f0f0f0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Tabla de Usuarios</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (isset($usuarios) && !empty($usuarios)) {
                    foreach ($usuarios as $usuario) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($usuario['id']) . "</td>";
                        echo "<td>" . htmlspecialchars($usuario['nombre']) . "</td>";
                        echo "<td class='actions'>";
                        echo "<a href='editar_usuario.php?id=" . $usuario['id'] . "'>Editar</a>";
                        echo "<a href='eliminar_usuario.php?id=" . $usuario['id'] . "' onclick=\"return confirm('¿Estás seguro de que deseas eliminar este usuario?');\">Eliminar</a>";
                        echo "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='3'>No se encontraron usuarios</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</html>

<?php 
$ruta_footer = 'footer.php';
if (file_exists($ruta_footer)) {
    $ruta_js = "js/main.js";
    include $ruta_footer;
} else {
    echo "El archivo $ruta_footer no existe.";
}
?>