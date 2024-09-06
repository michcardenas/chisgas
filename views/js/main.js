$(document).ready(function(){
  
  $('#atras_calendario').click(function(){  
    window.location.href = "calendario.php";
  });

  $('#valor_prenda').on('change', function() {
    // obtener el valor actual del campo
    var valor = $(this).val();

    // remover cualquier formato existente (puntos, comas y símbolo de moneda)
    valor = parseFloat(valor.replace(/[^0-9-.]/g, ''));

    // verificar si el valor es un número
    if (!isNaN(valor)) {
        // formatear el número
        valor = valor.toLocaleString('es-CO', { style: 'currency', currency: 'COP', maximumFractionDigits: 0 });

        // establecer el valor formateado de nuevo en el campo
        $(this).val(valor);
    }
});
$('.valor_actualizado').on('change', function() {
  // obtener el valor actual del campo
  var valor = $(this).val();

  // remover cualquier formato existente (puntos, comas y símbolo de moneda)
  valor = parseFloat(valor.replace(/[^0-9-.]/g, ''));

  // verificar si el valor es un número
  if (!isNaN(valor)) {
      // formatear el número
      valor = valor.toLocaleString('es-CO', { style: 'currency', currency: 'COP', maximumFractionDigits: 0 });

      // establecer el valor formateado de nuevo en el campo
      $(this).val(valor);
  }
});
//abono
var changingValue = false;

    $('#abono').on('input', function() {
        if (changingValue) return; // Salir si el valor ya está siendo cambiado

        // Indicar que se va a cambiar el valor programáticamente
        changingValue = true;

        // Tomar el valor del input, eliminar cualquier carácter no numérico
        var value = $(this).val().replace(/[^0-9]/g, '');

        // Convertir el valor limpio a un número
        var numericValue = parseFloat(value);

        // Hacer los cálculos que necesitas aquí, por ejemplo actualizar el campo "saldo"
        var valorTotal = parseFloat($('#valor_total').val().replace(/[^0-9]/g, '')) || 0;
        var saldo = valorTotal - numericValue;
        $('#saldo').val(saldo.toLocaleString('es-CO', { style: 'currency', currency: 'COP', maximumFractionDigits: 0 }));

        // Volver a formatear el valor del input como moneda
        $(this).val(numericValue.toLocaleString('es-CO', { style: 'currency', currency: 'COP', maximumFractionDigits: 0 }));

        // Indicar que se ha terminado de cambiar el valor
        changingValue = false;
    });
  
    
  
    // Función para buscar clientes
    $(".button-buscar").click(function(e){
        e.preventDefault();
        var telefono_cliente = $("#telefono_cliente").val();
        var nombre_cliente = $("#nombre_cliente").val();
        $.ajax({
            url: '../../controllers/clientesController.php',
            type: 'post',
            data: {action: 'buscar', telefono_cliente: telefono_cliente, nombre_cliente: nombre_cliente},
            success: function(response){
              // Vaciamos cualquier tabla existente
              $("#resultados").empty();
              
              // Crear la tabla y su encabezado con las mismas clases del formulario
              let tabla = $('<table>').addClass('form_card'); // Agregamos las clases del formulario
              let encabezado = $('<thead>');
              let filaEncabezado = $('<tr>');
              filaEncabezado.append($('<th>').text('Nombre').addClass('label')); // Agregamos la clase 'label' del formulario
              filaEncabezado.append($('<th>').text('Número de teléfono').addClass('label')); // Agregamos la clase 'label' del formulario
              filaEncabezado.append($('<th>').text('Editar').addClass('label')); // Columna adicional para la imagen de editar
              encabezado.append(filaEncabezado);
              tabla.append(encabezado);
          
              // Crear el cuerpo de la tabla
              let cuerpo = $('<tbody>');
          
              // Asumiendo que la respuesta es un array de objetos
              for(let i = 0; i < response.length; i++) {
                  let fila = $('<tr>');
                  let nombre = response[i].nombre;
                  let idCliente = response[i].id; // Supongamos que el ID del cliente está en la propiedad 'id'
                  
                  // Crear un enlace con el nombre que dirige a crear_orden.php con el ID como parámetro
                  let enlaceNombre = $('<a>').attr('href', 'recibir_orden.php?cliente=' + idCliente).text(nombre);
                  
                  fila.append($('<td>').append(enlaceNombre).addClass('input')); // Agregamos el enlace en lugar del texto
                  fila.append($('<td>').text(response[i].telefono).addClass('input')); // Agregamos la clase 'input' del formulario
          
                  // Crear una columna para el enlace de editar con la imagen
                  let editarColumna = $('<td>').addClass('input');
                  let enlaceEditar = $('<a>').attr('href', '../../controllers/clientesController.php?action=consultar&cliente=' + idCliente);
                  let imagenEditar = $('<img>').attr('src', '../img/lapiz.png').addClass('lapiz'); // Cambia la ruta de la imagen según la ubicación real
                  enlaceEditar.append(imagenEditar);
                  editarColumna.append(enlaceEditar);
                  fila.append(editarColumna);
                  
                  cuerpo.append(fila);
              }
          
              tabla.append(cuerpo);
          
              // Añadir la tabla al DOM
              $("#resultados").append(tabla);
          }
        });
    });
    $(".button-buscar_orden").click(function(e) {
      e.preventDefault();
      var nombre_telefono = $("#nombre_telefono").val();

      // Obtén el valor del campo de entrada
      if (nombre_telefono == "telefono") {
        var telefono_cliente = $("#nombre_cliente").val();

      }else{
        var nombre_cliente = $("#nombre_cliente").val();
      }
      // Realiza la solicitud AJAX
      $.ajax({
          url: '../../controllers/clientesController.php',
          type: 'post',
          data: {
              action: 'buscar',
              nombre_cliente: nombre_cliente,
              telefono_cliente: telefono_cliente
          },
          success: function(response) {
            // Vacía cualquier tabla existente
            $("#resultados").empty();
        
            // Verifica si la respuesta es un array vacío
            if (response.length === 0) {
                // Maneja el caso en el que no se encontraron resultados
                $("#resultados").append('<p>No se encontraron resultados.</p>');
                return;
            }
        
            // Crea una tabla para mostrar los nombres de los clientes
            var tabla = $('<table>').addClass('styled-table'); // Agrega una clase para los estilos de la tabla
        
            // Crea el encabezado de la tabla
            var encabezado = $('<thead>');
            var filaEncabezado = $('<tr>');
            filaEncabezado.append($('<th>').text('Nombre')); // Encabezado para el nombre
            encabezado.append(filaEncabezado);
            tabla.append(encabezado);
        
            // Crea el cuerpo de la tabla
            var cuerpo = $('<tbody>');
        
            // Asume que la respuesta es un array de objetos
            for (var i = 0; i < response.length; i++) {
                var nombre = response[i].nombre;
                var idCliente = response[i].id;
        
                // Crea una fila de la tabla
                var fila = $('<tr>');
        
                // Crea una celda para el nombre con un enlace
                var celdaNombre = $('<td>');
                var enlaceNombre = $('<a>').attr('href', 'detalles_orden.php?cliente=' + idCliente).text(nombre);
        
                // Agrega el enlace a la celda
                celdaNombre.append(enlaceNombre);
        
                // Agrega la celda a la fila
                fila.append(celdaNombre);
        
                // Agrega la fila al cuerpo de la tabla
                cuerpo.append(fila);
            }
        
            // Agrega el cuerpo a la tabla
            tabla.append(cuerpo);
        
            // Añade la tabla al DOM
            $("#resultados").append(tabla);
        }
        
        
      });
  });
  
    $("#editar").click(function(e){
      e.preventDefault();
      
      var telefono_cliente = $("#telefono_cliente").val();
      var nombre_cliente = $("#nombre_cliente").val();
      var cliente_id = $("#cliente_id").val();
      
      $.ajax({
          url: '../../controllers/clientesController.php',
          type: 'post',
          data: {action: 'editar', telefono_cliente: telefono_cliente, nombre_cliente: nombre_cliente, cliente_id: cliente_id},
          dataType: 'json',
          success: function(response){
            if (response.status === 'success') {
                $("#resultado_editar").text(response.message);
                setTimeout(function(){
                  window.location.href = "ordenes.php";
              }, 1000);
            } else if (response.status === 'error') {
                $("#resultado_editar").text(response.message);
            }
        }
        
      });
  });
    // Manejador de clic para el botón con id 'entregar'
    $("#entregar").click(function(e) {
        e.preventDefault(); // Evita que el botón realice la acción por defecto (como enviar un formulario)

        // Obtiene el valor del id de la orden
        var id_orden = $("#id_orden").val();
        console.log("ID de la orden:", id_orden);

        // Realiza la llamada AJAX
        $.ajax({
            url: '../../controllers/calendarioController.php', // La URL donde se procesará la solicitud
            type: 'post', // Método HTTP usado para la solicitud
            data: {
                action: 'entregar', // La acción a realizar en el backend
                id_orden: id_orden  // El ID de la orden a entregar
            },
            dataType: 'json', // El tipo de datos esperados en la respuesta
            success: function(response) {
                // Manejo de la respuesta en caso de éxito
                if (response.success) {
                    // Si la respuesta fue exitosa, muestra un mensaje
                    alert(response.message);
                    setTimeout(function(){
                window.location.href = 'entregar.php?id_orden=' + id_orden; // Redirige a entregar.php con el id_orden
                  }, 1000);
                    
                    // Aquí puedes añadir cualquier otra lógica necesaria, como redireccionar o actualizar la página
                } else {
                    // Si la respuesta indica un fallo, muestra el mensaje de error
                    alert(response.message);
                }
            },
            error: function(xhr, status, error) {
                // Manejo de error en la solicitud AJAX
                console.error("Error AJAX: " + status + ", " + error);
                // Aquí puedes añadir un manejo de errores, como mostrar un mensaje al usuario
            }
        });
    });


  $("#generar_orden").click(function(e) {
    e.preventDefault();
    var forma_pago = $("#forma_pago").val();
    var fechaEntrega = $("#fecha_entrega").val();
    var abono = extractNumber($("#abono").val());

    if (!fechaEntrega) {
        alert("Por favor, llena la fecha de entrega.");
        return;
    }

    if (isNaN(abono)) {
        alert("Por favor, ingresa un abono válido.");
        return;
    }

    var today = new Date().toISOString().split('T')[0];

    if (fechaEntrega < today) {
        if (!confirm('¿Estás seguro de que deseas agendar antes del día de hoy?')) {
            return;
        }
    }

    var franjaHoraria = $("#franja_horaria").val();
    var totalPrendas = extractNumber($("#total_prendas").val());
    var valorTotal = extractNumber($("#valor_total").val());
    var saldo = valorTotal - abono;

    if (saldo < 0) {
        alert("El saldo no puede ser negativo. Por favor, verifica el abono ingresado.");
        return;
    }

    $("#saldo").val("$ " + saldo.toLocaleString('es-CO', { maximumFractionDigits: 0 }));

     var prendaIDs = [];
   
     $(".btn-delete").each(function() {
         prendaIDs.push($(this).data("id"));
     });
    console.log(abono);
    console.log(saldo);
    console.log(forma_pago);
    var hayValoresEnCero = false;
    $(".valor_actualizado").each(function() {
        var valor = extractNumber($(this).val());
        if (valor === 0) {
            hayValoresEnCero = true;
            return false; // Salir del bucle
        }
    });

    if (hayValoresEnCero) {
        const userConfirmed = window.confirm("Hay valores de prenda en $0 . ¿Estás seguro de que quieres continuar, estas prendas seran pasadas como garantia?");
        if (!userConfirmed) {
            return; 
        }
    }

    $.ajax({
      url: '../../controllers/ordenController.php',
      type: 'post',
      data: {
        action: 'generar_orden', 
        fecha_entrega: fechaEntrega, 
        franja_horaria: franjaHoraria, 
        total_prendas: totalPrendas,
        valor_total: valorTotal,
        abono: abono,
        saldo: saldo,
        prenda_ids: prendaIDs,
        forma_pago: forma_pago  

       
      },
      dataType: 'json',
      success: function(response) {
        if (response.success) {
            $("#resultado_editar").text(response.message).css("color", "green");
            setTimeout(function(){
                window.location.href = "../../controllers/ordenController.php?order_id=" + response.order_id; // Cambia aquí
            }, 1000);
        } else {
            $("#resultado_editar").text(response.message).css("color", "red");
        }
    }
    
  });
  });




function extractNumber(str) {
  return parseFloat(str.replace(/[^0-9]/g, ''));
}



  $("#borrar").click(function(e){
    e.preventDefault();
    
    // Suponiendo que también tienes un cliente_id para saber qué cliente borrar
    var cliente_id = $("#cliente_id").val();
   
    $.ajax({
        url: '../../controllers/clientesController.php',
        type: 'post',
        data: {action: 'borrar', cliente_id: cliente_id},
        dataType: 'json',
        success: function(response){
          if (response.status === 'success') {
              $("#resultado_editar").text(response.message).css("color", "color");
              setTimeout(function(){
                  window.location.href = "ordenes.php";
              }, 1000);
          } else if (response.status === 'error') {
              $("#resultado_editar").text(response.message).css("color", "red");
          }
      }
      
    });

  


});
// crear orden

$("#agregar_prenda").click(function(e){
  e.preventDefault();

  var cliente_id                          = $("#cliente_id").val();
  var nombre_prenda                     = $("#nombre_prenda").val();
  var prendas_numero                      = $("#prendas_numero").val();
  var descripcion_arreglo                 = $("#descripcion_arreglo").val();
  var tiempo_estimado                     = $("#tiempo_estimado").val();
  var valor_prenda_sig                        = $("#valor_prenda").val();
  var estado                              = $("#estado").val();
  // Eliminar el signo de dólar y espacios en el valor de la prenda
  var valor_prenda_text = valor_prenda_sig.replace(/[$\s.]/g, "");

  // Convierte la cadena en un número (opcional)
  var valor_prenda = parseFloat(valor_prenda_text);

  console.log(valor_prenda); 
  $.ajax({
    url: '../../controllers/ordenController.php',
    type: 'post',
    data: {
        action: 'agregar_prenda',
        cliente_id: cliente_id,
        nombre_prenda: nombre_prenda,
        prendas_numero: prendas_numero,
        descripcion_arreglo: descripcion_arreglo,
        tiempo_estimado: tiempo_estimado,
        valor_prenda: valor_prenda,
        estado: estado
    },
    dataType: 'json',
    beforeSend: function() {
      // Mostrar el loader antes de enviar la petición
      $(".content_loader").show();
  },
    success: function(response){
      
      if (response.status === 'success') {
          $("#resultado_editar").text(response.message);
  
          // Limpiar los campos del formulario
          $("#nombre_prenda").val('');
          $("#prendas_numero").val('');
          $("#descripcion_arreglo").val('');
          $("#tiempo_estimado").val('');
          $("#valor_prenda").val('');
        
  
          // Hacer que #resultado_editar desaparezca después de 1 segundo
          setTimeout(function() {
              $("#resultado_editar").text('');
              $(".content_loader").hide();

          }, 2000);
  
      } else if (response.status === 'error') {
          $("#resultado_editar").text(response.message);
          $(".content_loader").hide();

      }
  }
  
  });




});
$('.actualizar_valor').click(function() {
  const id = $(this).data('id');
  const cliente_id = $(this).data('cliente_id'); 
  var valor_actualizado = $(this).closest('.card').find('.valor_actualizado').val(); 
  console.log(id);
  console.log(cliente_id);
  console.log(valor_actualizado);
 var valor_prenda_actualizado = valor_actualizado;
 var valor_prenda_text = valor_prenda_actualizado.replace(/[$\s.]/g, "");

  // Convierte la cadena en un número (opcional)
  var valor_prenda = parseFloat(valor_prenda_text);

  console.log(valor_prenda); 

  const userConfirmed = window.confirm("¿Estás seguro de actualizar este valor?");
  
  if (userConfirmed) {
    $.ajax({
      url: '../../controllers/ordenController.php',
      type: 'POST',
      data: {
          'action': 'actualizar_valor',
          'id': id,
          'valor_actualizado': valor_prenda,
          'cliente_id': cliente_id
      },
      dataType: 'json',
      success: function(response) {
          if (response.success) {
              alert(response.message);
              window.location.href = "../../controllers/ordenController.php?cliente_id=" + cliente_id + "&action=agendar_orden";
          } else {
              alert(response.message); // Muestra un mensaje de error
          }
      },
      error: function() {
          alert("Error en la comunicación con el servidor.");
      }
    });
  }
});



$('.btn-delete').click(function() {
  const id = $(this).data('id');
  const cliente_id = $(this).data('cliente_id');  // Asegúrate de que este dato esté disponible
  console.log(id);
  const userConfirmed = window.confirm("¿Estás seguro de que deseas eliminar esta prenda?");
  
  if (userConfirmed) {
      $.ajax({
          url: '../../controllers/ordenController.php',
          type: 'POST',
          data: {
              'action': 'delete',
              'id': id
          },
          dataType: 'json',  // Esperamos una respuesta en formato JSON
          success: function(response) {
              if (response.success) {
                  alert(response.message); // Muestra un mensaje de éxito
                  // Redirigir al usuario a la nueva ruta
                  window.location.href = "../../controllers/ordenController.php?cliente_id=" + cliente_id + "&action=agendar_orden";
              } else {
                  alert(response.message); // Muestra un mensaje de error
              }
          },
          error: function() {
              alert("Error en la comunicación con el servidor.");
          }
      });
  }
});

$('.btn-edit').click(function() {
  const id = $(this).data('id');
  const cliente_id = $(this).data('cliente_id');  // Asegúrate de que este dato esté disponible
  console.log(id);
  
  const userConfirmed = window.confirm("¿Estás seguro de que deseas editar esta prenda?");
  
  if (userConfirmed) {
    $.ajax({
      url: '../../controllers/ordenController.php',
      type: 'POST',
      data: {
        'action': 'edit',
        'id': id
      },
      dataType: 'json',  // Esperamos una respuesta en formato JSON
      success: function(response) {
        if (response.success) {
            alert(response.message);
            window.location.href = "editar_prenda.php"; // Redirección del lado del cliente
        } else {
            alert(response.message);
        }
    },
    
      error: function() {
        alert("Error en la comunicación con el servidor.");
      }
    });
  }
});
$('#editar_prenda').click(function() {
  const nombre_prenda = $('#nombre_prenda').val();
  const prendas_numero = $('#prendas_numero').val();
  const estado = $('#estado').val();
  const descripcion_arreglo = $('#descripcion_arreglo').val();
  const tiempo_estimado = $('#tiempo_estimado').val();
  const valor_prenda = $('#valor_prenda').val();
  const cliente_id = $('#cliente_id').val();
  const prenda_id = $('#id_prenda').val();
  // Eliminar el signo de dólar y espacios en el valor de la prenda
  var valor_prenda_text = valor_prenda.replace(/[$\s.]/g, "");

  // Convierte la cadena en un número (opcional)
  var valor_prenda_real = parseFloat(valor_prenda_text);
  const userConfirmed = window.confirm("¿Estás seguro de que deseas editar esta prenda?");
  
  if (userConfirmed) {
      $.ajax({
          url: '../../controllers/ordenController.php',
          type: 'POST',
          data: {
              'action': 'edit_prenda',
              'nombre_prenda': nombre_prenda,
              'prendas_numero': prendas_numero,
              'estado': estado,
              'descripcion_arreglo': descripcion_arreglo,
              'tiempo_estimado': tiempo_estimado,
              'valor_prenda': valor_prenda_real,
              'prenda_id': prenda_id
          },
          dataType: 'json',
          success: function(response) {
              if (response.success) {
                  alert(response.message);
                  window.location.href = "../../controllers/ordenController.php?cliente_id=" + cliente_id + "&action=agendar_orden"; // Redirección del lado del cliente
              } else {
                  alert(response.message);
              }
          },
          error: function() {
              alert("Error en la comunicación con el servidor.");
          }
      });
  }
});
$("#calendario").click(function() {
  $.ajax({
      url: '../controllers/calendarioController.php',
      type: 'POST',
      data: {
          'action': 'ver_calendario'
      },
      dataType: 'json',
      success: function(response) {
          if (response.success) {
              // Aquí puedes hacer lo que necesites con response.data
              // Por ejemplo, guardar en sessionStorage:
              sessionStorage.setItem('calendarioData', JSON.stringify(response.data));

              // Ahora, redirigir a la vista:
              window.location.href = "calendario/calendario.php";
            } else {
              alert(response.message);
          }
      },
      error: function() {
      }
  });
});

$('#editar_arreglo').click(function() {
    // Capturar los valores
    var prendaId = $('#prenda_id').val();
    var nombre_prenda = $('#nombre_prenda').val();
    var prendas_numero = $('#prendas_numero').val();
    var descripcion_arreglo = $('#descripcion_arreglo').val();
    var valor_prenda = $('#valor_prenda').val(); 
    var id_orden = $('#id_orden').val();
    var asignado = $('#Asignado').val();
    var estado = $('#estado_prenda').val();
    console.log(estado);
    // Corregido el ID del selector
    var valor_prenda_text = valor_prenda.replace(/[$\s.,]/g, "");
    // Convierte la cadena en un número (opcional)
    var valor_prenda_real = parseInt(valor_prenda_text);
    console.log(valor_prenda_real);

    // Enviarlos a través de AJAX
    $.ajax({
        type: "POST",
        url: '../../controllers/calendarioController.php',
        data: {
            action: "editar_arreglo",
            id: prendaId,
            nombre_prenda: nombre_prenda,
            prendas_numero: prendas_numero,
            descripcion_arreglo: descripcion_arreglo,
            valor: valor_prenda_real,
            asignado: asignado,
            estado: estado
        },
        dataType: "json",
        success: function(response) {
            if (response.success) {
                $("#resultado_editar").text(response.success);
                
                // Redirigir dependiendo del grupo de usuario
                setTimeout(function() {
                    if (grupoUsuario === 'sastre') {
                        window.location.href = '../sastre.php';
                    } else {
                        window.location.href = "ver_arreglos.php?id_orden=" + id_orden;
                    }
                }, 1000);
                
            } else {
                // Manejar errores
                alert(response.message);
            }
        },
        error: function() {
            // Si hay un error en la solicitud AJAX
            alert("Error al actualizar la prenda.");
        }
    });
});






$('.fecha-link').on('click', function(e){
  e.preventDefault(); // Evita que el enlace haga su acción por defecto

  let fecha = $(this).data('fecha'); // Obtiene la fecha del atributo data-fecha
 
  window.location.href = 'ver_dia.php?fecha_entrega=' + fecha; // Redirige al URL con la fecha como parámetro
});



$('#agendar_orden_btn').click(function(){
  var cliente_id = $('#cliente_id').val(); // Obtener el id del cliente del formulario anterior

  window.location.href = "../../controllers/ordenController.php?cliente_id=" + cliente_id + "&action=agendar_orden";
});
$("#atras").click(function(){
  console.log(window.location.href);
  window.location.href = "ordenes.php";
});
$('#botonAtras').on('click', function() {
    window.history.back();
});
$("#entrega_total").click(function(e) {
    e.preventDefault();

    var idOrden = $("#id_orden").val(); 
    var id_usuario = $("#id_usuario").val(); 
    var forma_pago = $("#forma_pago").val(); 
    var telefono = $("#telefono_cliente").val(); 
    
    console.log("idOrden:", idOrden);
    console.log("id_usuario:", id_usuario);
    console.log("forma_pago:", forma_pago);
    console.log("telefono:", telefono);
    
    var confirmar = confirm("¿Quieres entregar toda la orden?");
    if (!confirmar) {
        return;
    }

    // Deshabilitar el botón para evitar múltiples clics
    $(this).prop('disabled', true);

    $.ajax({
        url: '../../controllers/calendarioController.php',
        type: 'post',
        dataType: 'json',
        data: {
            action: 'entregaTotal',
            idOrden: idOrden,
            id_usuario: id_usuario,
            forma_pago: forma_pago
        },
        beforeSend: function() {
            // Aquí puedes mostrar un loader o un mensaje de carga si es necesario
        },
        success: function(responseData) {
            console.log(responseData);
            if (responseData.success) {
                var filePath = responseData.pdf;
                var fileName = filePath.split('/').pop();

                var whatsappMessage = `¡Gracias por elegirnos para el arreglo de tu prenda! 🎉 Tu orden está completa y los detalles están disponibles en el siguiente enlace: https://sastreriachisgas.shop/facturas/${fileName} 📝 Agradecemos tu confianza en nosotros y estamos aquí para cualquier otro servicio que necesites. 😊 ¡Esperamos verte pronto! 👋`;
                var whatsappUrl = `https://api.whatsapp.com/send?phone=+57${telefono}&text=${encodeURIComponent(whatsappMessage)}`;

                // Redirige a la URL de WhatsApp
                window.location.href = whatsappUrl;
            } else {
                alert("Hubo un problema al procesar la entrega total. Por favor, inténtalo de nuevo más tarde.");
            }
        },
        error: function(xhr, status, error) {
            console.error('Error en la solicitud AJAX:', status, error);
            alert("Hubo un error al procesar la solicitud. Por favor, inténtalo de nuevo más tarde.");
        },
        complete: function() {
            // Rehabilitar el botón después de completar la solicitud
            $("#entrega_total").prop('disabled', false);
        }
    });
});



$("#entrega_parcial").click(function(e) {
  e.preventDefault();

  var idOrden = $("#id_orden").val(); 
  var id_usuario = $("#id_usuario").val(); 
  console.log(idOrden);


  $.ajax({
      url: '../../controllers/calendarioController.php',
      type: 'post',
      dataType: 'json',
      data: {
          action: 'entregaParcial',
          idOrden: idOrden,
          id_usuario: id_usuario
      },
      beforeSend: function() {

    },
      success: function(response) {
        console.log(response);
        if(response.success) {
          // Construye la URL con los parámetros ID y nombre de usuario
          var url = 'entrega_parcial.php';
          url += '?idOrden=' + encodeURIComponent(response.idOrden);
          url += '&nombreUsuario=' + encodeURIComponent(response.nombreUsuario);
          
          // Redirige a la URL construida
          window.location.href = url;
      } else {
          alert(responseData.message); // Muestra el mensaje de error o información
      }
    },
  error: function(xhr, status, error) {
    alert(responseData.message);
  }
});
});

function cargarCalendarioPorEstadoPrenda(estado) {
    $.ajax({
        url: '../../controllers/calendarioController.php',
        type: 'POST',
        data: {
            'action': 'ver_calendario_estado_prenda',
            'estado': estado
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                console.log(response);
                sessionStorage.setItem('calendarioData', JSON.stringify(response.data));
                actualizarVistaCalendario(response.data);
                alert("Datos de calendario actualizados según el estado de la prenda.");
            } else {
                alert(response.message);
            }
        },
        error: function() {
            alert("Error en la comunicación con el servidor.");
        }
    });
}

function actualizarVistaCalendario(data) {
    data.sort(function(a, b) {
        return new Date(b.fecha_entrega) - new Date(a.fecha_entrega);
    });

    var tbody = $("#calendarioTabla tbody");
    tbody.empty();

    data.forEach(function(item) {
        var row = `
            <tr>
                <td><a href="#" class="fecha-link" data-fecha="${item.fecha_entrega}" onclick="verDetallesOrden('${item.fecha_entrega}')">${item.fecha_entrega}</a></td>
                <td>${item.numero_clientes}</td>
                <td>${convertirMinutosAHoras(item.tiempo_estimado_total)}</td>
            </tr>
        `;
        tbody.append(row);
    });
}

$("#estadoPrendaSelect").change(function() {
    var estado = $(this).val();
    cargarCalendarioPorEstadoPrenda(estado);
    console.log('Estado seleccionado:', estado);
});

function convertirMinutosAHoras(minutos) {
    var horas = Math.floor(minutos / 60);
    var minutosRestantes = minutos % 60;
    return horas + "h " + minutosRestantes + "m";
}
});
// Función para manejar los clics en las fechas y ver los detalles de la orden
function verDetallesOrden(fecha) {
    // Aquí puedes redirigir a una página de detalles o mostrar un modal con más información
    window.location.href = '../calendario/ver_dia.php?fecha_entrega=' + fecha;
};



$('.ff').on('submit', function(e) {
    var nombre_cliente = $('#nombre_cliente').val();
    var telefono_cliente = $('#telefono_cliente').val();

    if (!nombre_cliente && !telefono_cliente) {
      alert('Se nesecita llenar todos lso campos');
      e.preventDefault(); // detiene la acción por defecto del formulario
    }
  });

  function goBack() {
    window.history.back();
  }
  function enviarAWhatsapp() {
    let nombre_cliente = document.getElementById("nombre_cliente").value;
    let order_id = document.getElementById("order_id").value;
    let valor_total = document.getElementById("valor").value;
    let abono = document.getElementById("abono").value;
    let saldo = document.getElementById("saldo").value;
    let fecha_entrega = document.getElementById("fecha_entrega").value; // Asegúrate de tener un input con id "fecha_entrega" en tu formulario.
    let cliente_telefono = document.getElementById("telefono_cliente").value; 

    let mensaje = `¡Hola ${nombre_cliente}! 🎩\n`;
    mensaje += `Desde *Sastrería Chisgas* queremos contarte sobre tu orden:\n\n`;
    mensaje += `🔖 Número de Orden: *#${order_id}*\n`;
    mensaje += `💰 Valor Total: *${valor_total}*\n`;
    mensaje += `💵 Abono: *${abono}*\n`;
    mensaje += `🪙 Saldo Pendiente: *${saldo}*\n`;
    mensaje += `Puedes cancelar este saldo el día que vengas a recoger tu prenda.👖\n`;
    mensaje += `🗓 ¡Tu arreglo estará listo el ${fecha_entrega}! ✂️\n\n`;
    mensaje += `¡Gracias por confiar en nuestro talento y profesionalismo! 🌟`;

    // Añadir código de país y número de teléfono al enlace de WhatsApp
    let whatsappURL = `https://api.whatsapp.com/send?phone=+57${cliente_telefono}&text=${encodeURIComponent(mensaje)}`;
    window.open(whatsappURL);
}


$("#usuarios").click(function() {
    $.ajax({
        url: '../controllers/usuariosController.php',
        type: 'POST',
        data: {
            'action': 'obtener_usuarios'
        },
        dataType: 'json',
        success: function(response) {
            console.log("Respuesta recibida:", response); // Muestra la respuesta completa en la consola
            if (response.success) {
                // Almacena los datos en sessionStorage
                sessionStorage.setItem('usuariosData', JSON.stringify(response.data));
                
                // Redirige a la vista usuarios.php
                window.location.href = "usuarios.php";
            } else {
                alert(response.message);
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.log("Error en la solicitud AJAX:");
            console.log("Estado:", textStatus); // Muestra el estado de la solicitud (ej: 'error')
            console.log("Error lanzado:", errorThrown); // Muestra cualquier error específico lanzado
            console.log("Detalles del jqXHR:", jqXHR); // Muestra el objeto jqXHR completo para inspección
            alert("Ocurrió un error al procesar la solicitud.");
        }
    });
});

$("#sastre").click(function() {
    $.ajax({
        url: '../controllers/sastreController.php', // Ruta al controlador
        type: 'POST',
        data: {
            'action': 'obtener_arreglos_sastre'
        },
        dataType: 'json',
        success: function(response) {
            console.log("Respuesta recibida:", response); // Muestra la respuesta completa en la consola
            if (response.success) {
                // Almacena los datos en sessionStorage
                sessionStorage.setItem('sastreData', JSON.stringify(response.data));
                
                // Redirige a la vista sastre.php
                window.location.href = "sastre.php";
            } else {
                alert(response.message); // Muestra el mensaje de error en caso de que no sea exitoso
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.log("Error en la solicitud AJAX:");
            console.log("Estado:", textStatus); // Muestra el estado de la solicitud (ej: 'error')
            console.log("Error lanzado:", errorThrown); // Muestra cualquier error específico lanzado
            console.log("Detalles del jqXHR:", jqXHR); // Muestra el objeto jqXHR completo para inspección
            alert("Ocurrió un error al procesar la solicitud.");
        }
    });
});
