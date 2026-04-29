<?php
namespace es\ucm\fdi\aw\recompensas;

use es\ucm\fdi\aw\Formulario;
use es\ucm\fdi\aw\productos\Producto;

class FormularioEditarRecompensa extends Formulario
{
    private $idRecompensa;

    public function __construct($idRecompensa)
    {
        parent::__construct('formEditarRecompensa', [
            'urlRedireccion' => 'recompensas.php'
        ]);

        $this->idRecompensa = $idRecompensa;
    }

    protected function generaCamposFormulario(&$datos)
    {
        $recompensa = Recompensa::buscaRecompensa($this->idRecompensa);

        if (!$recompensa) {
            return "<p class='alerta-error'>Error: No se ha encontrado la recompensa solicitada.</p>";
        }

        $idProductoActual = $datos['id_producto'] ?? $recompensa->getIdProducto();
        $bistrocoins = $datos['bistrocoins'] ?? $recompensa->getBistrocoins();
        $activa = $datos['activa'] ?? $recompensa->getActiva();

        $productos = Producto::listaProductos(true);
        $opciones = "<option value=''>Selecciona un producto...</option>";

        foreach ($productos as $p) {
            $id = $p->getId();
            $nombre = htmlspecialchars($p->getNombre());
            $selected = ($id == $idProductoActual) ? "selected" : "";
            $opciones .= "<option value='{$id}' {$selected}>{$nombre}</option>";
        }

        $checkedActiva = $activa ? "checked" : "";

        return <<<EOF
        <input type="hidden" name="id" value="{$this->idRecompensa}">

        <div class="form-group">
            <label>Producto de la carta:</label>
            <select name="id_producto" class="form-control" required>
                {$opciones}
            </select>
        </div>

        <div class="form-group">
            <label>BistroCoins necesarios:</label>
            <input class="form-control" type="number" name="bistrocoins" min="1" value="{$bistrocoins}" required>
        </div>

        <div class="form-group">
            <label>
                <input type="checkbox" name="activa" value="1" {$checkedActiva}>
                Recompensa activa
            </label>
        </div>

        <div class="form-group mt-20">
            <button type="submit" class="btn-editar btn-lg">Actualizar Recompensa</button>
        </div>
EOF;
    }

    protected function procesaFormulario(&$datos)
    {
        $this->errores = [];

        $id = intval($datos['id'] ?? $this->idRecompensa);
        $idProducto = intval($datos['id_producto'] ?? 0);
        $bistrocoins = intval($datos['bistrocoins'] ?? 0);
        $activa = isset($datos['activa']) ? 1 : 0;

        if ($idProducto <= 0) {
            $this->errores[] = "Debes seleccionar un producto.";
        }

        if ($bistrocoins <= 0) {
            $this->errores[] = "Los BistroCoins deben ser mayores que 0.";
        }

        if (empty($this->errores)) {
            $exito = Recompensa::actualizaRecompensa($id, $idProducto, $bistrocoins, $activa);

            if (!$exito) {
                $this->errores[] = "Error al actualizar la recompensa en la base de datos.";
            }
        }
    }
}