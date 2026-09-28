<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/stripe.php';

$user = require_login('/pages/sign-in.php');

$orderId   = (int) ($_GET['order_id'] ?? 0);
$sessionId = $_GET['session_id'] ?? '';

$stmt = $pdo->prepare('SELECT * FROM orders WHERE id = ? AND buyer_id = ?');
$stmt->execute([$orderId, $user['id']]);
$order = $stmt->fetch();

if (!$order) {
    flash('error', 'Pedido não encontrado.');
    redirect('/pages/my-orders.php');
}

if ($order['status'] === 'awaiting_payment' && $sessionId !== '') {
    try {
        $session = stripe_get_checkout_session($sessionId);
    } catch (Throwable $e) {
        $session = [];
    }

    if (($session['payment_status'] ?? '') === 'paid') {
        $pdo->beginTransaction();
        $pdo->prepare('UPDATE orders SET status = "delivered" WHERE id = ?')->execute([$orderId]);
        $pdo->prepare('UPDATE users SET balance = balance + ? WHERE id = ?')->execute([$order['amount'], $order['seller_id']]);
        $pdo->commit();
        flash('success', 'Pagamento confirmado! Combine a entrega com o vendedor pelo chat.');
    } else {
        flash('error', 'Pagamento ainda não confirmado pela Stripe.');
    }
}

redirect('/pages/my-orders.php');
