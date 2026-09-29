<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';

$user = require_login('/pages/sign-in.php');

$purchaseId = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare(
    'SELECT pu.*, pp.label, p.name AS product_name FROM purchases pu
     JOIN product_packages pp ON pp.id = pu.package_id
     JOIN products p ON p.id = pp.product_id
     WHERE pu.id = ?'
);
$stmt->execute([$purchaseId]);
$purchase = $stmt->fetch();

// Recibo é dado pessoal (mostra o código entregue): só o dono da compra ou
// um admin pode ver, nunca por tentativa de adivinhar o id na URL.
if (!$purchase || ((int) $purchase['user_id'] !== (int) $user['id'] && $user['role'] !== 'admin')) {
    flash('error', 'Compra não encontrada.');
    redirect('/index.php');
}

$pageTitle = 'Recibo da Compra';
$active = '';
require __DIR__ . '/../includes/header.php';
?>

<section class="hero"><div class="container"><h1>RECIBO DA COMPRA</h1></div></section>

<section class="section">
  <div class="container" style="max-width:560px">
    <?= flashes_render() ?>
    <div class="panel">
      <h3><?= e($purchase['product_name']) ?> — <?= e($purchase['label']) ?></h3>
      <div class="summary-row"><span>Subtotal:</span><span><?= currency_symbol() ?><?= money((float) $purchase['amount']) ?></span></div>
      <div class="summary-row"><span>Desconto:</span><span><?= currency_symbol() ?><?= money((float) $purchase['discount']) ?></span></div>
      <div class="summary-row total"><span>Pago:</span><span><?= currency_symbol() ?><?= money((float) $purchase['payable']) ?></span></div>

      <div class="field" style="margin-top:20px">
        <label>Status</label>
        <span class="status <?= $purchase['status'] === 'delivered' ? 'accepted' : ($purchase['status'] === 'failed' ? 'rejected' : 'pending') ?>">
          <?= e($purchase['status']) ?>
        </span>
      </div>

      <?php if ($purchase['delivered_code']): ?>
        <div class="field">
          <label>Código entregue automaticamente</label>
          <input type="text" readonly value="<?= e($purchase['delivered_code']) ?>" onclick="this.select()">
        </div>
      <?php elseif ($purchase['status'] === 'paid'): ?>
        <p style="color:var(--text-dim);font-size:.85rem">
          Pagamento confirmado. Estoque deste pacote esgotado — a entrega será feita manualmente pela equipe.
          Repor estoque em <code>/admin/stock.php</code>.
        </p>
      <?php endif; ?>
    </div>
    <a href="<?= base_url('index.php') ?>" class="btn btn-outline" style="margin-top:20px">Voltar à loja</a>
  </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
