$(document).ready(function() {

    document.querySelectorAll('.js-form-cantidad').forEach(function (form) {
        activarValidacion(form, {
            cantidad: ['requerido', ['enteroEntre', 1, 99]]
        });
    });

    // AJAX POST idéntico a la diapositiva 33
    $(".js-form-cantidad").submit(function(event) {
        event.preventDefault();

        var formulario = $(this);
        var url = "procesar_carrito_ajax.php";
        var datos = formulario.serialize(); 

        $.post(url, datos, function(data, status) {
            console.log("Respuesta bruta del PHP:", data); // Chivato en la consola (F12)

            // Si data es un número, todo ha ido bien
            if (status === "success" && !isNaN(data) && data.trim() !== "") {
                $("#contador-carrito").text(data.trim());

                var boton = formulario.find(".btn-anadir");
                boton.text("✔ Añadido").css({
                    "background-color": "#28a745", 
                    "color": "white", 
                    "border-color": "#28a745"
                });

                setTimeout(function() {
                    boton.text("+ Añadir").css({
                        "background-color": "", 
                        "color": "", 
                        "border-color": ""
                    });
                }, 1500);
            } else {
                // Si no es un número, es que el PHP ha devuelto un error
                alert("Atención, el servidor dice: \n\n" + data);
            }
        }).fail(function() {
            alert("Error crítico: No se encuentra el archivo procesar_carrito_ajax.php");
        });
    });
});