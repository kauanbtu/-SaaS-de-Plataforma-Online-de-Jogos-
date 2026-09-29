<?php
declare(strict_types=1);
$pageTitle = 'Usuários';
$active = 'users';
require __DIR__ . '/_layout_top.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $userId = (int) ($_POST['user_id'] ?? 0);
    $role = $_POST['role'] ?? 'customer';
    if (in_array($role, ['customer', 'admin'], true) && $userId !== (int) $admin['id']) {
        $pdo->prepare('UPDATE users SET role = ? WHERE id = ?')->execute([$role, $userId]);
        flash('success', 'Papel do usuário atualizado.');
    }
    redirect('/admin/users.php');
}

$users = $pdo->query('SELECT * FROM users ORDER BY created_at DESC')->fetchAll();
?>

<section class="hero"><div class="container"><h1>USUÁRIOS</h1></div></section>

<section class="section">
  <div class="container">
    <?= flashes_render() ?>
    <div class="panel">
      <table class="data-table">
        <thead><tr><th>Nome</th><th>Email</th><th>Papel</th><th>Saldo</th><th>Desde</th><th></th></tr></thead>
        <tbody>
          <?php foreach ($users as $u): ?>
          <tr>
            <td><?= e($u['name']) ?></td>
            <td><?= e($u['email']) ?></td>
            <td><span class="status <?= $u['role'] === 'admin' ? 'accepted' : 'pending' ?>"><?= e($u['role']) ?></span></td>
            <td><?= currency_symbol() ?><?= money((float) $u['balance']) ?></td>
            <td><?= e(date('d/m/Y', strtotime($u['created_at']))) ?></td>
            <td>
              <?php if ((int) $u['id'] !== (int) $admin['id']): ?>
              <form method="post" action="" style="display:inline">
                <?= csrf_field() ?>
                <input type="hidden" name="user_id" value="<?= (int) $u['id'] ?>">
                <input type="hidden" name="role" value="<?= $u['role'] === 'admin' ? 'customer' : 'admin' ?>">
                <button class="btn btn-outline btn-sm"><?= $u['role'] === 'admin' ? 'Rebaixar' : 'Tornar Admin' ?></button>
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
