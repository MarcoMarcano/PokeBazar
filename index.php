<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';

$pageTitle = 'Catálogo Pokémon';
$search = trim($_GET['search'] ?? '');
$category = trim($_GET['category'] ?? '');
$sort = $_GET['sort'] ?? 'latest';

$sql = 'SELECT id, name, price, image_url, category, rarity, stock, description FROM products WHERE 1 = 1';
$params = [];

if ($search !== '') {
    $sql .= ' AND (name LIKE :search_name OR description LIKE :search_description)';
    $params['search_name'] = '%' . $search . '%';
    $params['search_description'] = '%' . $search . '%';
}

if ($category !== '') {
    $sql .= ' AND category = :category';
    $params['category'] = $category;
}

$orderBy = match ($sort) {
    'price_asc' => 'price ASC',
    'price_desc' => 'price DESC',
    'name_asc' => 'name ASC',
    default => 'id DESC',
};

$sql .= ' ORDER BY ' . $orderBy;
$stmt = db()->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();
$exchangeRate = getExchangeRate();
$exchangeRateLabel = 'Tasa actual: ' . number_format($exchangeRate, 2, '.', ',') . ' Bs/USD';

// Re-read latest stock from database so the catalog reflects recent purchases immediately.
$stockRefreshStmt = db()->query('SELECT id, stock FROM products');
$latestStocks = [];
foreach ($stockRefreshStmt->fetchAll() as $stockRow) {
    $latestStocks[(int) $stockRow['id']] = (int) $stockRow['stock'];
}

foreach ($products as &$product) {
    $productId = (int) $product['id'];
    if (isset($latestStocks[$productId])) {
        $product['stock'] = $latestStocks[$productId];
    }
}
unset($product);

$categoryStmt = db()->query('SELECT DISTINCT category FROM products WHERE category <> "" ORDER BY category ASC');
$categories = $categoryStmt->fetchAll(PDO::FETCH_COLUMN);

$catalogUser = currentUser();
$cartItemsByProduct = [];

if ($catalogUser) {
    $cartItemsStmt = db()->prepare('SELECT id, product_id, quantity FROM cart_items WHERE user_id = :user_id AND status = :status');
    $cartItemsStmt->execute([
        'user_id' => $catalogUser['id'],
        'status' => 'active',
    ]);

    foreach ($cartItemsStmt->fetchAll() as $cartItem) {
        $cartItemsByProduct[(int) $cartItem['product_id']] = [
            'id' => (int) $cartItem['id'],
            'quantity' => (int) $cartItem['quantity'],
        ];
    }
}

require_once __DIR__ . '/includes/header.php';
?>
<section class="hero-panel">
    <div>
        <p class="eyebrow">Mercado de cartas coleccionables</p>
        <h1>Encuentra cartas Pokemon para tu coleccion o tu mazo competitivo.</h1>
        <p class="hero-copy">PokeBazar combina catálogo, carrito, perfiles y administración en una sola experiencia hecha en PHP, HTML y JavaScript.</p>
    </div>
    <div class="hero-card">
        <span>Colecciones activas</span>
        <strong><?= count($products) ?></strong>
        <small>Productos visibles con búsqueda y filtros.</small>
    </div>
</section>

<section class="panel">
    <div style="margin-bottom: 12px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px;">
        <strong><?= e($exchangeRateLabel) ?></strong>
        <small>Precios mostrados en USD y equivalentes en Bs según la API.</small>
    </div>
    <form class="filters" method="get">
        <div class="field grow">
            <label for="search">Buscar productos</label>
            <input id="search" name="search" type="text" value="<?= e($search) ?>" placeholder="Ej. Charizard, Pikachu, booster box">
        </div>
        <div class="field">
            <label for="category">Categoria</label>
            <select id="category" name="category">
                <option value="">Todas</option>
                <?php foreach ($categories as $categoryOption): ?>
                    <option value="<?= e($categoryOption) ?>" <?= $categoryOption === $category ? 'selected' : '' ?>><?= e($categoryOption) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="field">
            <label for="sort">Ordenar</label>
            <select id="sort" name="sort">
                <option value="latest" <?= $sort === 'latest' ? 'selected' : '' ?>>Más recientes</option>
                <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Precio menor</option>
                <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Precio mayor</option>
                <option value="name_asc" <?= $sort === 'name_asc' ? 'selected' : '' ?>>Nombre A-Z</option>
            </select>
        </div>
        <button class="button primary" type="submit">Aplicar</button>
    </form>
</section>

<section class="product-grid">
    <?php if (!$products): ?>
        <article class="empty-state">
            <h2>No encontramos productos</h2>
            <p>Prueba con otra búsqueda o elimina los filtros activos.</p>
        </article>
    <?php endif; ?>

    <?php foreach ($products as $product): ?>
        <?php
        $productId = (int) $product['id'];
        $productStock = (int) $product['stock'];
        $productCartState = $cartItemsByProduct[$productId] ?? null;
        $productQuantity = (int) ($productCartState['quantity'] ?? 0);
        $productCartId = (int) ($productCartState['id'] ?? 0);
        ?>
        <article class="product-card">
            <div class="product-thumb">
                <?php if (!empty($product['image_url'])): ?>
                    <img class="js-product-zoomable" src="<?= e(assetUrl((string) $product['image_url'])) ?>" alt="<?= e($product['name']) ?>" data-fullsrc="<?= e(assetUrl((string) $product['image_url'])) ?>" onclick="window.openProductImage && window.openProductImage(this)">
                <?php else: ?>
                    <div class="product-placeholder">
                        <span>Imagen pendiente</span>
                        <strong><?= e($product['category'] ?: 'Pokemon TCG') ?></strong>
                    </div>
                <?php endif; ?>
                <span class="pill"><?= e($product['rarity'] ?: 'Coleccion') ?></span>
            </div>
            <div class="product-body">
                <div class="product-meta">
                    <span><?= e($product['category'] ?: 'Pokemon TCG') ?></span>
                    <span>Stock: <?= (int) $product['stock'] ?></span>
                </div>
                <h2><?= e($product['name']) ?></h2>
                <p><?= e($product['description']) ?></p>
                <div class="product-footer">
                    <div>
                        <strong><?= formatPrice((float) $product['price']) ?></strong>
                        <small>≈ <?= formatPriceBs((float) $product['price'], $exchangeRate) ?></small>
                    </div>
                    <form method="post" action="add_to_cart.php" class="inline-form js-catalog-cart-form" data-product-id="<?= $productId ?>" data-cart-id="<?= $productCartId ?>" data-quantity="<?= $productQuantity ?>" data-stock="<?= $productStock ?>">
                        <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                        <input type="hidden" name="product_id" value="<?= $productId ?>">
                        <input type="hidden" name="quantity" value="1">
                        <div class="catalog-cart-control" data-cart-control>
                            <button class="catalog-cart-step" type="button" data-cart-action="decrease" aria-label="Restar una unidad de <?= e($product['name']) ?>">-</button>
                            <span class="catalog-cart-quantity" data-cart-quantity><?= $productQuantity ?></span>
                            <button class="catalog-cart-step" type="button" data-cart-action="increase" aria-label="Sumar una unidad de <?= e($product['name']) ?>" <?= $productStock <= 0 || ($productStock > 0 && $productQuantity >= $productStock) ? 'disabled' : '' ?>>+</button>
                        </div>
                    </form>
                </div>
            </div>
        </article>
    <?php endforeach; ?>
</section>

<?php if ($catalogUser): ?>
    <a class="floating-cart-cta<?= $cartItemsCount > 0 ? ' is-visible' : '' ?>" href="<?= e(baseUrl('cart.php')) ?>" data-floating-cart-cta data-floating-cart-count="<?= $cartItemsCount ?>" aria-hidden="<?= $cartItemsCount > 0 ? 'false' : 'true' ?>">Agregar al carrito</a>
<?php endif; ?>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
