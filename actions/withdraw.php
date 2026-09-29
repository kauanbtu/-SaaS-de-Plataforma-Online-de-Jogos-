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

// Débito condicional e atômico: só é aplicado se o saldo no banco (não o
// valor da sessão, que pode estar desatualizado) ainda cobrir o saque
// no exato instante do UPDATE — impede que duas solicitações simultâneas
// consigam sacar em dobro o mesmo saldo (rowCount() = 0 quando não cobre).
$pdo->beginTransaction();
$stmt = $pdo->prepare('UPDATE users SET balance = balance - ? WHERE id = ? AND balance >= ?');
$stmt->execute([$amount, $user['id'], $amount]);

if ($stmt->rowCount() === 0) {
    $pdo->rollBack();
    flash('error', 'Valor solicitado maior que o saldo disponível.');
    redirect('/pages/payout.php');
}

$pdo->prepare('INSERT INTO withdrawals (user_id, amount, method, account_ref, status) VALUES (?, ?, ?, ?, "pending")')
    ->execute([$user['id'], $amount, $method, $accountRef]);
$pdo->commit();

flash('success', 'Solicitação de saque enviada e está pendente de aprovação.');
redirect('/pages/payout.php');
