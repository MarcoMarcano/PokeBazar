<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';

requireAdmin();

$pageTitle = 'Administrar usuarios';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        setFlash('danger', 'Token invalido.');
        redirectTo('admin/users.php');
    }

    $action = $_POST['action'] ?? '';

    if ($action === 'save') {
        $id = (int) ($_POST['id'] ?? 0);
        $role = $_POST['role'] === 'admin' ? 'admin' : 'user';
        $payload = [
            'first_name' => trim($_POST['first_name'] ?? ''),
            'last_name' => trim($_POST['last_name'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'role' => $role,
        ];

        if ($id > 0) {
            $stmt = db()->prepare('UPDATE users SET first_name = :first_name, last_name = :last_name, email = :email, role = :role WHERE id = :id');
            $payload['id'] = $id;
            $stmt->execute($payload);
            if (!empty($_POST['password'])) {
                $passwordStmt = db()->prepare('UPDATE users SET password = :password WHERE id = :id');
                $passwordStmt->execute([
                    'password' => password_hash((string) $_POST['password'], PASSWORD_DEFAULT),
                    'id' => $id,
                ]);
            }
            setFlash('success', 'Usuario actualizado.');
        } else {
            $stmt = db()->prepare('INSERT INTO users (first_name, last_name, email, password, role) VALUES (:first_name, :last_name, :email, :password, :role)');
            $stmt->execute([
                'first_name' => $payload['first_name'],
                'last_name' => $payload['last_name'],
                'email' => $payload['email'],
                'password' => password_hash((string) ($_POST['password'] ?: '123456'), PASSWORD_DEFAULT),
                'role' => $payload['role'],
            ]);
            setFlash('success', 'Usuario creado.');
        }

        redirectTo('admin/users.php');
    }

    if ($action === 'delete') {
        $targetId = (int) ($_POST['id'] ?? 0);
        if ($targetId === (int) currentUser()['id']) {
            setFlash('warning', 'No puedes eliminar tu propia cuenta desde aqui.');
            redirectTo('admin/users.php');
        }

        $stmt = db()->prepare('DELETE FROM users WHERE id = :id');
        $stmt->execute(['id' => $targetId]);
        setFlash('success', 'Usuario eliminado.');
        redirectTo('admin/users.php');
    }
}

$editingId = (int) ($_GET['edit'] ?? 0);
$userForm = [
    'id' => 0,
    'first_name' => '',
    'last_name' => '',
    'email' => '',
    'role' => 'user',
];

if ($editingId > 0) {
    $stmt = db()->prepare('SELECT id, first_name, last_name, email, role FROM users WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $editingId]);
    $row = $stmt->fetch();
    if ($row) {
        $userForm = $row;
    }
}

$users = db()->query('SELECT id, first_name, last_name, email, role, created_at FROM users ORDER BY id DESC')->fetchAll();
require_once __DIR__ . '/../includes/header.php';
?>
<section class="dashboard-grid admin-grid">
    <form method="post" class="panel stack-form">
        <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="id" value="<?= (int) $userForm['id'] ?>">
        <h1><?= $userForm['id'] ? 'Editar usuario' : 'Nuevo usuario' ?></h1>
        <div class="field-row">
            <div class="field">
                <label for="first_name">Nombre</label>
                <input id="first_name" name="first_name" type="text" required value="<?= e((string) $userForm['first_name']) ?>">
            </div>
            <div class="field">
                <label for="last_name">Apellido</label>
                <input id="last_name" name="last_name" type="text" required value="<?= e((string) $userForm['last_name']) ?>">
            </div>
        </div>
        <div class="field">
            <label for="email">Correo</label>
            <input id="email" name="email" type="email" required value="<?= e((string) $userForm['email']) ?>">
        </div>
        <div class="field-row">
            <div class="field">
                <label for="password">Contrasena <?= $userForm['id'] ? '(opcional)' : '' ?></label>
                <input id="password" name="password" type="password" <?= $userForm['id'] ? '' : 'required' ?>>
            </div>
            <div class="field">
                <label for="role">Rol</label>
                <select id="role" name="role">
                    <option value="user" <?= $userForm['role'] === 'user' ? 'selected' : '' ?>>Usuario</option>
                    <option value="admin" <?= $userForm['role'] === 'admin' ? 'selected' : '' ?>>Administrador</option>
                </select>
            </div>
        </div>
        <button class="button primary" type="submit">Guardar usuario</button>
    </form>

    <section class="panel">
        <h2>Usuarios registrados</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Correo</th>
                    <th>Rol</th>
                    <th>Fecha</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $row): ?>
                    <tr>
                        <td><?= (int) $row['id'] ?></td>
                        <td><?= e($row['first_name'] . ' ' . $row['last_name']) ?></td>
                        <td><?= e($row['email']) ?></td>
                        <td><?= e($row['role'] === 'admin' ? 'Administrador' : 'Usuario') ?></td>
                        <td><?= e(date('d/m/Y', strtotime((string) $row['created_at']))) ?></td>
                        <td>
                            <div class="table-actions">
                                <a class="button ghost" href="users.php?edit=<?= (int) $row['id'] ?>">Editar</a>
                                <form method="post">
                                    <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
                                    <button class="button danger" type="submit" onclick="return confirm('Eliminar este usuario?')">Eliminar</button>
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
