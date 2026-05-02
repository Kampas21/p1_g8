<?php
namespace es\ucm\fdi\aw\Formulario;

require_once __DIR__.'/Formulario.php';
require_once __DIR__.'/../ProductoDAO.php';

class FormularioProducto extends Formulario
{
    private $isCreate;
    private $categoria_id;
    private $producto;

    public function __construct(bool $isCreate,int $categoria_id,$producto=null)
    {
        parent::__construct(
            'formProducto',
            ['enctype'=>'multipart/form-data']
        );

        $this->isCreate=$isCreate;
        $this->categoria_id=$categoria_id;
        $this->producto=$producto;
    }

    protected function generaCamposFormulario(&$datos)
    {
        $nombre=htmlspecialchars(
            $datos['nombre'] ??
            ($this->producto ? $this->producto->getNombre() : ''),
            ENT_QUOTES,'UTF-8'
        );

        $descripcion=htmlspecialchars(
            $datos['descripcion'] ??
            ($this->producto ? $this->producto->getDescripcion() : ''),
            ENT_QUOTES,'UTF-8'
        );

        $precio=htmlspecialchars(
            (string)(
                $datos['precio'] ??
                ($this->producto ? $this->producto->getPrecio() : '')
            ),
            ENT_QUOTES,'UTF-8'
        );

        $iva=(int)(
            $datos['iva'] ??
            ($this->producto ? $this->producto->getIVA() : 21)
        );

        $selected4=$iva===4?'selected':'';
        $selected10=$iva===10?'selected':'';
        $selected21=$iva===21?'selected':'';

        $seCocinaChecked=
            ($this->producto &&
             method_exists($this->producto,'getSeCocina') &&
             $this->producto->getSeCocina())
            ? 'checked'
            : '';

        $precioFinal='';

        if($precio!=='' && is_numeric($precio)){
            $precioFinal=number_format(
                ((float)$precio)*(1+($iva/100)),
                2,'.',''
            );
        }

        $imagenActualHTML='';

        if(!$this->isCreate && $this->producto){

            $img=trim((string)$this->producto->getImagen());

            if($img!==''){

                $imagenActualHTML="
<p>
<strong>Imagen actual:</strong><br>

<img
src='../../{$img}'
width='140'
height='100'
class='img-rounded'>

</p>";
            }
        }

return <<<HTML

<p>
<label>Nombre:</label><br>
<input
type="text"
name="nombre"
value="{$nombre}"
required>
</p>


<p>
<label>Descripción:</label><br>

<textarea
name="descripcion"
required>{$descripcion}</textarea>

</p>


<p>
<label>Precio base (€):</label><br>

<input
id="precio"
type="number"
step="0.01"
name="precio"
value="{$precio}"
required>

</p>


<p>
<label>IVA:</label><br>

<select id="iva" name="iva">

<option value="4" {$selected4}>4%</option>
<option value="10" {$selected10}>10%</option>
<option value="21" {$selected21}>21%</option>

</select>

</p>


<p>
<strong>Precio final:</strong>

<span id="precioFinal">
{$precioFinal}
</span> €

</p>


<p>
<label>

<input
type="checkbox"
name="se_cocina"
value="1"
{$seCocinaChecked}>

Se prepara en cocina

</label>
</p>


{$imagenActualHTML}


<p>
<label>Cambiar imagen:</label><br>

<input
type="file"
name="imagen"
accept=".jpg,.jpeg,.png">

</p>


<input
type="hidden"
name="categoria_id"
value="{$this->categoria_id}">


<p>
<button type="submit">
Actualizar producto
</button>
</p>


<script>
(function(){

const precioInput=document.getElementById('precio');
const ivaSelect=document.getElementById('iva');
const precioFinal=document.getElementById('precioFinal');

function recalcular(){

const precio=parseFloat(precioInput.value||0);
const iva=parseInt(ivaSelect.value||0);

if(precio>0){
precioFinal.textContent=
(precio*(1+iva/100)).toFixed(2);
}

}

precioInput.addEventListener('input',recalcular);
ivaSelect.addEventListener('change',recalcular);

recalcular();

})();
</script>

HTML;

    }


protected function procesaFormulario(&$datos)
{
$this->errores=[];

$nombre=trim($datos['nombre']);
$descripcion=trim($datos['descripcion']);

$precio=(float)$datos['precio'];
$iva=(int)$datos['iva'];

$categoria_id=(int)$datos['categoria_id'];

$se_cocina=
isset($datos['se_cocina'])?1:0;


/* SUBIR NUEVA IMAGEN */

$nuevaImagen=null;

if(
isset($_FILES['imagen']) &&
$_FILES['imagen']['error']===0
){

$nombreFinal=
time().'_'.
preg_replace(
'/[^A-Za-z0-9._-]/',
'',
basename($_FILES['imagen']['name'])
);

$rutaDestino=
__DIR__.'/../../img/img_productos/'.
$nombreFinal;

if(
move_uploaded_file(
$_FILES['imagen']['tmp_name'],
$rutaDestino
)
){

$nuevaImagen=
'img/img_productos/'.$nombreFinal;

}

}


/* si no sube nueva, conserva la anterior */

$imagenGuardar=
$nuevaImagen
?? $this->producto->getImagen();


$ok=\ProductoDAO::update(
$this->producto->getId(),
$nombre,
$descripcion,
$categoria_id,
$precio,
$iva,
$se_cocina,
$imagenGuardar
);


if(!$ok){

$this->errores[]=
'No se pudo guardar';

return;

}


$this->urlRedireccion=
'mostrarProductosCategoria.php?id='.
$categoria_id;

}

}