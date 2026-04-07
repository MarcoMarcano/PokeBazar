<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verifyCsrfToken($_POST['csrf_token'] ?? null)) {
    setFlash('danger', 'No fue posible agregar el producto.');
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
    redirectTo('index.php');
}

$cartStmt = db()->prepare('SELECT id, quantity FROM cart_items WHERE user_id = :user_id AND product_id = :product_id AND status = :status LIMIT 1');
$cartStmt->execute([
    'user_id' => $user['id'],
    'product_id' => $productId,
    'status' => 'active',
]);
$cartItem = $cartStmt->fetch();

if ($cartItem) {
    $newQuantity = min((int) $product['stock'], (int) $cartItem['quantity'] + $quantity);
    $updateStmt = db()->prepare('UPDATE cart_items SET quantity = :quantity WHERE id = :id');
    $updateStmt->execute([
        'quantity' => $newQuantity,
        'id' => $cartItem['id'],
    ]);
} else {
    $insertStmt = db()->prepare('INSERT INTO cart_items (user_id, product_id, quantity, status) VALUES (:user_id, :product_id, :quantity, :status)');
    $insertStmt->execute([
        'user_id' => $user['id'],
        'product_id' => $productId,
        'quantity' => min((int) $product['stock'], $quantity),
        'status' => 'active',
    ]);
}

setFlash('success', 'Producto agregado al carrito.');
redirectTo('cart.php');
