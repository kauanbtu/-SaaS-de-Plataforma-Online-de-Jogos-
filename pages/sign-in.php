<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';

if (current_user()) {
    redirect('/pages/dashboard.php');
}

$pageTitle = 'Sign In';
require __DIR__ . '/../includes/header.php';
?>

<section class="hero"><div class="container"><h1>SIGN IN</h1></div></section>

<section class="section">
  <div class="container auth-wrap">
    <?= flashes_render() ?>
    <div class="panel">
      <div class="auth-tabs">
        <a href="<?= base_url('pages/sign-in.php') ?>" class="active">Sign In</a>
        <a href="<?= base_url('pages/sign-up.php') ?>">Sign Up</a>
      </div>
      <form method="post" action="<?= base_url('actions/login.php') ?>">
        <?= csrf_field() ?>
        <div class="field input-icon">
          <label>Email</label>
          <i class="fa-solid fa-envelope"></i>
          <input type="email" name="email" placeholder="seuemail@exemplo.com" required>
        </div>
        <div class="field input-icon">
          <label>Senha</label>
          <i class="fa-solid fa-lock"></i>
          <input type="password" name="password" placeholder="••••••••" required>
        </div>
        <button class="btn btn-accent btn-block" type="submit">Entrar</button>
      </form>
      <p style="color:var(--text-dim);font-size:.78rem;margin-top:16px">
        Acesso de demonstração: <code>admin@example.com</code> / <code>ChangeMe123!</code> — troque após instalar.
      </p>
    </div>
  </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
