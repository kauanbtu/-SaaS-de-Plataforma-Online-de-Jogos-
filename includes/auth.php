<?php
declare(strict_types=1);

require_once __DIR__ . '/functions.php';

function current_user(): ?array
{
    global $pdo;
    static $user = false;

    if ($user === false) {
        $user = null;
        if (!empty($_SESSION['user_id'])) {
            $stmt = $pdo->prepare('SELECT id, name, email, role, balance, must_change_password FROM users WHERE id = ?');
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch() ?: null;
        }
    }

    return $user;
}

/**
 * Enquanto must_change_password estiver ativo (caso da senha padrão
 * documentada em docs/INSTALACAO.md), bloqueia qualquer página protegida e
 * redireciona para a troca de senha — a senha publicada na documentação
 * nunca fica valendo além do primeiro login.
 *
 * @param array<string, mixed> $user
 */
function enforce_password_policy(array $user): void
{
    if (empty($user['must_change_password'])) {
        return;
    }

    $script = $_SERVER['SCRIPT_NAME'] ?? '';
    $allowed = ['/pages/change-password.php', '/actions/change_password.php', '/actions/logout.php'];
    foreach ($allowed as $path) {
        if (str_ends_with($script, $path)) {
            return;
        }
    }

    flash('error', 'Por segurança, defina uma nova senha antes de continuar.');
    redirect('/pages/change-password.php');
}

function require_login(string $redirectTo = '/pages/sign-in.php'): array
{
    $user = current_user();
    if (!$user) {
        flash('error', 'Você precisa entrar na sua conta para continuar.');
        redirect($redirectTo);
    }
    enforce_password_policy($user);
    return $user;
}

function require_admin(): array
{
    $user = require_login('/admin/login.php');
    if ($user['role'] !== 'admin') {
        http_response_code(403);
        die('Acesso restrito ao administrador.');
    }
    return $user;
}

function attempt_login(string $email, string $password): ?array
{
    global $pdo;

    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        session_regenerate_id(true);
        unset($user['password_hash']);
        return $user;
    }

    return null;
}

function logout_user(): void
{
    unset($_SESSION['user_id']);
    session_regenerate_id(true);
}
