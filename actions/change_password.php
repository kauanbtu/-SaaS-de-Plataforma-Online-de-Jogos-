<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';

$user = require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/pages/change-password.php');
}
csrf_verify();

$current = (string) ($_POST['current_password'] ?? '');
$new     = (string) ($_POST['new_password'] ?? '');
$confirm = (string) ($_POST['new_password_confirm'] ?? '');

$stmt = $pdo->prepare('SELECT password_hash FROM users WHERE id = ?');
$stmt->execute([$user['id']]);
$hash = $stmt->fetchColumn();

if (!$hash || !password_verify($current, $hash)) {
    flash('error', 'Senha atual incorreta.');
    redirect('/pages/change-password.php');
}
if (strlen($new) < 8) {
    flash('error', 'A nova senha deve ter pelo menos 8 caracteres.');
    redirect('/pages/change-password.php');
}
if ($new !== $confirm) {
    flash('error', 'A confirmação de senha não confere.');
    redirect('/pages/change-password.php');
}
if ($new === $current) {
    flash('error', 'A nova senha deve ser diferente da atual.');
    redirect('/pages/change-password.php');
}

$pdo->prepare('UPDATE users SET password_hash = ?, must_change_password = 0 WHERE id = ?')
    ->execute([password_hash($new, PASSWORD_BCRYPT), $user['id']]);

session_regenerate_id(true);
flash('success', 'Senha alterada com sucesso.');
redirect($user['role'] === 'admin' ? '/admin/index.php' : '/pages/dashboard.php');
