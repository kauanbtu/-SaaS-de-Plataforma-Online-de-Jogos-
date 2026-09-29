<?php
declare(strict_types=1);

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/stripe.php';
require_once __DIR__ . '/delivery.php';

/**
 * Confirma o pagamento de uma compra (gift card / voucher / top-up) — chamada
 * tanto pelo retorno do navegador (actions/purchase_confirm.php) quanto pelo
 * webhook (actions/stripe_webhook.php), sempre com o mesmo resultado.
 *
 * Nunca confia num session_id vindo de fora: usa exclusivamente o
 * stripe_session_id gravado no momento em que ESTA compra criou sua própria
 * Checkout Session, e só libera a entrega depois de confirmar, direto com a
 * Stripe, que aquela sessão está paga E que o valor pago bate com o valor
 * cobrado. A atualização é condicionada a status = "pending" para que duas
 * chamadas concorrentes (navegador + webhook) nunca entreguem o código duas vezes.
 *
 * @param array<string, mixed> $purchase
 * @return array{ok: bool, message: string}
 */
function finalize_purchase_payment(PDO $pdo, array $purchase): array
{
    if (in_array($purchase['status'], ['paid', 'delivered'], true)) {
        return ['ok' => true, 'message' => 'Pagamento já confirmado.'];
    }

    $sessionId = (string) ($purchase['stripe_session_id'] ?? '');
    if ($sessionId === '') {
        return ['ok' => false, 'message' => 'Pagamento ainda não iniciado.'];
    }

    try {
        $session = stripe_get_checkout_session($sessionId);
    } catch (Throwable $e) {
        return ['ok' => false, 'message' => 'Não foi possível confirmar com a Stripe. Tente novamente em instantes.'];
    }

    $expectedCents = (int) round(((float) $purchase['payable']) * 100);
    $paid = ($session['payment_status'] ?? '') === 'paid'
        && (string) ($session['id'] ?? '') === $sessionId
        && (int) ($session['amount_total'] ?? -1) === $expectedCents
        && strtolower((string) ($session['currency'] ?? '')) === strtolower(setting('currency', 'usd'));

    if (!$paid) {
        return ['ok' => false, 'message' => 'Pagamento ainda não confirmado pela Stripe.'];
    }

    $stmt = $pdo->prepare('UPDATE purchases SET status = "paid", payment_ref = ? WHERE id = ? AND status = "pending"');
    $stmt->execute([$sessionId, $purchase['id']]);

    if ($stmt->rowCount() > 0) {
        deliver_stock_code($pdo, (int) $purchase['package_id'], (int) $purchase['id'], $purchase['user_id']);
    }

    return ['ok' => true, 'message' => 'Pagamento confirmado.'];
}

/**
 * Mesmo princípio de finalize_purchase_payment(), para pedidos do
 * marketplace de IDs: só credita o saldo do vendedor depois de confirmar,
 * junto à Stripe, que a sessão criada PARA ESTE PEDIDO foi paga no valor
 * certo — sem isso, bastava pagar um item barato e usar aquele session_id
 * pago para liberar/creditar um pedido de valor maior.
 *
 * @param array<string, mixed> $order
 * @return array{ok: bool, message: string}
 */
function finalize_order_payment(PDO $pdo, array $order): array
{
    if ($order['status'] === 'delivered') {
        return ['ok' => true, 'message' => 'Pagamento já confirmado.'];
    }

    $sessionId = (string) ($order['stripe_session_id'] ?? '');
    if ($sessionId === '') {
        return ['ok' => false, 'message' => 'Pagamento ainda não iniciado.'];
    }

    try {
        $session = stripe_get_checkout_session($sessionId);
    } catch (Throwable $e) {
        return ['ok' => false, 'message' => 'Não foi possível confirmar com a Stripe. Tente novamente em instantes.'];
    }

    $expectedCents = (int) round(((float) $order['amount']) * 100);
    $paid = ($session['payment_status'] ?? '') === 'paid'
        && (string) ($session['id'] ?? '') === $sessionId
        && (int) ($session['amount_total'] ?? -1) === $expectedCents
        && strtolower((string) ($session['currency'] ?? '')) === strtolower(setting('currency', 'usd'));

    if (!$paid) {
        return ['ok' => false, 'message' => 'Pagamento ainda não confirmado pela Stripe.'];
    }

    $pdo->beginTransaction();
    $stmt = $pdo->prepare('UPDATE orders SET status = "delivered", payment_ref = ? WHERE id = ? AND status = "awaiting_payment"');
    $stmt->execute([$sessionId, $order['id']]);
    if ($stmt->rowCount() > 0) {
        $pdo->prepare('UPDATE users SET balance = balance + ? WHERE id = ?')->execute([$order['amount'], $order['seller_id']]);
    }
    $pdo->commit();

    return ['ok' => true, 'message' => 'Pagamento confirmado.'];
}
