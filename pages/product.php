<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';

$productId = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM products WHERE id = ? AND active = 1');
$stmt->execute([$productId]);
$product = $stmt->fetch();

if (!$product) {
    flash('error', 'Produto não encontrado.');
    redirect('/index.php');
}

$stmt = $pdo->prepare('SELECT * FROM product_packages WHERE product_id = ? ORDER BY price');
$stmt->execute([$productId]);
$packages = $stmt->fetchAll();

$discountRate = (float) setting('discount_rate', '0.15');
$needsPlayerId = $product['type'] === 'topup';

$pageTitle = $product['name'];
$active = $product['type'] === 'giftcard' ? 'gift-card' : ($product['type'] === 'voucher' ? 'voucher' : 'top-up');
require __DIR__ . '/../includes/header.php';
?>

<section class="hero"><div class="container"><h1><?= e(strtoupper($product['name'])) ?></h1></div></section>

<section class="section">
  <div class="container">
    <?= flashes_render() ?>
    <div class="details-layout" style="grid-template-columns:340px 1fr">
      <div>
        <div class="panel" style="padding:0;overflow:hidden">
          <div style="aspect-ratio:1/1;background:#fff;display:flex;align-items:center;justify-content:center">
            <i class="<?= e($product['icon']) ?>" style="font-size:5rem;background:linear-gradient(135deg,#ff6a1a,#ff2d95,#7b2ff7,#17c497);-webkit-background-clip:text;background-clip:text;color:transparent"></i>
          </div>
        </div>
        <h3 style="color:#fff;margin:20px 0 10px;font-size:1.3rem"><?= e(strtoupper($product['name'])) ?></h3>
        <p style="color:var(--text-dim);font-size:.9rem"><?= nl2br(e($product['description'])) ?></p>
      </div>

      <div>
        <?php if (empty($packages)): ?>
          <div class="panel"><p style="color:var(--text-dim)">Nenhum pacote cadastrado para este produto ainda. Configure em <code>/admin</code>.</p></div>
        <?php else: ?>
        <form method="post" action="<?= base_url('actions/purchase.php') ?>">
          <?= csrf_field() ?>
          <input type="hidden" name="return_to" value="/pages/product.php?id=<?= (int) $product['id'] ?>">

          <?php if ($needsPlayerId): ?>
          <div class="panel">
            <h3>PLAYER ID</h3>
            <div class="field"><label>User ID</label><input type="text" name="player_id" placeholder="Ex: 5123456789" required></div>
          </div>
          <?php endif; ?>

          <div class="panel">
            <h3>SELECT PACKAGE</h3>
            <div class="pill-grid">
              <?php foreach ($packages as $i => $pkg): ?>
              <label class="pill <?= $i === 0 ? 'selected' : '' ?>" style="display:block">
                <input type="radio" name="package_id" value="<?= (int) $pkg['id'] ?>" <?= $i === 0 ? 'checked' : '' ?> style="display:none">
                <?php if ($i === 0): ?><span class="check"><i class="fa-solid fa-check"></i></span><?php endif; ?>
                <?= e($pkg['label']) ?> — $<?= money((float) $pkg['price']) ?>
              </label>
              <?php endforeach; ?>
            </div>
          </div>

          <div class="panel">
            <h3>PURCHASE</h3>
            <p style="color:var(--text-dim);font-size:.85rem">Desconto automático de <?= (int) ($discountRate * 100) ?>% aplicado no checkout.</p>
            <p class="summary-note">Pagamento processado com segurança via Stripe. Sem chave configurada, o checkout roda em modo demonstração.</p>
            <button class="btn btn-accent btn-block"><i class="fa-solid fa-credit-card"></i> Buy Now</button>
          </div>
        </form>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
