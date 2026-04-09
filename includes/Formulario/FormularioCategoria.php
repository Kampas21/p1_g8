<?php
namespace es\ucm\fdi\aw\Formulario;

require_once __DIR__ . '/Formulario.php';
require_once __DIR__ . '/../../entities/categoria.php'; 
require_once __DIR__ . '/../../includes/auth.php';

class FormularioCategoria extends Formulario
{
    private $isCreate;
    private $categoriaToEdit;

    public function __construct(bool $isCreate, ?array $categoriaToEdit = null) {
        $this->isCreate = $isCreate;
        $this->categoriaToEdit = $categoriaToEdit;

        parent::__construct('formCategoria', [
            'urlRedireccion' => RUTA_APP.'/vistas/categorias/categoriasList.php'
        ]);
    }
    
    protected function generaCamposFormulario(&$datos)
    {
        $nombre = $datos['nombre'] ?? $this->categoriaToEdit['nombre'] ?? '';
        $descripcion = $datos['descripcion'] ?? $this->categoriaToEdit['descripcion'] ?? '';

        $htmlErroresGlobales = self::generaListaErroresGlobales($this->errores);
        $erroresCampos = self::generaErroresCampos(['nombre', 'descripcion'], $this->errores, 'span', array('class' => 'error'));

        return <<<EOF
        $htmlErroresGlobales
        <fieldset>
            <legend>Datos de la Categoría</legend>
            <div>
                <label for="nombre">Nombre de la Categoría:</label>
                <input id="nombre" type="text" name="nombre" value="$nombre" required />
                {$erroresCampos['nombre']}
            </div>
            <div style="margin-top: 10px;">
                <label for="descripcion">Descripción:</label>
                <textarea id="descripcion" name="descripcion" rows="5" style="width:100%; box-sizing:border-box;" required>$descripcion</textarea>
                {$erroresCampos['descripcion']}
            </div>
            <div style="margin-top: 20px;">
                <button type="submit" class="btn primary">Guardar Categoría</button>
            </div>
        </fieldset>
        EOF;
    }

    protected function procesaFormulario(&$datos)
    {
        $this->errores = [];

        $nombre = trim($datos['nombre'] ?? '');
        $descripcion = trim($datos['descripcion'] ?? '');

        if ($nombre === '') {
            $this->errores['nombre'] = 'El nombre no puede estar vacío.';
        }
        if ($descripcion === '') {
            $this->errores['descripcion'] = 'La descripción no puede estar vacía.';
        }

        if (count($this->errores) === 0) {
            if ($this->isCreate) {
                // LLamamos a la base de datos para crear
                \Categoria::crearCategoria($nombre, $descripcion);
            } else {
                // Llamamos a la base de datos para actualizar
                \Categoria::actualizarCategoria((int)$this->categoriaToEdit['id'], $nombre, $descripcion);
            }
        }
    }
}