<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/stripe.php';
$pageTitle = 'Configurações';
$active = 'settings';
require __DIR__ . '/_layout_top.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $keys = ['site_name', 'site_phone', 'site_email', 'site_address', 'currency', 'discount_rate', 'stripe_public_key', 'stripe_secret_key', 'stripe_webhook_secret'];
    $stmt = $pdo->prepare('INSERT INTO settings (`key`, `value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)');
    foreach ($keys as $key) {
        $stmt->execute([$key, trim($_POST[$key] ?? '')]);
    }
    $stmt->execute(['demo_mode', isset($_POST['demo_mode']) ? '1' : '0']);
    flash('success', 'Configurações salvas.');
    redirect('/admin/settings.php');
}

$envOverridesStripe = env('STRIPE_SECRET_KEY', '') !== '' || env('STRIPE_PUBLIC_KEY', '') !== '';
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
          <div class="field"><label>Moeda (código ISO, ex: USD, BRL, EUR)</label><input type="text" name="currency" value="<?= e(setting('currency', 'USD')) ?>"></div>
          <div class="field"><label>Desconto padrão (0.0 a 1.0)</label><input type="number" step="0.01" name="discount_rate" value="<?= e(setting('discount_rate', '0.15')) ?>"></div>
        </div>
        <p style="color:var(--text-dim);font-size:.85rem">
          Com a moeda BRL e a Stripe configurada, o <b>Pix</b> é oferecido automaticamente como forma de
          pagamento ao lado do cartão (requer conta Stripe habilitada para o Brasil).
        </p>
      </div>

      <div class="panel">
        <h3>Modo Demonstração</h3>
        <p style="color:var(--text-dim);font-size:.85rem">
          Com o modo demonstração ativado, toda compra feita <b>sem</b> chave da Stripe configurada é
          confirmada automaticamente <b>sem cobrança real</b> — útil só para testar o fluxo antes de configurar
          pagamentos de verdade. Fica <b>desligado por padrão</b>: nunca ative em produção, ou o site entregará
          produtos de graça para qualquer visitante.
        </p>
        <label style="display:flex;align-items:center;gap:8px;color:#fff">
          <input type="checkbox" name="demo_mode" value="1" <?= setting('demo_mode', '0') === '1' ? 'checked' : '' ?> style="width:auto">
          Ativar modo demonstração (sem cobrança real)
        </label>
        <?php if (setting('demo_mode', '0') === '1'): ?>
          <p style="color:var(--danger);font-size:.85rem;font-weight:700;margin-top:8px">
            ⚠ Modo demonstração ATIVO — desative antes de divulgar o site publicamente.
          </p>
        <?php endif; ?>
      </div>

      <div class="panel">
        <h3>Stripe (pagamentos)</h3>
        <p style="color:var(--text-dim);font-size:.85rem">
          Obtenha as chaves em <a href="https://dashboard.stripe.com/apikeys" target="_blank" rel="noopener" style="color:var(--accent)">dashboard.stripe.com/apikeys</a>.
          Sem chave secreta (aqui ou no <code>.env</code>) e sem o modo demonstração ativo, o checkout fica bloqueado.
        </p>
        <?php if ($envOverridesStripe): ?>
          <p style="color:var(--accent-2);font-size:.85rem">
            O arquivo <code>.env</code> deste servidor já define <code>STRIPE_SECRET_KEY</code>/<code>STRIPE_PUBLIC_KEY</code>
            — enquanto isso, essas variáveis têm prioridade sobre os campos abaixo.
          </p>
        <?php endif; ?>
        <div class="field"><label>Chave pública (publishable key)</label><input type="text" name="stripe_public_key" value="<?= e(setting('stripe_public_key')) ?>" placeholder="pk_live_..."></div>
        <div class="field"><label>Chave secreta (secret key)</label><input type="text" name="stripe_secret_key" value="<?= e(setting('stripe_secret_key')) ?>" placeholder="sk_live_..."></div>
        <div class="field">
          <label>Webhook signing secret</label>
          <input type="text" name="stripe_webhook_secret" value="<?= e(setting('stripe_webhook_secret')) ?>" placeholder="whsec_...">
        </div>
        <p style="color:var(--text-dim);font-size:.8rem">
          Em <a href="https://dashboard.stripe.com/webhooks" target="_blank" rel="noopener" style="color:var(--accent)">dashboard.stripe.com/webhooks</a>,
          crie um endpoint apontando para <code><?= e(base_url('actions/stripe_webhook.php')) ?></code> com o evento
          <code>checkout.session.completed</code>, e cole aqui o "signing secret" gerado. Isso garante que o pedido seja
          confirmado e o produto entregue mesmo se o comprador fechar a aba antes de voltar ao site.
        </p>
      </div>

      <button class="btn btn-accent" type="submit">Salvar Configurações</button>
    </form>
  </div>
</section>

<?php require __DIR__ . '/_layout_bottom.php'; ?>
