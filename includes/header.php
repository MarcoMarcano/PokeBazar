<?php

declare(strict_types=1);

$user = currentUser();
$flash = getFlash();
$pageTitle = $pageTitle ?? 'PokeBazar';
$cartItemsCount = $user ? cartCount((int) $user['id']) : 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> | PokeBazar</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;700&family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="<?= e(assetVersionedUrl('assets/images/Copia de Copia de Copia de Copia de logo turquesaç.png')) ?>">
    <link rel="stylesheet" href="<?= e(assetVersionedUrl('assets/css/styles.css')) ?>">
    <script defer src="<?= e(assetVersionedUrl('assets/js/app.js')) ?>"></script>
</head>
<body>
    <div class="site-shell">
        <header class="topbar">
            <a class="brand" href="<?= e(baseUrl('index.php')) ?>">
                <span class="brand-mark logo-mark">
                    <img src="<?= e(assetVersionedUrl('assets/images/Copia de Copia de Copia de Copia de logo turquesaç.png')) ?>" alt="Logo de PokeBazar">
                </span>
                <div>
                    <strong>PokeBazar</strong>
                    <small>Cartas, coleccion y batalla</small>
                </div>
            </a>
            <nav class="main-nav">
                <a href="<?= e(baseUrl('index.php')) ?>">Productos</a>
                <a href="<?= e(baseUrl('cart.php')) ?>">Carrito <span class="badge" data-cart-count><?= $cartItemsCount ?></span></a>
                <?php if ($user): ?>
                    <a href="<?= e(baseUrl('profile.php')) ?>">Mi perfil</a>
                    <?php if (isAdmin()): ?>
                        <a href="<?= e(baseUrl('admin/index.php')) ?>">Administración</a>
                    <?php endif; ?>
                    <a href="<?= e(baseUrl('logout.php')) ?>">Cerrar sesión</a>
                <?php else: ?>
                    <a href="<?= e(baseUrl('login.php')) ?>">Ingresar</a>
                    <a href="<?= e(baseUrl('register.php')) ?>">Crear cuenta</a>
                <?php endif; ?>
            </nav>
        </header>

        <main class="content-wrap">
            <?php if ($flash): ?>
                <div class="alert alert-<?= e($flash['type']) ?>">
                    <?= e($flash['message']) ?>
                </div>
            <?php endif; ?>
