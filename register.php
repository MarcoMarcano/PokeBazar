<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';

if (isLoggedIn()) {
    redirectTo('index.php');
}

$pageTitle = 'Crear cuenta';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        setFlash('danger', 'Token invalido. Intenta nuevamente.');
        redirectTo('register.php');
    }

    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($password !== $confirmPassword) {
        setFlash('danger', 'Las contrasenas no coinciden.');
        redirectTo('register.php');
    }

    $stmt = db()->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
    $stmt->execute(['email' => $email]);
    if ($stmt->fetch()) {
        setFlash('warning', 'Ese correo ya esta registrado.');
        redirectTo('register.php');
    }

    $insert = db()->prepare('INSERT INTO users (first_name, last_name, email, password, role) VALUES (:first_name, :last_name, :email, :password, :role)');
    $insert->execute([
        'first_name' => $firstName,
        'last_name' => $lastName,
        'email' => $email,
        'password' => password_hash($password, PASSWORD_DEFAULT),
        'role' => 'user',
    ]);

    setFlash('success', 'Cuenta creada correctamente. Ahora puedes iniciar sesion.');
    redirectTo('login.php');
}

require_once __DIR__ . '/includes/header.php';
?>
<section class="auth-layout">
    <article class="auth-panel alt">
        <p class="eyebrow">Registro</p>
        <h1>Crea tu cuenta de entrenador coleccionista.</h1>
        <p>Todas las cuentas creadas manualmente se registran como usuario comun. Solo un administrador puede ascender permisos.</p>
    </article>
    <form class="panel auth-form" method="post">
        <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
        <div class="field-row">
            <div class="field">
                <label for="first_name">Nombre</label>
                <input id="first_name" name="first_name" type="text" required value="<?= e((string) old('first_name')) ?>">
            </div>
            <div class="field">
                <label for="last_name">Apellido</label>
                <input id="last_name" name="last_name" type="text" required value="<?= e((string) old('last_name')) ?>">
            </div>
        </div>
        <div class="field">
            <label for="email">Correo</label>
            <input id="email" name="email" type="email" required value="<?= e((string) old('email')) ?>">
        </div>
        <div class="field-row">
            <div class="field">
                <label for="password">Contrasena</label>
                <input id="password" name="password" type="password" required minlength="6">
            </div>
            <div class="field">
                <label for="confirm_password">Confirmar contrasena</label>
                <input id="confirm_password" name="confirm_password" type="password" required minlength="6">
            </div>
        </div>
        <button class="button primary full" type="submit">Crear cuenta</button>
    </form>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
