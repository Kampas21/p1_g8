<?php
require_once __DIR__ . '/includes/config.php';

$tituloPagina = 'Contacto | Bistro FDI';
ob_start();
?>

<div class="panel">
    <h2>Contacto</h2>

    <p>¡Contáctanos!</p>

    
    <form action="mailto:robernab@ucm.es" method="post" enctype="text/plain" class="formulario-contacto">
        <fieldset>
            <legend>Envíanos un mensaje</legend>
            <div class="form-grupo">
                <label for="nombre">Nombre:</label><br>
                <input type="text" id="nombre" name="nombre" placeholder="Tu nombre..." required>
            </div>
            
            <div class="form-grupo"> 
                <label for="correo">Correo electrónico:</label><br>
                <input type="email" id="correo" name="correo" placeholder="ejemplo@correo.com" required>
            </div>
            
            <div class="form-grupo">
                <label for="mensaje">Mensaje:</label><br>
                <textarea id="mensaje" name="mensaje" rows="5" cols="40" placeholder="Escribe tu mensaje aquí..." required></textarea>
            </div>
            
            <div class="acciones">
                <button type="submit" class="btn primary">Enviar</button>
                <button type="reset" class="btn clear">Limpiar</button>
            </div>
        </fieldset>
    </form>
</div>

<?php 
$contenidoPrincipal = ob_get_clean();
require __DIR__ . '/includes/plantilla.php'; 
?>