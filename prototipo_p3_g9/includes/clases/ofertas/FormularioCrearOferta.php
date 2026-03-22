<?php
namespace es\ucm\fdi\aw\ofertas;

use es\ucm\fdi\aw\Formulario;
use es\ucm\fdi\aw\productos\Producto;

class FormularioCrearOferta extends Formulario
{
    public function __construct()
    {
        parent::__construct('formCrearOferta', [
            'urlRedireccion' => 'ofertas.php' // Redirige al listado si va bien
        ]);
    }

    protected function generaCamposFormulario(&$datos)
    {
        // 1. Obtener la lista de productos disponibles para el <select>
        $todosLosProductos = Producto::listaProductos();
        $opcionesProductos = "<option value=''>Selecciona un producto...</option>";
        foreach ($todosLosProductos as $p) {
            $id = $p->getId();
            $nombre = htmlspecialchars($p->getNombre());
            $precio = $p->getPrecioBase();
            $opcionesProductos .= "<option value='$id' data-precio='$precio'>$nombre (Base: $precio €)</option>";
        }

        // HTML del Formulario
        $html = <<<EOF
        <style>
            .form-group { margin-bottom: 15px; }
            .form-group label { display: block; font-weight: bold; margin-bottom: 5px; }
            .form-control { width: 100%; padding: 8px; box-sizing: border-box; }
            .producto-fila { display: flex; gap: 10px; margin-bottom: 10px; align-items: center; }
            .caja-resumen { background: #f9f9f9; padding: 15px; border: 1px solid #ddd; margin-top: 20px; border-radius: 5px; }
        </style>

        <div class="form-group">
            <label>Nombre de la Oferta:</label>
            <input class="form-control" type="text" name="nombre" required placeholder="Ej: Desayuno Andaluz">
        </div>

        <div class="form-group">
            <label>Descripción:</label>
            <textarea class="form-control" name="descripcion" rows="3" required placeholder="Ej: Café + Tostada entera a un precio especial"></textarea>
        </div>

        <div style="display: flex; gap: 20px;">
            <div class="form-group" style="flex: 1;">
                <label>Fecha y Hora de Inicio:</label>
                <input class="form-control" type="datetime-local" name="fecha_inicio" required>
            </div>
            <div class="form-group" style="flex: 1;">
                <label>Fecha y Hora de Fin:</label>
                <input class="form-control" type="datetime-local" name="fecha_fin" required>
            </div>
        </div>

        <hr>
        <h3>Productos Requeridos</h3>
        <p style="font-size: 0.9em; color: #555;">Añade los productos que el cliente debe comprar para que se aplique la oferta.</p>
        
        <div id="contenedor-productos">
            <div class="producto-fila">
                <select name="productos[0][id]" class="form-control sel-producto" required>
                    $opcionesProductos
                </select>
                <input type="number" name="productos[0][cantidad]" class="form-control cant-producto" value="1" min="1" required style="width: 80px;" title="Cantidad">
                <button type="button" class="btn-eliminar" style="background:#e74c3c; color:white; border:none; padding:8px; cursor:pointer;" onclick="eliminarFila(this)">X</button>
            </div>
        </div>
        
        <button type="button" onclick="agregarProducto()" style="background:#3498db; color:white; border:none; padding:8px 15px; cursor:pointer; margin-top:5px;">+ Añadir otro producto</button>

        <div class="caja-resumen">
            <p><strong>Valor original del pack:</strong> <span id="valor-original">0.00</span> €</p>
            
            <div class="form-group" style="max-width: 200px;">
                <label>Precio Final Deseado (€):</label>
                <input type="number" step="0.01" min="0" id="precio-final" class="form-control" placeholder="Ej: 2.50">
            </div>

            <div class="form-group" style="max-width: 200px;">
                <label>Porcentaje de Descuento a aplicar (%):</label>
                <input type="number" step="0.01" min="0" max="100" name="porcentaje_descuento" id="porcentaje-descuento" class="form-control" readonly style="background: #eee;">
            </div>
        </div>

        <div class="form-group" style="margin-top: 20px;">
            <button type="submit" style="background: #2ecc71; color: white; border: none; padding: 10px 20px; font-size: 16px; cursor: pointer;">Crear Oferta Promocional</button>
        </div>

        <script>
            let productoIndex = 1;
            const opcionesHTML = `$opcionesProductos`;

            // Función para añadir una nueva fila de producto
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
                vincularEventos(); // Re-vincular eventos a los nuevos select/input
            }

            function eliminarFila(btn) {
                btn.parentElement.remove();
                calcularTotales();
            }

            // Calculadora en vivo (Precio y Porcentaje)
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
                calcularPorcentaje();
            }

            function calcularPorcentaje() {
                const valorOriginal = parseFloat(document.getElementById('valor-original').textContent);
                const precioFinal = parseFloat(document.getElementById('precio-final').value);

                if (valorOriginal > 0 && precioFinal >= 0) {
                    // Fórmula: % = 100 - ((PrecioFinal * 100) / PrecioOriginal)
                    let descuento = 100 - ((precioFinal * 100) / valorOriginal);
                    
                    // Si el precio final es mayor que el original, no hay descuento (0%)
                    if (descuento < 0) descuento = 0;
                    
                    document.getElementById('porcentaje-descuento').value = descuento.toFixed(2);
                } else {
                    document.getElementById('porcentaje-descuento').value = '';
                }
            }

            // Vincular eventos 'change' y 'keyup'
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
        </script>
EOF;
        return $html;
    }

    protected function procesaFormulario(&$datos)
    {
        $this->errores = [];

        $nombre = trim($datos['nombre'] ?? '');
        $descripcion = trim($datos['descripcion'] ?? '');
        $fecha_inicio = trim($datos['fecha_inicio'] ?? '');
        $fecha_fin = trim($datos['fecha_fin'] ?? '');
        $porcentaje = floatval($datos['porcentaje_descuento'] ?? 0);
        $productosPost = $datos['productos'] ?? [];

        // Validaciones básicas
        if (empty($nombre)) $this->errores[] = "El nombre es obligatorio.";
        if (empty($fecha_inicio) || empty($fecha_fin)) $this->errores[] = "Las fechas son obligatorias.";
        if (strtotime($fecha_fin) <= strtotime($fecha_inicio)) $this->errores[] = "La fecha de fin debe ser posterior a la de inicio.";
        if ($porcentaje <= 0 || $porcentaje > 100) $this->errores[] = "El porcentaje debe calcularse correctamente entre 0.01 y 100.";

        // Procesar array de productos a la estructura que espera la clase Oferta
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
            // Reemplazar la 'T' del datetime-local por un espacio para MySQL
            $fecha_inicio_sql = str_replace('T', ' ', $fecha_inicio) . ':00';
            $fecha_fin_sql = str_replace('T', ' ', $fecha_fin) . ':00';

            $id_oferta = Oferta::creaOferta($nombre, $descripcion, $fecha_inicio_sql, $fecha_fin_sql, $porcentaje, $productosProcesados);

            if (!$id_oferta) {
                $this->errores[] = "Error al guardar la oferta en la base de datos.";
            }
        }
    }
}