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
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="JS/validaciones.js"></script>
        <script src="JS/registro.js"></script>
        
        </script>
        EOF;
        return $html;
    }

    protected function insertaUsuario($datos)
        {
            // 1. DOBLE COMPROBACIÓN DE SEGURIDAD EN EL SERVIDOR
            
            // ¿Existe ya el nombre de usuario?
            if (Usuario::buscaUsuarioPorNombre($datos['nombre_usuario'])) {
                $this->errores['nombre_usuario'] = "Error: El nombre de usuario ya está en uso.";
            }
            
            // ¿Existe ya el email?
            if (Usuario::buscaUsuarioPorEmail($datos['email'])) {
                $this->errores['email'] = "Error: Este email ya está registrado.";
            }

            // 2. Si la lista de errores está vacía, significa que todo es correcto
            if (count($this->errores) === 0) {
                // Llamamos al modelo para insertar
                if (Usuario::creaUsuario($datos['nombre_usuario'], $datos['password'], $datos['nombre'], $datos['apellidos'], $datos['email'], 'cliente')) {
                    return $this->urlRedireccion;
                } else {
                    $this->errores[] = "Error desconocido al crear el usuario en la base de datos.";
                }
            }
        }
}