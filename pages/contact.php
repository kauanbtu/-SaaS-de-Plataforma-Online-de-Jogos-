<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';

$pageTitle = 'Contact';
$active = 'contact';
require __DIR__ . '/../includes/header.php';
?>

<section class="hero"><div class="container"><h1>CONTACT US</h1></div></section>

<section class="section">
  <div class="container">
    <?= flashes_render() ?>
    <div class="grid grid-3" style="margin-bottom:40px">
      <div class="panel text-center">
        <i class="fa-solid fa-phone" style="font-size:1.6rem;color:var(--accent)"></i>
        <h3 style="margin-top:14px;font-size:1rem">Telefone</h3>
        <p style="color:var(--text-dim)"><?= e(setting('site_phone', 'Configure em /admin')) ?></p>
      </div>
      <div class="panel text-center">
        <i class="fa-solid fa-envelope" style="font-size:1.6rem;color:var(--accent)"></i>
        <h3 style="margin-top:14px;font-size:1rem">Email</h3>
        <p style="color:var(--text-dim)"><?= e(setting('site_email', 'Configure em /admin')) ?></p>
      </div>
      <div class="panel text-center">
        <i class="fa-solid fa-location-dot" style="font-size:1.6rem;color:var(--accent)"></i>
        <h3 style="margin-top:14px;font-size:1rem">Endereço</h3>
        <p style="color:var(--text-dim)"><?= e(setting('site_address', 'Configure em /admin')) ?></p>
      </div>
    </div>

    <div class="panel" style="max-width:700px;margin:0 auto">
      <h3>Envie uma mensagem</h3>
      <form method="post" action="<?= base_url('actions/contact.php') ?>">
        <?= csrf_field() ?>
        <div class="field-row">
          <div class="field"><label>Nome</label><input type="text" name="name" placeholder="Seu nome" required></div>
          <div class="field"><label>Email</label><input type="email" name="email" placeholder="seuemail@exemplo.com" required></div>
        </div>
        <div class="field"><label>Assunto</label><input type="text" name="subject" placeholder="Assunto"></div>
        <div class="field"><label>Mensagem</label><textarea rows="5" name="message" placeholder="Escreva sua mensagem..." required></textarea></div>
        <button class="btn btn-accent" type="submit">Enviar Mensagem</button>
      </form>
    </div>
  </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
