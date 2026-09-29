<?php
declare(strict_types=1);
$pageTitle = 'Produtos';
$active = 'products';
require __DIR__ . '/_layout_top.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $action = $_POST['action'] ?? '';

    if ($action === 'create_product') {
        $stmt = $pdo->prepare('INSERT INTO products (type, name, description, icon, active) VALUES (?, ?, ?, ?, 1)');
        $stmt->execute([
            $_POST['type'] ?? 'giftcard',
            trim($_POST['name'] ?? ''),
            trim($_POST['description'] ?? ''),
            trim($_POST['icon'] ?? 'fa-solid fa-gift'),
        ]);
        flash('success', 'Produto criado.');
    } elseif ($action === 'toggle_product') {
        $pdo->prepare('UPDATE products SET active = NOT active WHERE id = ?')->execute([(int) $_POST['product_id']]);
        flash('success', 'Status do produto atualizado.');
    } elseif ($action === 'create_package') {
        $stmt = $pdo->prepare('INSERT INTO product_packages (product_id, label, price) VALUES (?, ?, ?)');
        $stmt->execute([(int) $_POST['product_id'], trim($_POST['label'] ?? ''), (float) ($_POST['price'] ?? 0)]);
        flash('success', 'Pacote adicionado.');
    } elseif ($action === 'delete_package') {
        $pdo->prepare('DELETE FROM product_packages WHERE id = ?')->execute([(int) $_POST['package_id']]);
        flash('success', 'Pacote removido.');
    }

    redirect('/admin/products.php');
}

$products = $pdo->query('SELECT * FROM products ORDER BY type, name')->fetchAll();
$packagesByProduct = [];
foreach ($pdo->query('SELECT pp.*, (SELECT COUNT(*) FROM product_stock ps WHERE ps.package_id = pp.id AND ps.used = 0) AS in_stock FROM product_packages pp')->fetchAll() as $pk) {
    $packagesByProduct[$pk['product_id']][] = $pk;
}
?>

<section class="hero"><div class="container"><h1>PRODUTOS</h1></div></section>

<section class="section">
  <div class="container">
    <?= flashes_render() ?>

    <div class="panel">
      <h3>Novo Produto (Gift Card / Voucher / Top-up)</h3>
      <form method="post" action="" class="field-row" style="align-items:flex-end;flex-wrap:wrap">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="create_product">
        <div class="field" style="min-width:160px"><label>Tipo</label>
          <select name="type"><option value="giftcard">Gift Card</option><option value="voucher">Voucher</option><option value="topup">Top-up</option></select>
        </div>
        <div class="field" style="flex:2;min-width:200px"><label>Nome</label><input type="text" name="name" required></div>
        <div class="field" style="min-width:160px"><label>Ícone (Font Awesome)</label><input type="text" name="icon" placeholder="fa-solid fa-gift"></div>
        <div class="field" style="flex:3;min-width:240px"><label>Descrição</label><input type="text" name="description"></div>
        <button class="btn btn-accent" style="margin-bottom:18px">Adicionar</button>
      </form>
    </div>

    <?php foreach ($products as $p): ?>
    <div class="panel">
      <div class="flex between center">
        <h3 style="margin:0"><i class="<?= e($p['icon']) ?>"></i> <?= e($p['name']) ?> <span style="color:var(--text-dim);font-size:.75rem;text-transform:uppercase">(<?= e($p['type']) ?>)</span></h3>
        <form method="post" action="">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="toggle_product">
          <input type="hidden" name="product_id" value="<?= (int) $p['id'] ?>">
          <button class="btn btn-sm <?= $p['active'] ? 'btn-outline' : 'btn-accent' ?>"><?= $p['active'] ? 'Desativar' : 'Ativar' ?></button>
        </form>
      </div>

      <table class="data-table" style="margin-top:14px">
        <thead><tr><th>Pacote</th><th>Preço</th><th>Em estoque</th><th></th></tr></thead>
        <tbody>
          <?php foreach ($packagesByProduct[$p['id']] ?? [] as $pkg): ?>
          <tr>
            <td><?= e($pkg['label']) ?></td>
            <td><?= currency_symbol() ?><?= money((float) $pkg['price']) ?></td>
            <td><?= (int) $pkg['in_stock'] ?></td>
            <td>
              <a href="<?= base_url('admin/stock.php?package_id=' . $pkg['id']) ?>" class="btn btn-outline btn-sm">Estoque</a>
              <form method="post" action="" style="display:inline">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="delete_package">
                <input type="hidden" name="package_id" value="<?= (int) $pkg['id'] ?>">
                <button class="btn btn-sm" style="background:var(--danger);color:#fff" onclick="return confirm('Remover este pacote?')">Excluir</button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

      <form method="post" action="" class="field-row" style="align-items:flex-end;margin-top:14px">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="create_package">
        <input type="hidden" name="product_id" value="<?= (int) $p['id'] ?>">
        <div class="field"><label>Novo pacote — rótulo</label><input type="text" name="label" placeholder="Ex: 60 UC" required></div>
        <div class="field"><label>Preço (<?= e(setting('currency', 'USD')) ?>)</label><input type="number" step="0.01" name="price" required></div>
        <button class="btn btn-outline">Adicionar Pacote</button>
      </form>
    </div>
    <?php endforeach; ?>
  </div>
</section>

<?php require __DIR__ . '/_layout_bottom.php'; ?>
