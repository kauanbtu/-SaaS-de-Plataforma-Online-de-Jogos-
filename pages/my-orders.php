<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';

$user = require_login();

$stmt = $pdo->prepare(
    'SELECT o.*, l.title, u.name AS seller_name FROM orders o
     JOIN listings l ON l.id = o.listing_id
     JOIN users u ON u.id = o.seller_id
     WHERE o.buyer_id = ?
     ORDER BY o.created_at DESC'
);
$stmt->execute([$user['id']]);
$orders = $stmt->fetchAll();

$pageTitle = 'My Orders';
$active = 'my-orders';
require __DIR__ . '/../includes/header.php';
?>

<section class="hero"><div class="container"><h1>MY ORDERS</h1></div></section>

<section class="section">
  <div class="container">
    <?= flashes_render() ?>
    <div class="panel">
      <?php if (empty($orders)): ?>
        <p style="color:var(--text-dim)">Você ainda não fechou nenhum pedido no marketplace de IDs.</p>
      <?php else: ?>
      <table class="data-table">
        <thead><tr><th>Order ID</th><th>Item</th><th>Vendedor</th><th>Valor</th><th>Status</th><th>Data</th><th></th></tr></thead>
        <tbody>
          <?php foreach ($orders as $o): ?>
          <tr>
            <td>#ORD-<?= (int) $o['id'] ?></td>
            <td><?= e($o['title']) ?></td>
            <td><?= e($o['seller_name']) ?></td>
            <td>$<?= money((float) $o['amount']) ?></td>
            <td><span class="status <?= in_array($o['status'], ['paid','delivered']) ? 'accepted' : ($o['status'] === 'cancelled' ? 'rejected' : 'pending') ?>"><?= e($o['status']) ?></span></td>
            <td><?= e(date('d/m/Y', strtotime($o['created_at']))) ?></td>
            <td class="flex gap-10">
              <a href="<?= base_url('pages/conversation.php?offer_id=' . $o['offer_id']) ?>" class="btn btn-outline btn-sm">Chat</a>
              <?php if ($o['status'] === 'awaiting_payment'): ?>
              <form method="post" action="<?= base_url('actions/order_pay.php') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="order_id" value="<?= (int) $o['id'] ?>">
                <button class="btn btn-accent btn-sm">Pagar</button>
              </form>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
