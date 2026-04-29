<?php
namespace es\ucm\fdi\aw\usuarios;

use es\ucm\fdi\aw\usuarios\FormularioUsuarioBase;
use es\ucm\fdi\aw\usuarios\Usuario;


class FormularioRegistro extends FormularioUsuarioBase
{
    public function __construct() {
        // ID del formulario y Redirección al login tras éxito
        parent::__construct('formRegistro', ['urlRedireccion' => 'login.php?registrado=1']);
    }

    protected function generaCamposFormulario(&$datos)
    {
        // 1. Traemos los campos del padre, incluyendo el campo de confirmar contraseña
        $html = parent::generaCamposBasicos($datos, true);

        // 2. Añadimos el botón y el activador de validaciones cliente
        $html .= <<<EOF
        <div class="mt-20">
            <button type="submit">Registrarse</button>
        </div>
        <script>
        document.addEventListener('DOMContentLoaded', function () {
            activarValidacion('formRegistro', {
                nombre_usuario: ['requerido', ['minLen', 3], ['maxLen', 30]],
                password:       ['requerido', ['minLen', 6]],
                password2:      ['requerido', ['coincideCon', 'password']],
                nombre:         ['requerido', ['maxLen', 50]],
                email:          ['requerido', 'email']
            });
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
        });
        </script>
EOF;
        return $html;
    }

    protected function insertaUsuario($datos)
    {
        // Llamamos al modelo con rol 'cliente' fijo
        if (Usuario::creaUsuario($datos['nombre_usuario'], $datos['password'], $datos['nombre'], $datos['apellidos'], $datos['email'], 'cliente')) {
            // Si devuelve true, retornamos la URL de redirección (login)
            return $this->urlRedireccion;
        }
        
        // Si falla la BD
        $this->errores[] = "Error desconocido al crear el usuario en base de datos.";
    }
}