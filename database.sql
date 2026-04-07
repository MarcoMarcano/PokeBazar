CREATE DATABASE IF NOT EXISTS pokebazar CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE pokebazar;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    description TEXT NOT NULL,
    category VARCHAR(100) DEFAULT '',
    rarity VARCHAR(100) DEFAULT '',
    image_url VARCHAR(255) DEFAULT '',
    price DECIMAL(10,2) NOT NULL DEFAULT 0,
    stock INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS cart_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    status ENUM('active', 'completed') NOT NULL DEFAULT 'active',
    cart_reference VARCHAR(80) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_cart_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_cart_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS invoices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    cart_reference VARCHAR(80) NOT NULL,
    final_price DECIMAL(10,2) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_invoice_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS invoice_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_id INT NOT NULL,
    product_name VARCHAR(150) NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    CONSTRAINT fk_invoice_item_invoice FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE
);

INSERT INTO users (first_name, last_name, email, password, role)
SELECT 'Admin', 'PokeBazar', 'admin@pokebazar.com', 'admin123', 'admin'
WHERE NOT EXISTS (SELECT 1 FROM users WHERE email = 'admin@pokebazar.com');

INSERT INTO products (name, description, category, rarity, image_url, price, stock)
SELECT 'Vmax Charizard Shiny', 'Carta destacada para coleccionistas y jugadores que buscan impacto visual y poder competitivo.', 'Carta individual', 'Ultra rara', 'assets/images/1600 (1).jpg', 159.99, 8
WHERE NOT EXISTS (SELECT 1 FROM products WHERE name = 'Vmax Charizard Shiny');

INSERT INTO products (name, description, category, rarity, image_url, price, stock)
SELECT 'Champions Path Elite Trainer Box', 'Caja premium con sobres, accesorios y cartas promocionales para ampliar tu coleccion.', 'Box sellado', 'Edicion especial', 'assets/images/71G8-pFVOXL.jpg', 109.99, 12
WHERE NOT EXISTS (SELECT 1 FROM products WHERE name = 'Champions Path Elite Trainer Box');

INSERT INTO products (name, description, category, rarity, image_url, price, stock)
SELECT 'Pikachu Full Art', 'Carta ilustrada ideal para fans de Pikachu y colecciones tematicas.', 'Carta individual', 'Full Art', 'assets/images/this-full-art-pikachu-promo-looks-so-cool-v0-akxvoFgSXpBo5KZt0iCOK1xHKt1QwY45EwtuLe6yHVc.webp', 47.25, 15
WHERE NOT EXISTS (SELECT 1 FROM products WHERE name = 'Pikachu Full Art');

INSERT INTO products (name, description, category, rarity, image_url, price, stock)
SELECT 'Pokemon Mega Evolutions Booster Pack', 'Sobre individual de la expansion Mega Evolutions, ideal para aperturas y coleccion.', 'Booster pack', 'Expansion', 'assets/images/P10346_10-10055-108_01.jpg', 8.99, 40
WHERE NOT EXISTS (SELECT 1 FROM products WHERE name = 'Pokemon Mega Evolutions Booster Pack');

INSERT INTO products (name, description, category, rarity, image_url, price, stock)
SELECT 'Pokemon Mega Evolutions Booster Box', 'Caja sellada Mega Evolutions con multiples sobres para jugadores y coleccionistas.', 'Booster box', 'Expansion', 'assets/images/P10346_10-10057-127_01.jpg', 189.99, 10
WHERE NOT EXISTS (SELECT 1 FROM products WHERE name = 'Pokemon Mega Evolutions Booster Box');

INSERT INTO products (name, description, category, rarity, image_url, price, stock)
SELECT 'Pokemon Mega Evolutions Elite Trainer Box Lucario', 'Elite Trainer Box tematica de Lucario con accesorios y sobres Mega Evolutions.', 'Elite trainer box', 'Coleccion premium', 'assets/images/81XWQX2CYuL._AC_UF894,1000_QL80_.jpg', 79.99, 8
WHERE NOT EXISTS (SELECT 1 FROM products WHERE name = 'Pokemon Mega Evolutions Elite Trainer Box Lucario');

INSERT INTO products (name, description, category, rarity, image_url, price, stock)
SELECT 'Pokemon Mega Evolutions Elite Trainer Box Gardevoir', 'Elite Trainer Box tematica de Gardevoir pensada para coleccion y juego organizado.', 'Elite trainer box', 'Coleccion premium', 'assets/images/81HduOvXc-L._AC_UF894,1000_QL80_.jpg', 79.99, 8
WHERE NOT EXISTS (SELECT 1 FROM products WHERE name = 'Pokemon Mega Evolutions Elite Trainer Box Gardevoir');

INSERT INTO products (name, description, category, rarity, image_url, price, stock)
SELECT 'Pokemon Mega Evolutions Phantasmal Flames Booster Pack', 'Sobre Phantasmal Flames de Mega Evolutions con cartas de alto interes para la coleccion.', 'Booster pack', 'Expansion especial', 'assets/images/71JWrWHUBnL._AC_UF894,1000_QL80_.jpg', 9.49, 36
WHERE NOT EXISTS (SELECT 1 FROM products WHERE name = 'Pokemon Mega Evolutions Phantasmal Flames Booster Pack');

INSERT INTO products (name, description, category, rarity, image_url, price, stock)
SELECT 'Pokemon Mega Evolutions Phantasmal Flames Booster Box', 'Booster Box Phantasmal Flames para aperturas completas o ventas por sobre.', 'Booster box', 'Expansion especial', 'assets/images/P10347_10-10190-101_01.jpg', 199.99, 9
WHERE NOT EXISTS (SELECT 1 FROM products WHERE name = 'Pokemon Mega Evolutions Phantasmal Flames Booster Box');

INSERT INTO products (name, description, category, rarity, image_url, price, stock)
SELECT 'Pokemon Mega Evolutions Phantasmal Flames Elite Trainer Box', 'Caja Elite Trainer Box de Phantasmal Flames con contenido premium para jugadores.', 'Elite trainer box', 'Expansion especial', 'assets/images/ph-etb-4.webp', 84.99, 7
WHERE NOT EXISTS (SELECT 1 FROM products WHERE name = 'Pokemon Mega Evolutions Phantasmal Flames Elite Trainer Box');

INSERT INTO products (name, description, category, rarity, image_url, price, stock)
SELECT 'Pokemon Mega Evolutions Perfect Order Booster Pack', 'Sobre Perfect Order de Mega Evolutions con arte coleccionable y cartas de estreno.', 'Booster pack', 'Expansion especial', 'assets/images/P11218_10-10380-119_03.jpg', 9.29, 36
WHERE NOT EXISTS (SELECT 1 FROM products WHERE name = 'Pokemon Mega Evolutions Perfect Order Booster Pack');

INSERT INTO products (name, description, category, rarity, image_url, price, stock)
SELECT 'Pokemon Mega Evolutions Perfect Order Booster Box', 'Caja sellada Perfect Order pensada para aperturas grandes o reventa por unidades.', 'Booster box', 'Expansion especial', 'assets/images/P11218_10-10380-119_01.jpg', 194.99, 9
WHERE NOT EXISTS (SELECT 1 FROM products WHERE name = 'Pokemon Mega Evolutions Perfect Order Booster Box');

INSERT INTO products (name, description, category, rarity, image_url, price, stock)
SELECT 'Pokemon Mega Evolutions Perfect Order Elite Trainer Box', 'Elite Trainer Box Perfect Order con accesorios, sobres y presentacion premium.', 'Elite trainer box', 'Expansion especial', 'assets/images/P11218_10-10372-109_01.jpg', 82.99, 7
WHERE NOT EXISTS (SELECT 1 FROM products WHERE name = 'Pokemon Mega Evolutions Perfect Order Elite Trainer Box');

INSERT INTO products (name, description, category, rarity, image_url, price, stock)
SELECT 'Pokemon Journey Together Booster Pack', 'Sobre Journey Together orientado a aperturas casuales y nuevas incorporaciones al mazo.', 'Booster pack', 'Expansion moderna', 'assets/images/P10344_100-10341_02.jpg', 6.99, 48
WHERE NOT EXISTS (SELECT 1 FROM products WHERE name = 'Pokemon Journey Together Booster Pack');

INSERT INTO products (name, description, category, rarity, image_url, price, stock)
SELECT 'Pokemon Journey Together Booster Box', 'Booster Box Journey Together con precio aproximado de mercado para expansion moderna.', 'Booster box', 'Expansion moderna', 'assets/images/POK1010125101_1.webp', 134.99, 12
WHERE NOT EXISTS (SELECT 1 FROM products WHERE name = 'Pokemon Journey Together Booster Box');

INSERT INTO products (name, description, category, rarity, image_url, price, stock)
SELECT 'Pokemon Journey Together Elite Trainer Box', 'Elite Trainer Box Journey Together con sleeves, dados y sobres para entrenamiento.', 'Elite trainer box', 'Expansion moderna', 'assets/images/P10344_100-10356_01.jpg', 49.99, 10
WHERE NOT EXISTS (SELECT 1 FROM products WHERE name = 'Pokemon Journey Together Elite Trainer Box');

INSERT INTO products (name, description, category, rarity, image_url, price, stock)
SELECT 'Pokemon Destined Rivals Booster Pack', 'Sobre Destined Rivals con cartas recientes y buen punto de entrada para coleccionistas.', 'Booster pack', 'Expansion moderna', 'assets/images/P10345_100-10623_01.jpg', 7.49, 48
WHERE NOT EXISTS (SELECT 1 FROM products WHERE name = 'Pokemon Destined Rivals Booster Pack');

INSERT INTO products (name, description, category, rarity, image_url, price, stock)
SELECT 'Pokemon Destined Rivals Booster Box', 'Caja completa Destined Rivals con valor estimado para venta de sealed product.', 'Booster box', 'Expansion moderna', 'assets/images/Pokemon-DestinedRivalsBoosterBox.webp', 144.99, 12
WHERE NOT EXISTS (SELECT 1 FROM products WHERE name = 'Pokemon Destined Rivals Booster Box');

INSERT INTO products (name, description, category, rarity, image_url, price, stock)
SELECT 'Pokemon Destined Rivals Elite Trainer Box', 'Elite Trainer Box Destined Rivals con accesorios y sobres para jugadores y coleccionistas.', 'Elite trainer box', 'Expansion moderna', 'assets/images/P10345_100-10653_01.jpg', 54.99, 10
WHERE NOT EXISTS (SELECT 1 FROM products WHERE name = 'Pokemon Destined Rivals Elite Trainer Box');

INSERT INTO products (name, description, category, rarity, image_url, price, stock)
SELECT 'Binder Pokemon XY', 'Binder para cartas Pokemon de la era XY, pensado para proteger y organizar la coleccion.', 'Binder', 'Accesorio', 'assets/images/91xBt87pwpL.jpg', 24.99, 14
WHERE NOT EXISTS (SELECT 1 FROM products WHERE name = 'Binder Pokemon XY');

INSERT INTO products (name, description, category, rarity, image_url, price, stock)
SELECT 'Binder Pokemon Prismatic Evolutions + 5 Booster Packs', 'Binder tematico de Prismatic Evolutions que incluye 5 booster packs para iniciar o ampliar la coleccion.', 'Bundle', 'Accesorio premium', 'assets/images/P10406_10-10023-101_05.jpg', 44.99, 10
WHERE NOT EXISTS (SELECT 1 FROM products WHERE name = 'Binder Pokemon Prismatic Evolutions + 5 Booster Packs');

INSERT INTO products (name, description, category, rarity, image_url, price, stock)
SELECT 'Binder Pokemon Mega Evolutions', 'Binder de Pokemon Mega Evolutions para almacenar cartas destacadas de la expansion y piezas de coleccion.', 'Binder', 'Accesorio premium', 'assets/images/ultra-pro-9-pocket-portfolio-mega-evolutions.webp', 29.99, 12
WHERE NOT EXISTS (SELECT 1 FROM products WHERE name = 'Binder Pokemon Mega Evolutions');

UPDATE products SET name = 'Vmax Charizard Shiny', image_url = 'assets/images/1600 (1).jpg' WHERE name = 'Charizard VMAX';
UPDATE products SET image_url = 'assets/images/1600 (1).jpg' WHERE name = 'Vmax Charizard Shiny';
UPDATE products SET name = 'Champions Path Elite Trainer Box', image_url = 'assets/images/71G8-pFVOXL.jpg', price = 109.99 WHERE name = 'Elite Trainer Box';
UPDATE products SET image_url = 'assets/images/71G8-pFVOXL.jpg', price = 109.99 WHERE name = 'Champions Path Elite Trainer Box';
UPDATE products SET image_url = 'assets/images/this-full-art-pikachu-promo-looks-so-cool-v0-akxvoFgSXpBo5KZt0iCOK1xHKt1QwY45EwtuLe6yHVc.webp' WHERE name = 'Pikachu Full Art';
UPDATE products SET image_url = 'assets/images/P10346_10-10055-108_01.jpg' WHERE name = 'Pokemon Mega Evolutions Booster Pack';
UPDATE products SET image_url = 'assets/images/P10346_10-10057-127_01.jpg' WHERE name = 'Pokemon Mega Evolutions Booster Box';
UPDATE products SET image_url = 'assets/images/81XWQX2CYuL._AC_UF894,1000_QL80_.jpg' WHERE name = 'Pokemon Mega Evolutions Elite Trainer Box Lucario';
UPDATE products SET image_url = 'assets/images/81HduOvXc-L._AC_UF894,1000_QL80_.jpg' WHERE name = 'Pokemon Mega Evolutions Elite Trainer Box Gardevoir';
UPDATE products SET image_url = 'assets/images/71JWrWHUBnL._AC_UF894,1000_QL80_.jpg' WHERE name = 'Pokemon Mega Evolutions Phantasmal Flames Booster Pack';
UPDATE products SET image_url = 'assets/images/P10347_10-10190-101_01.jpg' WHERE name = 'Pokemon Mega Evolutions Phantasmal Flames Booster Box';
UPDATE products SET image_url = 'assets/images/ph-etb-4.webp' WHERE name = 'Pokemon Mega Evolutions Phantasmal Flames Elite Trainer Box';
UPDATE products SET image_url = 'assets/images/P11218_10-10380-119_03.jpg' WHERE name = 'Pokemon Mega Evolutions Perfect Order Booster Pack';
UPDATE products SET image_url = 'assets/images/P11218_10-10380-119_01.jpg' WHERE name = 'Pokemon Mega Evolutions Perfect Order Booster Box';
UPDATE products SET image_url = 'assets/images/P11218_10-10372-109_01.jpg' WHERE name = 'Pokemon Mega Evolutions Perfect Order Elite Trainer Box';
UPDATE products SET image_url = 'assets/images/P10344_100-10341_02.jpg' WHERE name = 'Pokemon Journey Together Booster Pack';
UPDATE products SET image_url = 'assets/images/POK1010125101_1.webp' WHERE name = 'Pokemon Journey Together Booster Box';
UPDATE products SET image_url = 'assets/images/P10344_100-10356_01.jpg' WHERE name = 'Pokemon Journey Together Elite Trainer Box';
UPDATE products SET image_url = 'assets/images/P10345_100-10623_01.jpg' WHERE name = 'Pokemon Destined Rivals Booster Pack';
UPDATE products SET image_url = 'assets/images/Pokemon-DestinedRivalsBoosterBox.webp' WHERE name = 'Pokemon Destined Rivals Booster Box';
UPDATE products SET image_url = 'assets/images/P10345_100-10653_01.jpg' WHERE name = 'Pokemon Destined Rivals Elite Trainer Box';
UPDATE products SET image_url = 'assets/images/91xBt87pwpL.jpg' WHERE name = 'Binder Pokemon XY';
UPDATE products SET image_url = 'assets/images/P10406_10-10023-101_05.jpg' WHERE name = 'Binder Pokemon Prismatic Evolutions + 5 Booster Packs';
UPDATE products SET image_url = 'assets/images/ultra-pro-9-pocket-portfolio-mega-evolutions.webp' WHERE name = 'Binder Pokemon Mega Evolutions';
