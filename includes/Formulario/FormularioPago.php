<?php
namespace es\ucm\fdi\aw\Formulario;

require_once __DIR__ . '/Formulario.php';
require_once __DIR__ . '/../../entities/pedido.php';

class FormularioPago extends Formulario {

    private $pedido_id;
    private $total;

    public function __construct(int $pedido_id, float $total) {
        $this->pedido_id = $pedido_id;
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

        return <<<EOF
        $htmlErroresGlobales

        <!-- Pagar al camarero -->
        <div class="panel">
          <h3>💵 Pagar al camarero</h3>
          <p>El camarero pasará a cobrarle en su mesa o en el mostrador.</p>
          <button type="submit" name="metodo_pago" value="camarero" class="btn primary">
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

          <div style="margin-top:14px;">
            <button type="submit" name="metodo_pago" value="tarjeta" class="btn primary">
              Pagar {$this->total} € con tarjeta
            </button>
          </div>
        </div>
        EOF;
    }

    protected function procesaFormulario(&$datos) {
        $metodo = $datos['metodo_pago'] ?? '';
        $errores = [];

        if ($metodo === 'camarero') {
            \Pedido::confirmarPedido($this->pedido_id, 'camarero', $this->total);
            $_SESSION['ultimo_pedido_id'] = $this->pedido_id;
            return;
        }

        if ($metodo === 'tarjeta') {
            $numero    = trim($datos['numero_tarjeta'] ?? '');
            $nombre    = trim($datos['nombre_tarjeta'] ?? '');
            $caducidad = trim($datos['caducidad']      ?? '');
            $cvv       = trim($datos['cvv']            ?? '');

            if (!preg_match('/^\d{16}$/', preg_replace('/\s+/', '', $numero))) {
                $errores['numero_tarjeta'] = 'El número de tarjeta debe tener 16 dígitos.';
            }
            if ($nombre === '') {
                $errores['nombre_tarjeta'] = 'Introduce el nombre del titular.';
            }
            if (!preg_match('/^(0[1-9]|1[0-2])\/\d{2}$/', $caducidad)) {
                $errores['caducidad'] = 'Formato de caducidad inválido (MM/AA).';
            }
            if (!preg_match('/^\d{3,4}$/', $cvv)) {
                $errores['cvv'] = 'El CVV debe tener 3 o 4 dígitos.';
            }

            if (count($errores) === 0) {
                \Pedido::confirmarPedido($this->pedido_id, 'tarjeta', $this->total);
                $_SESSION['ultimo_pedido_id'] = $this->pedido_id;
                return;
            } else {
                return $errores;
            }
        }

        return ['metodo_pago' => 'Selecciona un método de pago.'];
    }
}