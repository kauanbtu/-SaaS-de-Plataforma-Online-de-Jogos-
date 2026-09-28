<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';

$pageTitle = 'Shop Now';
$active = 'buy-id';
require __DIR__ . '/../includes/header.php';

$categories = $pdo->query('SELECT * FROM categories ORDER BY name')->fetchAll();

$categoryId = (int) ($_GET['category'] ?? 0);
$search     = trim($_GET['q'] ?? '');
$minPrice   = (float) ($_GET['min'] ?? 0);
$maxPrice   = (float) ($_GET['max'] ?? 0);

$sql = 'SELECT l.*, c.name AS category_name FROM listings l
        JOIN categories c ON c.id = l.category_id
        WHERE l.status = "active"';
$params = [];

if ($categoryId > 0) {
    $sql .= ' AND l.category_id = ?';
    $params[] = $categoryId;
}
if ($search !== '') {
    $sql .= ' AND l.title LIKE ?';
    $params[] = '%' . $search . '%';
}
if ($minPrice > 0) {
    $sql .= ' AND l.price >= ?';
    $params[] = $minPrice;
}
if ($maxPrice > 0) {
    $sql .= ' AND l.price <= ?';
    $params[] = $maxPrice;
}
$sql .= ' ORDER BY l.created_at DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$listings = $stmt->fetchAll();
?>

<section class="hero"><div class="container"><h1>SHOP NOW</h1></div></section>

<section class="section">
  <div class="container">
    <div class="shop-layout">
      <aside>
        <form method="get" action="">
        <div class="panel filter-box">
          <h4>Search</h4>
          <div class="search-box">
            <input type="text" name="q" value="<?= e($search) ?>" placeholder="Search items">
            <button type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
          </div>
        </div>
        <div class="panel filter-box">
          <h4>Filter By Price</h4>
          <div class="field-row">
            <div class="field"><input type="number" name="min" value="<?= $minPrice ?: '' ?>" placeholder="Min"></div>
            <div class="field"><input type="number" name="max" value="<?= $maxPrice ?: '' ?>" placeholder="Max"></div>
          </div>
          <button type="submit" class="btn btn-outline btn-sm btn-block">Aplicar</button>
        </div>
        <div class="panel filter-box">
          <h4>Categories</h4>
          <ul class="checkbox-list">
            <li><a href="?<?= http_build_query(['q' => $search]) ?>" style="<?= $categoryId === 0 ? 'color:#fff;font-weight:700' : '' ?>">Todas</a></li>
            <?php foreach ($categories as $c): ?>
            <li><a href="?<?= http_build_query(['q' => $search, 'category' => $c['id']]) ?>" style="<?= $categoryId === (int) $c['id'] ? 'color:#fff;font-weight:700' : '' ?>"><?= e($c['name']) ?></a></li>
            <?php endforeach; ?>
          </ul>
        </div>
        </form>
      </aside>

      <div>
        <div class="listing-toolbar">
          <span class="count">Showing <?= count($listings) ?> result<?= count($listings) === 1 ? '' : 's' ?></span>
          <?php if (current_user()): ?>
            <a href="<?= base_url('pages/listing-new.php') ?>" class="btn btn-accent btn-sm"><i class="fa-solid fa-plus"></i> Anunciar ID</a>
          <?php endif; ?>
        </div>

        <?php if (empty($listings)): ?>
          <p style="color:var(--text-dim)">Nenhum anúncio encontrado. <?= current_user() ? '<a href="' . base_url('pages/listing-new.php') . '" style="color:var(--accent)">Seja o primeiro a anunciar!</a>' : '' ?></p>
        <?php endif; ?>

        <?php foreach ($listings as $l): ?>
        <div class="listing-item">
          <div class="thumb" style="background:linear-gradient(135deg,#457b9d,#1d3557)"></div>
          <div class="body">
            <h5><?= e($l['title']) ?></h5>
            <div class="price">Price: <?= money((float) $l['price']) ?> USD</div>
            <div class="meta"><span><?= e($l['category_name']) ?></span><?php if ($l['level']): ?><span>Level: <?= e($l['level']) ?></span><?php endif; ?><?php if ($l['age_label']): ?><span><?= e($l['age_label']) ?></span><?php endif; ?></div>
          </div>
          <a href="<?= base_url('pages/id-details.php?id=' . $l['id']) ?>" class="btn btn-accent btn-sm">Make Offer »</a>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
