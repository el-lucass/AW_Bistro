<?php
namespace es\ucm\fdi\aw\ofertas;

use es\ucm\fdi\aw\Formulario;
use es\ucm\fdi\aw\productos\Producto;

class FormularioCrearOferta extends Formulario
{
    public function __construct()
    {
        parent::__construct('formCrearOferta', [
            'urlRedireccion' => 'ofertas.php'
        ]);
    }

    protected function generaCamposFormulario(&$datos)
    {
        $todosLosProductos = Producto::listaProductos();
        $opcionesProductos = "<option value=''>Selecciona un producto...</option>";
        
        
        foreach ($todosLosProductos as $p) {
            $id     = $p->getId();
            $nombre = htmlspecialchars($p->getNombre());
            $precioBase = $p->getPrecioBase();
            $iva = (float) $p->getIva(); 
            $precioIva = round($precioBase * (1 + $iva / 100), 2);
            $precio = number_format($precioIva, 2, '.', '');
            $opcionesProductos .= "<option value='$id' data-precio='$precio'>$nombre (Precio con IVA: $precio €)</option>";
        }

        $html = <<<EOF
        <div class="form-group">
            <label>Nombre de la Oferta:</label>
            <input class="form-control" type="text" name="nombre" required placeholder="Ej: Desayuno Andaluz">
        </div>

        <div class="form-group">
            <label>Descripción:</label>
            <textarea class="form-control" name="descripcion" rows="3" required placeholder="Ej: Café + Tostada entera a un precio especial"></textarea>
        </div>

        <div class="flex-fila gap-20">
            <div class="form-group flex-1">
                <label>Fecha y Hora de Inicio:</label>
                <input class="form-control" type="datetime-local" name="fecha_inicio" required>
            </div>
            <div class="form-group flex-1">
                <label>Fecha y Hora de Fin:</label>
                <input class="form-control" type="datetime-local" name="fecha_fin" required>
            </div>
        </div>

        <hr>
        <h3>Productos Requeridos</h3>
        <p class="texto-sm texto-gris">Añade los productos que el cliente debe comprar para que se aplique la oferta.</p>

        <div id="contenedor-productos">
            <div class="producto-fila">
                <select name="productos[0][id]" class="form-control sel-producto" required>
                    $opcionesProductos
                </select>
                <input type="number" name="productos[0][cantidad]" class="form-control input-mini cant-producto" value="1" min="1" required title="Cantidad">
                <button type="button" class="btn-peligro btn-sm" onclick="eliminarFila(this)">X</button>
            </div>
        </div>

        <button type="button" onclick="agregarProducto()" class="btn-azul mt-5">+ Añadir otro producto</button>

        <div class="caja-resumen">
            <p><strong>Valor original del pack con IVA:</strong> <span id="valor-original">0.00</span> €</p>

            <div class="form-group form-group-sm">
                <label>Precio Final Deseado (€):</label>
                <input type="number" step="0.01" min="0" id="precio-final" class="form-control" placeholder="Ej: 2.50">
            </div>

            <div class="form-group form-group-sm">
                <label>Porcentaje de Descuento a aplicar (%):</label>
                <input type="number" step="0.01" min="0" max="100" name="porcentaje_descuento" id="porcentaje-descuento" class="form-control input-readonly" readonly>
            </div>
        </div>

        <div class="form-group mt-20">
            <button type="submit" class="btn-crear btn-lg">Crear Oferta Promocional</button>
        </div>

        <script>
            let productoIndex = 1;
            const opcionesHTML = `$opcionesProductos`;

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
                const valorOriginal = parseFloat(document.getElementById('valor-original').textContent);
                const precioFinal   = parseFloat(document.getElementById('precio-final').value);

                if (valorOriginal > 0 && precioFinal >= 0) {
                    let descuento = 100 - ((precioFinal * 100) / valorOriginal);
                    if (descuento < 0) descuento = 0;
                    document.getElementById('porcentaje-descuento').value = descuento.toFixed(2);
                } else {
                    document.getElementById('porcentaje-descuento').value = '';
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

            // Validación al enviar el formulario
            document.getElementById('formCrearOferta').addEventListener('submit', function (e) {
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

        $nombre       = trim($datos['nombre']       ?? '');
        $descripcion  = trim($datos['descripcion']  ?? '');
        $fecha_inicio = trim($datos['fecha_inicio'] ?? '');
        $fecha_fin    = trim($datos['fecha_fin']    ?? '');
        $porcentaje   = floatval($datos['porcentaje_descuento'] ?? 0);
        $productosPost = $datos['productos'] ?? [];

        if (empty($nombre))                                         $this->errores[] = "El nombre es obligatorio.";
        if (empty($fecha_inicio) || empty($fecha_fin))              $this->errores[] = "Las fechas son obligatorias.";
        if (strtotime($fecha_fin) <= strtotime($fecha_inicio))      $this->errores[] = "La fecha de fin debe ser posterior a la de inicio.";
        if ($porcentaje <= 0 || $porcentaje > 100)                  $this->errores[] = "El porcentaje debe calcularse correctamente entre 0.01 y 100.";

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

            $id_oferta = Oferta::creaOferta($nombre, $descripcion, $fecha_inicio_sql, $fecha_fin_sql, $porcentaje, $productosProcesados);

            if (!$id_oferta) {
                $this->errores[] = "Error al guardar la oferta en la base de datos.";
            }
        }
    }
}
