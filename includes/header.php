<?php
/**
 * Cabeçalho compartilhado. Espera opcionalmente:
 *   $pageTitle (string) — título da aba
 *   $active    (string) — chave do item de menu ativo
 */
require_once __DIR__ . '/auth.php';
$__user = current_user();
$pageTitle = $pageTitle ?? setting('site_name', 'Gamers Arena');
$active = $active ?? '';
?><!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($pageTitle) ?> — <?= e(setting('site_name', 'Gamers Arena')) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@700;800;900&family=Rajdhani:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
</head>
<body data-currency-symbol="<?= e(currency_symbol()) ?>">

<header class="site-header">
  <nav class="nav">
    <a href="<?= base_url('index.php') ?>" class="brand"><span class="logo-mark">GA</span> <?= e(strtoupper(setting('site_name', 'Gamers Arena'))) ?></a>

    <?php if ($__user): ?>
    <ul class="nav-links">
      <li><a href="<?= base_url('index.php') ?>">Home</a></li>
      <li><a href="<?= base_url('pages/dashboard.php') ?>" class="<?= $active === 'dashboard' ? 'active' : '' ?>">Dashboard</a></li>
      <li class="dropdown"><a href="<?= base_url('pages/my-sales.php') ?>" class="<?= $active === 'my-sales' ? 'active' : '' ?>">My Sales <i class="fa-solid fa-chevron-down" style="font-size:.6rem"></i>
        <div class="dropdown-menu">
          <a href="<?= base_url('pages/my-sales.php') ?>">Offer List</a>
        </div>
      </a></li>
      <li class="dropdown"><a href="<?= base_url('pages/my-orders.php') ?>" class="<?= $active === 'my-orders' ? 'active' : '' ?>">My Orders</a></li>
      <li><a href="<?= base_url('pages/payout.php') ?>" class="<?= $active === 'payout' ? 'active' : '' ?>">Payout</a></li>
      <li><a href="<?= base_url('pages/history.php') ?>" class="<?= $active === 'history' ? 'active' : '' ?>">History</a></li>
    </ul>
    <div class="nav-cta">
      <?php if ($__user['role'] === 'admin'): ?>
        <a href="<?= base_url('admin/index.php') ?>" class="icon-btn" title="Painel Admin"><i class="fa-solid fa-gauge"></i></a>
      <?php endif; ?>
      <a href="<?= base_url('pages/change-password.php') ?>" class="icon-btn" title="Trocar senha"><i class="fa-solid fa-key"></i></a>
      <a href="<?= base_url('actions/logout.php') ?>" class="icon-btn" title="Sair"><i class="fa-solid fa-right-from-bracket"></i></a>
      <a href="<?= base_url('pages/dashboard.php') ?>" class="icon-btn"><i class="fa-solid fa-user"></i></a>
      <button class="hamburger"><i class="fa-solid fa-bars"></i></button>
    </div>
    <?php else: ?>
    <ul class="nav-links">
      <li><a href="<?= base_url('index.php') ?>" class="<?= $active === 'home' ? 'active' : '' ?>">Home</a></li>
      <li><a href="<?= base_url('pages/about.php') ?>" class="<?= $active === 'about' ? 'active' : '' ?>">About Us</a></li>
      <li><a href="<?= base_url('pages/top-up.php') ?>" class="<?= $active === 'top-up' ? 'active' : '' ?>">Top Up</a></li>
      <li><a href="<?= base_url('pages/voucher.php') ?>" class="<?= $active === 'voucher' ? 'active' : '' ?>">Voucher</a></li>
      <li><a href="<?= base_url('pages/gift-card.php') ?>" class="<?= $active === 'gift-card' ? 'active' : '' ?>">Gift Card</a></li>
      <li><a href="<?= base_url('pages/buy-id.php') ?>" class="<?= $active === 'buy-id' ? 'active' : '' ?>">Buy ID</a></li>
      <li><a href="<?= base_url('pages/contact.php') ?>" class="<?= $active === 'contact' ? 'active' : '' ?>">Contact</a></li>
    </ul>
    <div class="nav-cta">
      <a href="<?= base_url('pages/sign-in.php') ?>" class="btn btn-outline btn-sm">Sign In</a>
      <a href="<?= base_url('pages/sign-up.php') ?>" class="btn btn-accent btn-sm">Sign Up</a>
      <button class="hamburger"><i class="fa-solid fa-bars"></i></button>
    </div>
    <?php endif; ?>
  </nav>
</header>
