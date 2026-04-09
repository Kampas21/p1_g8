<?php
namespace es\ucm\fdi\aw\Formulario;

require_once __DIR__ . '/Formulario.php';
require_once __DIR__ . '/../categoriaService.php';

use CategoriaService;

class FormularioCategoria extends Formulario {

    private $categoria;

    public function __construct($categoria = null) {
        parent::__construct('formCategoria');
        $this->categoria = $categoria;
    }

    protected function generaCamposFormulario(&$datos) {

        // 🔒 ESCAPAR HTML (IMPORTANTE)
        $nombre = htmlspecialchars($datos['nombre'] ?? ($this->categoria ? $this->categoria->getNombre() : ''), ENT_QUOTES);
        $descripcion = htmlspecialchars($datos['descripcion'] ?? ($this->categoria ? $this->categoria->getDescripcion() : ''), ENT_QUOTES);

        $html = <<<EOF
        <p>
            <label>Nombre:</label><br>
            <input type="text" name="nombre" value="$nombre" required minlength="3"/>
        </p>

        <p>
            <label>Descripción:</label><br>
            <textarea name="descripcion" required minlength="3">$descripcion</textarea>
        </p>

        <p>
            <button type="submit">Guardar</button>
        </p>
        EOF;

        return $html;
    }

    protected function procesaFormulario(&$datos) {

        $this->errores = [];

        // 🔒 SANITIZAR INPUT
        $nombre = filter_var(trim($datos['nombre'] ?? ''), FILTER_SANITIZE_SPECIAL_CHARS);
        $descripcion = filter_var(trim($datos['descripcion'] ?? ''), FILTER_SANITIZE_SPECIAL_CHARS);

        // ✅ VALIDACIONES
        if (!$nombre || strlen($nombre) < 3) {
            $this->errores['nombre'] = "El nombre debe tener al menos 3 caracteres";
        }

        if (!$descripcion || strlen($descripcion) < 3) {
            $this->errores['descripcion'] = "La descripción debe tener al menos 3 caracteres";
        }

        // ❌ SI HAY ERRORES → PARAR
        if (count($this->errores) > 0) {
            return;
        }

        // 💾 GUARDAR (crear o editar)
        if ($this->categoria) {
            CategoriaService::update(
                $this->categoria->getId(),
                $nombre,
                $descripcion
            );
        } else {
            CategoriaService::create($nombre, $descripcion);
        }

        // 🔁 REDIRECCIÓN
        $this->urlRedireccion = "categoriasList.php";
    }
}