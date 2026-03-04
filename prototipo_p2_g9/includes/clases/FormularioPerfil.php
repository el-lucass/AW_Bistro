<?php
require_once 'Formulario.php';
require_once __DIR__ . '/../usuarios.php';

class FormularioPerfil extends Formulario
{
    public function __construct() {
        parent::__construct('formPerfil', [
            'urlRedireccion' => 'perfil.php?exito=1',
            'enctype' => 'multipart/form-data' // Vital para subir archivos
        ]);
    }

    protected function generaCamposFormulario(&$datos)
    {
        // Obtener datos del usuario logueado
        $idUsuario = $_SESSION['id'] ?? null;
        if (!$datos && $idUsuario) {
            $datos = buscaUsuario($idUsuario);
        }

        $nombre = $datos['nombre'] ?? '';
        $apellidos = $datos['apellidos'] ?? '';
        $email = $datos['email'] ?? '';
        
        // Errores
        $erroresGlobales = self::generaListaErroresGlobales($this->errores);
        $erroresNombre = self::createMensajeError($this->errores, 'nombre', 'span', ['class' => 'error']);
        $erroresEmail = self::createMensajeError($this->errores, 'email', 'span', ['class' => 'error']);
        $erroresFile = self::createMensajeError($this->errores, 'avatar_subida', 'span', ['class' => 'error']);

        // --- HTML DEL FORMULARIO ---
        // Fíjate que he copiado tu estructura de Fieldset y Radio Buttons
        $html = <<<EOF
        $erroresGlobales
        <fieldset>
            <legend>Información Personal</legend>
            <div>
                <label>Nombre:</label> 
                <input type="text" name="nombre" value="$nombre" required>
                $erroresNombre
            </div>
            <div>
                <label>Apellidos:</label> 
                <input type="text" name="apellidos" value="$apellidos">
            </div>
            <div>
                <label>Email:</label> 
                <input type="email" name="email" value="$email" required>
                $erroresEmail
            </div>
            
            <hr style="margin: 20px 0;">
            
            <h3>Cambiar Avatar</h3>
            
            <div style="margin-bottom: 15px;">
                <p><strong>A) Personajes divertidos:</strong></p>
                <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                    <label style="text-align:center; cursor:pointer;">
                        <input type="radio" name="avatar_opcion" value="predefinidos/chewbacca.png">
                        <br><img src="img/avatares/predefinidos/chewbacca.png" width="50" alt="Chewie">
                    </label>
                    <label style="text-align:center; cursor:pointer;">
                        <input type="radio" name="avatar_opcion" value="predefinidos/jake.png">
                        <br><img src="img/avatares/predefinidos/jake.png" width="50" alt="Jake">
                    </label>
                    <label style="text-align:center; cursor:pointer;">
                        <input type="radio" name="avatar_opcion" value="predefinidos/moe.png">
                        <br><img src="img/avatares/predefinidos/moe.png" width="50" alt="Moe">
                    </label>
                    <label style="text-align:center; cursor:pointer;">
                        <input type="radio" name="avatar_opcion" value="predefinidos/perry.png">
                        <br><img src="img/avatares/predefinidos/perry.png" width="50" alt="Perry">
                    </label>
                </div>
            </div>

            <div style="margin-bottom: 15px; border-top: 1px dashed #ccc; padding-top:10px;">
                <p><strong>B) Subir tu propia foto:</strong></p>
                <input type="file" name="avatar_subida" accept="image/*">
                $erroresFile
            </div>

            <div style="margin-bottom: 15px; border-top: 1px dashed #ccc; padding-top:10px;">
                <p><strong>C) Restaurar:</strong></p>
                <label><input type="radio" name="avatar_opcion" value="default.png"> Volver al avatar por defecto</label>
            </div>

            <br>
            <button type="submit" style="background: #27ae60; color: white; padding: 10px 20px; cursor:pointer; border:none; border-radius:5px;">Guardar Cambios</button>
        </fieldset>
EOF;
        return $html;
    }

    protected function procesaFormulario(&$datos)
    {
        $this->errores = [];
        $idUsuario = $_SESSION['id'];
        
        // 1. Datos Personales
        $nombre = trim($datos['nombre'] ?? '');
        $apellidos = trim($datos['apellidos'] ?? '');
        $email = trim($datos['email'] ?? '');

        if (!$nombre) $this->errores['nombre'] = 'El nombre es obligatorio.';
        if (!$email) $this->errores['email'] = 'El email es obligatorio.';
        
        if (count($this->errores) > 0) return;

        // 2. Lógica del Avatar (Prioridades)
        $userOld = buscaUsuario($idUsuario); // Necesitamos saber qué tenía antes para borrarlo si hace falta
        $avatarFinal = $userOld['avatar']; // Por defecto, no cambia
        $hayCambioDeImagen = false;

        // PRIORIDAD 1: ¿Ha subido un archivo?
        if (isset($_FILES['avatar_subida']) && $_FILES['avatar_subida']['error'] === UPLOAD_ERR_OK) {
            $archivo = $_FILES['avatar_subida'];
            $extension = pathinfo($archivo['name'], PATHINFO_EXTENSION);
            
            // Validar extensión
            if (!in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                $this->errores['avatar_subida'] = "Solo se permiten imágenes jpg, png, gif.";
                return;
            }

            $nombreArchivo = time() . "_" . uniqid() . "." . $extension;
            $rutaDestino = "img/avatares/usuarios/" . $nombreArchivo;

            if (move_uploaded_file($archivo['tmp_name'], $rutaDestino)) {
                $avatarFinal = $nombreArchivo;
                $hayCambioDeImagen = true;
            } else {
                $this->errores['avatar_subida'] = "Error al mover el archivo.";
                return;
            }
        } 
        // PRIORIDAD 2: ¿Ha seleccionado un Radio Button (Personaje o Default)?
        elseif (isset($datos['avatar_opcion']) && !empty($datos['avatar_opcion'])) {
            $avatarFinal = $datos['avatar_opcion']; // Esto valdrá "predefinidos/jake.png" o "default.png"
            $hayCambioDeImagen = true;
        }

        // 3. Limpieza de basura
        // Si ha cambiado la imagen, y la imagen ANTIGUA era una subida por el usuario (no default ni predefinida)
        // entonces borramos la vieja para no ocupar espacio en el servidor.
        if ($hayCambioDeImagen) {
            $avatarViejo = $userOld['avatar'];
            $esPredefinido = strpos($avatarViejo, 'predefinidos/') !== false;
            $esDefault = ($avatarViejo === 'default.png');
            
            // Solo borramos si NO es predefinido y NO es default
            if (!$esPredefinido && !$esDefault) {
                if (file_exists("img/avatares/usuarios/" . $avatarViejo)) {
                    unlink("img/avatares/usuarios/" . $avatarViejo);
                }
            }
        }

        // 4. Actualizar BD
        if (actualizaUsuario($idUsuario, $nombre, $apellidos, $email, $avatarFinal, $_SESSION['rol'])) {
            $_SESSION['nombre'] = $nombre; // Actualizar sesión
            return $this->urlRedireccion;
        } else {
            $this->errores[] = "Error en la base de datos.";
        }
    }
}