$(document).ready(function () {
    // 1. Validaciones básicas de validaciones.js
    activarValidacion('formRegistro', {
        nombre_usuario: ['requerido', ['minLen', 3], ['maxLen', 30]],
        password:       ['requerido', ['minLen', 6]],
        password2:      ['requerido', ['coincideCon', 'password']],
        nombre:         ['requerido', ['maxLen', 50]],
        email:          ['requerido', 'email']
    });

    // 2. Comprobación visual de contraseña
    var $inputPass = $('#formRegistro input[name="password"]');
    if ($inputPass.length > 0) {
        var $spanFortaleza = $('<span class="fortaleza-password" style="margin-left: 8px;"></span>');
        $inputPass.after($spanFortaleza); // Lo inserta justo después del input
        
        $inputPass.on('input', function () {
            var valor = $(this).val();
            if (!valor) { $spanFortaleza.text(''); return; }
            
            var f = fortalezaPassword(valor);
            $spanFortaleza.text(f.mensaje);
            $spanFortaleza.css('color', (f.nivel === 'débil') ? '#b00' : (f.nivel === 'media') ? '#a60' : '#0a0');
        });
    }

    // 3. VALIDACIÓN AJAX DE USUARIO
    var $campoUsuario = $('#formRegistro input[name="nombre_usuario"]');
    if ($campoUsuario.length > 0) {
        $campoUsuario.on('input', function() {
            var campoNativo = this; // Guardamos el elemento HTML puro para setCustomValidity
            var valor = $(this).val();
            campoNativo.setCustomValidity(""); 

            // Controlamos primero la longitud mínima antes de preguntar a la BD
            if (valor.length < 3) {
                mostrarErrorCampo(campoNativo, "El nombre del usuario tiene que tener 3 o más caracteres.");
                campoNativo.setCustomValidity("Corto");
                return;
            }

            var url = "comprobarUsuario.php?user=" + encodeURIComponent(valor);
            
            // Petición GET con jQuery (Estilo asignatura)
            $.get(url, function(data, status) {
                if (status === "success") {
                    if (data.trim() === "existe") {
                        mostrarErrorCampo(campoNativo, "El usuario ya existe, escoge otro.");
                        campoNativo.setCustomValidity("El usuario ya existe, escoge otro."); 
                    } else if (data.trim() === "disponible") {
                        limpiarErrorCampo(campoNativo);
                    }
                }
            });
        });
    }

    // 4. VALIDACIÓN AJAX DE EMAIL
    var $campoEmail = $('#formRegistro input[name="email"]');
    if ($campoEmail.length > 0) {
        $campoEmail.on('input', function() {
            var campoNativo = this;
            var valor = $(this).val();
            campoNativo.setCustomValidity(""); 

            // Verificamos el formato PRIMERO
            if (!Validaciones.esEmailValido(valor)) {
                mostrarErrorCampo(campoNativo, "Introduce un email válido.");
                campoNativo.setCustomValidity("Email inválido");
                return;
            }

            var url = "comprobarEmail.php?email=" + encodeURIComponent(valor);
            
            // Petición GET con jQuery (Estilo asignatura)
            $.get(url, function(data, status) {
                if (status === "success") {
                    if (data.trim() === "existe") {
                        mostrarErrorCampo(campoNativo, "Este email ya está registrado en otra cuenta.");
                        campoNativo.setCustomValidity("Email repetido."); 
                    } else if (data.trim() === "disponible") {
                        limpiarErrorCampo(campoNativo);
                    }
                }
            });
        });
    }
});