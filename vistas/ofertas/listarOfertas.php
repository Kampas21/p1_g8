<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);



require_once __DIR__ . '/../../includes/auth.php';

$user = current_user();

if (!$user || !user_has_role($user, 'gerente')) {
    $tituloPagina = 'Acceso bloqueado';
    $rutaCSS = '../../CSS/estilo.css';

    ob_start();
?>
    <div class="panel">
        <h1>Acceso bloqueado</h1>
        <p>Necesitas ser gerente para acceder a ofertas.</p>
        <p><a class="btn-volver" href="../../index.php">Volver al inicio</a></p>
    </div>
<?php
    $contenidoPrincipal = ob_get_clean();
    require __DIR__ . '/../../includes/plantilla.php';
    exit;
}

require_once __DIR__ . '/../../entities/oferta.php';
//require_once __DIR__ . '/../../entities/producto.php';
require_once __DIR__ . '/../../includes/productoService.php';
require_once __DIR__ . '/../../includes/ofertaService.php';

$ofertas = OfertaService::getAll();

$tituloPagina = 'Lista de ofertas';
$rutaCSS = '../../CSS/estilo.css';
ob_start();
?>

<link href="../../CSS/estilo.css" rel="stylesheet" type="text/css">

<h1>Lista de ofertas</h1>

<p><a class="btn-nuevo" href="crearOferta.php">Nueva Oferta</a></p>

<div class="panel table-wrap">
    <table>
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Descripción</th>
            <th>Fecha Inicio</th>
            <th>Fecha Fin</th>
            <th>Productos</th>
            <th>Precio total</th>
            <th>Descuento</th>
            <th>Precio final</th>
            <th>Estado</th>
            <th>Acciones</th>
        </tr>

        <?php foreach ($ofertas as $oferta): ?>
            <tr>
                <td><?= $oferta->getId() ?></td>
                <td><?= htmlspecialchars($oferta->getNombre()) ?></td>
                <td class="descripcion"><?= htmlspecialchars($oferta->getDescripcion()) ?></td>
                <td><?= htmlspecialchars(date('d/m/Y H:i', strtotime($oferta->getFechaInicio()))) ?></td>
                <td><?= htmlspecialchars(date('d/m/Y H:i', strtotime($oferta->getFechaFin()))) ?></td>

                <?php
                $precio_total = 0;
                $productos = ProductoService::getProductosDeOferta($oferta->getId()); // devuelve un array de productos con cantidad
                $lista = array_map(function ($p) use (&$precio_total) {
                    $precio = $p->getPrecioFinal();
                    $precio_cant = $precio * $p->cantidad;
                    $precio_total += $precio_cant;
                    return $p->getNombre() . ' (' . $p->cantidad . ') ' . round($precio_cant, 2) . '€';
                }, $productos);
                ?>

                <td><?php
                    foreach (array_chunk($lista, 2) as $grupo) {
                        echo htmlspecialchars(implode(', ', $grupo)) . "<br>";
                    }
                    ?>
                </td>

                <td><?= htmlspecialchars($precio_total) . '€' ?></td>
                <td><?= htmlspecialchars($oferta->getDescuento()) . '%' ?></td>
                <?php
                $precio_des = $oferta->aplicarDescuento($precio_total);
                ?>

                <td><?= htmlspecialchars($precio_des) . '€' ?></td>

                <td>
                    <?= (!$oferta->estaActiva())
                        ? '<span class="text-danger">Caducada</span>'
                        : '<span class="text-success">Activa</span>' ?>
                </td>
                <td>
                    <a class="btn editar" href="editarOferta.php?id=<?= $oferta->getId() ?>">Editar</a>
                    <a class="btn borrar" href="borrarOferta.php?id=<?= $oferta->getId() ?>">Borrar</a>

                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>

<p>
    <a class="btn-volver" href="../../index.php">Volver</a>
</p>

<?php
$contenidoPrincipal = ob_get_clean();
require __DIR__ . '/../../includes/plantilla.php';
