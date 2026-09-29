<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';

$user = require_login();

$offerId = (int) ($_GET['offer_id'] ?? 0);
$stmt = $pdo->prepare(
    'SELECT o.*, l.title, l.price, l.level, l.seller_id, l.age_label FROM offers o
     JOIN listings l ON l.id = o.listing_id
     WHERE o.id = ?'
);
$stmt->execute([$offerId]);
$offer = $stmt->fetch();

if (!$offer || ((int) $offer['buyer_id'] !== (int) $user['id'] && (int) $offer['seller_id'] !== (int) $user['id'])) {
    flash('error', 'Conversa não encontrada.');
    redirect('/pages/my-sales.php');
}

$stmt = $pdo->prepare(
    'SELECT m.*, u.name AS sender_name FROM messages m
     JOIN users u ON u.id = m.sender_id
     WHERE m.offer_id = ? ORDER BY m.created_at ASC'
);
$stmt->execute([$offerId]);
$messages = $stmt->fetchAll();

$pageTitle = 'Conversation';
$active = 'my-sales';
require __DIR__ . '/../includes/header.php';
?>

<section class="hero"><div class="container"><h1>CONVERSATION</h1></div></section>

<section class="section">
  <div class="container">
    <?= flashes_render() ?>
    <div class="chat-layout">
      <div class="panel">
        <h5 style="color:var(--accent-2);margin:0 0 6px"><?= e($offer['title']) ?></h5>
        <div class="price" style="color:var(--accent-2);font-weight:800;margin-bottom:12px">Oferta: <?= currency_symbol() ?><?= money((float) $offer['amount']) ?></div>
        <div class="meta" style="color:var(--text-dim);font-size:.85rem;display:flex;justify-content:space-between">
          <span><?= e($offer['level'] ? 'Level: ' . $offer['level'] : '') ?></span>
          <span class="status <?= $offer['status'] === 'accepted' ? 'accepted' : ($offer['status'] === 'rejected' ? 'rejected' : 'pending') ?>"><?= e(ucfirst($offer['status'])) ?></span>
        </div>
      </div>

      <div class="chat-window">
        <div class="chat-head">
          <span><i class="fa-solid fa-comments"></i> Conversation</span>
        </div>
        <div class="chat-body">
          <?php if (empty($messages)): ?>
            <p style="color:var(--text-dim);text-align:center">Nenhuma mensagem ainda. Diga olá!</p>
          <?php endif; ?>
          <?php foreach ($messages as $m): ?>
          <div class="msg <?= (int) $m['sender_id'] === (int) $user['id'] ? 'me' : '' ?>">
            <div class="avatar"></div>
            <div class="bubble"><?= e($m['body']) ?><span class="time"><?= e(date('M d, Y H:i', strtotime($m['created_at']))) ?></span></div>
          </div>
          <?php endforeach; ?>
        </div>
        <form class="chat-input" method="post" action="<?= base_url('actions/chat_send.php') ?>">
          <?= csrf_field() ?>
          <input type="hidden" name="offer_id" value="<?= (int) $offerId ?>">
          <input type="text" name="body" placeholder="Type your message" required>
          <button type="submit"><i class="fa-solid fa-paper-plane"></i></button>
        </form>
      </div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
