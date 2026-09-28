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
- **Entrega automática** — assim que o pagamento é confirmado, um código é
  reservado do estoque e entregue ao comprador sem intervenção manual.
- **Checkout via Stripe** — integração real (Checkout Sessions) via cURL; sem
  chaves configuradas, roda em modo demonstração para testes locais.
- **Painel administrativo** — produtos, estoque, moderação de anúncios,
  aprovação de saques, gestão de usuários e configurações do site.
- **Autenticação** — cadastro/login com senha hasheada (bcrypt), sessões e
  proteção CSRF em todos os formulários.

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

Login administrativo padrão: `admin@example.com` / `ChangeMe123!` — troque
após o primeiro acesso.

## Estrutura

```
index.php              → página inicial
pages/                  → páginas públicas e da área logada
admin/                  → painel administrativo
actions/                → handlers de formulários (login, compra, ofertas, chat...)
includes/                → conexão de banco, autenticação, helpers, cliente Stripe
sql/schema.sql          → schema completo do banco (MySQL)
assets/css, assets/js   → tema visual e interações de front-end
docs/INSTALACAO.md      → guia de instalação detalhado
```
