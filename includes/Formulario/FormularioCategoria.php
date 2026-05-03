<?php
namespace es\ucm\fdi\aw\Formulario;

require_once __DIR__ . '/Formulario.php';
require_once __DIR__ . '/../CategoriaDAO.php';

class FormularioCategoria extends Formulario
{
    private $categoria;

    public function __construct($categoria = null)
    {
        parent::__construct(
            'formCategoria',
            [
                'enctype' => 'multipart/form-data'
            ]
        );

        $this->categoria = $categoria;
    }

    private function selected($a, $b)
    {
        return $a === $b ? 'selected' : '';
    }

    protected function generaCamposFormulario(&$datos)
    {
        $nombre = htmlspecialchars(
            $datos['nombre'] ??
            ($this->categoria ? $this->categoria->getNombre() : ''),
            ENT_QUOTES,
            'UTF-8'
        );

        $descripcion = htmlspecialchars(
            $datos['descripcion'] ??
            ($this->categoria ? $this->categoria->getDescripcion() : ''),
            ENT_QUOTES,
            'UTF-8'
        );

        $imagen = htmlspecialchars(
            $datos['imagen'] ??
            ($this->categoria ? $this->categoria->getImagen() : 'platos_principales.jpg'),
            ENT_QUOTES,
            'UTF-8'
        );

        $errores = self::generaErroresCampos(
            ['nombre','descripcion'],
            $this->errores,
            'span',
            ['class'=>'text-danger']
        );

        $erroresGlobales =
            self::generaListaErroresGlobales(
                $this->errores,
                'text-danger'
            );

        $textoBoton =
            $this->categoria
            ? 'Actualizar categoría'
            : 'Crear categoría';

return <<<HTML

{$erroresGlobales}

<p>
<label for="nombre">Nombre:</label><br>

<input
id="nombre"
type="text"
name="nombre"
value="{$nombre}"
required
minlength="3"
maxlength="100">

{$errores['nombre']}
</p>


<p>
<label for="descripcion">Descripción:</label><br>

<textarea
id="descripcion"
name="descripcion"
required
minlength="3"
maxlength="500">{$descripcion}</textarea>

{$errores['descripcion']}
</p>


<p>
<label for="imagen">Imagen categoría:</label><br>

<p>

<label>Imagen actual:</label>

<br><br>

<img
src="../../img/categorias/{$imagen}"
width="140"
class="img-rounded">

</p>


<p>

<label>Subir nueva imagen:</label><br>

<input
type="file"
name="imagen_upload"
accept="image/jpeg,image/png,image/webp">

</p>

</p>


<p>

Vista previa:

<br><br>

<img
id="previewCategoria"
src="../../img/categorias/{$imagen}"
width="140"
class="img-rounded">

</p>


<p>
<button type="submit">
{$textoBoton}
</button>
</p>


<script>

document.addEventListener('DOMContentLoaded',function(){

const selector=
document.getElementById('imagen');

const preview=
document.getElementById('previewCategoria');

if(selector && preview){

selector.addEventListener(
'change',
function(){

preview.src='../../img/categorias/'+this.value;

}
);

}

});

</script>

HTML;

    }


    protected function procesaFormulario(&$datos)
{
    $this->errores = [];

    $nombre = trim((string)($datos['nombre'] ?? ''));
    $descripcion = trim((string)($datos['descripcion'] ?? ''));

    $nombre = filter_var(
        $nombre,
        FILTER_SANITIZE_SPECIAL_CHARS
    );

    $descripcion = filter_var(
        $descripcion,
        FILTER_SANITIZE_SPECIAL_CHARS
    );

    // Mantener imagen actual por defecto
    $imagen = $this->categoria
        ? $this->categoria->getImagen()
        : '';

    if ($nombre === '' || mb_strlen($nombre) < 3) {
        $this->errores['nombre'] =
            'El nombre debe tener al menos 3 caracteres.';
    }

    if ($descripcion === '' || mb_strlen($descripcion) < 3) {
        $this->errores['descripcion'] =
            'La descripción debe tener al menos 3 caracteres.';
    }

    if (!empty($this->errores)) {
        return;
    }


    /* SUBIR NUEVA IMAGEN */
    if (
        isset($_FILES['imagen_upload']) &&
        $_FILES['imagen_upload']['error'] == 0
    ) {

        $nombreArchivo =
            time() . '_' .
            basename($_FILES['imagen_upload']['name']);

        $destino =
            $_SERVER['DOCUMENT_ROOT'] .
            '/P1/p1_g8/img/categorias/' .
            $nombreArchivo;

        if (
            move_uploaded_file(
                $_FILES['imagen_upload']['tmp_name'],
                $destino
            )
        ) {
            $imagen = $nombreArchivo;
        }
    }


    $ok = $this->categoria

        ? \CategoriaDAO::update(
            $this->categoria->getId(),
            $nombre,
            $descripcion,
            $imagen
        )

        : \CategoriaDAO::create(
            $nombre,
            $descripcion,
            $imagen
        );


    if (!$ok) {

        $this->errores[] =
            'No se pudo guardar la categoría.';

        return;
    }

    $this->urlRedireccion='categoriasList.php';
}
}