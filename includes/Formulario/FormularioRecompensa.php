<?php
namespace es\ucm\fdi\aw\Formulario;

require_once __DIR__ . '/Formulario.php';
require_once __DIR__ . '/../ProductoDAO.php';
require_once __DIR__ . '/../RecompensaDAO.php';

class FormularioRecompensa extends Formulario {
    private $recompensa;

    public function __construct($recompensa = null) {
        parent::__construct('formRecompensa');
        $this->recompensa = $recompensa;
    }

    protected function generaCamposFormulario(&$datos) {
        $productoId = (int)($datos['producto_id'] ?? ($this->recompensa ? $this->recompensa->getProductoId() : 0));
        $bistrocoins = htmlspecialchars((string)($datos['bistrocoins'] ?? ($this->recompensa ? $this->recompensa->getBistrocoins() : '')), ENT_QUOTES, 'UTF-8');
        $activa = (int)($datos['activa'] ?? ($this->recompensa ? ($this->recompensa->isActiva() ? 1 : 0) : 1));
        $productos = \ProductoDAO::getAllActivos();
        $errores = self::generaErroresCampos(['producto_id', 'bistrocoins', 'activa'], $this->errores, 'span', ['class' => 'text-danger']);
        $erroresGlobales = self::generaListaErroresGlobales($this->errores, 'text-danger');
        $textoBoton = $this->recompensa ? 'Actualizar recompensa' : 'Crear recompensa';

        $opciones = '<option value="">Selecciona un producto</option>';
        foreach ($productos as $producto) {
            $selected = $producto->getId() === $productoId ? 'selected' : '';
            $precio = number_format($producto->getPrecioFinal(), 2);
            $nombre = htmlspecialchars($producto->getNombre(), ENT_QUOTES, 'UTF-8');
            $opciones .= "<option value=\"{$producto->getId()}\" {$selected}>{$nombre} ({$precio} €)</option>";
        }

        $checkedSi = $activa === 1 ? 'checked' : '';
        $checkedNo = $activa === 0 ? 'checked' : '';

        return <<<HTML
        {$erroresGlobales}
        <p>
            <label for="producto_id">Producto recompensable:</label><br>
            <select id="producto_id" name="producto_id" required>
                {$opciones}
            </select>
            {$errores['producto_id']}
        </p>
        <p>
            <label for="bistrocoins">BistroCoins necesarias:</label><br>
            <input id="bistrocoins" type="number" name="bistrocoins" min="1" step="1" required value="{$bistrocoins}">
            {$errores['bistrocoins']}
        </p>
        <p>
            <label>Activa:</label><br>
            <label><input type="radio" name="activa" value="1" {$checkedSi}> Sí</label>
            <label><input type="radio" name="activa" value="0" {$checkedNo}> No</label>
            {$errores['activa']}
        </p>
        <p>
            <button type="submit" class="btn primary">{$textoBoton}</button>
        </p>
        HTML;
    }

    protected function procesaFormulario(&$datos) {
        $this->errores = [];
        $productoId = filter_var($datos['producto_id'] ?? null, FILTER_VALIDATE_INT);
        $bistrocoins = filter_var($datos['bistrocoins'] ?? null, FILTER_VALIDATE_INT);
        $activa = filter_var($datos['activa'] ?? 1, FILTER_VALIDATE_INT);

        if (!$productoId || !\ProductoDAO::getById($productoId)) {
            $this->errores['producto_id'] = 'Debes seleccionar un producto válido.';
        }
        if ($bistrocoins === false || $bistrocoins < 1) {
            $this->errores['bistrocoins'] = 'Las BistroCoins deben ser un entero mayor que 0.';
        }
        if (!in_array($activa, [0, 1], true)) {
            $this->errores['activa'] = 'Debes indicar si la recompensa está activa.';
        }

        $excludeId = $this->recompensa ? (int)$this->recompensa->getId() : null;
        if ($productoId && \RecompensaDAO::existsForProducto($productoId, $excludeId)) {
            $this->errores['producto_id'] = 'Ese producto ya tiene una recompensa asociada.';
        }

        if (!empty($this->errores)) {
            return;
        }

        $ok = $this->recompensa
            ? \RecompensaDAO::update((int)$this->recompensa->getId(), $productoId, $bistrocoins, $activa)
            : \RecompensaDAO::create($productoId, $bistrocoins);

        if (!$ok) {
            $this->errores[] = 'No se pudo guardar la recompensa.';
            return;
        }

        $this->urlRedireccion = 'listarRecompensas.php';
    }
}
