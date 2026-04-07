<?php

declare(strict_types=1);

session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';

function currentUser(): ?array
{
    if (!isset($_SESSION['user_id'])) {
        return null;
    }

    static $user = null;

    if ($user !== null) {
        return $user;
    }

    $stmt = db()->prepare('SELECT id, first_name, last_name, email, role, created_at FROM users WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $_SESSION['user_id']]);
    $user = $stmt->fetch();

    if (!$user) {
        unset($_SESSION['user_id']);
        return null;
    }

    return $user;
}

function isLoggedIn(): bool
{
    return currentUser() !== null;
}

function isAdmin(): bool
{
    $user = currentUser();
    return $user !== null && $user['role'] === 'admin';
}

function requireLogin(): void
{
    if (!isLoggedIn()) {
        setFlash('warning', 'Debes iniciar sesion para continuar.');
        redirectTo('login.php');
    }
}

function requireAdmin(): void
{
    if (!isAdmin()) {
        setFlash('danger', 'No tienes permisos para acceder a esa seccion.');
        redirectTo('index.php');
    }
}
