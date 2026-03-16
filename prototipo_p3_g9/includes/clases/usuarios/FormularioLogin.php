<?php
// 1. Declaramos el espacio de nombres (namespace)
namespace es\ucm\fdi\aw\usuarios;

// 2. Incluimos las clases que necesitamos
use es\ucm\fdi\aw\Formulario;
use es\ucm\fdi\aw\usuarios\Usuario;

class FormularioLogin extends Formulario
{
    public function __construct() {
        // Inicializamos el formulario con su ID y la URL a la que irá si el login es correcto
        parent::__construct('formLogin', ['urlRedireccion' => 'index.php']);
    }

    protected function generaCamposFormulario(&$datos)
    {
        // Recuperamos el nombre de usuario por si se equivocó en la contraseña (para no hacerle escribirlo de nuevo)
        $nombreUsuario = $datos['nombre_usuario'] ?? '';

        // Usamos los métodos de la clase padre Formulario para pintar los errores
        $htmlErroresGlobales = self::generaListaErroresGlobales($this->errores);
        $erroresCampos = self::generaErroresCampos(['nombre_usuario', 'password'], $this->errores, 'span', array('class' => 'error', 'style' => 'color: red; font-size: 0.9em; display: block;'));

        // Generamos el HTML del formulario
        $html = <<<EOF
        $htmlErroresGlobales
        <fieldset style="max-width: 400px; margin: 0 auto; padding: 20px; border-radius: 8px; border: 1px solid #ccc;">
            <legend style="font-weight: bold; padding: 0 5px;">Identificación de Usuario</legend>
            
            <div style="margin-bottom: 15px;">
                <label style="display: block; font-weight: bold; margin-bottom: 5px;">Usuario:</label>
                <input type="text" name="nombre_usuario" value="$nombreUsuario" required style="width: 100%; padding: 8px; box-sizing: border-box;"/>
                {$erroresCampos['nombre_usuario']}
            </div>
            
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-weight: bold; margin-bottom: 5px;">Contraseña:</label>
                <input type="password" name="password" required style="width: 100%; padding: 8px; box-sizing: border-box;"/>
                {$erroresCampos['password']}
            </div>
            
            <div style="text-align: center;">
                <button type="submit" style="background-color: #2980b9; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px;">
                    Entrar al Bistro
                </button>
            </div>
        </fieldset>
EOF;
        return $html;
    }

    protected function procesaFormulario(&$datos)
    {
        $this->errores = [];
        
        // Limpiamos los datos de entrada
        $nombreUsuario = trim($datos['nombre_usuario'] ?? '');
        $password = trim($datos['password'] ?? '');

        // 1. Validaciones básicas
        if (!$nombreUsuario) {
            $this->errores['nombre_usuario'] = "El usuario es obligatorio.";
        }
        if (!$password) {
            $this->errores['password'] = "La contraseña es obligatoria.";
        }

        if (count($this->errores) > 0) {
            return; // Si hay errores, paramos aquí y el formulario se vuelve a pintar
        }

        // 2. Interacción con el Modelo (Clase Usuario)
        // OJO AQUÍ: Llamamos al método estático login() de la clase Usuario
        $usuario = Usuario::login($nombreUsuario, $password);

        if (!$usuario) {
            // Error genérico por seguridad (no especificamos si falló el usuario o la contraseña)
            $this->errores[] = "El usuario o la contraseña no coinciden.";
        } else {
            // 3. Login correcto: Guardamos los datos en la sesión
            // Como $usuario ahora es un OBJETO, usamos sus métodos getter: ->getId(), ->getNombreUsuario()...
            $_SESSION['login'] = true;
            $_SESSION['id'] = $usuario->getId();
            // Guardamos el nombre real o el de usuario según prefieras para la cabecera
            $_SESSION['nombre'] = $usuario->getNombreUsuario(); 
            $_SESSION['rol'] = $usuario->getRol();
            
            // Retornamos la URL para que la clase Formulario haga el header('Location: ...')
            return $this->urlRedireccion;
        }
    }
}