<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/stripe.php';
require_once __DIR__ . '/../includes/delivery.php';

$purchaseId = (int) ($_GET['purchase_id'] ?? 0);
$sessionId  = $_GET['session_id'] ?? '';

$stmt = $pdo->prepare('SELECT * FROM purchases WHERE id = ?');
$stmt->execute([$purchaseId]);
$purchase = $stmt->fetch();

if (!$purchase) {
    flash('error', 'Compra não encontrada.');
    redirect('/index.php');
}

if ($purchase['status'] === 'pending' && $sessionId !== '') {
    try {
        $session = stripe_get_checkout_session($sessionId);
    } catch (Throwable $e) {
        $session = [];
    }

    if (($session['payment_status'] ?? '') === 'paid') {
        $pdo->prepare('UPDATE purchases SET status = "paid", payment_ref = ? WHERE id = ?')
            ->execute([$sessionId, $purchaseId]);
        deliver_stock_code($pdo, (int) $purchase['package_id'], $purchaseId, $purchase['user_id']);
    } else {
        flash('error', 'Pagamento ainda não confirmado pela Stripe.');
        redirect('/index.php');
    }
}

redirect('/actions/purchase_receipt.php?id=' . $purchaseId);
