<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';

$user = require_login('/pages/sign-in.php');
$categories = $pdo->query('SELECT * FROM categories ORDER BY name')->fetchAll();

$pageTitle = 'Anunciar ID';
$active = 'buy-id';
require __DIR__ . '/../includes/header.php';
?>

<section class="hero"><div class="container"><h1>ANUNCIAR ID</h1></div></section>

<section class="section">
  <div class="container" style="max-width:640px">
    <?= flashes_render() ?>
    <div class="panel">
      <form method="post" action="<?= base_url('actions/listing_create.php') ?>">
        <?= csrf_field() ?>
        <div class="field"><label>Título</label><input type="text" name="title" placeholder="Ex: PUBG Level 52" required></div>
        <div class="field"><label>Categoria</label>
          <select name="category_id" required>
            <?php foreach ($categories as $c): ?>
              <option value="<?= (int) $c['id'] ?>"><?= e($c['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="field-row">
          <div class="field"><label>Preço (USD)</label><input type="number" step="0.01" name="price" required></div>
          <div class="field"><label>Level</label><input type="text" name="level" placeholder="Ex: 52"></div>
        </div>
        <div class="field"><label>Idade da conta</label><input type="text" name="age_label" placeholder="Ex: 2 Years"></div>
        <div class="field"><label>Descrição</label><textarea rows="4" name="description" placeholder="Detalhes da conta..."></textarea></div>
        <button class="btn btn-accent btn-block">Publicar Anúncio</button>
      </form>
    </div>
  </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
