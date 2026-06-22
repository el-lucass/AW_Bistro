<?php
namespace es\ucm\fdi\aw\pedidos;

use es\ucm\fdi\aw\Formulario;

class FormularioPago extends Formulario
{
    private array $carrito;

    public function __construct(array $carrito)
    {
        parent::__construct('formPago', ['action' => 'procesar_pedido.php']);
        $this->carrito = $carrito;
    }

    protected function generaFormulario(&$datos = [])
    {
        $campos = $this->generaCamposFormulario($datos);

        return <<<HTML
        <form method="POST" action="{$this->action}" id="{$this->formId}" onsubmit="return validarFormularioPago()">
            <input type="hidden" name="formId" value="{$this->formId}" />
            {$campos}
        </form>
        HTML;
    }

    protected function generaCamposFormulario(&$datos)
    {
        $html  = $this->generaResumenPedido();
        $html .= $this->generaMetodoPago($datos);
        $html .= $this->generaCamposTarjeta($datos);
        $html .= $this->generaInfoCamarero($datos);
        $html .= $this->generaBotonConfirmar();
        $html .= $this->generaScript();

        return $html;
    }

    protected function procesaFormulario(&$datos)
    {
        $metodo = $datos['metodo_pago'] ?? 'camarero';

        if ($metodo === 'tarjeta') {
            $num = trim($datos['num_tarjeta'] ?? '');
            if (!preg_match('/^\d{16}$/', $num)) {
                $this->errores['num_tarjeta'] = 'El número de tarjeta debe tener exactamente 16 dígitos numéricos.';
            }

            $cad = trim($datos['caducidad_tarjeta'] ?? '');
            if (!preg_match('/^\d{2}\/\d{2}$/', $cad)) {
                $this->errores['caducidad_tarjeta'] = 'La caducidad debe tener el formato MM/AA.';
            } else {
                [$mes, $anio] = array_map('intval', explode('/', $cad));
                if ($mes < 1 || $mes > 12) {
                    $this->errores['caducidad_tarjeta'] = 'El mes de caducidad no es válido.';
                } elseif (mktime(0, 0, 0, $mes + 1, 1, 2000 + $anio) <= time()) {
                    $this->errores['caducidad_tarjeta'] = 'La tarjeta está caducada.';
                }
            }

            $cvv = trim($datos['cvv_tarjeta'] ?? '');
            if (!preg_match('/^\d{3,4}$/', $cvv)) {
                $this->errores['cvv_tarjeta'] = 'El CVV debe tener 3 o 4 dígitos numéricos.';
            }
        }
    }

    private function generaResumenPedido(): string
    {
        $html = "
        <div class='pago-seccion'>
            <h3 class='mt-0 mb-20'>Resumen del Pedido</h3>";

        $subtotalSinDescuento = 0;
        foreach ($this->carrito['productos'] as $item) {
            $subtotal              = $item['precio'] * $item['cantidad'];
            $subtotalSinDescuento += $subtotal;
            $html .= "
            <div class='pago-item'>
                <span>" . htmlspecialchars($item['nombre']) . " x{$item['cantidad']}</span>
                <span>" . number_format($subtotal, 2) . " €</span>
            </div>";
        }

        $totalFinal         = $this->carrito['total_final'] ?? $subtotalSinDescuento;
        $descuentoAplicado  = $subtotalSinDescuento - $totalFinal;
        $bistrocoinsGanadas = floor($totalFinal);

        $html .= "<hr class='hr-sep'>";

        if ($descuentoAplicado > 0) {
            $html .= "
            <div class='pago-descuento'>
                <strong>Descuento por ofertas aplicadas:</strong>
                <strong>- " . number_format($descuentoAplicado, 2) . " €</strong>
            </div>";
        }

        $html .= "
            <div class='pago-total'>
                <strong>Total a Pagar:</strong>
                <strong>" . number_format($totalFinal, 2) . " €</strong>
            </div>";

        if ($bistrocoinsGanadas > 0) {
            $html .= "
            <div class='pago-coins'>
                <strong>Ganará:</strong>
                <strong>{$bistrocoinsGanadas} BistroCoins</strong>
            </div>";
        }

        $html .= "</div>";

        return $html;
    }

    private function generaMetodoPago(array &$datos): string
    {
        $metodo = $datos['metodo_pago'] ?? 'camarero';

        $claseTarjeta  = 'pago-opcion' . ($metodo === 'tarjeta'  ? ' pago-opcion-activa' : '');
        $claseCamarero = 'pago-opcion' . ($metodo === 'camarero' ? ' pago-opcion-activa' : '');

        return "
        <div class='pago-seccion'>
            <h3 class='mt-0 mb-20'>Método de Pago</h3>
            <div class='pago-opciones'>
                <div id='btn_tarjeta'  onclick=\"seleccionarMetodo('tarjeta')\"  class='{$claseTarjeta}'>
                    💳 <span>Tarjeta de Crédito/Débito</span>
                </div>
                <div id='btn_camarero' onclick=\"seleccionarMetodo('camarero')\" class='{$claseCamarero}'>
                    👤 <span>Pagar al Camarero</span>
                </div>
            </div>
            <input type='hidden' name='metodo_pago' id='input_metodo' value='{$metodo}'>
        </div>";
    }

    private function generaCamposTarjeta(array &$datos): string
    {
        $metodo      = $datos['metodo_pago'] ?? 'camarero';
        $claseOculto = ($metodo === 'tarjeta') ? '' : ' oculto';

        $titular = htmlspecialchars($datos['titular_tarjeta']   ?? '');
        $num     = htmlspecialchars($datos['num_tarjeta']        ?? '');
        $cad     = htmlspecialchars($datos['caducidad_tarjeta']  ?? '');
        $cvv     = htmlspecialchars($datos['cvv_tarjeta']        ?? '');

        $errNum = self::createMensajeError($this->errores, 'num_tarjeta',       'span', ['class' => 'error']);
        $errCad = self::createMensajeError($this->errores, 'caducidad_tarjeta', 'span', ['class' => 'error']);
        $errCvv = self::createMensajeError($this->errores, 'cvv_tarjeta',       'span', ['class' => 'error']);

        return "
        <div id='formulario_tarjeta' class='pago-tarjeta-box{$claseOculto}'>
            <h3 class='mt-0 mb-15'>Datos de la Tarjeta</h3>

            <div class='mb-15'>
                <label>Titular de la tarjeta</label>
                <input type='text' name='titular_tarjeta' value='{$titular}' placeholder='Ej. Juan Pérez' class='input-full'>
            </div>

            <div class='mb-15'>
                <label>Número de tarjeta</label>
                <input type='text' id='num_tarjeta' name='num_tarjeta' value='{$num}'
                       placeholder='1234567891011121' maxlength='16' class='input-full'>
                {$errNum}
            </div>

            <div class='flex-fila gap-15'>
                <div class='flex-1'>
                    <label>Caducidad (MM/AA)</label>
                    <input type='text' id='cad_tarjeta' name='caducidad_tarjeta' value='{$cad}'
                           placeholder='MM/AA' class='input-full'>
                    {$errCad}
                </div>
                <div class='flex-1'>
                    <label>CVV</label>
                    <input type='text' id='cvv_tarjeta' name='cvv_tarjeta' value='{$cvv}'
                           placeholder='123' maxlength='4' class='input-full'>
                    {$errCvv}
                </div>
            </div>
        </div>";
    }

    private function generaInfoCamarero(array &$datos): string
    {
        $metodo      = $datos['metodo_pago'] ?? 'camarero';
        $claseOculto = ($metodo === 'camarero') ? '' : ' oculto';

        return "
        <div id='info_camarero' class='pago-tarjeta-box{$claseOculto}'>
            <h3 class='mt-0 mb-10'>Pago al Camarero</h3>
            <p class='texto-gris texto-14 mb-0'>Tu pedido se procesará y podrás realizar el pago directamente al camarero cuando recojas o recibas tu pedido.</p>
        </div>";
    }

    private function generaBotonConfirmar(): string
    {
        return "
        <div class='flex-fin mt-10'>
            <button type='submit' class='btn-oscuro btn-lg'>Confirmar Pago</button>
        </div>";
    }

    private function generaScript(): string
    {
        return "
        <script>
        function validarFormularioPago() {
            const metodo = document.getElementById('input_metodo').value;
            if (metodo !== 'tarjeta') return true;

            const num = document.getElementById('num_tarjeta').value.trim();
            if (!/^\d{16}$/.test(num)) {
                alert('El número de tarjeta debe tener exactamente 16 dígitos numéricos.');
                return false;
            }

            const cad = document.getElementById('cad_tarjeta').value.trim();
            if (!/^\d{2}\/\d{2}$/.test(cad)) {
                alert('La caducidad debe tener el formato MM/AA.');
                return false;
            }
            const [mes, anio] = cad.split('/').map(Number);
            if (mes < 1 || mes > 12) {
                alert('El mes de caducidad no es válido.');
                return false;
            }
            const hoy = new Date();
            const expiracion = new Date(2000 + anio, mes, 1);
            if (expiracion <= hoy) {
                alert('La tarjeta está caducada.');
                return false;
            }

            const cvv = document.getElementById('cvv_tarjeta').value.trim();
            if (!/^\d{3,4}$/.test(cvv)) {
                alert('El CVV debe tener 3 o 4 dígitos numéricos.');
                return false;
            }

            return true;
        }

        function seleccionarMetodo(metodo) {
            document.getElementById('input_metodo').value = metodo;

            const btnTarjeta   = document.getElementById('btn_tarjeta');
            const btnCamarero  = document.getElementById('btn_camarero');
            const formTarjeta  = document.getElementById('formulario_tarjeta');
            const infoCamarero = document.getElementById('info_camarero');
            const inputsTarjeta = formTarjeta.querySelectorAll('input');

            if (metodo === 'tarjeta') {
                btnTarjeta.classList.add('pago-opcion-activa');
                btnCamarero.classList.remove('pago-opcion-activa');
                formTarjeta.classList.remove('oculto');
                infoCamarero.classList.add('oculto');
                inputsTarjeta.forEach(input => input.required = true);
            } else {
                btnCamarero.classList.add('pago-opcion-activa');
                btnTarjeta.classList.remove('pago-opcion-activa');
                formTarjeta.classList.add('oculto');
                infoCamarero.classList.remove('oculto');
                inputsTarjeta.forEach(input => input.required = false);
            }
        }
        </script>";
    }
}
