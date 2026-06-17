<?php
namespace es\ucm\fdi\aw\recompensas;

use es\ucm\fdi\aw\Formulario;
use es\ucm\fdi\aw\productos\Producto;

class FormularioCrearRecompensa extends Formulario
{
    public function __construct()
    {
        parent::__construct('formCrearRecompensa', [
            'urlRedireccion' => 'recompensas.php'
        ]);
    }

    protected function generaCamposFormulario(&$datos)
    {

        $htmlErrores = '';

        if (!empty($this->errores)) {
            $htmlErrores = "<div class='alerta-error mb-20'><ul>";
            foreach ($this->errores as $error) {
                $htmlErrores .= "<li>" . htmlspecialchars($error) . "</li>";
            }
        $htmlErrores .= "</ul></div>";
        
        }
        $productos = Producto::listaProductos(true);

        $opciones = "<option value=''>Selecciona un producto...</option>";

        foreach ($productos as $p) {
            $id = $p->getId();
            $nombre = htmlspecialchars($p->getNombre());
            $opciones .= "<option value='{$id}'>{$nombre}</option>";
        }

        return <<<EOF
        {$htmlErrores}
        <div class="form-group">
            <label>Producto de la carta:</label>
            <select name="id_producto" class="form-control" required>
                {$opciones}
            </select>
        </div>

        <div class="form-group">
            <label>BistroCoins necesarios:</label>
            <input class="form-control" type="number" name="bistrocoins" min="1" required placeholder="Ej: 25">
        </div>

        <div class="form-group mt-20">
            <button type="submit" class="btn-crear btn-lg">Crear Recompensa</button>
        </div>
EOF;
    }

    protected function procesaFormulario(&$datos)
    {
        $this->errores = [];

        $idProducto = intval($datos['id_producto'] ?? 0);
        $bistrocoins = intval($datos['bistrocoins'] ?? 0);

        if ($idProducto <= 0) {
            $this->errores[] = "Debes seleccionar un producto.";
        }

        if ($bistrocoins <= 0) {
            $this->errores[] = "Los BistroCoins deben ser mayores que 0.";
        }

        if (Recompensa::existeRecompensaProducto($idProducto)) {
           $this->errores[] = "Este producto ya tiene una recompensa asociada.";
        }

        if (empty($this->errores)) {
            $exito = Recompensa::creaRecompensa($idProducto, $bistrocoins);

            if (!$exito) {
                $this->errores[] = "Error al guardar la recompensa en la base de datos.";
            }
        }
    }
}