# Guia de Instalação — Gamers Arena

Este guia cobre instalação em hospedagem compartilhada (cPanel) e em servidor
próprio (VPS). Tempo estimado: 10–15 minutos.

## 1. Requisitos

- PHP **8.1 ou superior** (testado em 8.1–8.4)
- MySQL **5.7+** ou MariaDB **10.2+**
- Extensões PHP: `BCMath`, `Ctype`, `Fileinfo`, `JSON`, `Mbstring`, `OpenSSL`,
  `PDO`, `PDO_MYSQL`, `Tokenizer`, `XML`, `CURL`, `GD`, `GMP`
  (praticamente todo hosting PHP moderno já vem com essas extensões ativas;
  em cPanel, confirme em "Select PHP Version" → "Extensions")
- Não é necessário Composer, Node.js ou build step — é PHP puro.

## 2. Enviar os arquivos

1. Faça upload de todo o conteúdo do projeto para a pasta pública do seu
   domínio (`public_html/`, `htdocs/` ou equivalente).
2. Garanta que a pasta `uploads/` tenha permissão de escrita (`755` ou `775`).

## 3. Criar o banco de dados

No painel da hospedagem (ou via linha de comando):

```bash
mysql -u SEU_USUARIO -p -e "CREATE DATABASE gamers_arena CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u SEU_USUARIO -p gamers_arena < sql/schema.sql
```

Em cPanel: crie o banco e o usuário em "MySQL Databases", associe o usuário
ao banco com todos os privilégios, depois importe `sql/schema.sql` pelo
phpMyAdmin (aba "Importar").

## 4. Configurar o `.env`

Copie `.env.example` para `.env` e preencha:

```
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=gamers_arena
DB_USERNAME=seu_usuario
DB_PASSWORD=sua_senha

APP_URL=https://seudominio.com
APP_ENV=production

STRIPE_PUBLIC_KEY=
STRIPE_SECRET_KEY=
STRIPE_WEBHOOK_SECRET=
```

> **Sem chaves da Stripe**, o checkout fica **bloqueado** (nenhuma compra é
> liberada) até você configurar pagamentos — o site nunca entrega produtos
> de graça por padrão. Para testar o fluxo completo sem cobrar de verdade,
> ative manualmente o **modo demonstração** em `/admin/settings.php`
> (desligado por padrão; o painel mostra um aviso permanente enquanto
> estiver ativo — desligue antes de divulgar o site). Para cobrar de
> verdade, crie uma conta em [stripe.com](https://stripe.com), pegue as
> chaves em [dashboard.stripe.com/apikeys](https://dashboard.stripe.com/apikeys)
> e cole-as em `/admin` → Configurações (ou aqui no `.env` — quando
> preenchidas no `.env`, elas têm prioridade sobre o painel).
>
> Com a moeda do site definida como `BRL`, o **Pix** é oferecido
> automaticamente como forma de pagamento ao lado do cartão (requer conta
> Stripe habilitada para o Brasil).

### 4.1 Webhook da Stripe (recomendado)

Sem webhook, a compra só é confirmada quando o comprador volta ao site
depois de pagar. Se ele fechar a aba antes disso, o pedido fica pendente.
Para evitar isso:

1. Em [dashboard.stripe.com/webhooks](https://dashboard.stripe.com/webhooks), crie um endpoint apontando para:
   ```
   https://seudominio.com/actions/stripe_webhook.php
   ```
2. Selecione o evento `checkout.session.completed`.
3. Copie o "signing secret" (`whsec_...`) gerado e cole em `STRIPE_WEBHOOK_SECRET`
   no `.env`, ou em `/admin` → Configurações.

## 5. Acessar o site

Abra `https://seudominio.com/index.php` no navegador.

Login administrativo padrão:

- **URL:** `/admin/login.php`
- **E-mail:** `admin@example.com`
- **Senha:** `ChangeMe123!`

**A troca dessa senha é obrigatória**: no primeiro login, o sistema
redireciona automaticamente para a tela de troca de senha e bloqueia
qualquer outra página até uma nova senha ser definida — não é preciso
lembrar de trocar manualmente.

## 6. Primeiros passos no painel admin

1. **Configurações** (`/admin/settings.php`): defina nome do site, telefone,
   e-mail, endereço, moeda e (opcionalmente) as chaves da Stripe.
2. **Produtos** (`/admin/products.php`): cadastre seus gift cards, vouchers e
   pacotes de recarga, com os respectivos preços.
3. **Estoque** (`/admin/stock.php`): cole os códigos digitais (um por linha)
   de cada pacote. É esse estoque que alimenta a **entrega automática** —
   assim que um pagamento é confirmado, um código é reservado e entregue
   automaticamente ao comprador.
4. **Marketplace** (`/admin/listings.php`): modere os anúncios de IDs
   publicados pelos usuários.
5. **Saques** (`/admin/withdrawals.php`): aprove ou rejeite pedidos de saque
   dos vendedores.

## 7. Segurança pós-instalação

- A troca da senha do admin padrão já é forçada automaticamente (item 5) —
  não pule essa etapa no primeiro acesso.
- Restrinja o arquivo `.env` (permissão `600`) e confirme que ele **não**
  está acessível publicamente (`https://seudominio.com/.env` deve dar 403/404
  — na maioria dos hosts PHP isso já é bloqueado por padrão porque o arquivo
  não é interpretado, mas confira).
- Ative HTTPS (a maioria dos hosts oferece Let's Encrypt grátis).
- Configure o webhook da Stripe (seção 4.1) antes de divulgar o site.
- Confirme que o **modo demonstração** está desligado em
  `/admin/settings.php` antes de divulgar o site publicamente.
- Faça backup regular do banco de dados.

## 8. Problemas comuns

| Sintoma | Causa provável | Solução |
|---|---|---|
| Tela "Não foi possível conectar ao banco de dados" | `.env` errado ou schema não importado | Revise `DB_*` no `.env` e rode o import do passo 3 |
| Formulários retornam "Sessão expirada" | Cache/proxy servindo página antiga | Recarregue a página antes de enviar o formulário |
| Compra fica "pending" e não entrega código | Estoque zerado para aquele pacote, ou webhook não configurado e o comprador fechou a aba antes de voltar | Adicione códigos em `/admin/stock.php`; configure o webhook (seção 4.1) |
| Checkout diz "pagamentos ainda não configurados" | Nenhuma chave da Stripe definida e modo demonstração desligado (comportamento padrão, proposital) | Configure a Stripe ou ative o modo demonstração em `/admin/settings.php` |
| Sistema pede para trocar a senha e não deixa acessar mais nada | Comportamento esperado da conta com senha padrão/temporária | Troque a senha na tela exibida — o acesso volta ao normal em seguida |

## 9. Sobre a documentação em vídeo

Este pacote inclui documentação escrita completa (este arquivo). A
gravação de vídeos passo a passo está pendente de produção e será
disponibilizada separadamente — qualquer dúvida durante a instalação,
entre em contato pelo suporte.
