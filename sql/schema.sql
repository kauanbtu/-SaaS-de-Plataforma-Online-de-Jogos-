-- Gamers Arena — MySQL 8 schema
-- Import with: mysql -u USUARIO -p NOME_DO_BANCO < sql/schema.sql

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ===================================================================
-- settings: configurações do site editáveis pelo admin (sem valores
-- fixos herdados de outro template)
-- ===================================================================
CREATE TABLE IF NOT EXISTS settings (
  `key`   VARCHAR(80) PRIMARY KEY,
  `value` TEXT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO settings (`key`, `value`) VALUES
  ('site_name', 'Gamers Arena'),
  ('site_phone', ''),
  ('site_email', ''),
  ('site_address', ''),
  ('currency', 'USD'),
  ('discount_rate', '0.15'),
  ('stripe_public_key', ''),
  ('stripe_secret_key', '')
ON DUPLICATE KEY UPDATE `key` = `key`;

-- ===================================================================
-- users
-- ===================================================================
CREATE TABLE IF NOT EXISTS users (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name          VARCHAR(120) NOT NULL,
  email         VARCHAR(190) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role          ENUM('customer','admin') NOT NULL DEFAULT 'customer',
  balance       DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ===================================================================
-- categories (games) for the ID marketplace
-- ===================================================================
CREATE TABLE IF NOT EXISTS categories (
  id    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name  VARCHAR(100) NOT NULL,
  slug  VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ===================================================================
-- listings: IDs / contas de jogos à venda (marketplace)
-- ===================================================================
CREATE TABLE IF NOT EXISTS listings (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  seller_id    INT UNSIGNED NOT NULL,
  category_id  INT UNSIGNED NOT NULL,
  title        VARCHAR(160) NOT NULL,
  description  TEXT,
  price        DECIMAL(12,2) NOT NULL,
  level        VARCHAR(40),
  age_label    VARCHAR(60),
  image_path   VARCHAR(255),
  status       ENUM('active','sold','removed') NOT NULL DEFAULT 'active',
  created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (seller_id)   REFERENCES users(id)      ON DELETE CASCADE,
  FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ===================================================================
-- offers: propostas de compra feitas sobre um listing
-- ===================================================================
CREATE TABLE IF NOT EXISTS offers (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  listing_id  INT UNSIGNED NOT NULL,
  buyer_id    INT UNSIGNED NOT NULL,
  amount      DECIMAL(12,2) NOT NULL,
  message     TEXT,
  status      ENUM('pending','accepted','rejected') NOT NULL DEFAULT 'pending',
  created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (listing_id) REFERENCES listings(id) ON DELETE CASCADE,
  FOREIGN KEY (buyer_id)   REFERENCES users(id)    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ===================================================================
-- messages: chat de negociação vinculado a uma offer
-- ===================================================================
CREATE TABLE IF NOT EXISTS messages (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  offer_id    INT UNSIGNED NOT NULL,
  sender_id   INT UNSIGNED NOT NULL,
  body        TEXT NOT NULL,
  created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (offer_id)  REFERENCES offers(id) ON DELETE CASCADE,
  FOREIGN KEY (sender_id) REFERENCES users(id)  ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ===================================================================
-- orders: pedidos fechados do marketplace de IDs (após aceite da offer)
-- ===================================================================
CREATE TABLE IF NOT EXISTS orders (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  offer_id    INT UNSIGNED NOT NULL,
  listing_id  INT UNSIGNED NOT NULL,
  buyer_id    INT UNSIGNED NOT NULL,
  seller_id   INT UNSIGNED NOT NULL,
  amount      DECIMAL(12,2) NOT NULL,
  status      ENUM('awaiting_payment','paid','delivered','cancelled') NOT NULL DEFAULT 'awaiting_payment',
  created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (offer_id)   REFERENCES offers(id)   ON DELETE CASCADE,
  FOREIGN KEY (listing_id) REFERENCES listings(id) ON DELETE CASCADE,
  FOREIGN KEY (buyer_id)   REFERENCES users(id)    ON DELETE CASCADE,
  FOREIGN KEY (seller_id)  REFERENCES users(id)    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ===================================================================
-- products: gift cards, vouchers e pacotes de recarga (top-up)
-- ===================================================================
CREATE TABLE IF NOT EXISTS products (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  type         ENUM('giftcard','voucher','topup') NOT NULL,
  name         VARCHAR(160) NOT NULL,
  description  TEXT,
  icon         VARCHAR(60) DEFAULT 'fa-solid fa-gift',
  active       TINYINT(1) NOT NULL DEFAULT 1,
  created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ===================================================================
-- product_packages: valores/denominações vendidos de um product
-- ===================================================================
CREATE TABLE IF NOT EXISTS product_packages (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  product_id  INT UNSIGNED NOT NULL,
  label       VARCHAR(80) NOT NULL,
  price       DECIMAL(12,2) NOT NULL,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ===================================================================
-- product_stock: estoque de códigos digitais para entrega automática
-- ===================================================================
CREATE TABLE IF NOT EXISTS product_stock (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  package_id  INT UNSIGNED NOT NULL,
  code        VARCHAR(255) NOT NULL,
  used        TINYINT(1) NOT NULL DEFAULT 0,
  used_by     INT UNSIGNED NULL,
  used_at     DATETIME NULL,
  FOREIGN KEY (package_id) REFERENCES product_packages(id) ON DELETE CASCADE,
  FOREIGN KEY (used_by)    REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ===================================================================
-- purchases: compras de gift card / voucher / top-up com entrega
-- automática do código assim que o pagamento é confirmado
-- ===================================================================
CREATE TABLE IF NOT EXISTS purchases (
  id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id         INT UNSIGNED NULL,
  package_id      INT UNSIGNED NOT NULL,
  player_id       VARCHAR(120) NULL,
  amount          DECIMAL(12,2) NOT NULL,
  discount        DECIMAL(12,2) NOT NULL DEFAULT 0,
  payable         DECIMAL(12,2) NOT NULL,
  payment_method  VARCHAR(40) NOT NULL DEFAULT 'stripe',
  payment_ref     VARCHAR(190) NULL,
  status          ENUM('pending','paid','delivered','failed') NOT NULL DEFAULT 'pending',
  delivered_code  VARCHAR(255) NULL,
  created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id)    REFERENCES users(id) ON DELETE SET NULL,
  FOREIGN KEY (package_id) REFERENCES product_packages(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ===================================================================
-- withdrawals: saques solicitados pelos vendedores
-- ===================================================================
CREATE TABLE IF NOT EXISTS withdrawals (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id      INT UNSIGNED NOT NULL,
  amount       DECIMAL(12,2) NOT NULL,
  method       VARCHAR(60) NOT NULL,
  account_ref  VARCHAR(190) NOT NULL,
  status       ENUM('pending','paid','rejected') NOT NULL DEFAULT 'pending',
  created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ===================================================================
-- contact_messages / newsletter_subscribers: formulários reais
-- ===================================================================
CREATE TABLE IF NOT EXISTS contact_messages (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name        VARCHAR(120) NOT NULL,
  email       VARCHAR(190) NOT NULL,
  subject     VARCHAR(190),
  message     TEXT NOT NULL,
  created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS newsletter_subscribers (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  email       VARCHAR(190) NOT NULL UNIQUE,
  created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;

-- ===================================================================
-- Seed de exemplo (categorias, listings, produtos, pacotes, estoque)
-- ===================================================================
INSERT INTO categories (name, slug) VALUES
  ('Carrom Pool', 'carrom-pool'),
  ('PUBG', 'pubg'),
  ('Free Fire', 'free-fire'),
  ('Call Of Duty', 'call-of-duty'),
  ('Clash Of Clans', 'clash-of-clans'),
  ('Mobile Legend', 'mobile-legend')
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- Usuário admin padrão: admin@example.com / senha: ChangeMe123!
-- (troque a senha assim que instalar — ver docs/INSTALACAO.md)
INSERT INTO users (name, email, password_hash, role)
VALUES ('Administrador', 'admin@example.com',
  '$2y$12$P8FYWTMAnGfXZ8Blwf05Wuop9Du/18ZJ1fRX/qgJpZpp3oVSV4VgG', 'admin')
ON DUPLICATE KEY UPDATE email = VALUES(email);

INSERT INTO products (type, name, description, icon) VALUES
  ('giftcard', 'Apple Card', 'Cartão-presente Apple para App Store, iTunes e serviços Apple.', 'fa-brands fa-apple'),
  ('giftcard', 'PSN Card', 'Cartão PlayStation Network.', 'fa-brands fa-playstation'),
  ('giftcard', 'Google Play', 'Cartão Google Play para apps, jogos e assinaturas.', 'fa-brands fa-google-play'),
  ('voucher',  'Razer Gold', 'Voucher Razer Gold para diversos jogos e plataformas.', 'fa-solid fa-gamepad'),
  ('topup',    'PUBG Mobile UC', 'Recarga direta de UC do PUBG Mobile.', 'fa-solid fa-gun')
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO product_packages (product_id, label, price)
SELECT id, 'Razer Gold 5$', 5.44 FROM products WHERE name = 'Razer Gold' LIMIT 1;
INSERT INTO product_packages (product_id, label, price)
SELECT id, '60 UC', 1.00 FROM products WHERE name = 'PUBG Mobile UC' LIMIT 1;
