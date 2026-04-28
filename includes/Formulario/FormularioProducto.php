<?php
namespace es\ucm\fdi\aw\Formulario;

require_once __DIR__ . '/Formulario.php';
require_once __DIR__ . '/../ProductoDAO.php';

class FormularioProducto extends Formulario
{
    private $isCreate;
    private $categoria_id;
    private $producto;

    public function __construct(bool $isCreate, int $categoria_id, $producto = null)
    {
        parent::__construct('formProducto');
        $this->isCreate = $isCreate;
        $this->categoria_id = $categoria_id;
        $this->producto = $producto;
    }

    protected function generaCamposFormulario(&$datos)
    {
        $nombre = htmlspecialchars($datos['nombre'] ?? ($this->producto ? $this->producto->getNombre() : ''), ENT_QUOTES, 'UTF-8');
        $descripcion = htmlspecialchars($datos['descripcion'] ?? ($this->producto ? $this->producto->getDescripcion() : ''), ENT_QUOTES, 'UTF-8');
        $precio = htmlspecialchars((string)($datos['precio'] ?? ($this->producto ? $this->producto->getPrecio() : '')), ENT_QUOTES, 'UTF-8');
        $iva = (int)($datos['iva'] ?? ($this->producto ? $this->producto->getIVA() : 21));

        $errores = self::generaErroresCampos(['nombre', 'descripcion', 'precio', 'iva'], $this->errores, 'span', ['class' => 'text-danger']);
        $erroresGlobales = self::generaListaErroresGlobales($this->errores, 'text-danger');
        $textoBoton = $this->isCreate ? 'Crear producto' : 'Actualizar producto';
        $precioFinal = '';

        if ($precio !== '' && is_numeric($precio)) {
            $precioFinal = number_format(((float)$precio) * (1 + ($iva / 100)), 2, '.', '');
        }

        $selected4 = $iva === 4 ? 'selected' : '';
        $selected10 = $iva === 10 ? 'selected' : '';
        $selected21 = $iva === 21 ? 'selected' : '';
        $seCocinaChecked = ($this->producto && method_exists($this->producto, 'getSeCocina') && $this->producto->getSeCocina()) ? 'checked' : '';

        return <<<HTML
        {$erroresGlobales}

        <p>
            <label for="nombre">Nombre:</label><br>
            <input id="nombre" type="text" name="nombre" value="{$nombre}" required minlength="3" maxlength="100">
            {$errores['nombre']}
        </p>

        <p>
            <label for="descripcion">Descripción:</label><br>
            <textarea id="descripcion" name="descripcion" required minlength="3" maxlength="1000">{$descripcion}</textarea>
            {$errores['descripcion']}
        </p>

        <p>
            <label for="precio">Precio base (€):</label><br>
            <input id="precio" type="number" name="precio" step="0.01" min="0.01" value="{$precio}" required>
            {$errores['precio']}
        </p>

        <p>
            <label for="iva">IVA:</label><br>
            <select id="iva" name="iva" required>
                <option value="4" {$selected4}>4%</option>
                <option value="10" {$selected10}>10%</option>
                <option value="21" {$selected21}>21%</option>
            </select>
            {$errores['iva']}
        </p>

        <p>
            <strong>Precio final:</strong>
            <span id="precioFinal">{$precioFinal}</span> €
        </p>

        <p>
            <label for="se_cocina">
                <input id="se_cocina" type="checkbox" name="se_cocina" value="1" {$seCocinaChecked}>
                Se prepara en cocina (si no está marcado, lo prepara el camarero)
            </label>
        </p>
        <input type="hidden" name="categoria_id" value="{$this->categoria_id}">

        <p>
            <button type="submit">{$textoBoton}</button>
        </p>

        <script>
        (function () {
            const precioInput = document.getElementById('precio');
            const ivaSelect = document.getElementById('iva');
            const precioFinal = document.getElementById('precioFinal');

            function recalcular() {
                const precio = parseFloat(precioInput.value || '0');
                const iva = parseInt(ivaSelect.value || '0', 10);

                if (precio > 0 && [4, 10, 21].includes(iva)) {
                    precioFinal.textContent = (precio * (1 + iva / 100)).toFixed(2);
                } else {
                    precioFinal.textContent = '';
                }
            }

            precioInput.addEventListener('input', recalcular);
            ivaSelect.addEventListener('change', recalcular);
            recalcular();
        })();
        </script>
        HTML;
    }

    protected function procesaFormulario(&$datos)
    {
        $this->errores = [];

        $nombre = trim((string)($datos['nombre'] ?? ''));
        $descripcion = trim((string)($datos['descripcion'] ?? ''));
        $precio = filter_var($datos['precio'] ?? null, FILTER_VALIDATE_FLOAT);
        $iva = filter_var($datos['iva'] ?? null, FILTER_VALIDATE_INT);
        $categoria_id = filter_var($datos['categoria_id'] ?? null, FILTER_VALIDATE_INT);

        $nombre = filter_var($nombre, FILTER_SANITIZE_SPECIAL_CHARS);
        $descripcion = filter_var($descripcion, FILTER_SANITIZE_SPECIAL_CHARS);

        if ($nombre === '' || mb_strlen($nombre) < 3) {
            $this->errores['nombre'] = 'El nombre debe tener al menos 3 caracteres.';
        }

        if ($descripcion === '' || mb_strlen($descripcion) < 3) {
            $this->errores['descripcion'] = 'La descripción debe tener al menos 3 caracteres.';
        }

        if ($precio === false || $precio <= 0) {
            $this->errores['precio'] = 'El precio debe ser un número mayor que 0.';
        }

        if ($iva === false || !in_array($iva, [4, 10, 21], true)) {
            $this->errores['iva'] = 'El IVA debe ser 4, 10 o 21.';
        }

        if ($categoria_id === false || $categoria_id <= 0) {
            $this->errores[] = 'Categoría inválida.';
        }

        if (!empty($this->errores)) {
            return;
        }

        $se_cocina = isset($datos['se_cocina']) ? 1 : 0;

        $ok = $this->isCreate
            ? \ProductoDAO::create($nombre, $descripcion, $categoria_id, (float)$precio, $iva, $se_cocina)
            : \ProductoDAO::update($this->producto->getId(), $nombre, $descripcion, $categoria_id, (float)$precio, $iva, $se_cocina);

        if (!$ok) {
            $this->errores[] = 'No se pudo guardar el producto.';
            return;
        }

        $this->urlRedireccion = 'mostrarProductosCategoria.php?id=' . $categoria_id;
    }
}
