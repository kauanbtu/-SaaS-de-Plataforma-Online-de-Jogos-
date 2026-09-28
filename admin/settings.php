<?php
declare(strict_types=1);
$pageTitle = 'Configurações';
$active = 'settings';
require __DIR__ . '/_layout_top.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $keys = ['site_name', 'site_phone', 'site_email', 'site_address', 'currency', 'discount_rate', 'stripe_public_key', 'stripe_secret_key'];
    $stmt = $pdo->prepare('INSERT INTO settings (`key`, `value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)');
    foreach ($keys as $key) {
        $stmt->execute([$key, trim($_POST[$key] ?? '')]);
    }
    flash('success', 'Configurações salvas.');
    redirect('/admin/settings.php');
}
?>

<section class="hero"><div class="container"><h1>CONFIGURAÇÕES</h1></div></section>

<section class="section">
  <div class="container" style="max-width:640px">
    <?= flashes_render() ?>
    <form method="post" action="">
      <?= csrf_field() ?>
      <div class="panel">
        <h3>Identidade do Site</h3>
        <div class="field"><label>Nome do site</label><input type="text" name="site_name" value="<?= e(setting('site_name')) ?>" required></div>
        <div class="field"><label>Telefone</label><input type="text" name="site_phone" value="<?= e(setting('site_phone')) ?>" placeholder="Ex: +55 11 91234-5678"></div>
        <div class="field"><label>E-mail de contato</label><input type="email" name="site_email" value="<?= e(setting('site_email')) ?>"></div>
        <div class="field"><label>Endereço</label><input type="text" name="site_address" value="<?= e(setting('site_address')) ?>"></div>
      </div>

      <div class="panel">
        <h3>Financeiro</h3>
        <div class="field-row">
          <div class="field"><label>Moeda</label><input type="text" name="currency" value="<?= e(setting('currency', 'USD')) ?>"></div>
          <div class="field"><label>Desconto padrão (0.0 a 1.0)</label><input type="number" step="0.01" name="discount_rate" value="<?= e(setting('discount_rate', '0.15')) ?>"></div>
        </div>
      </div>

      <div class="panel">
        <h3>Stripe (pagamentos)</h3>
        <p style="color:var(--text-dim);font-size:.85rem">
          Obtenha as chaves em <a href="https://dashboard.stripe.com/apikeys" target="_blank" rel="noopener" style="color:var(--accent)">dashboard.stripe.com/apikeys</a>.
          Sem chave secreta, o checkout roda em modo demonstração.
        </p>
        <div class="field"><label>Chave pública (publishable key)</label><input type="text" name="stripe_public_key" value="<?= e(setting('stripe_public_key')) ?>" placeholder="pk_live_..."></div>
        <div class="field"><label>Chave secreta (secret key)</label><input type="text" name="stripe_secret_key" value="<?= e(setting('stripe_secret_key')) ?>" placeholder="sk_live_..."></div>
      </div>

      <button class="btn btn-accent" type="submit">Salvar Configurações</button>
    </form>
  </div>
</section>

<?php require __DIR__ . '/_layout_bottom.php'; ?>
