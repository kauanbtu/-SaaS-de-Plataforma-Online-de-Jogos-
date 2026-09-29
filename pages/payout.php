<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';

$user = require_login();

$stmt = $pdo->prepare('SELECT * FROM withdrawals WHERE user_id = ? ORDER BY created_at DESC');
$stmt->execute([$user['id']]);
$withdrawals = $stmt->fetchAll();

$pending = 0.0;
$paid = 0.0;
foreach ($withdrawals as $w) {
    if ($w['status'] === 'pending') $pending += (float) $w['amount'];
    if ($w['status'] === 'paid') $paid += (float) $w['amount'];
}

$pageTitle = 'Payout';
$active = 'payout';
require __DIR__ . '/../includes/header.php';
?>

<section class="hero"><div class="container"><h1>PAYOUT</h1></div></section>

<section class="section">
  <div class="container">
    <?= flashes_render() ?>
    <div class="dash-grid" style="grid-template-columns:repeat(3,1fr)">
      <div class="stat-card"><i class="fa-solid fa-wallet"></i><div class="val"><?= currency_symbol() ?><?= money((float) $user['balance']) ?></div><div class="lbl">Saldo Disponível</div></div>
      <div class="stat-card"><i class="fa-solid fa-hourglass-half"></i><div class="val"><?= currency_symbol() ?><?= money($pending) ?></div><div class="lbl">Pendente</div></div>
      <div class="stat-card"><i class="fa-solid fa-circle-check"></i><div class="val"><?= currency_symbol() ?><?= money($paid) ?></div><div class="lbl">Total Sacado</div></div>
    </div>

    <div class="panel" style="max-width:520px;margin-bottom:30px">
      <h3>Solicitar Saque</h3>
      <form method="post" action="<?= base_url('actions/withdraw.php') ?>">
        <?= csrf_field() ?>
        <div class="field"><label>Valor (<?= e(setting('currency', 'USD')) ?>)</label><input type="number" step="0.01" name="amount" max="<?= (float) $user['balance'] ?>" required></div>
        <div class="field"><label>Método de Recebimento</label>
          <select name="method"><option>PayPal</option><option>Transferência Bancária</option><option>Skrill</option></select>
        </div>
        <div class="field"><label>Conta / Email</label><input type="text" name="account_ref" placeholder="seuemail@exemplo.com" required></div>
        <button class="btn btn-accent btn-block" type="submit">Solicitar Saque</button>
      </form>
    </div>

    <div class="panel">
      <h3>Histórico de Saques</h3>
      <?php if (empty($withdrawals)): ?>
        <p style="color:var(--text-dim)">Nenhuma solicitação de saque ainda.</p>
      <?php else: ?>
      <table class="data-table">
        <thead><tr><th>ID</th><th>Valor</th><th>Método</th><th>Status</th><th>Data</th></tr></thead>
        <tbody>
          <?php foreach ($withdrawals as $w): ?>
          <tr>
            <td>#PY-<?= (int) $w['id'] ?></td>
            <td><?= currency_symbol() ?><?= money((float) $w['amount']) ?></td>
            <td><?= e($w['method']) ?></td>
            <td><span class="status <?= $w['status'] === 'paid' ? 'accepted' : ($w['status'] === 'rejected' ? 'rejected' : 'pending') ?>"><?= e(ucfirst($w['status'])) ?></span></td>
            <td><?= e(date('d/m/Y', strtotime($w['created_at']))) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
