<?php
declare(strict_types=1);
$pageTitle = 'Marketplace';
$active = 'listings';
require __DIR__ . '/_layout_top.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $listingId = (int) ($_POST['listing_id'] ?? 0);
    $status = $_POST['status'] ?? '';
    if (in_array($status, ['active', 'removed'], true)) {
        $pdo->prepare('UPDATE listings SET status = ? WHERE id = ?')->execute([$status, $listingId]);
        flash('success', 'Anúncio atualizado.');
    }
    redirect('/admin/listings.php');
}

$listings = $pdo->query(
    'SELECT l.*, c.name AS category_name, u.name AS seller_name FROM listings l
     JOIN categories c ON c.id = l.category_id
     JOIN users u ON u.id = l.seller_id
     ORDER BY l.created_at DESC'
)->fetchAll();
?>

<section class="hero"><div class="container"><h1>MODERAÇÃO DO MARKETPLACE</h1></div></section>

<section class="section">
  <div class="container">
    <?= flashes_render() ?>
    <div class="panel">
      <table class="data-table">
        <thead><tr><th>Título</th><th>Categoria</th><th>Vendedor</th><th>Preço</th><th>Status</th><th></th></tr></thead>
        <tbody>
          <?php foreach ($listings as $l): ?>
          <tr>
            <td><?= e($l['title']) ?></td>
            <td><?= e($l['category_name']) ?></td>
            <td><?= e($l['seller_name']) ?></td>
            <td>$<?= money((float) $l['price']) ?></td>
            <td><span class="status <?= $l['status'] === 'active' ? 'accepted' : ($l['status'] === 'removed' ? 'rejected' : 'pending') ?>"><?= e($l['status']) ?></span></td>
            <td>
              <form method="post" action="" style="display:inline">
                <?= csrf_field() ?>
                <input type="hidden" name="listing_id" value="<?= (int) $l['id'] ?>">
                <?php if ($l['status'] === 'active'): ?>
                  <input type="hidden" name="status" value="removed">
                  <button class="btn btn-sm" style="background:var(--danger);color:#fff" onclick="return confirm('Remover este anúncio?')">Remover</button>
                <?php elseif ($l['status'] === 'removed'): ?>
                  <input type="hidden" name="status" value="active">
                  <button class="btn btn-accent btn-sm">Reativar</button>
                <?php endif; ?>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>

<?php require __DIR__ . '/_layout_bottom.php'; ?>
