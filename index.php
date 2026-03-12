<?php
$tituloPagina = 'Inicio | Bistro FDI';
$rutaCSS = 'CSS/estilo.css';

ob_start();
?>

<h2>Descripción del proyecto</h2>

<p>
    Bistro FDI es una aplicación web para un bistró/cafetería que permite a los clientes realizar pedidos y seguir su
    estado. El personal del local puede gestionar los pedidos, organizar la preparación y controlar la entrega,
    mejorando la eficiencia del servicio y la experiencia del cliente. La plataforma contempla distintos roles
    (cliente, camarero, cocinero y gerente) y adapta las acciones disponibles a cada tipo de usuario.
</p>

<div class="hero">
    <img src="img/logo_personalizado.png" alt="Logo de Bistro FDI" class="foto_centrada">
</div>

<section class="caja-acceso">
    <h3>Acceso</h3>
    <p>Entra o crea una cuenta para acceder.</p>

    <div class="botones-acceso">
        <a href="vistas/usuarios/acceso.php#login" class="boton-enlace">Iniciar sesión</a>
        <a href="vistas/usuarios/acceso.php#registro" class="boton-enlace">Registrarse</a>
    </div>
</section>

<?php
$contenidoPrincipal = ob_get_clean();
require __DIR__ . '/includes/plantilla.php';
?>