<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';

$pageTitle = 'Top Up';
$active = 'top-up';
require __DIR__ . '/../includes/header.php';

$stmt = $pdo->query('SELECT * FROM products WHERE type = "topup" AND active = 1 ORDER BY name');
$products = $stmt->fetchAll();
?>

<section class="hero"><div class="container"><h1>TOP UP</h1></div></section>

<section class="section">
  <div class="container">
    <?php if (empty($products)): ?>
      <p style="color:var(--text-dim);text-align:center">Nenhum jogo para recarga cadastrado ainda. Adicione produtos em <code>/admin</code>.</p>
    <?php else: ?>
    <div class="grid grid-4">
      <?php foreach ($products as $p): ?>
      <a href="<?= base_url('pages/product.php?id=' . $p['id']) ?>" class="feature-card">
        <div class="ic"><i class="<?= e($p['icon']) ?>"></i></div>
        <h4><?= e($p['name']) ?></h4>
        <p><?= e($p['description']) ?></p>
      </a>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
