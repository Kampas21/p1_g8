<!doctype html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <title>Inicio | Bistro FDI</title>
  <link href="CSS/estilo.css" rel="stylesheet" type="text/css">
</head>

<body>

  <header id="main-header"></header>
  <script src="JS/header-loader.js"></script>

  <!-- ================= CONTENIDO ================= -->
  <main>
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

    <!-- ================= ACCESO ================= -->
    <section aria-label="Acceso al prototipo" style="margin-top: 30px;">
      <div style="display:flex; gap:20px; flex-wrap:wrap; margin-left:40px; margin-right:1300px;">

        <!-- Caja 1: Acceso -->
        <div style="flex:1; min-width:260px; border:1px solid #ddd; border-radius:12px; padding:16px; background:#fff;">
          <h4 style="margin:0 0 12px 0;">Acceso</h4>
          <p style="margin:0 0 14px 0;">Entra o crea una cuenta para acceder.</p>
          <div style="display:flex; gap:12px; flex-wrap:wrap;">
            <a href="vistas/usuarios/acceso.php#login" style="display:inline-block; padding:10px 14px; border:1px solid #bbb; border-radius:10px; text-decoration:none; font-weight:bold;">
              Iniciar sesión
            </a>
            <a href="vistas/usuarios/acceso.php#registro" style="display:inline-block; padding:10px 14px; border:1px solid #bbb; border-radius:10px; text-decoration:none; font-weight:bold;">
              Registrarse
            </a>
          </div>
        </div>
        </div>

      </div>
    </section>

  </main>

  <h2>Panel</h2>

  <ul>

    <li>
      <a href="vistas/categorias/categoriasList.php">
        Ver categorías
      </a>
    </li>

    </li>

     <li>
      <a href="vistas/pedidos/elegirTipo.php">
        Nuevo pedido
      </a>

  </ul>

</body>

</html>
