<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';

$user = require_login('/pages/sign-in.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/pages/my-sales.php');
}
csrf_verify();

$offerId = (int) ($_POST['offer_id'] ?? 0);
$decision = $_POST['decision'] ?? '';

$stmt = $pdo->prepare(
    'SELECT o.*, l.seller_id, l.title FROM offers o
     JOIN listings l ON l.id = o.listing_id
     WHERE o.id = ?'
);
$stmt->execute([$offerId]);
$offer = $stmt->fetch();

if (!$offer || (int) $offer['seller_id'] !== (int) $user['id']) {
    flash('error', 'Oferta não encontrada.');
    redirect('/pages/my-sales.php');
}
if ($offer['status'] !== 'pending') {
    flash('error', 'Esta oferta já foi respondida.');
    redirect('/pages/my-sales.php');
}

if ($decision === 'accept') {
    $pdo->beginTransaction();
    $pdo->prepare('UPDATE offers SET status = "accepted" WHERE id = ?')->execute([$offerId]);
    $pdo->prepare(
        'UPDATE offers SET status = "rejected" WHERE listing_id = ? AND id != ? AND status = "pending"'
    )->execute([$offer['listing_id'], $offerId]);
    $pdo->prepare('UPDATE listings SET status = "sold" WHERE id = ?')->execute([$offer['listing_id']]);
    $pdo->prepare(
        'INSERT INTO orders (offer_id, listing_id, buyer_id, seller_id, amount, status)
         VALUES (?, ?, ?, ?, ?, "awaiting_payment")'
    )->execute([$offerId, $offer['listing_id'], $offer['buyer_id'], $user['id'], $offer['amount']]);
    $pdo->commit();
    flash('success', 'Oferta aceita! Pedido criado para "' . $offer['title'] . '".');
} elseif ($decision === 'reject') {
    $pdo->prepare('UPDATE offers SET status = "rejected" WHERE id = ?')->execute([$offerId]);
    flash('success', 'Oferta rejeitada.');
} else {
    flash('error', 'Ação inválida.');
}

redirect('/pages/my-sales.php');
