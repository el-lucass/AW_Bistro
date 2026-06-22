<?php
namespace es\ucm\fdi\aw\usuarios;

use es\ucm\fdi\aw\Formulario;
use es\ucm\fdi\aw\usuarios\Usuario;

class FormularioLogin extends Formulario
{
    public function __construct() {
        parent::__construct('formLogin', ['urlRedireccion' => 'index.php']);
    }

    protected function generaCamposFormulario(&$datos)
    {
        $nombreUsuario = $datos['nombre_usuario'] ?? '';

        $htmlErroresGlobales = self::generaListaErroresGlobales($this->errores);
        $erroresCampos = self::generaErroresCampos(['nombre_usuario', 'password'], $this->errores, 'span', ['class' => 'error']);

        $html = <<<EOF
        $htmlErroresGlobales
        <fieldset class="fieldset-estrecho">
            <legend>Identificación de Usuario</legend>

            <div class="mb-15">
                <label>Usuario:</label>
                <input type="text" name="nombre_usuario" value="$nombreUsuario" required/>
                {$erroresCampos['nombre_usuario']}
            </div>

            <div class="mb-20">
                <label>Contraseña:</label>
                <input type="password" name="password" required/>
                {$erroresCampos['password']}
            </div>

            <div class="texto-centro">
                <button type="submit" class="btn-azul btn-lg">Entrar al Bistro</button>
            </div>
        </fieldset>
        <script>
        document.addEventListener('DOMContentLoaded', function () {
            activarValidacion('formLogin', {
                nombre_usuario: ['requerido'],
                password:       ['requerido']
            });
        });
        </script>
EOF;
        return $html;
    }

    protected function procesaFormulario(&$datos)
    {
        $this->errores = [];

        $nombreUsuario = trim($datos['nombre_usuario'] ?? '');
        $password      = trim($datos['password'] ?? '');

        if (!$nombreUsuario) {
            $this->errores['nombre_usuario'] = "El usuario es obligatorio.";
        }
        if (!$password) {
            $this->errores['password'] = "La contraseña es obligatoria.";
        }

        if (count($this->errores) > 0) {
            return;
        }

        $usuario = Usuario::login($nombreUsuario, $password);

        if (!$usuario) {
            $this->errores[] = "El usuario o la contraseña no coinciden.";
        } else {
            $_SESSION['login']  = true;
            $_SESSION['id']     = $usuario->getId();
            $_SESSION['nombre'] = $usuario->getNombreUsuario();
            $_SESSION['rol']    = $usuario->getRol();

            return $this->urlRedireccion;
        }
    }
}
