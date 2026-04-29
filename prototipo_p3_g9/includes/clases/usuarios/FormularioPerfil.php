<?php
namespace es\ucm\fdi\aw\usuarios;

use es\ucm\fdi\aw\Formulario;
use es\ucm\fdi\aw\usuarios\Usuario;

class FormularioPerfil extends Formulario
{
    public function __construct() {
        parent::__construct('formPerfil', [
            'urlRedireccion' => 'perfil.php?exito=1',
            'enctype' => 'multipart/form-data'
        ]);
    }

    protected function generaCamposFormulario(&$datos)
    {
        $idUsuario    = $_SESSION['id'] ?? null;
        $usuarioActual = null;

        if ($idUsuario) {
            $usuarioActual = Usuario::buscaUsuario($idUsuario);
        }

        $nombre      = $datos['nombre']    ?? ($usuarioActual ? $usuarioActual->getNombre()    : '');
        $apellidos   = $datos['apellidos'] ?? ($usuarioActual ? $usuarioActual->getApellidos() : '');
        $email       = $datos['email']     ?? ($usuarioActual ? $usuarioActual->getEmail()     : '');
        $avatarActual = $usuarioActual ? $usuarioActual->getAvatar() : 'default.png';

        $erroresGlobales = self::generaListaErroresGlobales($this->errores);
        $erroresNombre   = self::createMensajeError($this->errores, 'nombre',        'span', ['class' => 'error']);
        $erroresEmail    = self::createMensajeError($this->errores, 'email',         'span', ['class' => 'error']);
        $erroresFile     = self::createMensajeError($this->errores, 'avatar_subida', 'span', ['class' => 'error']);

        $html = <<<EOF
        $erroresGlobales
        <fieldset>
            <legend>Información Personal</legend>
            <div class="mb-15">
                <label>Nombre:</label>
                <input type="text" name="nombre" value="$nombre" required>
                $erroresNombre
            </div>
            <div class="mb-15">
                <label>Apellidos:</label>
                <input type="text" name="apellidos" value="$apellidos">
            </div>
            <div class="mb-15">
                <label>Email:</label>
                <input type="email" name="email" value="$email" required>
                $erroresEmail
            </div>

            <hr class="hr-sep">

            <h3 class="mt-0">Cambiar Avatar</h3>

            <div class="mb-15">
                <p><strong>A) Personajes divertidos:</strong></p>
                <div class="avatares-grid">
                    <label class="avatar-label">
                        <input type="radio" name="avatar_opcion" value="predefinidos/chewbacca.png">
                        <br><img src="img/avatares/predefinidos/chewbacca.png" width="50" alt="Chewie">
                    </label>
                    <label class="avatar-label">
                        <input type="radio" name="avatar_opcion" value="predefinidos/jake.png">
                        <br><img src="img/avatares/predefinidos/jake.png" width="50" alt="Jake">
                    </label>
                    <label class="avatar-label">
                        <input type="radio" name="avatar_opcion" value="predefinidos/moe.png">
                        <br><img src="img/avatares/predefinidos/moe.png" width="50" alt="Moe">
                    </label>
                    <label class="avatar-label">
                        <input type="radio" name="avatar_opcion" value="predefinidos/perry.png">
                        <br><img src="img/avatares/predefinidos/perry.png" width="50" alt="Perry">
                    </label>
                </div>
            </div>

            <div class="seccion-avatar">
                <p><strong>B) Subir tu propia foto:</strong></p>
                <input type="file" name="avatar_subida" accept="image/*">
                $erroresFile
            </div>

            <div class="seccion-avatar">
                <p><strong>C) Restaurar:</strong></p>
                <label><input type="radio" name="avatar_opcion" value="default.png"> Volver al avatar por defecto</label>
            </div>

            <br>
            <button type="submit" class="btn-exito btn-lg">Guardar Cambios</button>
        </fieldset>
EOF;
        return $html;
    }

    protected function procesaFormulario(&$datos)
    {
        $this->errores = [];
        $idUsuario = $_SESSION['id'];

        $nombre    = trim($datos['nombre']    ?? '');
        $apellidos = trim($datos['apellidos'] ?? '');
        $email     = trim($datos['email']     ?? '');

        if (!$nombre) $this->errores['nombre'] = 'El nombre es obligatorio.';
        if (!$email)  $this->errores['email']  = 'El email es obligatorio.';

        if (count($this->errores) > 0) return;

        $userOld        = Usuario::buscaUsuario($idUsuario);
        $avatarFinal    = $userOld ? $userOld->getAvatar() : 'default.png';
        $hayCambioDeImagen = false;

        if (isset($_FILES['avatar_subida']) && $_FILES['avatar_subida']['error'] === UPLOAD_ERR_OK) {
            $archivo   = $_FILES['avatar_subida'];
            $extension = pathinfo($archivo['name'], PATHINFO_EXTENSION);

            if (!in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                $this->errores['avatar_subida'] = "Solo se permiten imágenes jpg, png, gif o webp.";
                return;
            }

            $nombreArchivo = time() . "_" . uniqid() . "." . $extension;
            $rutaDestino   = "img/avatares/usuarios/" . $nombreArchivo;

            if (move_uploaded_file($archivo['tmp_name'], $rutaDestino)) {
                $avatarFinal       = $nombreArchivo;
                $hayCambioDeImagen = true;
            } else {
                $this->errores['avatar_subida'] = "Error al mover el archivo al servidor.";
                return;
            }
        } elseif (isset($datos['avatar_opcion']) && !empty($datos['avatar_opcion'])) {
            $avatarFinal       = $datos['avatar_opcion'];
            $hayCambioDeImagen = true;
        }

        if ($hayCambioDeImagen && $userOld) {
            $avatarViejo   = $userOld->getAvatar();
            $esPredefinido = strpos($avatarViejo, 'predefinidos/') !== false;
            $esDefault     = ($avatarViejo === 'default.png');

            if (!$esPredefinido && !$esDefault) {
                if (file_exists("img/avatares/usuarios/" . $avatarViejo)) {
                    unlink("img/avatares/usuarios/" . $avatarViejo);
                }
            }
        }

        if (Usuario::actualizaUsuario($idUsuario, $nombre, $apellidos, $email, $avatarFinal, $_SESSION['rol'])) {
            $_SESSION['nombre'] = $nombre;
            return $this->urlRedireccion;
        } else {
            $this->errores[] = "Error en la base de datos al guardar los cambios.";
        }
    }
}
