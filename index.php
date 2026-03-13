<?php
require_once __DIR__ . '/includes/config.php';

$tituloPagina = 'Inicio | Bistro FDI';
$rutaCSS = RUTA_APP . '/CSS/estilo.css';

ob_start();
?>

<div class="contenido-index">

<h2>Descripción del proyecto</h2>

<p>
    Bistro FDI es una aplicación web para un bistró/cafetería que permite a los clientes realizar pedidos y seguir su
    estado. El personal del local puede gestionar los pedidos, organizar la preparación y controlar la entrega,
    mejorando la eficiencia del servicio y la experiencia del cliente.
</p>

<p>
    La plataforma contempla distintos roles (cliente, camarero, cocinero y gerente) y adapta las acciones
    disponibles a cada tipo de usuario.
</p>

<ul class="lista-caracteristicas">
    <li><strong>📋 Pedidos online</strong><br>
    Los clientes pueden crear pedidos desde el catálogo de productos.</li>

    <li><strong>📦 Seguimiento</strong><br>
    Consulta el estado del pedido mientras se prepara.</li>

    <li><strong>👨‍🍳 Gestión interna</strong><br>
    El personal del bistró gestiona pedidos y categorías.</li>
</ul>

<section class="caja-acceso">
    <h3>Acceso</h3>
    <p>Entra o crea una cuenta para acceder.</p>

    <div class="botones-acceso">
        <a href="<?= RUTA_APP ?>/vistas/usuarios/acceso.php#login" class="boton-enlace">Iniciar sesión</a>
        <a href="<?= RUTA_APP ?>/vistas/usuarios/acceso.php#registro" class="boton-enlace">Registrarse</a>
    </div>
</section>

</div>

<?php
$contenidoPrincipal = ob_get_clean();
require __DIR__ . '/includes/plantilla.php';
?>