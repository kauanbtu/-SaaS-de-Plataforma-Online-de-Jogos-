<?php
declare(strict_types=1);
$pageTitle = 'Estoque';
$active = 'stock';
require __DIR__ . '/_layout_top.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $packageId = (int) ($_POST['package_id'] ?? 0);
    $codesRaw  = (string) ($_POST['codes'] ?? '');
    $codes = array_filter(array_map('trim', explode("\n", $codesRaw)));

    $stmt = $pdo->prepare('INSERT INTO product_stock (package_id, code) VALUES (?, ?)');
    foreach ($codes as $code) {
        $stmt->execute([$packageId, $code]);
    }
    flash('success', count($codes) . ' código(s) adicionado(s) ao estoque.');
    redirect('/admin/stock.php?package_id=' . $packageId);
}

$packages = $pdo->query(
    'SELECT pp.id, pp.label, p.name AS product_name,
            (SELECT COUNT(*) FROM product_stock ps WHERE ps.package_id = pp.id AND ps.used = 0) AS in_stock
     FROM product_packages pp JOIN products p ON p.id = pp.product_id
     ORDER BY p.name, pp.label'
)->fetchAll();

$selectedPackageId = (int) ($_GET['package_id'] ?? ($packages[0]['id'] ?? 0));
$stockList = [];
if ($selectedPackageId) {
    $stmt = $pdo->prepare('SELECT * FROM product_stock WHERE package_id = ? ORDER BY used, id DESC LIMIT 100');
    $stmt->execute([$selectedPackageId]);
    $stockList = $stmt->fetchAll();
}
?>

<section class="hero"><div class="container"><h1>ESTOQUE DE CÓDIGOS</h1></div></section>

<section class="section">
  <div class="container">
    <?= flashes_render() ?>
    <div class="panel">
      <h3>Selecionar pacote</h3>
      <form method="get" action="" class="field-row" style="align-items:flex-end">
        <div class="field" style="flex:2">
          <select name="package_id" onchange="this.form.submit()">
            <?php foreach ($packages as $p): ?>
              <option value="<?= (int) $p['id'] ?>" <?= $selectedPackageId === (int) $p['id'] ? 'selected' : '' ?>>
                <?= e($p['product_name'] . ' — ' . $p['label'] . ' (' . $p['in_stock'] . ' em estoque)') ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      </form>
    </div>

    <?php if ($selectedPackageId): ?>
    <div class="panel">
      <h3>Adicionar códigos (um por linha)</h3>
      <form method="post" action="">
        <?= csrf_field() ?>
        <input type="hidden" name="package_id" value="<?= $selectedPackageId ?>">
        <div class="field"><textarea rows="6" name="codes" placeholder="XXXX-XXXX-XXXX&#10;YYYY-YYYY-YYYY" required></textarea></div>
        <button class="btn btn-accent">Adicionar ao Estoque</button>
      </form>
    </div>

    <div class="panel">
      <h3>Últimos 100 códigos</h3>
      <table class="data-table">
        <thead><tr><th>Código</th><th>Status</th><th>Usado por</th><th>Data</th></tr></thead>
        <tbody>
          <?php foreach ($stockList as $s): ?>
          <tr>
            <td><?= e($s['code']) ?></td>
            <td><span class="status <?= $s['used'] ? 'rejected' : 'accepted' ?>"><?= $s['used'] ? 'Usado' : 'Disponível' ?></span></td>
            <td><?= $s['used_by'] ? '#' . (int) $s['used_by'] : '—' ?></td>
            <td><?= $s['used_at'] ? e(date('d/m/Y H:i', strtotime($s['used_at']))) : '—' ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>
</section>

<?php require __DIR__ . '/_layout_bottom.php'; ?>
