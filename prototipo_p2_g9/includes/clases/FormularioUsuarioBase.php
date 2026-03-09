<?php
namespace es\ucm\fdi\aw;


/**
 * Clase intermedia que gestiona los campos comunes (Nombre, Usuario, Pass, Email)
 * y la validación básica.
 */
abstract class FormularioUsuarioBase extends Formulario
{
    // Método que devuelve el HTML de los campos comunes
    protected function generaCamposBasicos(&$datos)
    {
        // Recuperamos valores para mantenerlos si hay error
        $usuario = $datos['nombre_usuario'] ?? '';
        $nombre = $datos['nombre'] ?? '';
        $apellidos = $datos['apellidos'] ?? '';
        $email = $datos['email'] ?? '';

        // Gestión de errores específicos de campos usando la función de tu clase base
        $erroresCampos = self::generaErroresCampos(['nombre_usuario', 'password', 'nombre', 'email'], $this->errores, 'span', array('class' => 'error'));

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