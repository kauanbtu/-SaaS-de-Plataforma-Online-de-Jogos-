<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';

$user = require_login();

$stmt = $pdo->prepare(
    "SELECT * FROM (
        SELECT 'Venda' AS tipo, l.title AS item, u.name AS pessoa, o.amount AS amount, o.status AS status, o.created_at AS created_at
        FROM orders o JOIN listings l ON l.id = o.listing_id JOIN users u ON u.id = o.buyer_id
        WHERE o.seller_id = ?
        UNION ALL
        SELECT 'Compra' AS tipo, l.title AS item, u.name AS pessoa, o.amount AS amount, o.status AS status, o.created_at AS created_at
        FROM orders o JOIN listings l ON l.id = o.listing_id JOIN users u ON u.id = o.seller_id
        WHERE o.buyer_id = ?
        UNION ALL
        SELECT 'Compra' AS tipo, CONCAT(p.name, ' — ', pp.label) AS item, ? AS pessoa, pu.payable AS amount, pu.status AS status, pu.created_at AS created_at
        FROM purchases pu JOIN product_packages pp ON pp.id = pu.package_id JOIN products p ON p.id = pp.product_id
        WHERE pu.user_id = ?
    ) history
    ORDER BY created_at DESC"
);
$stmt->execute([$user['id'], $user['id'], setting('site_name', 'Gamers Arena'), $user['id']]);
$rows = $stmt->fetchAll();

$pageTitle = 'History';
$active = 'history';
require __DIR__ . '/../includes/header.php';
?>

<section class="hero"><div class="container"><h1>HISTORY</h1></div></section>

<section class="section">
  <div class="container">
    <?= flashes_render() ?>
    <div class="panel">
      <h3>Purchase &amp; Sales History</h3>
      <?php if (empty($rows)): ?>
        <p style="color:var(--text-dim)">Nenhum histórico ainda.</p>
      <?php else: ?>
      <table class="data-table">
        <thead><tr><th>Tipo</th><th>Item</th><th>Com quem</th><th>Valor</th><th>Status</th><th>Data</th></tr></thead>
        <tbody>
          <?php foreach ($rows as $r): ?>
          <tr>
            <td><?= e($r['tipo']) ?></td>
            <td><?= e($r['item']) ?></td>
            <td><?= e($r['pessoa']) ?></td>
            <td>$<?= money((float) $r['amount']) ?></td>
            <td><span class="status <?= in_array($r['status'], ['paid','delivered']) ? 'accepted' : ($r['status'] === 'cancelled' || $r['status'] === 'failed' ? 'rejected' : 'pending') ?>"><?= e($r['status']) ?></span></td>
            <td><?= e(date('d/m/Y', strtotime($r['created_at']))) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
