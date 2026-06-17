<?php
namespace es\ucm\fdi\aw\usuarios;

use es\ucm\fdi\aw\Formulario;
use es\ucm\fdi\aw\usuarios\Usuario;

class FormularioAdminEditar extends Formulario
{
    private $idUsuarioEditar;

    public function __construct($idUsuario) {
        $this->idUsuarioEditar = $idUsuario;
        // Al terminar de editar, devolvemos al admin a la lista de usuarios
        parent::__construct('formAdminEditar', ['urlRedireccion' => 'usuarios.php?msg=editado']);
    }

    protected function generaCamposFormulario(&$datos)
    {
        // Si no hay datos POST (primera carga), los sacamos de la BD usando el nuevo modelo
        if (!$datos) {
            $usuario = Usuario::buscaUsuario($this->idUsuarioEditar);
            if ($usuario) {
                $datos['nombre'] = $usuario->getNombre();
                $datos['apellidos'] = $usuario->getApellidos();
                $datos['email'] = $usuario->getEmail();
                $datos['rol'] = $usuario->getRol();
            } else {
                return "<p>Error: Usuario no encontrado.</p>";
            }
        }

        $nombre = $datos['nombre'] ?? '';
        $apellidos = $datos['apellidos'] ?? '';
        $email = $datos['email'] ?? '';
        $rol = $datos['rol'] ?? 'cliente';

        // Preparamos los atributos 'selected' para el desplegable
        $selCliente = ($rol === 'cliente') ? 'selected' : '';
        $selCamarero = ($rol === 'camarero') ? 'selected' : '';
        $selCocinero = ($rol === 'cocinero') ? 'selected' : '';
        $selGerente = ($rol === 'gerente') ? 'selected' : '';

        $erroresGlobales = self::generaListaErroresGlobales($this->errores);

        $html = <<<EOF
        $erroresGlobales
        <fieldset>
            <legend>Datos del Empleado/Cliente</legend>
            <input type="hidden" name="id" value="{$this->idUsuarioEditar}">
            
            <div class="mb-10">
                <label>Nombre:</label>
                <input type="text" name="nombre" value="$nombre" required>
            </div>
            
            <div class="mb-10">
                <label>Apellidos:</label>
                <input type="text" name="apellidos" value="$apellidos">
            </div>
            
            <div class="mb-10">
                <label>Email:</label>
                <input type="email" name="email" value="$email" required>
            </div>
            
            <div class="mb-15">
                <label>Rol:</label>
                <select name="rol">
                    <option value="cliente" $selCliente>Cliente</option>
                    <option value="camarero" $selCamarero>Camarero</option>
                    <option value="cocinero" $selCocinero>Cocinero</option>
                    <option value="gerente" $selGerente>Gerente</option>
                </select>
            </div>
            
            <button type="submit">Guardar Cambios</button>
        </fieldset>
        <script>
        document.addEventListener('DOMContentLoaded', function () {
            activarValidacion('formAdminEditar', {
                nombre: ['requerido', ['maxLen', 50]],
                email:  ['requerido', 'email']
            });
        });
        </script>
EOF;
        return $html;
    }

    protected function procesaFormulario(&$datos)
    {
        $this->errores = [];
        $id = $datos['id'];
        $nombre = trim($datos['nombre'] ?? '');
        $apellidos = trim($datos['apellidos'] ?? '');
        $email = trim($datos['email'] ?? '');
        $rol = trim($datos['rol'] ?? 'cliente');

        if (!$nombre || !$email) {
            $this->errores[] = "Nombre y email son obligatorios.";
            return;
        }

        // Recuperamos el usuario de la BD para no borrarle su avatar actual por accidente
        $usuarioActual = Usuario::buscaUsuario($id);
        if (!$usuarioActual) {
            $this->errores[] = "El usuario ya no existe en la base de datos.";
            return;
        }
        $avatar = $usuarioActual->getAvatar();

        // Llamamos al método estático de la clase Usuario
        if (Usuario::actualizaUsuario($id, $nombre, $apellidos, $email, $avatar, $rol)) {
            return $this->urlRedireccion;
        } else {
            $this->errores[] = "Error al actualizar la base de datos.";
        }
    }
}