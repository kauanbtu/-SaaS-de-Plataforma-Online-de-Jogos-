<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';

$user = require_login();

$pageTitle = 'Trocar Senha';
$active = '';
require __DIR__ . '/../includes/header.php';
?>

<section class="hero"><div class="container"><h1>TROCAR SENHA</h1></div></section>

<section class="section">
  <div class="container" style="max-width:480px">
    <?= flashes_render() ?>
    <?php if (!empty($user['must_change_password'])): ?>
      <p style="color:var(--text-dim);font-size:.85rem;margin-bottom:16px">
        Esta conta ainda está usando uma senha padrão/temporária. Defina uma nova senha para continuar.
      </p>
    <?php endif; ?>
    <div class="panel">
      <form method="post" action="<?= base_url('actions/change_password.php') ?>">
        <?= csrf_field() ?>
        <div class="field"><label>Senha atual</label><input type="password" name="current_password" required></div>
        <div class="field"><label>Nova senha (mín. 8 caracteres)</label><input type="password" name="new_password" minlength="8" required></div>
        <div class="field"><label>Confirmar nova senha</label><input type="password" name="new_password_confirm" minlength="8" required></div>
        <button class="btn btn-accent btn-block" type="submit">Salvar Nova Senha</button>
      </form>
    </div>
  </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
