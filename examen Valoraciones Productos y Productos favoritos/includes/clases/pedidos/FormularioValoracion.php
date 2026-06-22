<?php
namespace es\ucm\fdi\aw\pedidos;

use es\ucm\fdi\aw\Formulario;
use es\ucm\fdi\aw\pedidos\Valoracion;

class FormularioValoracion extends Formulario
{
    private $id_producto;

    public function __construct($id_producto)
    {
        parent::__construct('formValorarProducto', [
            'urlRedireccion' => 'historial_pedidos.php'
        ]);
        $this->id_producto = $id_producto;
    }

     protected function generaCamposFormulario(&$datos)
    {

        $html = <<<EOF
            <fieldset class="fieldset-estrecho">
                <legend>Identificación de Usuario</legend>

                <div class="form-group form-group-sm">
                    <label>Puntación: (0-5):</label>
                    <input type="number" step="0.01" min="0" max="5" name="puntuacion" class="form-control" placeholder="Ej: 2.50" required>
                </div>
                <div class="form-group">
                    <label>Comentario:</label>
                    <textarea class="form-control" name="comentario" rows="3" placeholder="Ej: Café + Tostada entera a un precio especial"></textarea>
                </div>
                <div class="form-group mt-20">
                    <button type="submit" class="btn-editar btn-lg">Actualizar Valoración</button>
                </div>
            </fieldset>
        EOF;
        return $html;
    }

    protected function procesaFormulario(&$datos)
    {
        $this->errores = [];

        $puntuacion = intval($datos['puntuacion'] ?? 0);
        $comentario       = trim($datos['comentario']       ?? '');

        if (empty($puntuacion)) {
            $this->errores[] = "La puntuacion es obligatoria.";
            return;
        }
        else {
            $id_usuario = $_SESSION['id'];
            $id_producto = $this->id_producto;
            Valoracion::actualizaValoracion($id_usuario, $id_producto, $puntuacion, $comentario);
        }
    }
}
