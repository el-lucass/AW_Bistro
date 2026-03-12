<?php
namespace es\ucm\fdi\aw;

require_once __DIR__ . '/Formulario.php';

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
        $html = $this->generaResumenPedido();
        $html .= $this->generaMetodoPago($datos);
        $html .= $this->generaCamposTarjeta($datos);
        $html .= $this->generaInfoCamarero();
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
        <div style='border: 1px solid #eee; padding: 25px; margin-bottom: 25px; border-radius: 5px;'>
            <h3 style='margin-top: 0; margin-bottom: 20px; font-size: 16px;'>Resumen del Pedido</h3>";

        $total = 0;
        foreach ($this->carrito['productos'] as $item) {
            $subtotal = $item['precio'] * $item['cantidad'];
            $total   += $subtotal;
            $html .= "
            <div style='display: flex; justify-content: space-between; margin-bottom: 12px; font-size: 14px;'>
                <span>" . htmlspecialchars($item['nombre']) . " x{$item['cantidad']}</span>
                <span>" . number_format($subtotal, 2) . " €</span>
            </div>";
        }

        $totalFmt = number_format($total, 2);
        $html .= "
            <hr style='border: 0; border-top: 1px solid #eee; margin: 20px 0;'>
            <div style='display: flex; justify-content: space-between; font-size: 18px;'>
                <strong>Total:</strong>
                <strong>{$totalFmt} €</strong>
            </div>
        </div>";

        return $html;
    }

    private function generaMetodoPago(array &$datos): string
    {
        $metodo = $datos['metodo_pago'] ?? 'camarero';

        $borderTarjeta  = ($metodo === 'tarjeta')  ? '2px solid black' : '1px solid #ccc';
        $borderCamarero = ($metodo === 'camarero') ? '2px solid black' : '1px solid #ccc';

        return "
        <div style='border: 1px solid #eee; padding: 25px; margin-bottom: 25px; border-radius: 5px;'>
            <h3 style='margin-top: 0; margin-bottom: 20px; font-size: 16px;'>Método de Pago</h3>
            <div style='display: flex; gap: 20px;'>
                <div id='btn_tarjeta' onclick=\"seleccionarMetodo('tarjeta')\"
                     style='flex: 1; border: {$borderTarjeta}; padding: 20px; border-radius: 5px; cursor: pointer; display: flex; align-items: center; gap: 10px;'>
                    💳 <span>Tarjeta de Crédito/Débito</span>
                </div>
                <div id='btn_camarero' onclick=\"seleccionarMetodo('camarero')\"
                     style='flex: 1; border: {$borderCamarero}; padding: 20px; border-radius: 5px; cursor: pointer; display: flex; align-items: center; gap: 10px;'>
                    👤 <span>Pagar al Camarero</span>
                </div>
            </div>
            <input type='hidden' name='metodo_pago' id='input_metodo' value='{$metodo}'>
        </div>";
    }

    private function generaCamposTarjeta(array &$datos): string
    {
        $metodo  = $datos['metodo_pago'] ?? 'camarero';
        $display = ($metodo === 'tarjeta') ? 'block' : 'none';

        $titular = htmlspecialchars($datos['titular_tarjeta'] ?? '');
        $num     = htmlspecialchars($datos['num_tarjeta']     ?? '');
        $cad     = htmlspecialchars($datos['caducidad_tarjeta'] ?? '');
        $cvv     = htmlspecialchars($datos['cvv_tarjeta']     ?? '');

        $errNum = self::createMensajeError($this->errores, 'num_tarjeta',      'span', ['style' => 'color:red; font-size:0.85em; display:block;']);
        $errCad = self::createMensajeError($this->errores, 'caducidad_tarjeta','span', ['style' => 'color:red; font-size:0.85em; display:block;']);
        $errCvv = self::createMensajeError($this->errores, 'cvv_tarjeta',      'span', ['style' => 'color:red; font-size:0.85em; display:block;']);

        return "
        <div id='formulario_tarjeta' style='display: {$display}; border: 1px solid #eee; padding: 25px; margin-bottom: 30px; border-radius: 5px; background-color: #fafafa;'>
            <h3 style='margin-top: 0; margin-bottom: 15px; font-size: 16px;'>Datos de la Tarjeta</h3>

            <div style='margin-bottom: 15px;'>
                <label style='display: block; margin-bottom: 5px; font-size: 14px;'>Titular de la tarjeta</label>
                <input type='text' name='titular_tarjeta' value='{$titular}' placeholder='Ej. Juan Pérez'
                       style='width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box;'>
            </div>

            <div style='margin-bottom: 15px;'>
                <label style='display: block; margin-bottom: 5px; font-size: 14px;'>Número de tarjeta</label>
                <input type='text' id='num_tarjeta' name='num_tarjeta' value='{$num}'
                       placeholder='1234567891011121' maxlength='16'
                       style='width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box;'>
                {$errNum}
            </div>

            <div style='display: flex; gap: 15px;'>
                <div style='flex: 1;'>
                    <label style='display: block; margin-bottom: 5px; font-size: 14px;'>Caducidad (MM/AA)</label>
                    <input type='text' id='cad_tarjeta' name='caducidad_tarjeta' value='{$cad}'
                           placeholder='MM/AA'
                           style='width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box;'>
                    {$errCad}
                </div>
                <div style='flex: 1;'>
                    <label style='display: block; margin-bottom: 5px; font-size: 14px;'>CVV</label>
                    <input type='text' id='cvv_tarjeta' name='cvv_tarjeta' value='{$cvv}'
                           placeholder='123' maxlength='4'
                           style='width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box;'>
                    {$errCvv}
                </div>
            </div>
        </div>";
    }

    private function generaInfoCamarero(): string
    {
        return "
        <div id='info_camarero' style='border: 1px solid #eee; padding: 25px; margin-bottom: 30px; border-radius: 5px; background-color: #fafafa;'>
            <h3 style='margin-top: 0; margin-bottom: 10px; font-size: 16px;'>Pago al Camarero</h3>
            <p style='color: #666; font-size: 14px; margin: 0;'>Tu pedido se procesará y podrás realizar el pago directamente al camarero cuando recojas o recibas tu pedido.</p>
        </div>";
    }

    private function generaBotonConfirmar(): string
    {
        return "
        <div style='display: flex; justify-content: flex-end;'>
            <button type='submit' style='padding: 10px 30px; font-size: 14px; background: black; color: white; border: none; cursor: pointer; border-radius: 5px;'>
                Confirmar Pago
            </button>
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

            const btnTarjeta  = document.getElementById('btn_tarjeta');
            const btnCamarero = document.getElementById('btn_camarero');
            const formTarjeta = document.getElementById('formulario_tarjeta');
            const infoCamarero = document.getElementById('info_camarero');
            const inputsTarjeta = formTarjeta.querySelectorAll('input');

            if (metodo === 'tarjeta') {
                btnTarjeta.style.border  = '2px solid black';
                btnCamarero.style.border = '1px solid #ccc';
                formTarjeta.style.display  = 'block';
                infoCamarero.style.display = 'none';
                inputsTarjeta.forEach(input => input.required = true);
            } else {
                btnCamarero.style.border = '2px solid black';
                btnTarjeta.style.border  = '1px solid #ccc';
                formTarjeta.style.display  = 'none';
                infoCamarero.style.display = 'block';
                inputsTarjeta.forEach(input => input.required = false);
            }
        }
        </script>";
    }
}
