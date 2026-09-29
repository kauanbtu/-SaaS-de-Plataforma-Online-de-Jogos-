<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/payments.php';

$user = require_login('/pages/sign-in.php');

$orderId = (int) ($_GET['order_id'] ?? 0);

$stmt = $pdo->prepare('SELECT * FROM orders WHERE id = ? AND buyer_id = ?');
$stmt->execute([$orderId, $user['id']]);
$order = $stmt->fetch();

if (!$order) {
    flash('error', 'Pedido não encontrado.');
    redirect('/pages/my-orders.php');
}

if ($order['status'] === 'awaiting_payment') {
    $result = finalize_order_payment($pdo, $order);
    flash(
        $result['ok'] ? 'success' : 'error',
        $result['ok'] ? 'Pagamento confirmado! Combine a entrega com o vendedor pelo chat.' : $result['message']
    );
}

redirect('/pages/my-orders.php');
