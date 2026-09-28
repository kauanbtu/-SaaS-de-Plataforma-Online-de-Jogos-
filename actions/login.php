<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/pages/sign-in.php');
}
csrf_verify();

$email    = trim($_POST['email'] ?? '');
$password = (string) ($_POST['password'] ?? '');

$user = attempt_login($email, $password);

if (!$user) {
    flash('error', 'E-mail ou senha inválidos.');
    redirect('/pages/sign-in.php');
}

flash('success', 'Login realizado com sucesso.');
redirect($user['role'] === 'admin' ? '/admin/index.php' : '/pages/dashboard.php');
