<?php

namespace es\ucm\fdi\aw\Formulario;

require_once __DIR__ . '/Formulario.php';
require_once __DIR__ . '/../../includes/ProductoDAO.php';
require_once __DIR__ . '/../../includes/ofertaDAO.php';
require_once __DIR__ . '/../../includes/OfertaProductoDAO.php';

class FormularioOferta extends Formulario
{
    private $oferta;

    public function __construct($oferta = null)
    {
        parent::__construct('formOferta');
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

        $descuento = $datos['descuento']
            ?? ($this->oferta ? $this->oferta->getDescuento() : 0);

        
        // ─────────────────────────────────────
        // PRODUCTOS DISPONIBLES
        // ─────────────────────────────────────
        $productosDisponibles = \ProductoDAO::getAllActivos();

        $productosDisponibles = array_map(function ($p) {
            return [
                'id' => $p->getId(),
                'nombre' => $p->getNombre(),
                'precio' => (float)$p->getPrecioFinal()
            ];
        }, $productosDisponibles);

        // ─────────────────────────────────────
        // PRODUCTOS YA ASIGNADOS (EDICIÓN)
        // ─────────────────────────────────────
        $productosOferta = [];

        if ($this->oferta) {

            $productosOferta = \ProductoDAO::getProductosDeOferta($this->oferta->getId());


            $productosOferta = array_map(function ($p) {
                return [
                    'id' => $p->getId(),
                    'nombre' => $p->getNombre(),
                    'precio' => (float)$p->getPrecioFinal(),
                    'cantidad' => $p->cantidad
                ];
            }, $productosOferta);

        }

        $productosJSON = json_encode($productosDisponibles, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
        $productosEditJSON = json_encode($productosOferta, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);

        $errores = self::generaErroresCampos(
            ['nombre', 'descripcion', 'fecha_inicio', 'fecha_fin', 'precio_inicial', 'precio_final'],
            $this->errores,
            'span',
            ['class' => 'text-danger']
        );

        $erroresGlobales = self::generaListaErroresGlobales(
            $this->errores,
            'text-danger'
        );
        return <<<HTML

{$erroresGlobales}

<script>
    const productosDisponibles = $productosJSON;
    const productosSeleccionados = $productosEditJSON;
</script>

<p>
<label for="nombre">Nombre:</label><br>
<input type="text" name="nombre" value="{$nombre}" required minlength="3" maxlength="100">
{$errores['nombre']}
</p>

<p>
<label>Descripción:</label><br>
<textarea name="descripcion">{$descripcion}</textarea>
</p>

<p>
<label>Fecha inicio:</label><br>
<input type="datetime-local" name="fecha_inicio" value="{$fecha_inicio}">
</p>

<p>
<label>Fecha fin:</label><br>
<input type="datetime-local" name="fecha_fin" value="{$fecha_fin}">
</p>

<br>

<div id="contenedorProductosDinamicos"></div>

<button type="button" id="aAddProduct">Añadir producto</button>

<br><br>

<p>
<label>Precio inicial:</label>
<input id="precio_inicial" type="number" readonly>
</p>

<p>
<label>Precio final:</label>
<input id="precio_final" name="precio_final" type="number" min='0'> <!--value="{precio_final}" -->
</p>

<p>
<label>Descuento:</label>
<input id="descuento" type="text" readonly value="{$descuento}">
</p>

<input type="hidden" id="descuentoHidden" name="descuento" value="{$descuento}">

<script src="../../JS/ofertaProductos.js"></script>
<script src="../../JS/ofertaDescuento.js"></script>

<p>
<button type="submit">Guardar oferta</button>
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

        $precio_final = trim((string)($datos['precio_final'] ?? ''));
        $descuento = (float)($_POST['descuento'] ?? 0);

        $productos = $_POST['productos'] ?? [];
        $cantidades = $_POST['cantidades'] ?? [];

        $nombre = filter_var($nombre, FILTER_SANITIZE_SPECIAL_CHARS);
        $descripcion = filter_var($descripcion, FILTER_SANITIZE_SPECIAL_CHARS);

        if ($nombre === '' || mb_strlen($nombre) < 3) {
            $this->errores['nombre'] = 'El nombre debe tener al menos 3 caracteres.';
        }

        if ($fecha_inicio === '') {
            $this->errores['fecha_inicio'] = 'La fecha de inicio es obligatoria.';
        }

        if ($fecha_fin === '') {
            $this->errores['fecha_fin'] = 'La fecha de fin es obligatoria.';
        }

        if ($precio_final === '' || !is_numeric($precio_final)) {
            $this->errores['precio_final'] = 'Precio final no válido.';
        }

        if ($descuento < 0) {
            $this->errores['precio_final'] = 'Precio final no válido.';
        }

        if (!empty($this->errores)) {
            return;
        }

        
        // CREAR O EDITAR OFERTA
        
        if ($this->oferta) {

            $ofertaId = $this->oferta->getId();

            \OfertaDAO::editarOferta(
                $ofertaId,
                $nombre,
                $descripcion,
                $fecha_inicio,
                $fecha_fin,
                $descuento
            );

            //  LIMPIAR RELACIONES (IMPORTANTE)
            \OfertaProductoDAO::removeProductosDeOferta($ofertaId);
        } else {

            $ofertaId = \OfertaDAO::crearOferta(
                $nombre,
                $descripcion,
                $fecha_inicio,
                $fecha_fin,
                $descuento
            );
        }

        
        // GUARDAR PRODUCTOS
        
        foreach ($productos as $i => $productoId) {

            $cantidad = $cantidades[$i] ?? 0;

            if ($cantidad > 0) {
                \OfertaProductoDAO::addProducto(
                    $ofertaId,
                    $productoId,
                    $cantidad
                );
            }
        }

        if (!$ofertaId) {
            $this->errores[] = 'No se pudo guardar la oferta.';
            return;
        }

        $this->urlRedireccion = 'listarOfertas.php';
    }
}
