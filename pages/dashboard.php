<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';

$user = require_login();

$pageTitle = 'Dashboard';
$active = 'dashboard';
require __DIR__ . '/../includes/header.php';

$stmt = $pdo->prepare('SELECT COUNT(*) FROM listings WHERE seller_id = ? AND status = "active"');
$stmt->execute([$user['id']]);
$listingsCount = (int) $stmt->fetchColumn();

$stmt = $pdo->prepare(
    'SELECT COUNT(*) FROM offers o JOIN listings l ON l.id = o.listing_id WHERE l.seller_id = ? AND o.status = "pending"'
);
$stmt->execute([$user['id']]);
$pendingOffers = (int) $stmt->fetchColumn();

$stmt = $pdo->prepare('SELECT COUNT(*) FROM orders WHERE buyer_id = ? AND status != "delivered"');
$stmt->execute([$user['id']]);
$openOrders = (int) $stmt->fetchColumn();

$stmt = $pdo->prepare(
    'SELECT o.*, l.title FROM offers o
     JOIN listings l ON l.id = o.listing_id
     WHERE l.seller_id = ? OR o.buyer_id = ?
     ORDER BY o.created_at DESC LIMIT 6'
);
$stmt->execute([$user['id'], $user['id']]);
$recentOffers = $stmt->fetchAll();
?>

<section class="hero"><div class="container"><h1>DASHBOARD</h1></div></section>

<section class="section">
  <div class="container">
    <?= flashes_render() ?>
    <div class="dash-grid">
      <div class="stat-card"><i class="fa-solid fa-id-badge"></i><div class="val"><?= $listingsCount ?></div><div class="lbl">IDs à Venda</div></div>
      <div class="stat-card"><i class="fa-solid fa-handshake"></i><div class="val"><?= $pendingOffers ?></div><div class="lbl">Ofertas Pendentes</div></div>
      <div class="stat-card"><i class="fa-solid fa-bag-shopping"></i><div class="val"><?= $openOrders ?></div><div class="lbl">Pedidos em Aberto</div></div>
      <div class="stat-card"><i class="fa-solid fa-sack-dollar"></i><div class="val"><?= currency_symbol() ?><?= money((float) $user['balance']) ?></div><div class="lbl">Saldo Disponível</div></div>
    </div>

    <div class="panel">
      <h3>Atividade Recente</h3>
      <?php if (empty($recentOffers)): ?>
        <p style="color:var(--text-dim)">Nenhuma atividade ainda.</p>
      <?php else: ?>
      <table class="data-table">
        <thead><tr><th>Item</th><th>Valor</th><th>Status</th><th>Data</th></tr></thead>
        <tbody>
          <?php foreach ($recentOffers as $o): ?>
          <tr>
            <td><?= e($o['title']) ?></td>
            <td><?= currency_symbol() ?><?= money((float) $o['amount']) ?></td>
            <td><span class="status <?= $o['status'] === 'accepted' ? 'accepted' : ($o['status'] === 'rejected' ? 'rejected' : 'pending') ?>"><?= e($o['status']) ?></span></td>
            <td><?= e(date('d/m/Y', strtotime($o['created_at']))) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
