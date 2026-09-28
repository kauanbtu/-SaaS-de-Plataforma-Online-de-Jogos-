<?php
/**
 * Cabeçalho do painel admin. Espera $pageTitle e $active definidos antes do include.
 */
require_once __DIR__ . '/../includes/auth.php';
$admin = require_admin();
$pageTitle = $pageTitle ?? 'Admin';
$active = $active ?? '';
?><!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($pageTitle) ?> — Admin — <?= e(setting('site_name', 'Gamers Arena')) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@700;800;900&family=Rajdhani:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
</head>
<body>

<header class="site-header">
  <nav class="nav">
    <a href="<?= base_url('admin/index.php') ?>" class="brand"><span class="logo-mark">GA</span> ADMIN</a>
    <ul class="nav-links">
      <li><a href="<?= base_url('admin/index.php') ?>" class="<?= $active === 'dashboard' ? 'active' : '' ?>">Dashboard</a></li>
      <li><a href="<?= base_url('admin/products.php') ?>" class="<?= $active === 'products' ? 'active' : '' ?>">Produtos</a></li>
      <li><a href="<?= base_url('admin/stock.php') ?>" class="<?= $active === 'stock' ? 'active' : '' ?>">Estoque</a></li>
      <li><a href="<?= base_url('admin/listings.php') ?>" class="<?= $active === 'listings' ? 'active' : '' ?>">Marketplace</a></li>
      <li><a href="<?= base_url('admin/withdrawals.php') ?>" class="<?= $active === 'withdrawals' ? 'active' : '' ?>">Saques</a></li>
      <li><a href="<?= base_url('admin/users.php') ?>" class="<?= $active === 'users' ? 'active' : '' ?>">Usuários</a></li>
      <li><a href="<?= base_url('admin/settings.php') ?>" class="<?= $active === 'settings' ? 'active' : '' ?>">Configurações</a></li>
    </ul>
    <div class="nav-cta">
      <a href="<?= base_url('index.php') ?>" class="icon-btn" title="Ver site"><i class="fa-solid fa-arrow-up-right-from-square"></i></a>
      <a href="<?= base_url('actions/logout.php') ?>" class="icon-btn" title="Sair"><i class="fa-solid fa-right-from-bracket"></i></a>
      <button class="hamburger"><i class="fa-solid fa-bars"></i></button>
    </div>
  </nav>
</header>
