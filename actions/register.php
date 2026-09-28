<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/pages/sign-up.php');
}
csrf_verify();

$name     = trim($_POST['name'] ?? '');
$email    = trim($_POST['email'] ?? '');
$password = (string) ($_POST['password'] ?? '');
$confirm  = (string) ($_POST['confirm_password'] ?? '');

if ($name === '' || $email === '' || $password === '') {
    flash('error', 'Preencha todos os campos.');
    redirect('/pages/sign-up.php');
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    flash('error', 'Informe um e-mail válido.');
    redirect('/pages/sign-up.php');
}
if (strlen($password) < 8) {
    flash('error', 'A senha deve ter pelo menos 8 caracteres.');
    redirect('/pages/sign-up.php');
}
if ($password !== $confirm) {
    flash('error', 'As senhas não coincidem.');
    redirect('/pages/sign-up.php');
}

$stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
$stmt->execute([$email]);
if ($stmt->fetch()) {
    flash('error', 'Já existe uma conta com este e-mail.');
    redirect('/pages/sign-up.php');
}

$stmt = $pdo->prepare('INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)');
$stmt->execute([$name, $email, password_hash($password, PASSWORD_BCRYPT), 'customer']);

$_SESSION['user_id'] = (int) $pdo->lastInsertId();
session_regenerate_id(true);

flash('success', 'Conta criada com sucesso. Bem-vindo!');
redirect('/pages/dashboard.php');
