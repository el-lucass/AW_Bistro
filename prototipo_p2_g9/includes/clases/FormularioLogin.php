<?php
require_once 'Formulario.php';
require_once __DIR__ . '/../usuarios.php'; // Tu modelo

class FormularioLogin extends Formulario
{
    public function __construct() {
        parent::__construct('formLogin', ['urlRedireccion' => 'index.php']);
    }

    protected function generaCamposFormulario(&$datos)
    {
        // Reutilizamos el nombre si falló la contraseña
        $nombreUsuario = $datos['nombre_usuario'] ?? '';

        // Usamos los helpers de errores de la clase padre
        $htmlErroresGlobales = self::generaListaErroresGlobales($this->errores);
        $erroresCampos = self::generaErroresCampos(['nombre_usuario', 'password'], $this->errores, 'span', array('class' => 'error'));

        $html = <<<EOF
        $htmlErroresGlobales
        <fieldset>
            <legend>Identificación</legend>
            <div>
                <label>Usuario:</label>
                <input type="text" name="nombre_usuario" value="$nombreUsuario" required/>
                {$erroresCampos['nombre_usuario']}
            </div>
            <div>
                <label>Contraseña:</label>
                <input type="password" name="password" required/>
                {$erroresCampos['password']}
            </div>
            <button type="submit">Entrar</button>
        </fieldset>
EOF;
        return $html;
    }

    protected function procesaFormulario(&$datos)
    {
        $this->errores = [];
        $nombreUsuario = trim($datos['nombre_usuario'] ?? '');
        $password = trim($datos['password'] ?? '');

        if (!$nombreUsuario || !$password) {
            $this->errores[] = "Usuario y contraseña son obligatorios";
            return;
        }

        // Usamos la función de tu modelo includes/usuarios.php
        $usuario = login($nombreUsuario, $password);

        if (!$usuario) {
            $this->errores[] = "Usuario o contraseña incorrectos";
        } else {
            // Login correcto: Guardamos en sesión
            $_SESSION['login'] = true;
            $_SESSION['nombre'] = $usuario['nombre_usuario']; // O 'nombre'
            $_SESSION['id'] = $usuario['id'];
            $_SESSION['rol'] = $usuario['rol'];
            
            // Retornamos la URL de éxito
            return $this->urlRedireccion;
        }
    }
}