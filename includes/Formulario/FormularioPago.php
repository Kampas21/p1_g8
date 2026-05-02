<?php
namespace es\ucm\fdi\aw\Formulario;

require_once __DIR__ . '/Formulario.php';
require_once __DIR__ . '/../../entities/pedido.php';
require_once __DIR__ . '/../../includes/pedidoService.php';

class FormularioPago extends Formulario {

  private $usuario_id;
  private $total_sin_descuentos;
  private $total_descuento;
  private $total;

  public function __construct(int $usuario_id, float $total_sin_descuentos, float $total_descuento, float $total) {
    $this->usuario_id = $usuario_id;
    $this->total_sin_descuentos = $total_sin_descuentos;
    $this->total_descuento = $total_descuento;
    $this->total = $total;
    parent::__construct('formPago', ['urlRedireccion' => 'confirmacion.php']);
    }

    protected function generaCamposFormulario(&$datos) {
        $numero_tarjeta = $datos['numero_tarjeta'] ?? '';
        $nombre_tarjeta = $datos['nombre_tarjeta'] ?? '';
        $caducidad = $datos['caducidad'] ?? '';
        $cvv = $datos['cvv'] ?? '';
        
        $htmlErroresGlobales = self::generaListaErroresGlobales($this->errores);
        $erroresCampos = self::generaErroresCampos(['metodo_pago', 'numero_tarjeta', 'nombre_tarjeta', 'caducidad', 'cvv'], $this->errores, 'span', ['class' => 'mensaje-error']);

        if ($this->total <= 0) {
            return <<<EOF
            $htmlErroresGlobales
            <div class="panel">
              <h3>🎉 Pedido gratuito</h3>
              <p>El total de tu pedido es de 0.00€. No es necesario realizar ningún pago.</p>
              <br>
              <button type="submit" name="metodo_pago" value="gratis" class="btn primary">
                Confirmar y preparar pedido
              </button>
            </div>
            EOF;
        }

        return <<<EOF
        $htmlErroresGlobales

        <!-- Pagar al camarero -->
        <div class="panel">
          <h3>💵 Pagar al camarero</h3>
          <p>El camarero pasará a cobrarle en su mesa o en el mostrador.</p>
          <button type="button" class="btn primary"
            onclick="this.name='metodo_pago'; this.value='camarero'; this.form.submit();">
            Pagar al camarero
          </button>
        </div>

        <!-- Pagar con tarjeta -->
        <div class="panel">
          <h3>💳 Pagar con tarjeta</h3>
          {$erroresCampos['metodo_pago']}

          <div class="form-grid">
            <div class="full">
              <label>Número de tarjeta</label>
              <input type="text" name="numero_tarjeta" maxlength="19" placeholder="1234 5678 9012 3456" value="{$numero_tarjeta}">
              {$erroresCampos['numero_tarjeta']}
            </div>

            <div class="full">
              <label>Nombre del titular</label>
              <input type="text" name="nombre_tarjeta" placeholder="NOMBRE APELLIDO" value="{$nombre_tarjeta}">
              {$erroresCampos['nombre_tarjeta']}
            </div>

            <div>
              <label>Caducidad (MM/AA)</label>
              <input type="text" name="caducidad" maxlength="5" placeholder="MM/AA" value="{$caducidad}">
              {$erroresCampos['caducidad']}
            </div>

            <div>
              <label>CVV</label>
              <input type="text" name="cvv" maxlength="4" placeholder="123" value="{$cvv}">
              {$erroresCampos['cvv']}
            </div>
          </div>

          <div class="mt-16">
            <button type="submit" name="metodo_pago" value="tarjeta" class="btn primary">
              Pagar {$this->total} € con tarjeta
            </button>
          </div>
        </div>
        EOF;
    }

    protected function procesaFormulario(&$datos) {
        $this->errores = [];
        $metodo = $datos['metodo_pago'] ?? '';

        if ($metodo === 'gratis') {
            if ($this->total > 0) {
                $this->errores['metodo_pago'] = 'Operación inválida.';
                return;
            }
            
            $pedido_id = \PedidoService::confirmarCarrito($this->usuario_id, 'tarjeta', $this->total_sin_descuentos, $this->total_descuento);
            if (!$pedido_id) {
                $this->errores['metodo_pago'] = 'No se ha podido confirmar el pedido.';
            }
            return;
        }

        if ($metodo === 'camarero') {
            $pedido_id = \PedidoService::confirmarCarrito($this->usuario_id, 'camarero', $this->total_sin_descuentos, $this->total_descuento);
            if (!$pedido_id) {
                $this->errores['metodo_pago'] = 'No se ha podido confirmar el pedido.';
            }
            return;
        }

        if ($metodo === 'tarjeta') {
            $numero = trim($datos['numero_tarjeta'] ?? '');
            $nombre = trim($datos['nombre_tarjeta'] ?? '');
            $caducidad = trim($datos['caducidad'] ?? '');
            $cvv = trim($datos['cvv'] ?? '');
            if (!preg_match('/^\d{16}$/', preg_replace('/\s+/', '', $numero))) {
                $this->errores['numero_tarjeta'] = 'El número de tarjeta debe tener 16 dígitos.';
            }
            if ($nombre === '') {
                $this->errores['nombre_tarjeta'] = 'Introduce el nombre del titular.';
            }
            if (!preg_match('/^(0[1-9]|1[0-2])\/\d{2}$/', $caducidad)) {
                $this->errores['caducidad'] = 'Formato de caducidad inválido (MM/AA).';
            }
            if (!preg_match('/^\d{3,4}$/', $cvv)) {
                $this->errores['cvv'] = 'El CVV debe tener 3 o 4 dígitos.';
            }
            if (count($this->errores) === 0) {
                $pedido_id = \PedidoService::confirmarCarrito($this->usuario_id, 'tarjeta', $this->total_sin_descuentos, $this->total_descuento);
                if (!$pedido_id) {
                    $this->errores['metodo_pago'] = 'No se ha podido confirmar el pedido.';
                    return;
                }
                return;
            }
            return;
        }

        $this->errores['metodo_pago'] = 'Selecciona un método de pago.';
    }
}
