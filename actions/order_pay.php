<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/stripe.php';

$user = require_login('/pages/sign-in.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/pages/my-orders.php');
}
csrf_verify();

$orderId = (int) ($_POST['order_id'] ?? 0);
$stmt = $pdo->prepare(
    'SELECT o.*, l.title FROM orders o JOIN listings l ON l.id = o.listing_id WHERE o.id = ? AND o.buyer_id = ?'
);
$stmt->execute([$orderId, $user['id']]);
$order = $stmt->fetch();

if (!$order || $order['status'] !== 'awaiting_payment') {
    flash('error', 'Pedido não encontrado ou já pago.');
    redirect('/pages/my-orders.php');
}

if (stripe_configured()) {
    $successUrl = base_url('actions/order_confirm.php') . '?order_id=' . $orderId;
    $cancelUrl  = base_url('pages/my-orders.php');

    try {
        $session = stripe_create_checkout_session($order['title'], (float) $order['amount'], $successUrl, $cancelUrl);
    } catch (Throwable $e) {
        $session = null;
    }

    if ($session) {
        $pdo->prepare('UPDATE orders SET stripe_session_id = ? WHERE id = ?')->execute([$session['id'], $orderId]);
        redirect($session['url']);
    }

    flash('error', 'Não foi possível iniciar o pagamento com a Stripe.');
    redirect('/pages/my-orders.php');
}

if (setting('demo_mode', '0') !== '1') {
    flash('error', 'Pagamentos ainda não configurados neste site. Contate o suporte.');
    redirect('/pages/my-orders.php');
}

// Modo demonstração — precisa ser ativado manualmente em /admin/settings.php.
$pdo->beginTransaction();
$pdo->prepare('UPDATE orders SET status = "delivered", payment_ref = "demo" WHERE id = ?')->execute([$orderId]);
$pdo->prepare('UPDATE users SET balance = balance + ? WHERE id = ?')->execute([$order['amount'], $order['seller_id']]);
$pdo->commit();

flash('success', 'Pagamento confirmado (modo demonstração). Combine a entrega com o vendedor pelo chat.');
redirect('/pages/my-orders.php');
