<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';

requireLogin();

$user = currentUser();
$pageTitle = 'Mi perfil';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        setFlash('danger', 'Token invalido.');
        redirectTo('profile.php');
    }

    $action = $_POST['action'] ?? '';

    if ($action === 'update_profile') {
        $firstName = trim($_POST['first_name'] ?? '');
        $lastName = trim($_POST['last_name'] ?? '');
        $email = trim($_POST['email'] ?? '');

        $stmt = db()->prepare('UPDATE users SET first_name = :first_name, last_name = :last_name, email = :email WHERE id = :id');
        $stmt->execute([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'id' => $user['id'],
        ]);

        setFlash('success', 'Perfil actualizado.');
        redirectTo('profile.php');
    }

    if ($action === 'change_password') {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        $passwordStmt = db()->prepare('SELECT password FROM users WHERE id = :id LIMIT 1');
        $passwordStmt->execute(['id' => $user['id']]);
        $passwordHash = (string) $passwordStmt->fetchColumn();

        if (!passwordMatches($currentPassword, $passwordHash)) {
            setFlash('danger', 'La contrasena actual no es correcta.');
            redirectTo('profile.php');
        }

        if ($newPassword !== $confirmPassword) {
            setFlash('danger', 'La nueva contrasena y su confirmacion no coinciden.');
            redirectTo('profile.php');
        }

        $updatePassword = db()->prepare('UPDATE users SET password = :password WHERE id = :id');
        $updatePassword->execute([
            'password' => password_hash($newPassword, PASSWORD_DEFAULT),
            'id' => $user['id'],
        ]);

        setFlash('success', 'Contrasena actualizada.');
        redirectTo('profile.php');
    }
}

$user = currentUser();
require_once __DIR__ . '/includes/header.php';
?>
<section class="panel split-panel">
    <div>
        <p class="eyebrow">Perfil de usuario</p>
        <h1><?= e($user['first_name'] . ' ' . $user['last_name']) ?></h1>
        <p class="muted">Rol actual: <strong><?= e($user['role'] === 'admin' ? 'Administrador' : 'Usuario comun') ?></strong></p>
        <p>Desde aqui puedes editar tu informacion personal y cambiar la contrasena de tu cuenta.</p>
    </div>
    <div class="stat-card">
        <span>Correo</span>
        <strong><?= e($user['email']) ?></strong>
        <small>Miembro desde <?= e(date('d/m/Y', strtotime((string) $user['created_at']))) ?></small>
    </div>
</section>

<section class="dashboard-grid">
    <form method="post" class="panel stack-form">
        <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
        <input type="hidden" name="action" value="update_profile">
        <h2>Editar datos</h2>
        <div class="field-row">
            <div class="field">
                <label for="first_name">Nombre</label>
                <input id="first_name" name="first_name" type="text" required value="<?= e($user['first_name']) ?>">
            </div>
            <div class="field">
                <label for="last_name">Apellido</label>
                <input id="last_name" name="last_name" type="text" required value="<?= e($user['last_name']) ?>">
            </div>
        </div>
        <div class="field">
            <label for="email">Correo</label>
            <input id="email" name="email" type="email" required value="<?= e($user['email']) ?>">
        </div>
        <button class="button primary" type="submit">Guardar cambios</button>
    </form>

    <form method="post" class="panel stack-form">
        <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
        <input type="hidden" name="action" value="change_password">
        <h2>Cambiar contrasena</h2>
        <div class="field">
            <label for="current_password">Contrasena actual</label>
            <input id="current_password" name="current_password" type="password" required>
        </div>
        <div class="field-row">
            <div class="field">
                <label for="new_password">Nueva contrasena</label>
                <input id="new_password" name="new_password" type="password" required minlength="6">
            </div>
            <div class="field">
                <label for="confirm_password">Confirmar nueva contrasena</label>
                <input id="confirm_password" name="confirm_password" type="password" required minlength="6">
            </div>
        </div>
        <button class="button secondary" type="submit">Actualizar contrasena</button>
    </form>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
