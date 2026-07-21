<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';

requireAdmin();

$pageTitle = 'Administrar productos';
$editingId = (int) ($_GET['edit'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        setFlash('danger', 'Token invalido.');
        redirectTo('admin/products.php');
    }

    $action = $_POST['action'] ?? '';

    if ($action === 'save') {
        $id = (int) ($_POST['id'] ?? 0);
        $payload = [
            'name' => trim($_POST['name'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'category' => trim($_POST['category'] ?? ''),
            'rarity' => trim($_POST['rarity'] ?? ''),
            'image_url' => trim($_POST['image_url'] ?? ''),
            'price' => (float) ($_POST['price'] ?? 0),
            'stock' => (int) ($_POST['stock'] ?? 0),
        ];

        if ($id > 0) {
            $stmt = db()->prepare('UPDATE products SET name = :name, description = :description, category = :category, rarity = :rarity, image_url = :image_url, price = :price, stock = :stock WHERE id = :id');
            $payload['id'] = $id;
            $stmt->execute($payload);
            setFlash('success', 'Producto actualizado.');
        } else {
            $stmt = db()->prepare('INSERT INTO products (name, description, category, rarity, image_url, price, stock) VALUES (:name, :description, :category, :rarity, :image_url, :price, :stock)');
            $stmt->execute($payload);
            setFlash('success', 'Producto creado.');
        }

        redirectTo('admin/products.php');
    }

    if ($action === 'delete') {
        $stmt = db()->prepare('DELETE FROM products WHERE id = :id');
        $stmt->execute(['id' => (int) ($_POST['id'] ?? 0)]);
        setFlash('success', 'Producto eliminado.');
        redirectTo('admin/products.php');
    }
}

$product = [
    'id' => 0,
    'name' => '',
    'description' => '',
    'category' => '',
    'rarity' => '',
    'image_url' => '',
    'price' => '',
    'stock' => '',
];

if ($editingId > 0) {
    $stmt = db()->prepare('SELECT * FROM products WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $editingId]);
    $productData = $stmt->fetch();
    if ($productData) {
        $product = $productData;
    }
}

$products = db()->query('SELECT id, name, category, rarity, price, stock FROM products ORDER BY id DESC')->fetchAll();
require_once __DIR__ . '/../includes/header.php';
?>
<section class="dashboard-grid admin-grid">
    <form method="post" class="panel stack-form product-editor">
        <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="id" value="<?= (int) $product['id'] ?>">
        <div class="editor-header">
            <div>
                <p class="eyebrow">Gestor de catálogo</p>
                <h1><?= $product['id'] ? 'Editar producto' : 'Nuevo producto' ?></h1>
            </div>
            <p class="editor-note">Completa solo lo esencial. Si la imagen es local, usa la ruta relativa como <strong>assets/images/archivo.jpg</strong>.</p>
        </div>

        <div class="editor-grid-compact">
            <div class="field field-span-2">
                <label for="name">Nombre</label>
                <input id="name" name="name" type="text" required value="<?= e((string) $product['name']) ?>">
            </div>

            <div class="field">
                <label for="category">Categoria</label>
                <input id="category" name="category" type="text" value="<?= e((string) $product['category']) ?>">
            </div>

            <div class="field">
                <label for="rarity">Rareza</label>
                <input id="rarity" name="rarity" type="text" value="<?= e((string) $product['rarity']) ?>">
            </div>

            <div class="field">
                <label for="price">Precio</label>
                <input id="price" name="price" type="number" step="0.01" min="0" required value="<?= e((string) $product['price']) ?>">
            </div>

            <div class="field">
                <label for="stock">Stock</label>
                <input id="stock" name="stock" type="number" min="0" required value="<?= e((string) $product['stock']) ?>">
            </div>

            <div class="field field-span-2">
                <label for="image_url">URL o ruta de imagen</label>
                <input id="image_url" name="image_url" type="text" value="<?= e((string) $product['image_url']) ?>" placeholder="assets/images/producto.jpg o https://...">
            </div>
        </div>

        <div class="field">
            <label for="description">Descripcion</label>
            <textarea id="description" name="description" rows="5" required placeholder="Describe el producto, su estado, expansion o contenido."><?= e((string) $product['description']) ?></textarea>
        </div>

        <div class="editor-actions">
            <button class="button primary" type="submit">Guardar producto</button>
            <?php if ($product['id']): ?>
                <a class="button ghost" href="products.php">Crear uno nuevo</a>
            <?php endif; ?>
        </div>
    </form>

    <section class="panel">
        <h2>Catálogo actual</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Producto</th>
                    <th>Categoria</th>
                    <th>Rareza</th>
                    <th>Precio</th>
                    <th>Stock</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $row): ?>
                    <tr>
                        <td><?= (int) $row['id'] ?></td>
                        <td><?= e($row['name']) ?></td>
                        <td><?= e($row['category']) ?></td>
                        <td><?= e($row['rarity']) ?></td>
                        <td><?= formatPrice((float) $row['price']) ?></td>
                        <td><?= (int) $row['stock'] ?></td>
                        <td>
                            <div class="table-actions">
                                <a class="button ghost" href="products.php?edit=<?= (int) $row['id'] ?>">Editar</a>
                                <form method="post">
                                    <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
                                    <button class="button danger" type="submit" onclick="return confirm('Eliminar este producto?')">Eliminar</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
