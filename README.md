# Gamers Arena — SaaS de Plataforma Online de Jogos

Plataforma estática (HTML/CSS/JS) de loja online de jogos: recarga direta, gift cards,
vouchers e marketplace de venda de IDs, com design responsivo inspirado no layout de
referência fornecido.

## Funcionalidades

- **Design Responsivo**: desktop, tablet e mobile.
- **Recarga Direta** (`pages/top-up.html`): seleção de pacote, método de pagamento e resumo de compra.
- **Gift Cards** (`pages/gift-card.html`, `pages/gift-card-details.html`): PSN, Google Play, Apple Card, iTunes etc.
- **Vouchers** (`pages/voucher.html`): game cards, payment cards, música e vídeo.
- **Venda de IDs / Marketplace** (`pages/buy-id.html`, `pages/id-details.html`): busca, filtro por preço, categorias e ofertas.
- **Área logada**: Dashboard, My Sales (Offer List + Conversation/chat), My Orders, Payout e History.
- **Autenticação**: Sign In / Sign Up.

## Executar localmente

```bash
python3 -m http.server 8000
```

Depois acesse `http://localhost:8000`.

## Estrutura

```
index.html
pages/               → todas as demais telas
assets/css/style.css → tema visual (dark + laranja)
assets/js/main.js    → interações (menus, seleção de pacote/pagamento, chat, etc.)
```
