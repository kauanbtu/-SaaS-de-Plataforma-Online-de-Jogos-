<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';

$user = require_login();

$pageTitle = 'Offer List';
$active = 'my-sales';
require __DIR__ . '/../includes/header.php';

$stmt = $pdo->prepare(
    'SELECT o.*, l.title, u.name AS buyer_name FROM offers o
     JOIN listings l ON l.id = o.listing_id
     JOIN users u ON u.id = o.buyer_id
     WHERE l.seller_id = ?
     ORDER BY o.created_at DESC'
);
$stmt->execute([$user['id']]);
$offers = $stmt->fetchAll();
?>

<section class="hero"><div class="container"><h1>OFFER LIST</h1></div></section>

<section class="section">
  <div class="container">
    <?= flashes_render() ?>
    <div class="panel">
      <?php if (empty($offers)): ?>
        <p style="color:var(--text-dim)">Você ainda não recebeu nenhuma oferta nos seus anúncios.</p>
      <?php endif; ?>

      <?php foreach ($offers as $o): ?>
      <div class="offer-item">
        <div class="avatar"><?= e(strtoupper(substr($o['buyer_name'], 0, 2))) ?></div>
        <div class="body">
          <div class="flex between center" style="flex-wrap:wrap;gap:10px">
            <div>
              <h5><?= e($o['buyer_name']) ?> <?= e($o['title']) ?></h5>
              <span class="price"><?= currency_symbol() ?><?= money((float) $o['amount']) ?></span>
              <span class="status <?= $o['status'] === 'accepted' ? 'accepted' : ($o['status'] === 'rejected' ? 'rejected' : 'pending') ?>" style="margin-left:10px"><?= e(ucfirst($o['status'])) ?></span>
              <span class="time" style="margin-left:10px"><i class="fa-regular fa-clock"></i> <?= e(date('d/m/Y H:i', strtotime($o['created_at']))) ?></span>
            </div>
            <div class="flex gap-10">
              <?php if ($o['status'] === 'pending'): ?>
                <form method="post" action="<?= base_url('actions/offer_respond.php') ?>">
                  <?= csrf_field() ?>
                  <input type="hidden" name="offer_id" value="<?= (int) $o['id'] ?>">
                  <input type="hidden" name="decision" value="accept">
                  <button class="btn btn-accent btn-sm">Aceitar</button>
                </form>
                <form method="post" action="<?= base_url('actions/offer_respond.php') ?>">
                  <?= csrf_field() ?>
                  <input type="hidden" name="offer_id" value="<?= (int) $o['id'] ?>">
                  <input type="hidden" name="decision" value="reject">
                  <button class="btn btn-outline btn-sm">Rejeitar</button>
                </form>
              <?php else: ?>
                <a href="<?= base_url('pages/conversation.php?offer_id=' . $o['id']) ?>" class="btn btn-outline btn-sm"><i class="fa-solid fa-comment"></i> Conversation</a>
              <?php endif; ?>
            </div>
          </div>
          <?php if ($o['message']): ?><p><?= e($o['message']) ?></p><?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>

      <p style="color:var(--text-dim);font-size:.82rem;margin-top:16px">Showing all <?= count($offers) ?> results</p>
    </div>
  </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
