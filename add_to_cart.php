<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';

if (!isLoggedIn()) {
    setFlash('warning', 'Debes iniciar sesión para continuar.');

    if (requestExpectsJson()) {
        jsonResponse([
            'ok' => false,
            'message' => 'Debes iniciar sesión para continuar.',
            'redirect' => baseUrl('login.php'),
        ], 401);
    }

    redirectTo('login.php');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verifyCsrfToken($_POST['csrf_token'] ?? null)) {
    setFlash('danger', 'No fue posible agregar el producto.');

    if (requestExpectsJson()) {
        jsonResponse([
            'ok' => false,
            'message' => 'No fue posible agregar el producto.',
        ], 422);
    }

    redirectTo('index.php');
}

$productId = (int) ($_POST['product_id'] ?? 0);
$quantity = max(1, (int) ($_POST['quantity'] ?? 1));
$user = currentUser();

$productStmt = db()->prepare('SELECT id, stock FROM products WHERE id = :id LIMIT 1');
$productStmt->execute(['id' => $productId]);
$product = $productStmt->fetch();

if (!$product) {
    setFlash('danger', 'El producto no existe.');

    if (requestExpectsJson()) {
        jsonResponse([
            'ok' => false,
            'message' => 'El producto no existe.',
        ], 404);
    }

    redirectTo('index.php');
}

$cartStmt = db()->prepare('SELECT id, quantity FROM cart_items WHERE user_id = :user_id AND product_id = :product_id AND status = :status LIMIT 1');
$cartStmt->execute([
    'user_id' => $user['id'],
    'product_id' => $productId,
    'status' => 'active',
]);
$cartItem = $cartStmt->fetch();

$cartId = 0;
$finalQuantity = min((int) $product['stock'], $quantity);

if ($finalQuantity < 1) {
    setFlash('warning', 'Este producto ya no tiene stock disponible.');

    if (requestExpectsJson()) {
        jsonResponse([
            'ok' => false,
            'message' => 'Este producto ya no tiene stock disponible.',
        ], 409);
    }

    redirectTo('index.php');
}

if ($cartItem) {
    $cartId = (int) $cartItem['id'];
    $newQuantity = min((int) $product['stock'], (int) $cartItem['quantity'] + $quantity);
    $updateStmt = db()->prepare('UPDATE cart_items SET quantity = :quantity WHERE id = :id');
    $updateStmt->execute([
        'quantity' => $newQuantity,
        'id' => $cartItem['id'],
    ]);
    $finalQuantity = $newQuantity;
} else {
    $insertStmt = db()->prepare('INSERT INTO cart_items (user_id, product_id, quantity, status) VALUES (:user_id, :product_id, :quantity, :status)');
    $insertStmt->execute([
        'user_id' => $user['id'],
        'product_id' => $productId,
        'quantity' => $finalQuantity,
        'status' => 'active',
    ]);
    $cartId = (int) db()->lastInsertId();
}

setFlash('success', 'Producto agregado al carrito.');

if (requestExpectsJson()) {
    jsonResponse([
        'ok' => true,
        'message' => 'Producto agregado al carrito.',
        'cartId' => $cartId,
        'productId' => $productId,
        'quantity' => $finalQuantity,
        'cartCount' => cartCount((int) $user['id']),
    ]);
}

redirectTo('cart.php');
