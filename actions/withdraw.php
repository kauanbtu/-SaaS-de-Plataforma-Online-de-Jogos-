<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';

$user = require_login('/pages/sign-in.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/pages/payout.php');
}
csrf_verify();

$amount     = (float) ($_POST['amount'] ?? 0);
$method     = trim($_POST['method'] ?? '');
$accountRef = trim($_POST['account_ref'] ?? '');

if ($amount <= 0 || $method === '' || $accountRef === '') {
    flash('error', 'Preencha valor, método e conta para receber o saque.');
    redirect('/pages/payout.php');
}
if (bccomp((string) $amount, (string) $user['balance'], 2) === 1) {
    flash('error', 'Valor solicitado maior que o saldo disponível.');
    redirect('/pages/payout.php');
}

$pdo->beginTransaction();
$stmt = $pdo->prepare(
    'INSERT INTO withdrawals (user_id, amount, method, account_ref, status) VALUES (?, ?, ?, ?, "pending")'
);
$stmt->execute([$user['id'], $amount, $method, $accountRef]);
$newBalance = bcsub((string) $user['balance'], (string) $amount, 2);
$pdo->prepare('UPDATE users SET balance = ? WHERE id = ?')->execute([$newBalance, $user['id']]);
$pdo->commit();

flash('success', 'Solicitação de saque enviada e está pendente de aprovação.');
redirect('/pages/payout.php');
