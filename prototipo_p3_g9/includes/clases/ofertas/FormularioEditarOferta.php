<?php
namespace es\ucm\fdi\aw\ofertas;

use es\ucm\fdi\aw\Formulario;
use es\ucm\fdi\aw\productos\Producto;

class FormularioEditarOferta extends Formulario
{
    private $idOferta;

    public function __construct($idOferta)
    {
        parent::__construct('formEditarOferta', [
            'urlRedireccion' => 'ofertas.php' // Redirige a la tabla si todo va bien
        ]);
        $this->idOferta = $idOferta;
    }

    protected function generaCamposFormulario(&$datos)
    {
        //Buscamos los datos actuales de la oferta en la BD
        $ofertaActual = Oferta::buscaOferta($this->idOferta);
        
        if (!$ofertaActual) {
            return "<p style='color:red;'>Error: No se ha encontrado la oferta solicitada.</p>";
        }

        // Valores por defecto: 

        $nombre = $datos['nombre'] ?? $ofertaActual->getNombre();
        $descripcion = $datos['descripcion'] ?? $ofertaActual->getDescripcion();
        
        // Formateamos las fechas para el <input type="datetime-local"> (requiere formato YYYY-MM-DDThh:mm)
        $fechaInicioStr = $datos['fecha_inicio'] ?? $ofertaActual->getFechaInicio();
        $fechaFinStr = $datos['fecha_fin'] ?? $ofertaActual->getFechaFin();
        $fecha_inicio = date('Y-m-d\TH:i', strtotime($fechaInicioStr));
        $fecha_fin = date('Y-m-d\TH:i', strtotime($fechaFinStr));
        
        $porcentaje = $datos['porcentaje_descuento'] ?? $ofertaActual->getPorcentajeDescuento();

        // Preparamos el catálogo de productos
        $todosLosProductos = Producto::listaProductos();
        
        // Opciones en HTML para el JS (cuando se añada una fila nueva)
        $opcionesProductosJS = "<option value=''>Selecciona un producto...</option>";
        foreach ($todosLosProductos as $p) {
            $id = $p->getId();
            $nombreP = htmlspecialchars($p->getNombre());
            $precio = $p->getPrecioBase();
            $opcionesProductosJS .= "<option value='$id' data-precio='$precio'>$nombreP (Base: $precio €)</option>";
        }

        // Productos que YA TIENE la oferta
    
        $productosAsignados = $datos['productos'] ?? $ofertaActual->getProductos();
        $htmlFilasProductos = '';
        $idx = 0;

        if (!empty($productosAsignados)) {
            foreach ($productosAsignados as $prod) {
                // Compatibilidad de claves (dependiendo de si viene de BD o de $_POST)
                $idProd = $prod['id'] ?? $prod['id_producto'] ?? '';
                $cantidad = $prod['cantidad'] ?? $prod['cantidad_requerida'] ?? 1;

                $selectHtml = "<select name='productos[$idx][id]' class='form-control sel-producto' required>";
                $selectHtml .= "<option value=''>Selecciona un producto...</option>";
                
                foreach ($todosLosProductos as $p) {
                    $pId = $p->getId();
                    $pNombre = htmlspecialchars($p->getNombre());
                    $pPrecio = $p->getPrecioBase();
                    $selected = ($pId == $idProd) ? "selected" : "";
                    $selectHtml .= "<option value='$pId' data-precio='$pPrecio' $selected>$pNombre (Base: $pPrecio €)</option>";
                }
                $selectHtml .= "</select>";

                $htmlFilasProductos .= "
                <div class='producto-fila'>
                    $selectHtml
                    <input type='number' name='productos[$idx][cantidad]' class='form-control cant-producto' value='$cantidad' min='1' required style='width: 80px;' title='Cantidad'>
                    <button type='button' class='btn-eliminar' style='background:#e74c3c; color:white; border:none; padding:8px; cursor:pointer;' onclick='eliminarFila(this)'>X</button>
                </div>";
                $idx++;
            }
        } else {
            // Fila de seguridad por si no hay productos
            $htmlFilasProductos .= "
            <div class='producto-fila'>
                <select name='productos[0][id]' class='form-control sel-producto' required>
                    $opcionesProductosJS
                </select>
                <input type='number' name='productos[0][cantidad]' class='form-control cant-producto' value='1' min='1' required style='width: 80px;' title='Cantidad'>
                <button type='button' class='btn-eliminar' style='background:#e74c3c; color:white; border:none; padding:8px; cursor:pointer;' onclick='eliminarFila(this)'>X</button>
            </div>";
            $idx = 1;
        }

        // Escapamos variables para HTML
        $valNombre = htmlspecialchars($nombre);
        $valDesc = htmlspecialchars($descripcion);
        $valPorc = htmlspecialchars($porcentaje);

        //  HTML completo
        $html = <<<EOF
        <style>
            .form-group { margin-bottom: 15px; }
            .form-group label { display: block; font-weight: bold; margin-bottom: 5px; }
            .form-control { width: 100%; padding: 8px; box-sizing: border-box; }
            .producto-fila { display: flex; gap: 10px; margin-bottom: 10px; align-items: center; }
            .caja-resumen { background: #f9f9f9; padding: 15px; border: 1px solid #ddd; margin-top: 20px; border-radius: 5px; }
        </style>

        <input type="hidden" name="id" value="{$this->idOferta}">

        <div class="form-group">
            <label>Nombre de la Oferta:</label>
            <input class="form-control" type="text" name="nombre" value="$valNombre" required>
        </div>

        <div class="form-group">
            <label>Descripción:</label>
            <textarea class="form-control" name="descripcion" rows="3" required>$valDesc</textarea>
        </div>

        <div style="display: flex; gap: 20px;">
            <div class="form-group" style="flex: 1;">
                <label>Fecha y Hora de Inicio:</label>
                <input class="form-control" type="datetime-local" name="fecha_inicio" value="$fecha_inicio" required>
            </div>
            <div class="form-group" style="flex: 1;">
                <label>Fecha y Hora de Fin:</label>
                <input class="form-control" type="datetime-local" name="fecha_fin" value="$fecha_fin" required>
            </div>
        </div>

        <hr>
        <h3>Productos Requeridos</h3>
        
        <div id="contenedor-productos">
            $htmlFilasProductos
        </div>
        
        <button type="button" onclick="agregarProducto()" style="background:#3498db; color:white; border:none; padding:8px 15px; cursor:pointer; margin-top:5px;">+ Añadir otro producto</button>

        <div class="caja-resumen">
            <p><strong>Valor original del pack:</strong> <span id="valor-original">0.00</span> €</p>
            
            <div class="form-group" style="max-width: 200px;">
                <label>Precio Final Deseado (€):</label>
                <input type="number" step="0.01" min="0" id="precio-final" class="form-control" placeholder="Modifica para recalcular">
            </div>

            <div class="form-group" style="max-width: 200px;">
                <label>Porcentaje de Descuento Actual (%):</label>
                <input type="number" step="0.01" min="0" max="100" name="porcentaje_descuento" id="porcentaje-descuento" class="form-control" value="$valPorc" readonly style="background: #eee;">
            </div>
        </div>

        <div class="form-group" style="margin-top: 20px;">
            <button type="submit" style="background: #f39c12; color: white; border: none; padding: 10px 20px; font-size: 16px; cursor: pointer; border-radius:3px;">Actualizar Oferta</button>
        </div>

        <script>
            let productoIndex = $idx;
            const opcionesHTML = `$opcionesProductosJS`;

            function agregarProducto() {
                const div = document.createElement('div');
                div.className = 'producto-fila';
                div.innerHTML = `
                    <select name="productos[\${productoIndex}][id]" class="form-control sel-producto" required>
                        \${opcionesHTML}
                    </select>
                    <input type="number" name="productos[\${productoIndex}][cantidad]" class="form-control cant-producto" value="1" min="1" required style="width: 80px;">
                    <button type="button" style="background:#e74c3c; color:white; border:none; padding:8px; cursor:pointer;" onclick="eliminarFila(this)">X</button>
                `;
                document.getElementById('contenedor-productos').appendChild(div);
                productoIndex++;
                vincularEventos();
            }

            function eliminarFila(btn) {
                btn.parentElement.remove();
                calcularTotales();
            }

            function calcularTotales() {
                let totalOriginal = 0;
                const selects = document.querySelectorAll('.sel-producto');
                const cantidades = document.querySelectorAll('.cant-producto');

                for (let i = 0; i < selects.length; i++) {
                    const select = selects[i];
                    const cantidad = parseInt(cantidades[i].value) || 0;
                    
                    if (select.selectedIndex > 0) {
                        const precioBase = parseFloat(select.options[select.selectedIndex].getAttribute('data-precio'));
                        totalOriginal += (precioBase * cantidad);
                    }
                }

                document.getElementById('valor-original').textContent = totalOriginal.toFixed(2);
                calcularPorcentaje(); // Recalcula el % si cambiamos productos
            }

            function calcularPorcentaje() {
                const valorOriginal = parseFloat(document.getElementById('valor-original').textContent);
                const precioFinalStr = document.getElementById('precio-final').value;
                
                // Solo calculamos si el usuario ha escrito un precio final manual
                if (precioFinalStr !== '' && valorOriginal > 0) {
                    const precioFinal = parseFloat(precioFinalStr);
                    let descuento = 100 - ((precioFinal * 100) / valorOriginal);
                    if (descuento < 0) descuento = 0;
                    document.getElementById('porcentaje-descuento').value = descuento.toFixed(2);
                }
            }

            function calcularPrecioFinalDesdePorcentaje() {
                // Función para que al cargar la página se calcule el "Precio Final" a partir del descuento de la BD
                const valorOriginal = parseFloat(document.getElementById('valor-original').textContent);
                const porcentaje = parseFloat(document.getElementById('porcentaje-descuento').value);
                
                if (valorOriginal > 0 && porcentaje >= 0) {
                    const precioFinal = valorOriginal - (valorOriginal * (porcentaje / 100));
                    document.getElementById('precio-final').value = precioFinal.toFixed(2);
                }
            }

            function vincularEventos() {
                document.querySelectorAll('.sel-producto, .cant-producto').forEach(el => {
                    el.removeEventListener('change', calcularTotales);
                    el.addEventListener('change', calcularTotales);
                    el.removeEventListener('keyup', calcularTotales);
                    el.addEventListener('keyup', calcularTotales);
                });
            }

            // Iniciar eventos la primera vez
            vincularEventos();
            document.getElementById('precio-final').addEventListener('keyup', calcularPorcentaje);
            document.getElementById('precio-final').addEventListener('change', calcularPorcentaje);

            // Al cargar la página, calcular totales para ver el precio base de la BD
            calcularTotales();
            // Y rellenar la cajita de "Precio final" usando el descuento guardado
            calcularPrecioFinalDesdePorcentaje();
        </script>
EOF;
        return $html;
    }

    protected function procesaFormulario(&$datos)
    {
        $this->errores = [];

        // Cogemos el ID que viaja oculto en el POST
        $id_oferta = intval($datos['id'] ?? $this->idOferta);
        $nombre = trim($datos['nombre'] ?? '');
        $descripcion = trim($datos['descripcion'] ?? '');
        $fecha_inicio = trim($datos['fecha_inicio'] ?? '');
        $fecha_fin = trim($datos['fecha_fin'] ?? '');
        $porcentaje = floatval($datos['porcentaje_descuento'] ?? 0);
        $productosPost = $datos['productos'] ?? [];

        // Validaciones
        if (empty($nombre)) $this->errores[] = "El nombre es obligatorio.";
        if (empty($fecha_inicio) || empty($fecha_fin)) $this->errores[] = "Las fechas son obligatorias.";
        if (strtotime($fecha_fin) <= strtotime($fecha_inicio)) $this->errores[] = "La fecha de fin debe ser posterior a la de inicio.";
        
        $productosProcesados = [];
        foreach ($productosPost as $p) {
            if (!empty($p['id']) && $p['cantidad'] > 0) {
                $productosProcesados[] = [
                    'id_producto' => intval($p['id']),
                    'cantidad' => intval($p['cantidad'])
                ];
            }
        }

        if (empty($productosProcesados)) {
            $this->errores[] = "Debes añadir al menos un producto a la oferta.";
        }

        if (empty($this->errores)) {
            $fecha_inicio_sql = str_replace('T', ' ', $fecha_inicio) . ':00';
            $fecha_fin_sql = str_replace('T', ' ', $fecha_fin) . ':00';

            // IMPORTANTE: Asegúrate de tener un método actualizaOferta en tu clase Oferta
            $exito = Oferta::actualizaOferta($id_oferta, $nombre, $descripcion, $fecha_inicio_sql, $fecha_fin_sql, $porcentaje, $productosProcesados);

            if (!$exito) {
                $this->errores[] = "Error al actualizar la oferta en la base de datos.";
            }
        }
    }
}