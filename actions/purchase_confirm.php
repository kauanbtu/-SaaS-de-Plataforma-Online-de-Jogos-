<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/payments.php';

$user = require_login('/pages/sign-in.php');

$purchaseId = (int) ($_GET['purchase_id'] ?? 0);

$stmt = $pdo->prepare('SELECT * FROM purchases WHERE id = ? AND user_id = ?');
$stmt->execute([$purchaseId, $user['id']]);
$purchase = $stmt->fetch();

if (!$purchase) {
    flash('error', 'Compra não encontrada.');
    redirect('/index.php');
}

if ($purchase['status'] === 'pending') {
    $result = finalize_purchase_payment($pdo, $purchase);
    if (!$result['ok']) {
        flash('error', $result['message']);
        redirect('/index.php');
    }
}

redirect('/actions/purchase_receipt.php?id=' . $purchaseId);
