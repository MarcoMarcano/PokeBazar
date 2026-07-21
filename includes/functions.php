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

function requestExpectsJson(): bool
{
    $accept = strtolower((string) ($_SERVER['HTTP_ACCEPT'] ?? ''));
    $requestedWith = strtolower((string) ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? ''));

    return str_contains($accept, 'application/json') || $requestedWith === 'xmlhttprequest';
}

function jsonResponse(array $payload, int $statusCode = 200): void
{
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
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

function getExchangeRate(float $fallback = 36.0): float
{
    $cachePath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'exchange_rate.json';
    $cacheDir = dirname($cachePath);

    if (!is_dir($cacheDir)) {
        @mkdir($cacheDir, 0777, true);
    }

    $apiUrl = getenv('EXCHANGE_API_URL') ?: 'https://ve.dolarapi.com/v1/dolares/oficial';

    if (is_file($cachePath)) {
        $cached = @json_decode((string) file_get_contents($cachePath), true);
        if (is_array($cached) && isset($cached['rate'], $cached['fetched_at'])) {
            $age = time() - (int) $cached['fetched_at'];
            $sameSource = ($cached['source'] ?? '') === $apiUrl;
            if ($age < 3600 && $sameSource) {
                return (float) $cached['rate'];
            }
        }
    }
    $context = stream_context_create([
        'http' => [
            'timeout' => 5,
            'ignore_errors' => true,
        ],
    ]);

    $payload = @file_get_contents($apiUrl, false, $context);
    if (is_string($payload) && $payload !== '') {
        $decoded = json_decode($payload, true);
        $rate = $decoded['promedio'] ?? $decoded['venta'] ?? $decoded['compra'] ?? $decoded['rate'] ?? $decoded['price'] ?? $decoded['dolar']['price'] ?? null;

        if (is_numeric($rate)) {
            $rateValue = (float) $rate;
            @file_put_contents($cachePath, json_encode([
                'rate' => $rateValue,
                'fetched_at' => time(),
                'source' => $apiUrl,
            ], JSON_UNESCAPED_UNICODE));

            return $rateValue;
        }
    }

    return $fallback;
}

function formatPriceBs(float $price, ?float $rate = null): string
{
    $rateValue = $rate ?? getExchangeRate();

    return 'Bs ' . number_format($price * $rateValue, 2, '.', ',');
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
