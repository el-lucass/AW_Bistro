<?php
namespace es\ucm\fdi\aw\usuarios;

use es\ucm\fdi\aw\Formulario;
use es\ucm\fdi\aw\usuarios\Usuario;
/**
 * Clase intermedia que gestiona los campos comunes (Nombre, Usuario, Pass, Email)
 * y la validación básica.
 */
abstract class FormularioUsuarioBase extends Formulario
{
    // Método que devuelve el HTML de los campos comunes.
    // $incluyePassword2 = true añade el campo "Confirmar contraseña" (registro / alta admin).
    protected function generaCamposBasicos(&$datos, $incluyePassword2 = false)
    {
        // Recuperamos valores para mantenerlos si hay error
        $usuario = $datos['nombre_usuario'] ?? '';
        $nombre = $datos['nombre'] ?? '';
        $apellidos = $datos['apellidos'] ?? '';
        $email = $datos['email'] ?? '';

        // Gestión de errores específicos de campos usando la función de tu clase base
        $erroresCampos = self::generaErroresCampos(['nombre_usuario', 'password', 'password2', 'nombre', 'email'], $this->errores, 'span', array('class' => 'error'));

        $bloquePassword2 = '';
        if ($incluyePassword2) {
            $bloquePassword2 = <<<EOF
            <div>
                <label>Confirmar contraseña:</label>
                <input type="password" name="password2" required />
                {$erroresCampos['password2']}
            </div>
EOF;
        }

        $html = <<<EOF
        <fieldset>
            <legend>Datos de la cuenta</legend>
            <div>
                <label>Usuario:</label>
                <input type="text" name="nombre_usuario" value="$usuario" required />
                {$erroresCampos['nombre_usuario']}
            </div>
            <div>
                <label>Contraseña:</label>
                <input type="password" name="password" required />
                {$erroresCampos['password']}
            </div>
            $bloquePassword2
        </fieldset>

        <fieldset>
            <legend>Datos personales</legend>
            <div>
                <label>Nombre:</label>
                <input type="text" name="nombre" value="$nombre" required />
                {$erroresCampos['nombre']}
            </div>
            <div>
                <label>Apellidos:</label>
                <input type="text" name="apellidos" value="$apellidos" />
            </div>
            <div>
                <label>Email:</label>
                <input type="email" name="email" value="$email" required />
                {$erroresCampos['email']}
            </div>
        </fieldset>
EOF;
        return $html;
    }

    // Validación común para todos
    protected function procesaFormulario(&$datos)
    {
        $this->errores = [];

        $usuario = trim($datos['nombre_usuario'] ?? '');
        $pass = trim($datos['password'] ?? '');
        $nombre = trim($datos['nombre'] ?? '');
        $email = trim($datos['email'] ?? '');

        // 1. Validaciones básicas
        if (empty($usuario)) { $this->errores['nombre_usuario'] = 'El nombre de usuario es obligatorio.'; }
        if (empty($pass)) { $this->errores['password'] = 'La contraseña es obligatoria.'; }
        if (empty($nombre)) { $this->errores['nombre'] = 'El nombre es obligatorio.'; }
        if (empty($email)) { $this->errores['email'] = 'El email es obligatorio.'; }

        // 1b. Si el formulario envía confirmación de contraseña, debe coincidir.
        if (isset($datos['password2'])) {
            $pass2 = trim($datos['password2']);
            if ($pass !== $pass2) {
                $this->errores['password2'] = 'Las contraseñas no coinciden.';
            }
        }

        // 2. Verificar si usuario existe (usando tu modelo includes/usuarios.php)
        if (empty($this->errores) && Usuario::buscaUsuarioPorNombre($usuario)) {
            $this->errores['nombre_usuario'] = 'El usuario ya existe.';
        }

        // Si hay errores, no seguimos
        if (count($this->errores) > 0) {
            return;
        }

        // Si todo está bien, llamamos al método abstracto que definirá cada hijo
        // Esto permite que el registro use 'cliente' y el admin use lo que quiera.
        return $this->insertaUsuario($datos);
    }

    // Método que obligamos a implementar a los hijos
    abstract protected function insertaUsuario($datos);
}