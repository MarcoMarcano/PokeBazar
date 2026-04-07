<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verifyCsrfToken($_POST['csrf_token'] ?? null)) {
    setFlash('danger', 'Solicitud invalida.');
    redirectTo('cart.php');
}

$user = currentUser();
$cartId = (int) ($_POST['cart_id'] ?? 0);
$action = $_POST['action'] ?? 'update';
$quantity = max(1, (int) ($_POST['quantity'] ?? 1));

$stmt = db()->prepare('SELECT ci.id, p.stock FROM cart_items ci INNER JOIN products p ON p.id = ci.product_id WHERE ci.id = :id AND ci.user_id = :user_id AND ci.status = :status LIMIT 1');
$stmt->execute([
    'id' => $cartId,
    'user_id' => $user['id'],
    'status' => 'active',
]);
$item = $stmt->fetch();

if (!$item) {
    setFlash('warning', 'No encontramos ese producto en tu carrito.');
    redirectTo('cart.php');
}

if ($action === 'remove') {
    $deleteStmt = db()->prepare('DELETE FROM cart_items WHERE id = :id');
    $deleteStmt->execute(['id' => $cartId]);
    setFlash('success', 'Producto eliminado del carrito.');
    redirectTo('cart.php');
}

$updateStmt = db()->prepare('UPDATE cart_items SET quantity = :quantity WHERE id = :id');
$updateStmt->execute([
    'quantity' => min((int) $item['stock'], $quantity),
    'id' => $cartId,
]);

setFlash('success', 'Cantidad actualizada.');
redirectTo('cart.php');
