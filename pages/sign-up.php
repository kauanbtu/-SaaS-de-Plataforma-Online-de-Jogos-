<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';

if (current_user()) {
    redirect('/pages/dashboard.php');
}

$pageTitle = 'Sign Up';
require __DIR__ . '/../includes/header.php';
?>

<section class="hero"><div class="container"><h1>SIGN UP</h1></div></section>

<section class="section">
  <div class="container auth-wrap">
    <?= flashes_render() ?>
    <div class="panel">
      <div class="auth-tabs">
        <a href="<?= base_url('pages/sign-in.php') ?>">Sign In</a>
        <a href="<?= base_url('pages/sign-up.php') ?>" class="active">Sign Up</a>
      </div>
      <form method="post" action="<?= base_url('actions/register.php') ?>">
        <?= csrf_field() ?>
        <div class="field"><label>Nome completo</label><input type="text" name="name" placeholder="Seu nome" required></div>
        <div class="field input-icon">
          <label>Email</label>
          <i class="fa-solid fa-envelope"></i>
          <input type="email" name="email" placeholder="seuemail@exemplo.com" required>
        </div>
        <div class="field input-icon">
          <label>Senha</label>
          <i class="fa-solid fa-lock"></i>
          <input type="password" name="password" placeholder="Mínimo 8 caracteres" minlength="8" required>
        </div>
        <div class="field input-icon">
          <label>Confirmar senha</label>
          <i class="fa-solid fa-lock"></i>
          <input type="password" name="confirm_password" placeholder="••••••••" minlength="8" required>
        </div>
        <button class="btn btn-accent btn-block" type="submit">Criar Conta</button>
      </form>
    </div>
  </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
