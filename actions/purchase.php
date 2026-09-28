<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/stripe.php';
require_once __DIR__ . '/../includes/delivery.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/index.php');
}
csrf_verify();

$packageId = (int) ($_POST['package_id'] ?? 0);
$playerId  = trim($_POST['player_id'] ?? '');
$returnTo  = trim($_POST['return_to'] ?? '/index.php');

$stmt = $pdo->prepare(
    'SELECT pp.*, p.name AS product_name FROM product_packages pp
     JOIN products p ON p.id = pp.product_id
     WHERE pp.id = ?'
);
$stmt->execute([$packageId]);
$package = $stmt->fetch();

if (!$package) {
    flash('error', 'Pacote inválido.');
    redirect($returnTo);
}

$user = current_user();

$discountRate = (float) setting('discount_rate', '0.15');
$subtotal = (string) $package['price'];
$discount = bcmul($subtotal, (string) $discountRate, 2);
$payable  = bcsub($subtotal, $discount, 2);

$stmt = $pdo->prepare(
    'INSERT INTO purchases (user_id, package_id, player_id, amount, discount, payable, payment_method, status)
     VALUES (?, ?, ?, ?, ?, ?, ?, "pending")'
);
$stmt->execute([
    $user['id'] ?? null,
    $packageId,
    $playerId !== '' ? $playerId : null,
    $subtotal,
    $discount,
    $payable,
    stripe_configured() ? 'stripe' : 'demo',
]);
$purchaseId = (int) $pdo->lastInsertId();

if (stripe_configured()) {
    $successUrl = base_url('actions/purchase_confirm.php') . '?purchase_id=' . $purchaseId . '&session_id={CHECKOUT_SESSION_ID}';
    $cancelUrl  = base_url(ltrim($returnTo, '/'));

    try {
        $checkoutUrl = stripe_create_checkout_session($package['product_name'] . ' — ' . $package['label'], (float) $payable, $successUrl, $cancelUrl);
    } catch (Throwable $e) {
        $checkoutUrl = null;
    }

    if ($checkoutUrl) {
        redirect($checkoutUrl);
    }

    $pdo->prepare('UPDATE purchases SET status = "failed" WHERE id = ?')->execute([$purchaseId]);
    flash('error', 'Não foi possível iniciar o pagamento com a Stripe. Tente novamente.');
    redirect($returnTo);
}

// Modo demonstração: sem chave da Stripe configurada em /admin, a compra é
// confirmada instantaneamente para fins de teste local (sem cobrança real).
$pdo->prepare('UPDATE purchases SET status = "paid", payment_ref = "demo" WHERE id = ?')->execute([$purchaseId]);
$code = deliver_stock_code($pdo, $packageId, $purchaseId, $user['id'] ?? null);

if ($code === null) {
    flash('error', 'Pagamento confirmado, mas o estoque deste pacote está esgotado. A equipe fará a entrega manual em breve.');
} else {
    flash('success', 'Pagamento confirmado (modo demonstração). Código entregue: ' . $code);
}

redirect('/actions/purchase_receipt.php?id=' . $purchaseId);
