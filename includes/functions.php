<?php

declare(strict_types=1);

function baseUrl(string $path = ''): string
{
    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $prefix = str_contains($scriptName, '/admin/') ? '../' : '';

    return $prefix . ltrim($path, '/');
}

function assetUrl(string $path): string
{
    if (preg_match('/^https?:\/\//i', $path) === 1) {
        return $path;
    }

    $segments = array_map(
        static fn (string $segment): string => rawurlencode($segment),
        explode('/', ltrim(str_replace('\\', '/', $path), '/'))
    );

    return baseUrl(implode('/', $segments));
}

function assetVersionedUrl(string $path): string
{
    $url = assetUrl($path);

    if (preg_match('/^https?:\/\//i', $path) === 1) {
        return $url;
    }

    $absolutePath = dirname(__DIR__) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, ltrim($path, '/'));

    if (!file_exists($absolutePath)) {
        return $url;
    }

    return $url . '?v=' . filemtime($absolutePath);
}

function redirectTo(string $path): void
{
    header('Location: ' . baseUrl($path));
    exit;
}

function setFlash(string $type, string $message): void
{
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message,
    ];
}

function getFlash(): ?array
{
    if (!isset($_SESSION['flash'])) {
        return null;
    }

    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);

    return $flash;
}

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function formatPrice(float $price): string
{
    return '$' . number_format($price, 2, '.', ',');
}

function cartCount(int $userId): int
{
    $stmt = db()->prepare('SELECT COALESCE(SUM(quantity), 0) FROM cart_items WHERE user_id = :user_id AND status = :status');
    $stmt->execute([
        'user_id' => $userId,
        'status' => 'active',
    ]);

    return (int) $stmt->fetchColumn();
}

function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function verifyCsrfToken(?string $token): bool
{
    return isset($_SESSION['csrf_token']) && is_string($token) && hash_equals($_SESSION['csrf_token'], $token);
}

function old(string $key, mixed $default = ''): mixed
{
    return $_POST[$key] ?? $default;
}

function passwordMatches(string $plainPassword, string $storedPassword): bool
{
    $info = password_get_info($storedPassword);

    if (!empty($info['algo'])) {
        return password_verify($plainPassword, $storedPassword);
    }

    return hash_equals($storedPassword, $plainPassword);
}

function needsPasswordRehashOrMigration(string $storedPassword): bool
{
    $info = password_get_info($storedPassword);

    if (empty($info['algo'])) {
        return true;
    }

    return password_needs_rehash($storedPassword, PASSWORD_DEFAULT);
}
