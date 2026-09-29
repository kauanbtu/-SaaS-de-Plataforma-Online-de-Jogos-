# Gamers Arena — Plataforma de Loja Online de Jogos

Sistema completo (PHP 8.1+ / MySQL) para venda de recargas de jogos, gift
cards, vouchers e IDs de jogador, com painel administrativo, entrega
automática de códigos digitais e checkout via Stripe.

## Recursos

- **Design responsivo** — desktop, tablet e mobile.
- **Recarga direta** — pacotes configuráveis por jogo, com entrega instantânea.
- **Gift cards e vouchers** — catálogo gerenciado pelo painel admin.
- **Marketplace de IDs** — anúncios, sistema de ofertas e chat de negociação
  entre comprador e vendedor, com fechamento de pedido e saldo do vendedor.
- **Entrega automática** — assim que o pagamento é confirmado (pelo retorno
  do comprador ou pelo webhook, o que chegar primeiro), um código é
  reservado do estoque e entregue sem intervenção manual.
- **Checkout via Stripe, com verificação real** — integração via cURL (sem
  SDK); cada pedido só é liberado depois de confirmar, direto com a Stripe,
  que a sessão de pagamento *daquele pedido específico* foi paga no valor
  certo — nunca a partir de dados vindos do navegador. Webhook
  (`checkout.session.completed`) garante a confirmação mesmo se o
  comprador fechar a aba antes de voltar ao site. Com a moeda BRL, o
  **Pix** é oferecido automaticamente ao lado do cartão.
- **Checkout bloqueado por padrão sem pagamento configurado** — sem Stripe
  configurada, o site não entrega nada de graça: é preciso configurar a
  Stripe ou ativar manualmente o modo demonstração em `/admin/settings.php`
  (desligado por padrão, com aviso permanente no painel enquanto ativo).
- **Painel administrativo** — produtos, estoque, moderação de anúncios,
  aprovação de saques (débito de saldo atômico, à prova de solicitações
  simultâneas), gestão de usuários e configurações do site.
- **Autenticação** — cadastro/login com senha hasheada (bcrypt), sessões,
  proteção CSRF em todos os formulários e troca de senha obrigatória no
  primeiro login de qualquer conta com senha padrão/temporária.
- **Recibos privados** — só o dono da compra (ou um admin) pode ver o
  código entregue; não dá para acessar o recibo de outra pessoa trocando o
  id na URL.

## Requisitos técnicos

- PHP 8.1 ou superior
- MySQL 5.7+ ou MariaDB 10.2+
- Extensões PHP: `BCMath`, `Ctype`, `Fileinfo`, `JSON`, `Mbstring`,
  `OpenSSL`, `PDO`, `PDO_MYSQL`, `Tokenizer`, `XML`, `CURL`, `GD`, `GMP`

Sem dependência de Composer, Node.js ou build step.

## Instalação

Veja o passo a passo completo em [`docs/INSTALACAO.md`](docs/INSTALACAO.md).

Resumo rápido:

```bash
cp .env.example .env        # preencha os dados do seu MySQL
mysql -u USUARIO -p BANCO < sql/schema.sql
php -S localhost:8000       # para testar localmente
```

Login administrativo padrão: `admin@example.com` / `ChangeMe123!` — o
sistema **exige a troca dessa senha já no primeiro login**, antes de
liberar qualquer outra página.

## Estrutura

```
index.php              → página inicial
pages/                  → páginas públicas e da área logada
admin/                  → painel administrativo
actions/                → handlers de formulários (login, compra, ofertas, chat, webhook da Stripe...)
includes/                → conexão de banco, autenticação, helpers, cliente Stripe, confirmação de pagamento
sql/schema.sql          → schema completo do banco (MySQL)
assets/css, assets/js   → tema visual e interações de front-end
docs/INSTALACAO.md      → guia de instalação detalhado
```
