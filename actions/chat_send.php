<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';

$user = require_login('/pages/sign-in.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/pages/my-sales.php');
}
csrf_verify();

$offerId = (int) ($_POST['offer_id'] ?? 0);
$body    = trim($_POST['body'] ?? '');

$stmt = $pdo->prepare(
    'SELECT o.id, o.buyer_id, l.seller_id FROM offers o
     JOIN listings l ON l.id = o.listing_id
     WHERE o.id = ?'
);
$stmt->execute([$offerId]);
$offer = $stmt->fetch();

if (!$offer || ((int) $offer['buyer_id'] !== (int) $user['id'] && (int) $offer['seller_id'] !== (int) $user['id'])) {
    flash('error', 'Conversa não encontrada.');
    redirect('/pages/my-sales.php');
}

if ($body !== '') {
    $stmt = $pdo->prepare('INSERT INTO messages (offer_id, sender_id, body) VALUES (?, ?, ?)');
    $stmt->execute([$offerId, $user['id'], $body]);
}

redirect('/pages/conversation.php?offer_id=' . $offerId);
