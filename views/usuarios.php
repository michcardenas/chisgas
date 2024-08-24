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
    $ruta_cerrar_sesion = 'login/cerrar_sesion.php';
    $ruta_image_menu = 'menu.php';
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
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        th,
        td {
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

        .actions button {
            text-decoration: none;
            color: #333;
            padding: 5px 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .actions button:hover {
            background-color: #f0f0f0;
        }

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
    <div class="container">
        <h2>Tabla de Usuarios</h2>
        <table id="tablaUsuarios" class="table table-striped">
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
    <div class="modal fade" id="editarUsuarioModal" tabindex="-1" aria-labelledby="editarUsuarioModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editarUsuarioModalLabel">Editar Usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formEditarUsuario">
                        <input type="hidden" id="editarUsuarioId" name="id">
                        <div class="mb-3">
                            <label for="editarUsuarioLogin" class="form-label">Nombre de Usuario</label>
                            <input type="text" class="form-control" id="editarUsuarioLogin" name="login" required>
                        </div>
                        <div class="mb-3">
                            <label for="editarUsuarioPassword" class="form-label">Contraseña</label>
                            <input type="text" class="form-control" id="editarUsuarioPassword" name="contrasena">
                        </div>
                        <div class="mb-3">
    <label for="editarUsuarioGrupo" class="form-label">Grupo de Usuario</label>
    <select class="form-select" id="editarUsuarioGrupo" name="grupo_usuario">
        <option value="administrador">Administrador</option>
        <option value="caja">Caja</option>
        <option value="sastre">Sastre</option>
    </select>
</div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="guardarCambiosUsuario()">Guardar Cambios</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle (includes Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

<script>
    document.addEventListener('DOMContentLoaded', function () {
    // Cargar los usuarios en la tabla
    cargarUsuarios();

});

function cargarUsuarios() {
    fetch('../controllers/usuariosController.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: new URLSearchParams({
            'action': 'obtener_usuarios'
        })
    }).then(response => response.json())
        .then(data => {
            if (data.success) {
                var usuariosData = data.data;
                var tableBody = document.querySelector("#tablaUsuarios tbody");
                tableBody.innerHTML = ""; // Limpia la tabla antes de agregar nuevos datos

                usuariosData.forEach(function (usuario) {
                    var row = "<tr>" +
                        "<td>" + usuario.id + "</td>" +
                        "<td>" + usuario.login + "</td>" +
                        "<td class='actions'>" +
                        "<button class='btn btn-warning' onclick='abrirModalEditar(" + usuario.id + ", \"" + usuario.login + "\", \"" + usuario.contrasena + "\", \"" + usuario.grupo_usuario + "\")'>Editar</button> " +
                        "<button class='btn btn-danger' onclick='eliminarUsuario(" + usuario.id + ")'>Eliminar</button>" +
                        "</td>" +
                        "</tr>";
                    tableBody.innerHTML += row;
                });
            }
        })
        .catch(error => {
            console.error('Error al cargar los usuarios:', error);
        });
}



// Función para abrir el modal de edición
function abrirModalEditar(id, login, contrasena, grupo_usuario) {
    console.log('ID:', id);
    console.log('Login:', login);
    console.log('Contraseña:', contrasena);
    console.log('Grupo Usuario:', grupo_usuario);

    document.getElementById('editarUsuarioId').value = id;
    document.getElementById('editarUsuarioLogin').value = login;
    document.getElementById('editarUsuarioPassword').value = contrasena;

    var selectGrupoUsuario = document.getElementById('editarUsuarioGrupo');

    // Autocompletar el valor del grupo de usuario si coincide, o mantener el select sin selección si es undefined
    if (grupo_usuario && grupo_usuario !== "undefined") {
        for (let i = 0; i < selectGrupoUsuario.options.length; i++) {
            if (selectGrupoUsuario.options[i].value === grupo_usuario) {
                selectGrupoUsuario.selectedIndex = i;
                break;
            }
        }
    } else {
        selectGrupoUsuario.selectedIndex = -1; // Dejar sin selección
    }

    var editarUsuarioModal = new bootstrap.Modal(document.getElementById('editarUsuarioModal'));
    editarUsuarioModal.show();
}



function guardarCambiosUsuario() {
    var id = document.getElementById('editarUsuarioId').value;
    var login = document.getElementById('editarUsuarioLogin').value;
    var contrasena = document.getElementById('editarUsuarioPassword').value;
    var grupo_usuario = document.getElementById('editarUsuarioGrupo').value;

    fetch('../controllers/usuariosController.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: new URLSearchParams({
            'action': 'editar_usuario',
            'id': id,
            'login': login,
            'contrasena': contrasena,
            'grupo_usuario': grupo_usuario
        })
    }).then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Usuario actualizado con éxito.');
                var editarUsuarioModal = bootstrap.Modal.getInstance(document.getElementById('editarUsuarioModal'));
                editarUsuarioModal.hide(); // Cerrar el modal
                location.reload(); // Recargar la página para actualizar la tabla
            } else {
                alert('Error al actualizar el usuario: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error al actualizar el usuario:', error);
            alert('Hubo un error al actualizar el usuario.');
        });
}

function eliminarUsuario(id) {
    if (confirm('¿Estás seguro de que deseas eliminar este usuario?')) {
        fetch('../controllers/usuariosController.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: new URLSearchParams({
                'action': 'eliminar_usuario',
                'id': id
            })
        }).then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Usuario eliminado con éxito.');
                    location.reload(); // Recargar la página para actualizar la tabla
                } else {
                    alert('Error al eliminar el usuario: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error al eliminar el usuario:', error);
                alert('Hubo un error al eliminar el usuario.');
            });
    }
}



</script>

</html>
