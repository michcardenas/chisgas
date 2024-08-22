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
        <table id="tablaUsuarios">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>

            </tbody>
        </table>
    </div>

    <!-- Modal para editar usuario -->
<div id="editarUsuarioModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Usuario</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formEditarUsuario">
                    <input type="hidden" id="editarUsuarioId" name="id">
                    <div class="form-group">
                        <label for="editarUsuarioLogin">Nombre de Usuario</label>
                        <input type="text" class="form-control" id="editarUsuarioLogin" name="login" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="guardarCambiosUsuario()">Guardar Cambios</button>
            </div>
        </div>
    </div>
</div>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>


</body>
</html>

<script>
$(document).ready(function() {
    var usuariosData = JSON.parse(sessionStorage.getItem('usuariosData') || localStorage.getItem('usuariosData'));

    if (usuariosData) {
        var tableBody = document.querySelector("#tablaUsuarios tbody");
        tableBody.innerHTML = ""; // Limpia la tabla antes de agregar nuevos datos

        usuariosData.forEach(function(usuario) {
            var row = "<tr>" +
                "<td>" + usuario.id + "</td>" +
                "<td>" + usuario.login + "</td>" +
                "<td class='actions'>" +
                "<button class='btn btn-warning' onclick='abrirModalEditar(" + usuario.id + ", \"" + usuario.login + "\")'>Editar</button> " +
                "<button class='btn btn-danger' onclick='eliminarUsuario(" + usuario.id + ")'>Eliminar</button>" +
                "</td>" +
                "</tr>";
            tableBody.innerHTML += row;
        });
    }
});

// Función para abrir el modal de edición
function abrirModalEditar(id, login) {
    // Rellenar el modal con los datos del usuario
    document.getElementById('editarUsuarioId').value = id;
    document.getElementById('editarUsuarioLogin').value = login;

    // Mostrar el modal
    $('#editarUsuarioModal').modal('show');
}

// Función para eliminar el usuario
function eliminarUsuario(id) {
    if (confirm('¿Estás seguro de que deseas eliminar este usuario?')) {
        $.ajax({
            url: '../controllers/usuariosController.php',
            type: 'POST',
            data: {
                'action': 'eliminar_usuario',
                'id': id
            },
            success: function(response) {
                alert("Usuario eliminado con éxito.");
                location.reload(); // Recargar la página para actualizar la tabla
            },
            error: function() {
                alert("Error al eliminar el usuario.");
            }
        });
    }
}

// Función para guardar los cambios del usuario
function guardarCambiosUsuario() {
    var id = document.getElementById('editarUsuarioId').value;
    var login = document.getElementById('editarUsuarioLogin').value;

    $.ajax({
        url: '../controllers/usuariosController.php',
        type: 'POST',
        data: {
            'action': 'editar_usuario',
            'id': id,
            'login': login
        },
        success: function(response) {
            alert("Usuario actualizado con éxito.");
            $('#editarUsuarioModal').modal('hide'); // Cerrar el modal
            location.reload(); // Recargar la página para actualizar la tabla
        },
        error: function() {
            alert("Error al actualizar el usuario.");
        }
    });
}
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
