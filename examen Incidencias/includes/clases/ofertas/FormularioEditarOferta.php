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
            'urlRedireccion' => 'ofertas.php'
        ]);
        $this->idOferta = $idOferta;
    }

    protected function generaCamposFormulario(&$datos)
    {
        $ofertaActual = Oferta::buscaOferta($this->idOferta);

        if (!$ofertaActual) {
            return "<p class='alerta-error'>Error: No se ha encontrado la oferta solicitada.</p>";
        }

        $nombre      = $datos['nombre']      ?? $ofertaActual->getNombre();
        $descripcion = $datos['descripcion'] ?? $ofertaActual->getDescripcion();

        $fechaInicioStr = $datos['fecha_inicio'] ?? $ofertaActual->getFechaInicio();
        $fechaFinStr    = $datos['fecha_fin']    ?? $ofertaActual->getFechaFin();
        $fecha_inicio   = date('Y-m-d\TH:i', strtotime($fechaInicioStr));
        $fecha_fin      = date('Y-m-d\TH:i', strtotime($fechaFinStr));

        $porcentaje = $datos['porcentaje_descuento'] ?? $ofertaActual->getPorcentajeDescuento();

        $todosLosProductos = Producto::listaProductos();

        $opcionesProductosJS = "<option value=''>Selecciona un producto...</option>";
        foreach ($todosLosProductos as $p) {
            $id      = $p->getId();
            $nombreP = htmlspecialchars($p->getNombre());
            $precioBase = $p->getPrecioBase();
            $iva = (float) $p->getIva(); 
            $precioIva = round($precioBase * (1 + $iva / 100), 2);
            $precio = number_format($precioIva, 2, '.', '');
            $opcionesProductosJS .= "<option value='$id' data-precio='$precio'>$nombreP (Precio con IVA: $precio €)</option>";
        }

        $productosAsignados = $datos['productos'] ?? $ofertaActual->getProductos();
        $htmlFilasProductos = '';
        $idx = 0;

        if (!empty($productosAsignados)) {
            foreach ($productosAsignados as $prod) {
                $idProd   = $prod['id'] ?? $prod['id_producto'] ?? '';
                $cantidad = $prod['cantidad'] ?? $prod['cantidad_requerida'] ?? 1;

                $selectHtml  = "<select name='productos[$idx][id]' class='form-control sel-producto' required>";
                $selectHtml .= "<option value=''>Selecciona un producto...</option>";

                foreach ($todosLosProductos as $p) {
                    $pId     = $p->getId();
                    $pNombre = htmlspecialchars($p->getNombre());
                    $pPrecioBase = $p->getPrecioBase();
                    $pIva = (float) $p->getIva(); 
                    $pPrecioIva = round($pPrecioBase * (1 + $pIva / 100), 2);
                    $pPrecio = number_format($pPrecioIva, 2, '.', '');
                    $selected = ($pId == $idProd) ? "selected" : "";
                    $selectHtml .= "<option value='$pId' data-precio='$pPrecio' $selected>$pNombre (Precio con IVA: $pPrecio €)</option>";
                }
                $selectHtml .= "</select>";

                $htmlFilasProductos .= "
                <div class='producto-fila'>
                    $selectHtml
                    <input type='number' name='productos[$idx][cantidad]' class='form-control input-mini cant-producto' value='$cantidad' min='1' required title='Cantidad'>
                    <button type='button' class='btn-peligro btn-sm' onclick='eliminarFila(this)'>X</button>
                </div>";
                $idx++;
            }
        } else {
            $htmlFilasProductos .= "
            <div class='producto-fila'>
                <select name='productos[0][id]' class='form-control sel-producto' required>
                    $opcionesProductosJS
                </select>
                <input type='number' name='productos[0][cantidad]' class='form-control input-mini cant-producto' value='1' min='1' required title='Cantidad'>
                <button type='button' class='btn-peligro btn-sm' onclick='eliminarFila(this)'>X</button>
            </div>";
            $idx = 1;
        }

        $valNombre = htmlspecialchars($nombre);
        $valDesc   = htmlspecialchars($descripcion);
        $valPorc   = htmlspecialchars($porcentaje);

        $html = <<<EOF
        <input type="hidden" name="id" value="{$this->idOferta}">

        <div class="form-group">
            <label>Nombre de la Oferta:</label>
            <input class="form-control" type="text" name="nombre" value="$valNombre" required>
        </div>

        <div class="form-group">
            <label>Descripción:</label>
            <textarea class="form-control" name="descripcion" rows="3" required>$valDesc</textarea>
        </div>

        <div class="flex-fila gap-20">
            <div class="form-group flex-1">
                <label>Fecha y Hora de Inicio:</label>
                <input class="form-control" type="datetime-local" name="fecha_inicio" value="$fecha_inicio" required>
            </div>
            <div class="form-group flex-1">
                <label>Fecha y Hora de Fin:</label>
                <input class="form-control" type="datetime-local" name="fecha_fin" value="$fecha_fin" required>
            </div>
        </div>

        <hr>
        <h3>Productos Requeridos</h3>

        <div id="contenedor-productos">
            $htmlFilasProductos
        </div>

        <button type="button" onclick="agregarProducto()" class="btn-azul mt-5">+ Añadir otro producto</button>

        <div class="caja-resumen">
            <p><strong>Valor original del pack con IVA:</strong> <span id="valor-original">0.00</span> €</p>

            <div class="form-group form-group-sm">
                <label>Precio Final Deseado (€):</label>
                <input type="number" step="0.01" min="0" id="precio-final" class="form-control" placeholder="Modifica para recalcular">
            </div>

            <div class="form-group form-group-sm">
                <label>Porcentaje de Descuento Actual (%):</label>
                <input type="number" step="0.01" min="0" max="100" name="porcentaje_descuento" id="porcentaje-descuento" class="form-control input-readonly" value="$valPorc" readonly>
            </div>
        </div>

        <div class="form-group mt-20">
            <button type="submit" class="btn-editar btn-lg">Actualizar Oferta</button>
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
                    <input type="number" name="productos[\${productoIndex}][cantidad]" class="form-control input-mini cant-producto" value="1" min="1" required>
                    <button type="button" class="btn-peligro btn-sm" onclick="eliminarFila(this)">X</button>
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
                const selects    = document.querySelectorAll('.sel-producto');
                const cantidades = document.querySelectorAll('.cant-producto');

                for (let i = 0; i < selects.length; i++) {
                    const select   = selects[i];
                    const cantidad = parseInt(cantidades[i].value) || 0;
                    if (select.selectedIndex > 0) {
                        const precioBase = parseFloat(select.options[select.selectedIndex].getAttribute('data-precio'));
                        totalOriginal += (precioBase * cantidad);
                    }
                }

                document.getElementById('valor-original').textContent = totalOriginal.toFixed(2);
                calcularPorcentaje();
            }

            function calcularPorcentaje() {
                const valorOriginal   = parseFloat(document.getElementById('valor-original').textContent);
                const precioFinalStr  = document.getElementById('precio-final').value;

                if (precioFinalStr !== '' && valorOriginal > 0) {
                    const precioFinal = parseFloat(precioFinalStr);
                    let descuento = 100 - ((precioFinal * 100) / valorOriginal);
                    if (descuento < 0) descuento = 0;
                    document.getElementById('porcentaje-descuento').value = descuento.toFixed(2);
                }
            }

            function calcularPrecioFinalDesdePorcentaje() {
                const valorOriginal = parseFloat(document.getElementById('valor-original').textContent);
                const porcentaje    = parseFloat(document.getElementById('porcentaje-descuento').value);

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

            vincularEventos();
            document.getElementById('precio-final').addEventListener('keyup',  calcularPorcentaje);
            document.getElementById('precio-final').addEventListener('change', calcularPorcentaje);

            calcularTotales();
            calcularPrecioFinalDesdePorcentaje();

            // Validación al enviar el formulario
            document.getElementById('formEditarOferta').addEventListener('submit', function (e) {
                const fIni = document.querySelector('[name="fecha_inicio"]');
                const fFin = document.querySelector('[name="fecha_fin"]');
                const precioFinal = document.getElementById('precio-final');
                const valorOriginal = parseFloat(document.getElementById('valor-original').textContent) || 0;
                const finalNum = parseFloat(precioFinal.value);
                let ok = true;

                limpiarErrorCampo(fIni); limpiarErrorCampo(fFin); limpiarErrorCampo(precioFinal);

                if (!fIni.value || !fFin.value) {
                    mostrarErrorCampo(fFin, 'Debes indicar las dos fechas.');
                    ok = false;
                } else if (new Date(fFin.value) <= new Date(fIni.value)) {
                    mostrarErrorCampo(fFin, 'La fecha de fin debe ser posterior a la de inicio.');
                    ok = false;
                }

                const seleccionados = Array.from(document.querySelectorAll('.sel-producto'))
                    .filter(s => s.selectedIndex > 0).length;
                if (seleccionados === 0) {
                    alert('Debes añadir al menos un producto seleccionado.');
                    ok = false;
                }

                if (isNaN(finalNum) || finalNum <= 0) {
                    mostrarErrorCampo(precioFinal, 'El precio final debe ser mayor que 0.');
                    ok = false;
                } else if (valorOriginal > 0 && finalNum >= valorOriginal) {
                    mostrarErrorCampo(precioFinal, 'El precio final debe ser menor que el valor original.');
                    ok = false;
                }

                if (!ok) e.preventDefault();
            });
        </script>
EOF;
        return $html;
    }

    protected function procesaFormulario(&$datos)
    {
        $this->errores = [];

        $id_oferta    = intval($datos['id'] ?? $this->idOferta);
        $nombre       = trim($datos['nombre']       ?? '');
        $descripcion  = trim($datos['descripcion']  ?? '');
        $fecha_inicio = trim($datos['fecha_inicio'] ?? '');
        $fecha_fin    = trim($datos['fecha_fin']    ?? '');
        $porcentaje   = floatval($datos['porcentaje_descuento'] ?? 0);
        $productosPost = $datos['productos'] ?? [];

        if (empty($nombre))                                     $this->errores[] = "El nombre es obligatorio.";
        if (empty($fecha_inicio) || empty($fecha_fin))          $this->errores[] = "Las fechas son obligatorias.";
        if (strtotime($fecha_fin) <= strtotime($fecha_inicio))  $this->errores[] = "La fecha de fin debe ser posterior a la de inicio.";

        $productosProcesados = [];
        foreach ($productosPost as $p) {
            if (!empty($p['id']) && $p['cantidad'] > 0) {
                $productosProcesados[] = [
                    'id_producto' => intval($p['id']),
                    'cantidad'    => intval($p['cantidad'])
                ];
            }
        }

        if (empty($productosProcesados)) {
            $this->errores[] = "Debes añadir al menos un producto a la oferta.";
        }

        if (empty($this->errores)) {
            $fecha_inicio_sql = str_replace('T', ' ', $fecha_inicio) . ':00';
            $fecha_fin_sql    = str_replace('T', ' ', $fecha_fin)    . ':00';

            $exito = Oferta::actualizaOferta($id_oferta, $nombre, $descripcion, $fecha_inicio_sql, $fecha_fin_sql, $porcentaje, $productosProcesados);

            if (!$exito) {
                $this->errores[] = "Error al actualizar la oferta en la base de datos.";
            }
        }
    }
}
