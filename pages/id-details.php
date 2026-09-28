<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';

$id = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare(
    'SELECT l.*, c.name AS category_name, u.name AS seller_name FROM listings l
     JOIN categories c ON c.id = l.category_id
     JOIN users u ON u.id = l.seller_id
     WHERE l.id = ?'
);
$stmt->execute([$id]);
$listing = $stmt->fetch();

if (!$listing) {
    flash('error', 'Anúncio não encontrado.');
    redirect('/pages/buy-id.php');
}

$user = current_user();
$pageTitle = $listing['title'];
$active = 'buy-id';
require __DIR__ . '/../includes/header.php';
?>

<section class="hero hero-small">
  <div class="container">
    <h1 style="font-size:1.6rem"><?= e(strtoupper($listing['title'])) ?></h1>
    <span class="crumb"><a href="<?= base_url('pages/buy-id.php') ?>">Buy ID</a> / <?= e($listing['title']) ?></span>
  </div>
</section>

<section class="section">
  <div class="container">
    <?= flashes_render() ?>
    <div class="details-layout">
      <div class="details-gallery">
        <div style="aspect-ratio:3/2;background:linear-gradient(135deg,#241536,#457b9d);border-radius:var(--radius);border:1px solid var(--border)"></div>
      </div>

      <div>
        <div class="flex gap-10" style="margin-bottom:14px;flex-wrap:wrap">
          <span class="stat-chip"><b><?= money((float) $listing['price']) ?> USD</b>Price</span>
          <?php if ($listing['level']): ?><span class="stat-chip"><b><?= e($listing['level']) ?></b>Level</span><?php endif; ?>
          <span class="stat-chip"><b><?= e($listing['category_name']) ?></b>Categoria</span>
        </div>
        <p style="color:var(--text-dim)"><?= nl2br(e($listing['description'] ?: 'Sem descrição adicional.')) ?></p>
        <p style="color:var(--text-dim);font-size:.85rem">Vendedor: <?= e($listing['seller_name']) ?></p>

        <?php if (!$user): ?>
          <div class="offer-box">
            <p>Você precisa <a href="<?= base_url('pages/sign-in.php') ?>" style="color:var(--accent)">entrar na sua conta</a> para fazer uma oferta.</p>
          </div>
        <?php elseif ((int) $listing['seller_id'] === (int) $user['id']): ?>
          <div class="offer-box"><p style="color:var(--text-dim)">Este é o seu próprio anúncio.</p></div>
        <?php elseif ($listing['status'] !== 'active'): ?>
          <div class="offer-box"><p style="color:var(--text-dim)">Este anúncio já foi vendido.</p></div>
        <?php else: ?>
        <div class="offer-box">
          <h3 style="margin:0 0 16px;color:#fff;font-size:1rem">FAZER UMA OFERTA</h3>
          <form method="post" action="<?= base_url('actions/offer_create.php') ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="listing_id" value="<?= (int) $listing['id'] ?>">
            <div class="field">
              <label>Seu valor de oferta (USD)</label>
              <input type="number" step="0.01" name="amount" placeholder="Ex: 90" required>
            </div>
            <div class="field">
              <label>Mensagem</label>
              <textarea rows="3" name="message" placeholder="Escreva uma mensagem para o vendedor..."></textarea>
            </div>
            <button class="btn btn-accent btn-block">Enviar Oferta »</button>
          </form>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
