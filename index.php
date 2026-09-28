<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/auth.php';

$pageTitle = 'Início';
$active = 'home';
require __DIR__ . '/includes/header.php';

$games = $pdo->query('SELECT * FROM categories ORDER BY name')->fetchAll();
?>

<?= flashes_render() ?>

<section class="top-banner">
  <div class="container">
    <div class="banner-left">
      <div class="banner-char"><i class="fa-solid fa-user-ninja"></i></div>
      <div class="banner-arrows"><span></span><span></span><span></span></div>
    </div>
    <div class="hex-grid">
      <div class="hex"><i class="fa-solid fa-crosshairs"></i></div>
      <div class="hex alt"><i class="fa-solid fa-car"></i></div>
      <div class="hex"><i class="fa-solid fa-gun"></i></div>
      <div class="hex alt"><i class="fa-solid fa-shield-halved"></i></div>
      <div class="hex"><i class="fa-solid fa-circle-dot"></i></div>
    </div>
    <div class="banner-right">
      <p class="tagline">ON DEMAND<br><span>GAME SHOP</span><br>PLATFORM</p>
      <span class="version-tag">VERSION 1.0</span>
    </div>
  </div>
</section>

<section class="hero">
  <div class="container">
    <span class="badge-tag">Marketplace de Jogos</span>
    <h1>SUA LOJA ONLINE DE JOGOS COMPLETA</h1>
    <p>Recarga direta, gift cards, vouchers e venda de IDs — tudo em uma única plataforma responsiva, pronta para desktop, tablet e mobile.</p>
    <div class="flex gap-10 center" style="justify-content:center;margin-top:26px">
      <a href="<?= base_url('pages/top-up.php') ?>" class="btn btn-accent">Começar Recarga</a>
      <a href="<?= base_url('pages/buy-id.php') ?>" class="btn btn-outline">Ver Marketplace</a>
    </div>
  </div>
</section>

<section class="section">
  <div class="container">
    <h2 class="section-title text-center">O que você encontra na <?= e(setting('site_name', 'Gamers Arena')) ?></h2>
    <p class="section-sub text-center">Quatro pilares em um único painel para seu negócio de jogos crescer.</p>
    <div class="grid grid-4">
      <div class="feature-card">
        <div class="ic"><i class="fa-solid fa-bolt"></i></div>
        <h4>Recarga Direta</h4>
        <p>Integração com jogos mobile populares e apps VOD, entrega instantânea na conta do jogador.</p>
      </div>
      <div class="feature-card">
        <div class="ic"><i class="fa-solid fa-gift"></i></div>
        <h4>Gift Cards</h4>
        <p>Venda de PSN, Google Play, Apple Card, iTunes e diversos outros cartões digitais.</p>
      </div>
      <div class="feature-card">
        <div class="ic"><i class="fa-solid fa-ticket"></i></div>
        <h4>Vouchers</h4>
        <p>Game cards, payment cards e gift cards para plataformas de música e vídeo.</p>
      </div>
      <div class="feature-card">
        <div class="ic"><i class="fa-solid fa-id-badge"></i></div>
        <h4>Venda de IDs</h4>
        <p>Marketplace completo para gerenciar vendas de contas e IDs de jogadores.</p>
      </div>
    </div>
  </div>
</section>

<section class="section" style="padding-top:0">
  <div class="container">
    <h2 class="section-title text-center">Categorias do Marketplace</h2>
    <p class="section-sub text-center">Compre e venda contas destes jogos.</p>
    <div class="grid grid-4">
      <?php foreach ($games as $g): ?>
      <a href="<?= base_url('pages/buy-id.php?category=' . $g['id']) ?>" class="game-card">
        <div class="thumb" style="background:linear-gradient(135deg,#ff9a3d,#7b2ff7)"></div>
        <div class="info"><h5><?= e($g['name']) ?></h5><span>Ver anúncios</span></div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
