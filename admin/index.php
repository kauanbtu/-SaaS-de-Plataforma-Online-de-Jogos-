<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/stripe.php';
$pageTitle = 'Dashboard';
$active = 'dashboard';
require __DIR__ . '/_layout_top.php';

$totalUsers    = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
$totalListings = (int) $pdo->query('SELECT COUNT(*) FROM listings WHERE status = "active"')->fetchColumn();
$totalOrders   = (int) $pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn();
$totalRevenue  = (float) $pdo->query('SELECT COALESCE(SUM(payable),0) FROM purchases WHERE status IN ("paid","delivered")')->fetchColumn();
$pendingWithdrawals = (int) $pdo->query('SELECT COUNT(*) FROM withdrawals WHERE status = "pending"')->fetchColumn();
$lowStock = $pdo->query(
    'SELECT p.name, pp.label, pp.id, COUNT(ps.id) AS available
     FROM product_packages pp
     JOIN products p ON p.id = pp.product_id
     LEFT JOIN product_stock ps ON ps.package_id = pp.id AND ps.used = 0
     GROUP BY pp.id
     HAVING available < 3'
)->fetchAll();
?>

<section class="hero"><div class="container"><h1>ADMIN DASHBOARD</h1></div></section>

<section class="section">
  <div class="container">
    <?= flashes_render() ?>
    <div class="dash-grid">
      <div class="stat-card"><i class="fa-solid fa-users"></i><div class="val"><?= $totalUsers ?></div><div class="lbl">Usuários</div></div>
      <div class="stat-card"><i class="fa-solid fa-id-badge"></i><div class="val"><?= $totalListings ?></div><div class="lbl">Anúncios Ativos</div></div>
      <div class="stat-card"><i class="fa-solid fa-bag-shopping"></i><div class="val"><?= $totalOrders ?></div><div class="lbl">Pedidos Marketplace</div></div>
      <div class="stat-card"><i class="fa-solid fa-sack-dollar"></i><div class="val">$<?= money($totalRevenue) ?></div><div class="lbl">Receita (Gift Card/Top-up)</div></div>
    </div>

    <?php if ($pendingWithdrawals > 0): ?>
      <div class="panel" style="border-color:var(--accent-2)">
        <p style="margin:0"><i class="fa-solid fa-triangle-exclamation" style="color:var(--accent-2)"></i>
        Há <b><?= $pendingWithdrawals ?></b> solicitação(ões) de saque pendente(s).
        <a href="<?= base_url('admin/withdrawals.php') ?>" style="color:var(--accent)">Revisar agora »</a></p>
      </div>
    <?php endif; ?>

    <?php if (!empty($lowStock)): ?>
      <div class="panel" style="border-color:var(--danger)">
        <h3 style="margin-top:0">Estoque baixo</h3>
        <table class="data-table">
          <thead><tr><th>Produto</th><th>Pacote</th><th>Disponível</th><th></th></tr></thead>
          <tbody>
          <?php foreach ($lowStock as $s): ?>
            <tr>
              <td><?= e($s['name']) ?></td>
              <td><?= e($s['label']) ?></td>
              <td><?= (int) $s['available'] ?></td>
              <td><a href="<?= base_url('admin/stock.php?package_id=' . $s['id']) ?>" class="btn btn-outline btn-sm">Repor</a></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>

    <?php if (!stripe_configured()): ?>
    <div class="panel" style="border-color:var(--accent-2)">
      <p style="margin:0"><i class="fa-solid fa-circle-info" style="color:var(--accent-2)"></i>
      Chave da Stripe não configurada — as compras estão rodando em <b>modo demonstração</b> (sem cobrança real).
      <a href="<?= base_url('admin/settings.php') ?>" style="color:var(--accent)">Configurar Stripe »</a></p>
    </div>
    <?php endif; ?>
  </div>
</section>

<?php require __DIR__ . '/_layout_bottom.php'; ?>
