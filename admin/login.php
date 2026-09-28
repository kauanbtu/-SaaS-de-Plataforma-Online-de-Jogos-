<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';

$user = current_user();
if ($user) {
    redirect($user['role'] === 'admin' ? '/admin/index.php' : '/pages/dashboard.php');
}

$pageTitle = 'Admin Login';
?><!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login — <?= e(setting('site_name', 'Gamers Arena')) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@700;800;900&family=Rajdhani:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
</head>
<body>
<section class="section" style="min-height:100vh;display:flex;align-items:center">
  <div class="container auth-wrap">
    <?= flashes_render() ?>
    <div class="panel">
      <h3 style="text-align:center;color:#fff">PAINEL ADMINISTRATIVO</h3>
      <form method="post" action="<?= base_url('actions/login.php') ?>">
        <?= csrf_field() ?>
        <div class="field"><label>Email</label><input type="email" name="email" required></div>
        <div class="field"><label>Senha</label><input type="password" name="password" required></div>
        <button class="btn btn-accent btn-block" type="submit">Entrar</button>
      </form>
      <p style="color:var(--text-dim);font-size:.78rem;margin-top:16px;text-align:center">
        Padrão: <code>admin@example.com</code> / <code>ChangeMe123!</code> — troque a senha após instalar.
      </p>
    </div>
  </div>
</section>
</body>
</html>
