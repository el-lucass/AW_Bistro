<?php
namespace es\ucm\fdi\aw\usuarios;

use es\ucm\fdi\aw\Formulario;
use es\ucm\fdi\aw\usuarios\Usuario;

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
        // 1. Obtener datos del usuario logueado usando la nueva clase
        $idUsuario = $_SESSION['id'] ?? null;
        $usuarioActual = null;
        
        if ($idUsuario) {
            $usuarioActual = Usuario::buscaUsuario($idUsuario);
        }

        // 2. Rellenamos las variables. 
        // Si $datos tiene algo, es que venimos de un error de validación y mantenemos lo escrito.
        // Si está vacío, sacamos los datos de la Base de Datos usando los GETTERS.
        $nombre = $datos['nombre'] ?? ($usuarioActual ? $usuarioActual->getNombre() : '');
        $apellidos = $datos['apellidos'] ?? ($usuarioActual ? $usuarioActual->getApellidos() : '');
        $email = $datos['email'] ?? ($usuarioActual ? $usuarioActual->getEmail() : '');
        $avatarActual = $usuarioActual ? $usuarioActual->getAvatar() : 'default.png';
        
        // Errores
        $erroresGlobales = self::generaListaErroresGlobales($this->errores);
        $erroresNombre = self::createMensajeError($this->errores, 'nombre', 'span', ['class' => 'error', 'style' => 'color:red; font-size: 0.9em; display:block;']);
        $erroresEmail = self::createMensajeError($this->errores, 'email', 'span', ['class' => 'error', 'style' => 'color:red; font-size: 0.9em; display:block;']);
        $erroresFile = self::createMensajeError($this->errores, 'avatar_subida', 'span', ['class' => 'error', 'style' => 'color:red; font-size: 0.9em; display:block;']);

        // --- HTML DEL FORMULARIO ---
        $html = <<<EOF
        $erroresGlobales
        <fieldset style="border: 1px solid #ccc; padding: 20px; border-radius: 8px;">
            <legend style="font-weight: bold; padding: 0 5px;">Información Personal</legend>
            <div style="margin-bottom: 15px;">
                <label style="display:block; margin-bottom: 5px;">Nombre:</label> 
                <input type="text" name="nombre" value="$nombre" required style="width:100%; padding:8px; box-sizing:border-box;">
                $erroresNombre
            </div>
            <div style="margin-bottom: 15px;">
                <label style="display:block; margin-bottom: 5px;">Apellidos:</label> 
                <input type="text" name="apellidos" value="$apellidos" style="width:100%; padding:8px; box-sizing:border-box;">
            </div>
            <div style="margin-bottom: 15px;">
                <label style="display:block; margin-bottom: 5px;">Email:</label> 
                <input type="email" name="email" value="$email" required style="width:100%; padding:8px; box-sizing:border-box;">
                $erroresEmail
            </div>
            
            <hr style="margin: 20px 0;">
            
            <h3 style="margin-top:0;">Cambiar Avatar</h3>
            
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
            <button type="submit" style="background: #27ae60; color: white; padding: 10px 20px; cursor:pointer; border:none; border-radius:5px; font-size:16px;">Guardar Cambios</button>
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
        // OJO AQUÍ: Llamamos a Usuario::buscaUsuario y usamos el getter ->getAvatar()
        $userOld = Usuario::buscaUsuario($idUsuario); 
        $avatarFinal = $userOld ? $userOld->getAvatar() : 'default.png'; 
        $hayCambioDeImagen = false;

        // PRIORIDAD 1: ¿Ha subido un archivo?
        if (isset($_FILES['avatar_subida']) && $_FILES['avatar_subida']['error'] === UPLOAD_ERR_OK) {
            $archivo = $_FILES['avatar_subida'];
            $extension = pathinfo($archivo['name'], PATHINFO_EXTENSION);
            
            if (!in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                $this->errores['avatar_subida'] = "Solo se permiten imágenes jpg, png, gif o webp.";
                return;
            }

            $nombreArchivo = time() . "_" . uniqid() . "." . $extension;
            $rutaDestino = "img/avatares/usuarios/" . $nombreArchivo;

            if (move_uploaded_file($archivo['tmp_name'], $rutaDestino)) {
                $avatarFinal = $nombreArchivo;
                $hayCambioDeImagen = true;
            } else {
                $this->errores['avatar_subida'] = "Error al mover el archivo al servidor.";
                return;
            }
        } 
        // PRIORIDAD 2: ¿Ha seleccionado un Radio Button (Personaje o Default)?
        elseif (isset($datos['avatar_opcion']) && !empty($datos['avatar_opcion'])) {
            $avatarFinal = $datos['avatar_opcion']; 
            $hayCambioDeImagen = true;
        }

        // 3. Limpieza de basura (Fotos viejas)
        if ($hayCambioDeImagen && $userOld) {
            $avatarViejo = $userOld->getAvatar();
            $esPredefinido = strpos($avatarViejo, 'predefinidos/') !== false;
            $esDefault = ($avatarViejo === 'default.png');
            
            if (!$esPredefinido && !$esDefault) {
                if (file_exists("img/avatares/usuarios/" . $avatarViejo)) {
                    unlink("img/avatares/usuarios/" . $avatarViejo);
                }
            }
        }

        // 4. Actualizar BD (Llamando al método estático)
        if (Usuario::actualizaUsuario($idUsuario, $nombre, $apellidos, $email, $avatarFinal, $_SESSION['rol'])) {
            $_SESSION['nombre'] = $nombre; // Actualizamos el nombre en sesión por si ha cambiado
            return $this->urlRedireccion;
        } else {
            $this->errores[] = "Error en la base de datos al guardar los cambios.";
        }
    }
}