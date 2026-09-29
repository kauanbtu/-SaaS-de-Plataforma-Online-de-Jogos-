<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/payments.php';

/**
 * Endpoint de webhook da Stripe (evento checkout.session.completed).
 *
 * Garante a confirmação do pagamento mesmo se o comprador fechar a aba
 * antes de voltar ao site — sem isso, o pedido ficava "pending" para
 * sempre e o cliente nunca recebia o produto. Configure em
 * dashboard.stripe.com/webhooks apontando para esta URL e cole o
 * "signing secret" em /admin/settings.php (ou STRIPE_WEBHOOK_SECRET no .env).
 */

$payload   = file_get_contents('php://input') ?: '';
$sigHeader = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';
$secret    = stripe_webhook_secret();

if (!stripe_verify_webhook_signature($payload, $sigHeader, $secret)) {
    http_response_code(401);
    exit('Assinatura inválida.');
}

$event = json_decode($payload, true);
if (!is_array($event) || ($event['type'] ?? '') !== 'checkout.session.completed') {
    http_response_code(200);
    exit('ok');
}

$sessionId = $event['data']['object']['id'] ?? null;
if (!is_string($sessionId) || $sessionId === '') {
    http_response_code(200);
    exit('ok');
}

$stmt = $pdo->prepare('SELECT * FROM purchases WHERE stripe_session_id = ?');
$stmt->execute([$sessionId]);
$purchase = $stmt->fetch();
if ($purchase) {
    finalize_purchase_payment($pdo, $purchase);
    http_response_code(200);
    exit('ok');
}

$stmt = $pdo->prepare('SELECT * FROM orders WHERE stripe_session_id = ?');
$stmt->execute([$sessionId]);
$order = $stmt->fetch();
if ($order) {
    finalize_order_payment($pdo, $order);
    http_response_code(200);
    exit('ok');
}

http_response_code(200);
exit('ok');
