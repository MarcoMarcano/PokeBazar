<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';

if (!isLoggedIn()) {
    setFlash('warning', 'Debes iniciar sesion para continuar.');

    if (requestExpectsJson()) {
        jsonResponse([
            'ok' => false,
            'message' => 'Debes iniciar sesion para continuar.',
            'redirect' => baseUrl('login.php'),
        ], 401);
    }

    redirectTo('login.php');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verifyCsrfToken($_POST['csrf_token'] ?? null)) {
    setFlash('danger', 'Solicitud invalida.');

    if (requestExpectsJson()) {
        jsonResponse([
            'ok' => false,
            'message' => 'Solicitud invalida.',
        ], 422);
    }

    redirectTo('cart.php');
}

$user = currentUser();
$cartId = (int) ($_POST['cart_id'] ?? 0);
$action = $_POST['action'] ?? 'update';
$quantity = max(1, (int) ($_POST['quantity'] ?? 1));

$stmt = db()->prepare('SELECT ci.id, ci.product_id, p.stock FROM cart_items ci INNER JOIN products p ON p.id = ci.product_id WHERE ci.id = :id AND ci.user_id = :user_id AND ci.status = :status LIMIT 1');
$stmt->execute([
    'id' => $cartId,
    'user_id' => $user['id'],
    'status' => 'active',
]);
$item = $stmt->fetch();

if (!$item) {
    setFlash('warning', 'No encontramos ese producto en tu carrito.');

    if (requestExpectsJson()) {
        jsonResponse([
            'ok' => false,
            'message' => 'No encontramos ese producto en tu carrito.',
        ], 404);
    }

    redirectTo('cart.php');
}

if ($action === 'remove') {
    $deleteStmt = db()->prepare('DELETE FROM cart_items WHERE id = :id');
    $deleteStmt->execute(['id' => $cartId]);
    setFlash('success', 'Producto eliminado del carrito.');

    if (requestExpectsJson()) {
        jsonResponse([
            'ok' => true,
            'message' => 'Producto eliminado del carrito.',
            'cartId' => 0,
            'productId' => (int) $item['product_id'],
            'quantity' => 0,
            'cartCount' => cartCount((int) $user['id']),
        ]);
    }

    redirectTo('cart.php');
}

$finalQuantity = min((int) $item['stock'], $quantity);
$updateStmt = db()->prepare('UPDATE cart_items SET quantity = :quantity WHERE id = :id');
$updateStmt->execute([
    'quantity' => $finalQuantity,
    'id' => $cartId,
]);

setFlash('success', 'Cantidad actualizada.');

if (requestExpectsJson()) {
    jsonResponse([
        'ok' => true,
        'message' => 'Cantidad actualizada.',
        'cartId' => $cartId,
        'productId' => (int) $item['product_id'],
        'quantity' => $finalQuantity,
        'cartCount' => cartCount((int) $user['id']),
    ]);
}

redirectTo('cart.php');
