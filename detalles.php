
<?php
$tituloPagina = 'Detalles';
$rutaCSS = '../../CSS/estilo.css';
ob_start();?>

<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title>Detalles | Bistro FDI</title>

    <link href="CSS/estilo.css" rel="stylesheet" type="text/css">


</head>

<body>
   
    <main>
        <h1>Detalles de la página web</h1>
        <h2>Introducción</h2>
        <p>La página web de Bistro FDI es una plataforma digital diseñada para gestionar de forma integral el
            funcionamiento de un bistró moderno, centralizando en una única aplicación todos los procesos relacionados
            con la atención al cliente, la gestión interna del personal y la administración del negocio.
        </p>
        <p>
            A través de esta web, los clientes pueden registrarse, consultar la carta de productos, ver ofertas y
            recompensas disponibles, realizar pedidos de forma sencilla desde sus dispositivos y efectuar el pago,
            además de hacer seguimiento en tiempo real del estado de sus pedidos. De este modo, se mejora la experiencia
            del usuario al ofrecer un sistema rápido, cómodo y transparente.
        </p>
        <p>
            Por otro lado, el personal del bistró dispone de interfaces adaptadas a su rol y dispositivo de trabajo. Los
            camareros pueden gestionar la entrega y el cobro de los pedidos, los cocineros pueden visualizar y preparar
            los pedidos desde cocina de manera ordenada y eficiente, y el gerente puede administrar usuarios, productos,
            categorías, ofertas y recompensas, así como supervisar el estado de todos los pedidos en tiempo real.</p>
        </p>

        <br>
        <br>
        <br>


        <h2>Tipos de usuarios:</h2>
        <p><strong>● Cliente:</strong> Consumidor de los productos y servicios que ofrece Bistro FDI.
        </p>
        <p><strong>● Cocinero:</strong> Personal del Bistro FDI que se encarga de preparar los pedidos.
        </p>
        <p><strong>● Camarero:</strong> Personal del Bistro FDI que se encarga de entregar los pedidos
            preparados a los clientes y, si no se ha hecho previamente, cobrar a los clientes por
            los servicios ofrecidos.
        </p>
        <p> <strong>● Gerente:</strong> Personal del Bistro FDI que se encarga de gestionar los productos y
            servicios que se ofrecen en el Bistro FDI. </p>
        </p>
        <br>
        <br>
        <br>

        <h2>Funcionalidades</h2>

        <h3>Gestión de Usuarios</h3>
        <p>Descripción: Esta funcionalidad permite gestionar todos los usuarios de Bistro FDI, tanto el personal como
            los clientes. Se almacenan datos como nombre de usuario, email, nombre y apellidos, contraseña, rol y
            avatar/foto. Los usuarios pueden registrarse como clientes, iniciar sesión y actualizar su perfil. El
            gerente puede crear, listar, actualizar y borrar usuarios, así como asignarles roles (cliente, camarero,
            cocinero o gerente), respetando la jerarquía de permisos, donde cada rol hereda las acciones de los roles de
            menor prioridad. </p>

        <p>Esta función la pueden realizar los siguentes usuarios:</p>
        <p>● Cliente</p>
        <p>● Gerente</p>
        <br>

        <h3>Gestión de Productos</h3>
        <p>Descripción: Esta funcionalidad permite al gerente gestionar las categorías y productos que se ofrecen en
            Bistro FDI. Desde un ordenador, el gerente puede crear, listar, actualizar y “borrar” productos (marcándolos
            como no ofertados) y categorías, incluyendo nombre, descripción, imágenes, precio, tipo de IVA,
            disponibilidad y estado de oferta. La interfaz facilita la selección de categorías y calcula automáticamente
            el precio final con IVA, asegurando una gestión sencilla y clara de los productos disponibles para los
            clientes. </p>

        <p>Esta función la pueden realizar los siguentes usuarios:</p>
        <p>● Gerente</p>
        <br>


        <h3>Gestión de Pedidos</h3>
        <p>Descripción: Esta funcionalidad permite gestionar el ciclo completo de un pedido, desde que el cliente lo
            crea y paga hasta que es preparado y entregado. El cliente puede añadir productos, confirmar o cancelar el
            pedido y
            consultar su estado, mientras que los camareros y cocineros gestionan su preparación, cobro y entrega. La
            interfaz se adapta a los distintos dispositivos según el tipo de usuario.</p>

        <p>Esta función la pueden realizar los siguentes usuarios:</p>
        <p>● Cliente</p>
        <p>● Cocinero</p>
        <p>● Camarero</p>
        <p>● Gerente</p>
        <br>


        <h3>Preparación de los pedidos</h3>
        <p>Descripción: Esta funcionalidad gestiona la preparación de los pedidos en cocina. Los cocineros, desde una
            tablet en formato horizontal, pueden seleccionar un pedido en estado “En preparación” para empezar a
            cocinarlo (pasándolo a “Cocinando”), marcar los productos como preparados y, una vez completado todo,
            finalizar el pedido para que quede en estado “Listo cocina”.

            El gerente puede visualizar en una única vista todos los pedidos pendientes (Recibido, En preparación,
            Cocinando, Listo cocina y Terminado) y consultar el detalle del estado de cada producto. Además, puede ver
            claramente el avatar del cocinero responsable de cada pedido en preparación para supervisar su gestión. </p>

        <p>Esta función la pueden realizar los siguentes usuarios:</p>
        <p>● Cocinero</p>
        <p>● Gerente</p>
        <br>


        <h3>Gestión de Ofertas</h3>
        <p>Descripción: Esta funcionalidad permite gestionar ofertas especiales en la aplicación. Una oferta consiste en
            un pack de uno o varios productos con un descuento aplicado, con fechas de inicio y fin, y se almacena con
            su nombre, descripción, productos, cantidades y porcentaje de descuento.

            El gerente puede listar, crear, actualizar y borrar ofertas, mientras que los clientes pueden ver las
            ofertas disponibles, consultar los productos y cantidades necesarios para que sean aplicables y decidir
            aplicarlas a su pedido. La aplicación calcula automáticamente el precio con y sin descuento, registrando el
            ahorro en el pedido. Las ofertas se pueden aplicar varias veces si se cumplen las condiciones, pero no se
            pueden superponer sobre los mismos productos. </p>

        <p>Esta función la pueden realizar los siguentes usuarios:</p>
        <p>● Cliente</p>
        <p>● Gerente</p>
        <br>


        <h3>Gestión de Recompensas</h3>
        <p>Descripción: Esta funcionalidad gestiona el programa de fidelización de Bistro FDI mediante “BistroCoins”.
            Los clientes acumulan 1 BistroCoin por cada 1€ gastado y pueden canjearlos por productos de la carta.

            El gerente puede listar, crear, actualizar y borrar recompensas. Los clientes pueden consultar su saldo de
            BistroCoins, ver qué recompensas están disponibles y añadirlas a su pedido. El pedido registra cuáles
            productos se obtienen como recompensa, y el historial de pedidos refleja este detalle. Los BistroCoins se
            descuentan del saldo del cliente una vez que se paga el pedido. </p>

        <p>Esta función la pueden realizar los siguentes usuarios:</p>
        <p>● Cliente</p>
        <p>● Gerente</p>

        <br>
        <br>
        <br>

    </main>



</body>

</html>

<?php
$contenidoPrincipal = ob_get_clean();
require __DIR__ . '/includes/plantilla.php';