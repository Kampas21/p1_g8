<?php
namespace es\ucm\fdi\aw\Formulario;

require_once __DIR__ . '/Formulario.php';
require_once __DIR__ . '/../categoriaService.php';

class FormularioCategoria extends Formulario
{
    private $categoria;

    public function __construct($categoria = null)
    {
        parent::__construct('formCategoria');
        $this->categoria = $categoria;
    }

    protected function generaCamposFormulario(&$datos)
    {
        $nombre = htmlspecialchars($datos['nombre'] ?? ($this->categoria ? $this->categoria->getNombre() : ''), ENT_QUOTES, 'UTF-8');
        $descripcion = htmlspecialchars($datos['descripcion'] ?? ($this->categoria ? $this->categoria->getDescripcion() : ''), ENT_QUOTES, 'UTF-8');

        $errores = self::generaErroresCampos(['nombre', 'descripcion'], $this->errores, 'span', ['class' => 'text-danger']);
        $erroresGlobales = self::generaListaErroresGlobales($this->errores, 'text-danger');
        $textoBoton = $this->categoria ? 'Actualizar categoría' : 'Crear categoría';

        return <<<HTML
        {$erroresGlobales}

        <p>
            <label for="nombre">Nombre:</label><br>
            <input id="nombre" type="text" name="nombre" value="{$nombre}" required minlength="3" maxlength="100">
            {$errores['nombre']}
        </p>

        <p>
            <label for="descripcion">Descripción:</label><br>
            <textarea id="descripcion" name="descripcion" required minlength="3" maxlength="500">{$descripcion}</textarea>
            {$errores['descripcion']}
        </p>

        <p>
            <button type="submit">{$textoBoton}</button>
        </p>
        HTML;
    }

    protected function procesaFormulario(&$datos)
    {
        $this->errores = [];

        $nombre = trim((string)($datos['nombre'] ?? ''));
        $descripcion = trim((string)($datos['descripcion'] ?? ''));

        $nombre = filter_var($nombre, FILTER_SANITIZE_SPECIAL_CHARS);
        $descripcion = filter_var($descripcion, FILTER_SANITIZE_SPECIAL_CHARS);

        if ($nombre === '' || mb_strlen($nombre) < 3) {
            $this->errores['nombre'] = 'El nombre debe tener al menos 3 caracteres.';
        }

        if ($descripcion === '' || mb_strlen($descripcion) < 3) {
            $this->errores['descripcion'] = 'La descripción debe tener al menos 3 caracteres.';
        }

        if (!empty($this->errores)) {
            return;
        }

        $ok = $this->categoria
            ? \CategoriaService::update($this->categoria->getId(), $nombre, $descripcion)
            : \CategoriaService::create($nombre, $descripcion);

        if (!$ok) {
            $this->errores[] = 'No se pudo guardar la categoría.';
            return;
        }

        $this->urlRedireccion = 'categoriasList.php';
    }
}
