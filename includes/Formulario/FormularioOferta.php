<?php
namespace es\ucm\fdi\aw\Formulario;

require_once __DIR__ . '/Formulario.php';
require_once __DIR__ . '/../OfertaDAO.php';

class FormularioOferta extends Formulario
{
    private $oferta;

    public function __construct($oferta = null)
    {
        parent::__construct(
            'formOferta'
        );

        $this->oferta = $oferta;
    }

    protected function generaCamposFormulario(&$datos)
    {
        $nombre = htmlspecialchars(
            $datos['nombre'] ??
            ($this->oferta ? $this->oferta->getNombre() : ''),
            ENT_QUOTES,
            'UTF-8'
        );

        $descripcion = htmlspecialchars(
            $datos['descripcion'] ??
            ($this->oferta ? $this->oferta->getDescripcion() : ''),
            ENT_QUOTES,
            'UTF-8'
        );

        $fecha_inicio = htmlspecialchars(
            $datos['fecha_inicio'] ??
            ($this->oferta ? $this->oferta->getFechaInicio() : ''),
            ENT_QUOTES,
            'UTF-8'
        );

        $fecha_fin = htmlspecialchars(
            $datos['fecha_fin'] ??
            ($this->oferta ? $this->oferta->getFechaFin() : ''),
            ENT_QUOTES,
            'UTF-8'
        );

        $descuento = htmlspecialchars(
            $datos['descuento'] ??
            ($this->oferta ? $this->oferta->getDescuento() : ''),
            ENT_QUOTES,
            'UTF-8'
        );

        $errores = self::generaErroresCampos(
            ['nombre','descripcion','fecha_inicio','fecha_fin','descuento'],
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
            $this->oferta
            ? 'Actualizar oferta'
            : 'Crear oferta';

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
name="descripcion">{$descripcion}</textarea>

{$errores['descripcion']}
</p>


<p>
<label for="fecha_inicio">Fecha inicio:</label><br>

<input
id="fecha_inicio"
type="datetime-local"
name="fecha_inicio"
value="{$fecha_inicio}"
required>

{$errores['fecha_inicio']}
</p>


<p>
<label for="fecha_fin">Fecha fin:</label><br>

<input
id="fecha_fin"
type="datetime-local"
name="fecha_fin"
value="{$fecha_fin}"
required>

{$errores['fecha_fin']}
</p>


<p>
<label for="descuento">Descuento (%):</label><br>

<input
id="descuento"
type="number"
step="0.01"
name="descuento"
value="{$descuento}"
required>

{$errores['descuento']}
</p>


<p>
<button type="submit">
{$textoBoton}
</button>
</p>

HTML;
    }


    protected function procesaFormulario(&$datos)
    {
        $this->errores = [];

        $nombre = trim((string)($datos['nombre'] ?? ''));
        $descripcion = trim((string)($datos['descripcion'] ?? ''));
        $fecha_inicio = trim((string)($datos['fecha_inicio'] ?? ''));
        $fecha_fin = trim((string)($datos['fecha_fin'] ?? ''));
        $descuento = trim((string)($datos['descuento'] ?? ''));

        $nombre = filter_var($nombre, FILTER_SANITIZE_SPECIAL_CHARS);
        $descripcion = filter_var($descripcion, FILTER_SANITIZE_SPECIAL_CHARS);

        // VALIDACIONES
        if ($nombre === '' || mb_strlen($nombre) < 3) {
            $this->errores['nombre'] =
                'El nombre debe tener al menos 3 caracteres.';
        }

        if ($fecha_inicio === '') {
            $this->errores['fecha_inicio'] =
                'La fecha de inicio es obligatoria.';
        }

        if ($fecha_fin === '') {
            $this->errores['fecha_fin'] =
                'La fecha de fin es obligatoria.';
        }

        if ($descuento === '' || !is_numeric($descuento)) {
            $this->errores['descuento'] =
                'El descuento debe ser un número válido.';
        }

        if (!empty($this->errores)) {
            return;
        }

        // GUARDAR
        $ok = $this->oferta

            ? \OfertaDAO::editarOferta(
                $this->oferta->getId(),
                $nombre,
                $descripcion,
                $fecha_inicio,
                $fecha_fin,
                $descuento
            )

            : \OfertaDAO::crearOferta(
                $nombre,
                $descripcion,
                $fecha_inicio,
                $fecha_fin,
                $descuento
            );

        if (!$ok) {
            $this->errores[] =
                'No se pudo guardar la oferta.';
            return;
        }

        $this->urlRedireccion = 'listarOfertas.php';
    }
}