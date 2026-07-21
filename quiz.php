<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';

requireLogin();

$user = currentUser();


db()->exec(
    'CREATE TABLE IF NOT EXISTS anti_scalper_blocks (
        user_id INT PRIMARY KEY,
        blocked_until DATETIME NULL,
        CONSTRAINT fk_block_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )'
);


$blockStmt = db()->prepare('SELECT blocked_until FROM anti_scalper_blocks WHERE user_id = :user_id LIMIT 1');
$blockStmt->execute(['user_id' => $user['id']]);
$block = $blockStmt->fetch();

if ($block && $block['blocked_until'] !== null && strtotime($block['blocked_until']) > time()) {
    $wait = strtotime($block['blocked_until']) - time();
    $minutes = ceil($wait / 60);
    setFlash('danger', "Respuesta incorrecta anteriormente. Espera $minutes minutos antes de intentar nuevamente.");
    redirectTo('cart.php');
}


$QUIZ_QUESTIONS = require __DIR__ . '/includes/quiz_questions.php';

function findQuestion(array $questions, int $id): ?array
{
    foreach ($questions as $q) {
        if ($q['id'] === $id) {
            return $q;
        }
    }
    return null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        setFlash('danger', 'Token CSRF invalido.');
        redirectTo('cart.php');
    }

   
    if (isset($_POST['question_id'], $_POST['answer'])) {
        $qid = (int) $_POST['question_id'];
        $answer = trim((string) $_POST['answer']);
        $question = findQuestion($QUIZ_QUESTIONS, $qid);

        if (!$question) {
            setFlash('danger', 'Pregunta invalida.');
            redirectTo('cart.php');
        }

  
        if (mb_strtolower($answer) === mb_strtolower($question['answer'])) {
            $stmt = db()->prepare(
                'SELECT ci.id, ci.product_id, ci.quantity, p.name, p.price
                 FROM cart_items ci
                 INNER JOIN products p ON p.id = ci.product_id
                 WHERE ci.user_id = :user_id AND ci.status = :status'
            );
            $stmt->execute([
                'user_id' => $user['id'],
                'status' => 'active',
            ]);
            $items = $stmt->fetchAll();

            if (!$items) {
                setFlash('warning', 'No hay productos para facturar.');
                redirectTo('cart.php');
            }

            $exchangeRate = getExchangeRate();
            $total = 0.0;
            $cartReference = 'CART-' . $user['id'] . '-' . date('YmdHis');
            foreach ($items as $item) {
                $total += (float) $item['price'] * (int) $item['quantity'];
            }

            $invoiceStmt = db()->prepare('INSERT INTO invoices (user_id, cart_reference, final_price) VALUES (:user_id, :cart_reference, :final_price)');
            $invoiceStmt->execute([
                'user_id' => $user['id'],
                'cart_reference' => $cartReference,
                'final_price' => $total,
            ]);
            $invoiceId = (int) db()->lastInsertId();

            $itemStmt = db()->prepare('INSERT INTO invoice_items (invoice_id, product_name, unit_price, quantity) VALUES (:invoice_id, :product_name, :unit_price, :quantity)');
            $archiveStmt = db()->prepare('UPDATE cart_items SET status = :status, cart_reference = :cart_reference WHERE id = :id');

            foreach ($items as $item) {
                $itemStmt->execute([
                    'invoice_id' => $invoiceId,
                    'product_name' => $item['name'],
                    'unit_price' => $item['price'],
                    'quantity' => $item['quantity'],
                ]);

                $archiveStmt->execute([
                    'status' => 'completed',
                    'cart_reference' => $cartReference,
                    'id' => $item['id'],
                ]);
            }

            
            $del = db()->prepare('DELETE FROM anti_scalper_blocks WHERE user_id = :user_id');
            $del->execute(['user_id' => $user['id']]);

            
            $invoiceQuery = db()->prepare('SELECT i.id, i.cart_reference, i.final_price, i.created_at, u.first_name, u.last_name, u.email FROM invoices i INNER JOIN users u ON u.id = i.user_id WHERE i.id = :id LIMIT 1');
            $invoiceQuery->execute(['id' => $invoiceId]);
            $invoice = $invoiceQuery->fetch();

            $invoiceItemsQuery = db()->prepare('SELECT product_name, unit_price, quantity FROM invoice_items WHERE invoice_id = :invoice_id ORDER BY id ASC');
            $invoiceItemsQuery->execute(['invoice_id' => $invoiceId]);
            $invoiceItems = $invoiceItemsQuery->fetchAll();

            $pageTitle = 'Factura';
            require_once __DIR__ . '/includes/header.php';
            ?>
            <section class="invoice-printable panel">
                <div class="invoice-watermark" aria-hidden="true">Para uso personal</div>
                <div class="invoice-head">
                    <div>
                        <p class="eyebrow">Factura generada</p>
                        <h1>Factura #<?= (int) $invoice['id'] ?></h1>
                        <p>Referencia de carrito: <?= e($invoice['cart_reference']) ?></p>
                    </div>
                    <button class="button secondary" type="button" onclick="window.print()">Imprimir</button>
                </div>

                <div class="invoice-meta-grid">
                    <div>
                        <span>Cliente</span>
                        <strong><?= e($invoice['first_name'] . ' ' . $invoice['last_name']) ?></strong>
                        <small><?= e($invoice['email']) ?></small>
                    </div>
                    <div>
                        <span>Fecha</span>
                        <strong><?= e(date('d/m/Y H:i', strtotime((string) $invoice['created_at']))) ?></strong>
                        <small>PokeBazar</small>
                    </div>
                </div>

                <table class="table">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Precio unitario</th>
                            <th>Cantidad</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($invoiceItems as $item): ?>
                            <tr>
                                <td><?= e($item['product_name']) ?></td>
                                <td><?= formatPrice((float) $item['unit_price']) ?></td>
                                <td><?= (int) $item['quantity'] ?></td>
                                <td><?= formatPrice((float) $item['unit_price'] * (int) $item['quantity']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="invoice-total">
                    <span>Total final USD</span>
                    <strong><?= formatPrice((float) $invoice['final_price']) ?></strong>
                </div>
                <div class="invoice-total">
                    <span>Total final BS</span>
                    <strong><?= formatPriceBs((float) $invoice['final_price'], $exchangeRate) ?></strong>
                </div>
            </section>
            <?php
            require_once __DIR__ . '/includes/footer.php';
            exit;
        }

        
        $until = date('Y-m-d H:i:s', time() + 30 * 60);
        $upsert = db()->prepare('REPLACE INTO anti_scalper_blocks (user_id, blocked_until) VALUES (:user_id, :blocked_until)');
        $upsert->execute([
            'user_id' => $user['id'],
            'blocked_until' => $until,
        ]);

        setFlash('danger', 'Respuesta incorrecta. Intenta nuevamente en 30 minutos.');
        redirectTo('cart.php');
    }

   
    $rand = $QUIZ_QUESTIONS[array_rand($QUIZ_QUESTIONS)];
    $question = $rand;

    $pageTitle = 'Verificación Pokemon - Paso de compra';
    require_once __DIR__ . '/includes/header.php';
    ?>
    <section class="panel">
        <p class="eyebrow">Verificación anti-scalpers</p>
        <h1>Respuesta una pregunta rápida sobre Pokemon</h1>
        <p>Para completar su compra, responde correctamente esta pregunta. Si fallas, deberás esperar 30 minutos.</p>

        <form method="post" action="quiz.php" class="stack">
            <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
            <input type="hidden" name="question_id" value="<?= (int) $question['id'] ?>">
            <fieldset>
                <legend><?= e($question['question']) ?></legend>
                <?php foreach ($question['options'] as $opt): ?>
                    <label><input type="radio" name="answer" value="<?= e($opt) ?>"> <?= e($opt) ?></label>
                <?php endforeach; ?>
            </fieldset>
            <div>
                <button class="button primary" type="submit">Enviar respuesta</button>
                <a class="button ghost" href="cart.php">Volver al carrito</a>
            </div>
        </form>
    </section>
    <?php
    require_once __DIR__ . '/includes/footer.php';
    exit;
}


redirectTo('cart.php');
