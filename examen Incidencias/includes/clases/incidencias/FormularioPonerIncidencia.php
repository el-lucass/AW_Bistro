<?php
namespace es\ucm\fdi\aw\incidencias;

use es\ucm\fdi\aw\Formulario;
use es\ucm\fdi\aw\incidencias\Incidencia;
use es\ucm\fdi\aw\pedidos\Pedido;


class FormularioPonerIncidencia extends Formulario
{
    private $id_pedido;

    public function __construct($id_pedido) {
        $this->id_pedido = $id_pedido;
        parent::__construct('formPonerIncidencia', [
            'urlRedireccion' => 'historial_pedidos.php?msg=Incidencia puesta',
                        'enctype' => 'multipart/form-data'
            ], 
        );
    }

    protected function generaCamposFormulario(&$datos)
    {
        // Si no hay datos POST (primera carga), los sacamos de la BD usando el nuevo modelo


        $erroresGlobales = self::generaListaErroresGlobales($this->errores);
        $erroresFile     = self::createMensajeError($this->errores, 'foto_incidencia', 'span', ['class' => 'error']);
        $erroresDescripcion   = self::createMensajeError($this->errores, 'descripcion', 'span', ['class' => 'error']);

        $causas = Incidencia::opcionesDeCausa();
        $opcionesCausa = '';
        $opcionesCausa .= "<h2> Causas de la incidencia </h2>";
        $opcionesCausa .= "<div>";
        if (empty($causas)) {
            $opcionesCausa = "<span>-- No hay causas disponibles --</span>";
        } else {
            foreach ($causas as $cau) {
                $opcionesCausa .= "
                <label>
                    <input type='checkbox' name='causas[]' value = '{$cau}'>
                    {$cau}
                </label>";
            }
        }
        $opcionesCausa .= "</div>";


        $html = <<<EOF
        $erroresGlobales
        <fieldset>
            {$opcionesCausa}
            <div class="form-group">
                <label>Descripción:</label>
                <textarea class="form-control" name="descripcion" rows="3" required placeholder="Ej: El envoltorio mojado" required></textarea>
                $erroresDescripcion
            </div>

            <div class="seccion-avatar">
                <p><strong>Subir foto de incidencia:</strong></p>
                <input type="file" name="foto_incidencia" accept="image/*">
                $erroresFile
            </div>
            <button type="submit">Guardar Cambios</button>
        </fieldset>
EOF;
        return $html;
    }

    protected function procesaFormulario(&$datos)
    {

        $id_usuario = $_SESSION['id'];
        $id_pedido = $this->id_pedido;       
        $estado = "pendiente";

        $descripcion = trim($datos['descripcion'] ?? '');
        if (!$descripcion) {
            $this->errores['descripcion'] = "La descripción es obligatoria.";
            return;
        }

        $arrayCausas = $datos['causas'] ?? [];
        if(empty($arrayCausas)){
            $this->errores['causas'] = "Elegir al menos una causa es obligatorio";
        }

        $i = 0;
        $causas = "";
        foreach($arrayCausas as $cau){
            if($i != 0){
                $causas .= ",";
            }
            $i++;
            $causas .= "$cau";
        }

        $imagen = '';
        if (isset($_FILES['foto_incidencia']) && $_FILES['foto_incidencia']['error'] === UPLOAD_ERR_OK) {
            $archivo   = $_FILES['foto_incidencia'];
            $extension = pathinfo($archivo['name'], PATHINFO_EXTENSION);

            if (!in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                $this->errores['foto_incidencia'] = "Solo se permiten imágenes jpg, png, gif o webp.";
                return;
            }

            $nombreArchivo = time() . "_" . uniqid() . "." . $extension;
            $rutaDestino   = "img/incidencias/" . $nombreArchivo;

            if (move_uploaded_file($archivo['tmp_name'], $rutaDestino)) {
                $imagen       = $nombreArchivo;
                $hayCambioDeImagen = true;
            } else {
                $this->errores['foto_incidencia'] = "Error al mover el archivo al servidor.";
                return;
            }
        }

        if (Incidencia::ponerIncidencia($id_usuario, $id_pedido, $causas, $descripcion, $imagen, $estado)) {
            $_SESSION['nombre'] = $nombre;
            return $this->urlRedireccion;
        } else {
            $this->errores[] = "Error en la base de datos al guardar los cambios.";
        }
    }
}