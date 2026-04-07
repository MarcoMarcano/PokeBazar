<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';

if (isLoggedIn()) {
    redirectTo('index.php');
}

$pageTitle = 'Iniciar sesion';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        setFlash('danger', 'Token invalido. Intenta nuevamente.');
        redirectTo('login.php');
    }

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = db()->prepare('SELECT id, password FROM users WHERE email = :email LIMIT 1');
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();

    if ($user && passwordMatches($password, $user['password'])) {
        if (needsPasswordRehashOrMigration($user['password'])) {
            $rehashStmt = db()->prepare('UPDATE users SET password = :password WHERE id = :id');
            $rehashStmt->execute([
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'id' => $user['id'],
            ]);
        }

        $_SESSION['user_id'] = (int) $user['id'];
        setFlash('success', 'Sesion iniciada correctamente.');
        redirectTo('index.php');
    }

    setFlash('danger', 'Correo o contrasena incorrectos.');
    redirectTo('login.php');
}

require_once __DIR__ . '/includes/header.php';
?>
<section class="auth-layout">
    <article class="auth-panel">
        <p class="eyebrow">Acceso de usuario</p>
        <h1>Bienvenido de vuelta a PokeBazar.</h1>
        <p>Inicia sesion para agregar cartas al carrito, revisar tu perfil y generar facturas.</p>
    </article>
    <form class="panel auth-form" method="post">
        <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
        <div class="field">
            <label for="email">Correo</label>
            <input id="email" name="email" type="email" required value="<?= e((string) old('email')) ?>">
        </div>
        <div class="field">
            <label for="password">Contrasena</label>
            <input id="password" name="password" type="password" required>
        </div>
        <button class="button primary full" type="submit">Entrar</button>
        <p class="muted">Si aun no tienes cuenta, crea una cuenta de usuario normal desde el registro.</p>
    </form>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
