<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';

$pageTitle = 'About Us';
$active = 'about';
require __DIR__ . '/../includes/header.php';
?>

<section class="hero"><div class="container"><h1>ABOUT US</h1></div></section>

<section class="section">
  <div class="container">
    <div class="grid grid-2" style="align-items:center;margin-bottom:50px">
      <div>
        <span class="badge-tag">Quem Somos</span>
        <h2 class="section-title">A plataforma que unifica sua loja de jogos</h2>
        <p style="color:var(--text-dim)">
          <?= e(setting('site_name', 'Gamers Arena')) ?> simplifica a vida de quem vende recargas, gift cards,
          vouchers e contas de jogos online. Com checkout seguro via Stripe, entrega automática de códigos
          digitais e um sistema de ofertas com chat integrado, conectamos compradores e vendedores em um
          único painel.
        </p>
      </div>
      <div class="panel" style="aspect-ratio:4/3;display:flex;align-items:center;justify-content:center">
        <i class="fa-solid fa-gamepad" style="font-size:4rem;color:var(--accent)"></i>
      </div>
    </div>

    <div class="grid grid-4">
      <div class="feature-card">
        <div class="ic"><i class="fa-solid fa-mobile-screen-button"></i></div>
        <h4>Responsivo</h4>
        <p>Compatível com desktop, tablet e mobile.</p>
      </div>
      <div class="feature-card">
        <div class="ic"><i class="fa-solid fa-bolt"></i></div>
        <h4>Entrega Automática</h4>
        <p>Códigos entregues assim que o pagamento é confirmado.</p>
      </div>
      <div class="feature-card">
        <div class="ic"><i class="fa-solid fa-gift"></i></div>
        <h4>Gift Cards e Vouchers</h4>
        <p>Catálogo configurável direto pelo painel admin.</p>
      </div>
      <div class="feature-card">
        <div class="ic"><i class="fa-solid fa-id-badge"></i></div>
        <h4>Venda de IDs</h4>
        <p>Marketplace completo com sistema de ofertas.</p>
      </div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
