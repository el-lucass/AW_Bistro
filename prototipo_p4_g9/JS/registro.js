document.addEventListener('DOMContentLoaded', function () {
            // 1. Validaciones básicas de validaciones.js
            activarValidacion('formRegistro', {
                nombre_usuario: ['requerido', ['minLen', 3], ['maxLen', 30]],
                password:       ['requerido', ['minLen', 6]],
                password2:      ['requerido', ['coincideCon', 'password']],
                nombre:         ['requerido', ['maxLen', 50]],
                email:          ['requerido', 'email']
            });

            // 2. Comprobación visual de contraseña
            var inputPass = document.querySelector('#formRegistro input[name="password"]');
            if (inputPass) {
                var spanFortaleza = document.createElement('span');
                spanFortaleza.className = 'fortaleza-password';
                spanFortaleza.style.marginLeft = '8px';
                inputPass.parentNode.appendChild(spanFortaleza);
                inputPass.addEventListener('input', function () {
                    if (!inputPass.value) { spanFortaleza.textContent = ''; return; }
                    var f = fortalezaPassword(inputPass.value);
                    spanFortaleza.textContent = f.mensaje;
                    spanFortaleza.style.color = (f.nivel === 'débil') ? '#b00' : (f.nivel === 'media') ? '#a60' : '#0a0';
                });
            }

            // 3.: VALIDACIÓN AJAX DE USUARIO
            var campoUsuario = document.querySelector('#formRegistro input[name="nombre_usuario"]');
            
            if (campoUsuario) {
                // Usamos 'input' para que sea en tiempo real (según teclea), 
                // si quieres que sea al salir del campo como en el ej3, cámbialo a 'change'
                campoUsuario.addEventListener('input', function() {
                    var url = "comprobarUsuario.php?user=" + encodeURIComponent(campoUsuario.value);
                    
                    // Sustituto de $.get(url, usuarioExiste) usando JS nativo
                    fetch(url)
                        .then(response => response.text())
                        .then(data => usuarioExiste(data, campoUsuario)); 
                });
            }
            
            function usuarioExiste(data, campo) {
                campo.setCustomValidity(""); // Limpiar validaciones previas

                if (data.trim() === "existe") {
                    // En vez de usar $("#userMarcador"), usamos la función de tu validaciones.js
                    mostrarErrorCampo(campo, "El usuario ya existe, escoge otro.");
                    campo.setCustomValidity("El usuario ya existe, escoge otro."); // Bloquea el envío HTML5
                } else if (data.trim() === "disponible") {
                    // Si el nombre tiene 3 caracteres o más y está disponible, limpiamos
                    if (campo.value.length >= 3) {
                        limpiarErrorCampo(campo);
                    }
                    else {
                        mostrarErrorCampo(campo, "El nombre del usuario tiene que tener 3 o más caracteres.");
                    }
                }
            }

            // 4. VALIDACION AJAX DE EMAIL
            var campoEmail = document.querySelector('#formRegistro input[name="email"]');
                
                if (campoEmail) {
                    // CAMBIO AQUÍ: Usamos 'input' en lugar de 'change'
                    campoEmail.addEventListener('input', function() {
                        campoEmail.setCustomValidity(""); // Limpiar

                        // A. Verificamos el formato PRIMERO
                        if (!Validaciones.esEmailValido(campoEmail.value)) {
                            mostrarErrorCampo(campoEmail, "Introduce un email válido.");
                            mostrarErrorCampo(campoEmail, "Ejemplo: pepe@ucm.es");
                            // Mientras el email no esté completo (ej: "pepe@g"), bloqueamos sin hacer AJAX
                            campoEmail.setCustomValidity("Email inválido");
                            return;
                        }

                        // B. Si el formato ya es bueno (ej: "pepe@gmail.com"), lanzamos el AJAX en tiempo real
                        var url = "comprobarEmail.php?email=" + encodeURIComponent(campoEmail.value);
                        
                        fetch(url)
                            .then(response => response.text())
                            .then(data => emailExiste(data, campoEmail)); 
                    });
                }

                function emailExiste(data, campo) {
                    campo.setCustomValidity(""); 

                    if (data.trim() === "existe") {
                        mostrarErrorCampo(campo, "Este email ya está registrado en otra cuenta.");
                        campo.setCustomValidity("Email repetido."); // Bloquea el envío HTML5
                    } else if (data.trim() === "disponible") {
                        limpiarErrorCampo(campo);
                    }
                }

        });