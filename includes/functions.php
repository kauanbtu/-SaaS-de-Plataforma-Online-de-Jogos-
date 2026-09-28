<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';

function setting(string $key, string $default = ''): string
{
    global $pdo;
    static $cache = null;

    if ($cache === null) {
        $cache = [];
        $stmt = $pdo->query('SELECT `key`, `value` FROM settings');
        foreach ($stmt->fetchAll() as $row) {
            $cache[$row['key']] = $row['value'];
        }
    }

    $value = $cache[$key] ?? '';
    return $value !== '' ? $value : $default;
}

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function money(float $value): string
{
    return number_format($value, 2, '.', ',');
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function csrf_verify(): void
{
    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(419);
        die('Sessão expirada. Volte e tente novamente.');
    }
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function flashes_render(): string
{
    if (empty($_SESSION['flash'])) {
        return '';
    }
    $html = '';
    foreach ($_SESSION['flash'] as $f) {
        $cls = $f['type'] === 'error' ? 'rejected' : 'accepted';
        $html .= '<div class="status ' . $cls . '" style="display:block;margin-bottom:12px;padding:12px 16px;font-size:.85rem">'
            . e($f['message']) . '</div>';
    }
    unset($_SESSION['flash']);
    return $html;
}

function redirect(string $path): never
{
    header('Location: ' . $path);
    exit;
}

function base_url(string $path = ''): string
{
    $base = rtrim(env('APP_URL', ''), '/');
    return $base . '/' . ltrim($path, '/');
}
