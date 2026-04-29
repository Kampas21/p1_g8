<?php 

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/OfertaDAO.php';

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

$ofertas = OfertaDAO::getAll();

$tituloPagina = 'Lista de ofertas';
$rutaCSS = '../../CSS/estilo.css';

ob_start();
?>

<h1>Lista de ofertas</h1>

<p>
    <a class="btn-nuevo" href="crearOferta.php">Nueva Oferta</a>
</p>

<div class="panel table-wrap">
    <table class="tabla">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Fecha Inicio</th>
                <th>Fecha Fin</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>

        <tbody>
            <?php foreach ($ofertas as $oferta): ?>
                <?php require __DIR__ . '/_fila_oferta.php'; ?>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<p>
    <a class="btn-volver" href="../../index.php">Volver</a>
</p>

<?php
$contenidoPrincipal = ob_get_clean();
require __DIR__ . '/../../includes/plantilla.php';