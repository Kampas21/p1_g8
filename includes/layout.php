<?php
declare(strict_types=1);

function nav_links(?array $user): array {
    $links = [
        ['href' => '/p1_g8/index.php', 'label' => 'Inicio'],
        ['href' => '/p1_g8/vistas/usuarios/acceso.php', 'label' => 'Acceso'],
    ];

    if ($user) {
        $links = [
            ['href' => '/p1_g8/index.php', 'label' => 'Inicio'],
            ['href' => '/p1_g8/vistas/usuarios/perfil.php', 'label' => 'Perfil'],
        ];

        if (user_has_role($user, 'gerente')) {
            $links[] = ['href' => '/p1_g8/entities/usuarios.php', 'label' => 'Usuarios'];
        }

        $links[] = ['href' => '/p1_g8/vistas/usuarios/logout.php', 'label' => 'Salir'];
    }

    return $links;
}

function layout_header(string $title, string $activeHref = ''): void {
    $user = current_user();
    ?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($title) ?> | Bistro FDI</title>
  <link rel="stylesheet" href="/p1_g8/CSS/style.css">
</head>
<body>
<header class="site-header">
  <div class="header-container">
    <div class="logo-title">
      <img src="/p1_g8/img/logo_personalizado.png" alt="Logo Bistro FDI" class="logo">
      <div>
        <h1>Bistro FDI</h1>
      </div>
    </div>
    <nav aria-label="Navegación principal">
      <ul class="nav-list">
        <?php foreach (nav_links($user) as $link): ?>
          <li><a href="<?= e($link['href']) ?>"<?= $activeHref === $link['href'] ? ' class="active"' : '' ?>><?= e($link['label']) ?></a></li>
        <?php endforeach; ?>
      </ul>
    </nav>
  </div>
</header>
<?php
}

function layout_flash_messages(): void {
    foreach (flash_get_all() as $item) {
        $type = in_array($item['type'], ['error', 'success', 'info'], true) ? $item['type'] : 'info';
        echo '<div class="notice ' . e($type) . '">' . e($item['message']) . '</div>';
    }
}

