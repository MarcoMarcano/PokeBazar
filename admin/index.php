<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';

requireAdmin();

$pageTitle = 'Panel administrador';
$totals = [
    'products' => (int) db()->query('SELECT COUNT(*) FROM products')->fetchColumn(),
    'users' => (int) db()->query('SELECT COUNT(*) FROM users')->fetchColumn(),
    'admins' => (int) db()->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn(),
    'invoices' => (int) db()->query('SELECT COUNT(*) FROM invoices')->fetchColumn(),
];

$recentInvoices = db()->query(
    'SELECT i.id, i.final_price, i.created_at, u.first_name, u.last_name
     FROM invoices i
     INNER JOIN users u ON u.id = i.user_id
     ORDER BY i.id DESC LIMIT 5'
)->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>
<section class="panel split-panel">
    <div>
        <p class="eyebrow">Administrador</p>
        <h1>Centro de control de PokeBazar.</h1>
        <p>Desde aquí puedes gestionar el catálogo, usuarios y privilegios administrativos.</p>
    </div>
    <div class="admin-links">
        <a class="button primary" href="products.php">Gestionar productos</a>
        <a class="button secondary" href="users.php">Gestionar usuarios</a>
    </div>
</section>

<section class="stats-grid">
    <article class="stat-card"><span>Productos</span><strong><?= $totals['products'] ?></strong></article>
    <article class="stat-card"><span>Usuarios</span><strong><?= $totals['users'] ?></strong></article>
    <article class="stat-card"><span>Administradores</span><strong><?= $totals['admins'] ?></strong></article>
    <article class="stat-card"><span>Facturas</span><strong><?= $totals['invoices'] ?></strong></article>
</section>

<section class="panel">
    <h2>Facturas recientes</h2>
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Cliente</th>
                <th>Fecha</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($recentInvoices as $invoice): ?>
                <tr>
                    <td>#<?= (int) $invoice['id'] ?></td>
                    <td><?= e($invoice['first_name'] . ' ' . $invoice['last_name']) ?></td>
                    <td><?= e(date('d/m/Y H:i', strtotime((string) $invoice['created_at']))) ?></td>
                    <td><?= formatPrice((float) $invoice['final_price']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
