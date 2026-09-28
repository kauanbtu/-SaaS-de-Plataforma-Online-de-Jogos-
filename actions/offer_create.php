<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';

$user = require_login('/pages/sign-in.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/pages/buy-id.php');
}
csrf_verify();

$listingId = (int) ($_POST['listing_id'] ?? 0);
$amount    = (float) ($_POST['amount'] ?? 0);
$message   = trim($_POST['message'] ?? '');

$stmt = $pdo->prepare('SELECT * FROM listings WHERE id = ? AND status = "active"');
$stmt->execute([$listingId]);
$listing = $stmt->fetch();

if (!$listing) {
    flash('error', 'Este anúncio não está mais disponível.');
    redirect('/pages/buy-id.php');
}
if ((int) $listing['seller_id'] === (int) $user['id']) {
    flash('error', 'Você não pode fazer uma oferta no seu próprio anúncio.');
    redirect('/pages/id-details.php?id=' . $listingId);
}
if ($amount <= 0) {
    flash('error', 'Informe um valor de oferta válido.');
    redirect('/pages/id-details.php?id=' . $listingId);
}

$stmt = $pdo->prepare('INSERT INTO offers (listing_id, buyer_id, amount, message) VALUES (?, ?, ?, ?)');
$stmt->execute([$listingId, $user['id'], $amount, $message]);

flash('success', 'Oferta enviada ao vendedor.');
redirect('/pages/id-details.php?id=' . $listingId);
