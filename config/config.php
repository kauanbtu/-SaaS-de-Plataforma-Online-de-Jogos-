<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('BASE_PATH', dirname(__DIR__));

/**
 * Carrega variáveis do .env sem depender de nenhuma lib externa
 * (facilita instalação em hospedagem compartilhada, sem composer).
 */
function env(string $key, ?string $default = null): ?string
{
    static $vars = null;

    if ($vars === null) {
        $vars = [];
        $envFile = BASE_PATH . '/.env';
        if (is_file($envFile)) {
            foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
                $line = trim($line);
                if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
                    continue;
                }
                [$k, $v] = explode('=', $line, 2);
                $vars[trim($k)] = trim($v);
            }
        }
    }

    return $vars[$key] ?? getenv($key) ?: $default;
}

$dbHost = env('DB_HOST', '127.0.0.1');
$dbPort = env('DB_PORT', '3306');
$dbName = env('DB_DATABASE', 'gamers_arena');
$dbUser = env('DB_USERNAME', 'root');
$dbPass = env('DB_PASSWORD', '');

$dsn = "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    die(
        '<div style="font-family:sans-serif;max-width:640px;margin:60px auto;padding:24px;' .
        'background:#fee;border:1px solid #f99;border-radius:8px">' .
        '<h2>Não foi possível conectar ao banco de dados</h2>' .
        '<p>Verifique as credenciais em <code>.env</code> e se o schema foi importado ' .
        '(<code>sql/schema.sql</code>). Veja <code>docs/INSTALACAO.md</code>.</p>' .
        '<p><small>' . htmlspecialchars($e->getMessage()) . '</small></p></div>'
    );
}
