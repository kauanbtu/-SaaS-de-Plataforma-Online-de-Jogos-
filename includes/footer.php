<footer class="site-footer">
  <div class="container">
    <div class="footer-grid">
      <div>
        <a href="<?= base_url('index.php') ?>" class="brand"><span class="logo-mark">GA</span> <?= e(strtoupper(setting('site_name', 'Gamers Arena'))) ?></a>
        <p>Precisa falar com a gente? Use um dos canais abaixo.</p>
        <ul class="footer-contact">
          <?php if (setting('site_phone')): ?><li><i class="fa-solid fa-phone"></i> <?= e(setting('site_phone')) ?></li><?php endif; ?>
          <?php if (setting('site_email')): ?><li><i class="fa-solid fa-envelope"></i> <?= e(setting('site_email')) ?></li><?php endif; ?>
          <?php if (setting('site_address')): ?><li><i class="fa-solid fa-location-dot"></i> <?= e(setting('site_address')) ?></li><?php endif; ?>
          <?php if (!setting('site_phone') && !setting('site_email') && !setting('site_address')): ?>
            <li style="color:var(--text-dim)">Configure telefone, e-mail e endereço em <code>/admin</code> → Configurações.</li>
          <?php endif; ?>
        </ul>
      </div>
      <div>
        <h4>Quick Links</h4>
        <ul class="footer-links">
          <li><a href="<?= base_url('index.php') ?>">Home</a></li>
          <li><a href="<?= base_url('pages/about.php') ?>">About</a></li>
          <li><a href="<?= base_url('pages/contact.php') ?>">Contact</a></li>
        </ul>
      </div>
      <div>
        <h4>Useful Links</h4>
        <ul class="footer-links">
          <li><a href="<?= base_url('pages/contact.php') ?>">FAQ</a></li>
          <li><a href="#">Terms &amp; Conditions</a></li>
          <li><a href="#">Privacy Policy</a></li>
        </ul>
      </div>
      <div>
        <h4>Subscribe Newsletter</h4>
        <form class="newsletter" method="post" action="<?= base_url('actions/newsletter.php') ?>">
          <?= csrf_field() ?>
          <input type="email" name="email" placeholder="Enter Email" required>
          <button type="submit"><i class="fa-solid fa-paper-plane"></i></button>
        </form>
        <div class="socials">
          <a href="#"><i class="fa-brands fa-facebook-f"></i></a>
          <a href="#"><i class="fa-brands fa-instagram"></i></a>
          <a href="#"><i class="fa-brands fa-skype"></i></a>
          <a href="#"><i class="fa-brands fa-twitter"></i></a>
        </div>
      </div>
    </div>
    <div class="footer-bottom">
      <span>Copyright &copy; <?= date('Y') ?> <?= e(setting('site_name', 'Gamers Arena')) ?>. Todos os direitos reservados.</span>
      <span class="langs"><a href="#">English</a><a href="#">Português</a></span>
    </div>
  </div>
</footer>

<a href="#" class="back-top"><i class="fa-solid fa-arrow-up"></i></a>
<script src="<?= base_url('assets/js/main.js') ?>"></script>
</body>
</html>
