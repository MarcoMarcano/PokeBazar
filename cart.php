<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';

requireLogin();

$user = currentUser();
$pageTitle = 'Mi carrito';

$stmt = db()->prepare(
    'SELECT ci.id, ci.quantity, p.id AS product_id, p.name, p.price, p.image_url, p.stock
     FROM cart_items ci
     INNER JOIN products p ON p.id = ci.product_id
     WHERE ci.user_id = :user_id AND ci.status = :status
     ORDER BY ci.id DESC'
);
$stmt->execute([
    'user_id' => $user['id'],
    'status' => 'active',
]);
$items = $stmt->fetchAll();
$total = 0.0;
foreach ($items as $item) {
    $total += (float) $item['price'] * (int) $item['quantity'];
}

$fallbackCartImage = 'https://images.unsplash.com/photo-1613771404721-1f92d799e49f?auto=format&fit=crop&w=600&q=80';

require_once __DIR__ . '/includes/header.php';
?>
<section class="panel split-panel">
    <div>
        <p class="eyebrow">Carrito de compras</p>
        <h1>Revisa tus cartas seleccionadas.</h1>
        <p>Solo los usuarios con sesion iniciada pueden mantener productos en el carrito y generar una factura imprimible.</p>
    </div>
    <div class="price-panel">
        <span>Total actual</span>
        <strong><?= formatPrice($total) ?></strong>
        <small><?= count($items) ?> productos cargados</small>
    </div>
</section>

<section class="cart-layout">
    <div class="panel">
        <?php if (!$items): ?>
            <div class="empty-state compact">
                <h2>Tu carrito esta vacio</h2>
                <p>Explora el catalogo y agrega cartas para iniciar una compra.</p>
                <a class="button primary" href="index.php">Ir al catalogo</a>
            </div>
        <?php endif; ?>

        <?php foreach ($items as $item): ?>
            <article class="cart-item">
                <?php $cartImage = assetUrl((string) ($item['image_url'] ?: $fallbackCartImage)); ?>
                <img class="js-product-zoomable cart-product-image" src="<?= e($cartImage) ?>" alt="<?= e($item['name']) ?>" data-fullsrc="<?= e($cartImage) ?>" onclick="window.openProductImage && window.openProductImage(this)">
                <div class="cart-item-content">
                    <div>
                        <h2><?= e($item['name']) ?></h2>
                        <p><?= formatPrice((float) $item['price']) ?> c/u</p>
                    </div>
                    <form method="post" action="update_cart.php" class="cart-actions">
                        <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                        <input type="hidden" name="cart_id" value="<?= (int) $item['id'] ?>">
                        <input type="number" name="quantity" min="1" max="<?= (int) $item['stock'] ?>" value="<?= (int) $item['quantity'] ?>">
                        <button class="button ghost" type="submit" name="action" value="update">Actualizar</button>
                        <button class="button danger" type="submit" name="action" value="remove">Eliminar</button>
                    </form>
                </div>
                <strong><?= formatPrice((float) $item['price'] * (int) $item['quantity']) ?></strong>
            </article>
        <?php endforeach; ?>
    </div>

    <aside class="panel invoice-box">
        <h2>Resumen</h2>
        <div class="summary-row"><span>Subtotal</span><strong><?= formatPrice($total) ?></strong></div>
        <div class="summary-row"><span>Envio</span><strong>Gratis</strong></div>
        <div class="summary-row total"><span>Total</span><strong><?= formatPrice($total) ?></strong></div>
        <form method="post" action="invoice.php">
            <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
            <button class="button primary full" type="submit" <?= !$items ? 'disabled' : '' ?>>Generar factura</button>
        </form>
    </aside>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
