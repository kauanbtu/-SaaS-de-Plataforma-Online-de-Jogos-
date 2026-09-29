<?php
declare(strict_types=1);
$pageTitle = 'Saques';
$active = 'withdrawals';
require __DIR__ . '/_layout_top.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $id = (int) ($_POST['withdrawal_id'] ?? 0);
    $decision = $_POST['decision'] ?? '';

    $stmt = $pdo->prepare('SELECT * FROM withdrawals WHERE id = ? AND status = "pending"');
    $stmt->execute([$id]);
    $w = $stmt->fetch();

    if ($w) {
        if ($decision === 'paid') {
            $pdo->prepare('UPDATE withdrawals SET status = "paid" WHERE id = ?')->execute([$id]);
            flash('success', 'Saque marcado como pago.');
        } elseif ($decision === 'reject') {
            $pdo->beginTransaction();
            $pdo->prepare('UPDATE withdrawals SET status = "rejected" WHERE id = ?')->execute([$id]);
            $pdo->prepare('UPDATE users SET balance = balance + ? WHERE id = ?')->execute([$w['amount'], $w['user_id']]);
            $pdo->commit();
            flash('success', 'Saque rejeitado e saldo estornado ao usuário.');
        }
    }
    redirect('/admin/withdrawals.php');
}

$withdrawals = $pdo->query(
    'SELECT w.*, u.name AS user_name, u.email FROM withdrawals w JOIN users u ON u.id = w.user_id ORDER BY w.created_at DESC'
)->fetchAll();
?>

<section class="hero"><div class="container"><h1>SAQUES</h1></div></section>

<section class="section">
  <div class="container">
    <?= flashes_render() ?>
    <div class="panel">
      <table class="data-table">
        <thead><tr><th>Usuário</th><th>Valor</th><th>Método</th><th>Conta</th><th>Status</th><th></th></tr></thead>
        <tbody>
          <?php foreach ($withdrawals as $w): ?>
          <tr>
            <td><?= e($w['user_name']) ?><br><small style="color:var(--text-dim)"><?= e($w['email']) ?></small></td>
            <td><?= currency_symbol() ?><?= money((float) $w['amount']) ?></td>
            <td><?= e($w['method']) ?></td>
            <td><?= e($w['account_ref']) ?></td>
            <td><span class="status <?= $w['status'] === 'paid' ? 'accepted' : ($w['status'] === 'rejected' ? 'rejected' : 'pending') ?>"><?= e(ucfirst($w['status'])) ?></span></td>
            <td>
              <?php if ($w['status'] === 'pending'): ?>
              <form method="post" action="" style="display:inline">
                <?= csrf_field() ?>
                <input type="hidden" name="withdrawal_id" value="<?= (int) $w['id'] ?>">
                <input type="hidden" name="decision" value="paid">
                <button class="btn btn-accent btn-sm">Marcar Pago</button>
              </form>
              <form method="post" action="" style="display:inline">
                <?= csrf_field() ?>
                <input type="hidden" name="withdrawal_id" value="<?= (int) $w['id'] ?>">
                <input type="hidden" name="decision" value="reject">
                <button class="btn btn-outline btn-sm">Rejeitar</button>
              </form>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>

<?php require __DIR__ . '/_layout_bottom.php'; ?>
