-- ============================================================
-- DRIP ZONE — Men's Wear Booking System
-- Database Schema
-- ============================================================

CREATE DATABASE IF NOT EXISTS drip_zone
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE drip_zone;

-- ============================================================
-- TABLE: Admin_Users
-- ============================================================
CREATE TABLE IF NOT EXISTS Admin_Users (
    id          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    username    VARCHAR(60)   NOT NULL UNIQUE,
    password    VARCHAR(255)  NOT NULL,
    full_name   VARCHAR(120)  NOT NULL DEFAULT '',
    created_at  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Default: username=admin | password=Admin@1234
INSERT INTO Admin_Users (username, password, full_name)
VALUES (
    'admin',
    '$2y$12$7V3v1TKE/Cq3sG.yzFHD9.IkHKbpP5FBFMaSl7U5FNJzPl9IUV6u',
    'Store Manager'
);

-- ============================================================
-- TABLE: Stock
-- ============================================================
CREATE TABLE IF NOT EXISTS Stock (
    id              INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    item_name       VARCHAR(180)     NOT NULL,
    description     TEXT                      DEFAULT NULL,
    category        VARCHAR(80)      NOT NULL DEFAULT 'Uncategorized',
    image_path      VARCHAR(300)              DEFAULT NULL,
    price           DECIMAL(10,2)    NOT NULL DEFAULT 0.00,
    stock_qty       INT              NOT NULL DEFAULT 0,
    sizes           VARCHAR(200)              DEFAULT 'S,M,L,XL,XXL',
    display_status  TINYINT(1)       NOT NULL DEFAULT 1,
    created_at      DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_display  (display_status),
    INDEX idx_category (category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- SEED DATA — Men's Wear Products
-- ============================================================
INSERT INTO Stock (item_name, description, category, price, stock_qty, sizes, display_status) VALUES

-- TEES
('Oversized Graphic Tee',       'Bold 300GSM cotton drop-shoulder tee. Front chest print. Street-ready.',     'Tees',      799.00,  50, 'S,M,L,XL,XXL', 1),
('Acid Wash Vintage Tee',       'Washed-out vintage feel, faded colors, relaxed unisex fit.',                 'Tees',      899.00,  35, 'S,M,L,XL',     1),
('Tie-Dye Chaos Tee',           'Every piece unique. Spiral tie-dye in neon palette. 100% cotton.',           'Tees',     1199.00,  20, 'S,M,L,XL,XXL', 1),
('Slogan Drop-Shoulder Tee',    '"No Sleep" slogan front print. Ultra-soft 200GSM jersey.',                   'Tees',      699.00,  60, 'S,M,L,XL,XXL', 1),

-- SHIRTS
('Relaxed Linen Shirt',         'Premium linen blend. Cuban collar. Breezy summer wear.',                     'Shirts',   1499.00,  30, 'S,M,L,XL,XXL', 1),
('Hawaiian Print Shirt',        'Loud botanical print, resort style. Camp collar, coconut buttons.',           'Shirts',   1299.00,  25, 'S,M,L,XL',     1),
('Flannel Check Shirt',         'Heavy brushed flannel. Classic grunge plaid check.',                         'Shirts',   1199.00,  40, 'S,M,L,XL,XXL', 1),
('Oversized Denim Shirt',       'Washed denim shirt with raw hem. Layer or wear alone.',                      'Shirts',   1799.00,  18, 'S,M,L,XL',     1),

-- JEANS
('Baggy Wide-Leg Jeans',        '90s silhouette. Super wide leg, high rise. Dark indigo wash.',               'Jeans',    2499.00,  22, '28,30,32,34,36', 1),
('Distressed Skinny Jeans',     'Ripped knees, faded washes, slim taper. Street classic.',                   'Jeans',    1999.00,  30, '28,30,32,34',   1),
('Straight Cut Raw Denim',      'Unwashed raw selvedge denim. Breaks-in to your body.',                      'Jeans',    2999.00,  15, '30,32,34,36',   1),

-- CARGO
('Utility Cargo Pants',         '8-pocket heavy canvas cargo. Drawstring ankles, relaxed fit.',               'Cargo',    1899.00,  28, '28,30,32,34,36', 1),
('Parachute Cargo Pants',       'Lightweight nylon parachute fabric. Y2K aesthetic. Side zips.',              'Cargo',    2199.00,  20, '28,30,32,34',   1),
('Cargo Shorts',                'Multi-pocket above-knee shorts. Summer street.',                             'Cargo',     999.00,  45, '28,30,32,34,36', 1),

-- HOODIES
('Oversized Pullover Hoodie',   '500GSM heavyweight fleece. Kangaroo pocket, dropped shoulders.',             'Hoodies',  1999.00,  35, 'S,M,L,XL,XXL', 1),
('Zip-Up Hoodie',               'Full zip, thick fleece, ribbed cuffs. Staple outerwear.',                   'Hoodies',  2199.00,  25, 'S,M,L,XL,XXL', 1),
('Cropped Hoodie',              'Crop length, boxy fit, washed finish. Pair with cargos.',                   'Hoodies',  1699.00,  30, 'S,M,L,XL',     1),

-- JACKETS
('Puffer Jacket',               'Quilted puffer, oversized silhouette, packable. Cold weather ready.',       'Jackets',  3499.00,  12, 'S,M,L,XL,XXL', 1),
('Vintage Denim Jacket',        'Washed trucker jacket. Sticker-bomb it yourself. Canvas lining.',           'Jackets',  2799.00,  18, 'S,M,L,XL',     1),
('Windbreaker',                 'Lightweight shell. Color-block print, packable into pocket.',                'Jackets',  2299.00,  20, 'S,M,L,XL,XXL', 1),

-- JOGGERS & SWEATS
('Sweat Joggers',               'Brushed fleece interior, tapered ankle, elastic waist. All-day.',           'Joggers',  1299.00,  40, 'S,M,L,XL,XXL', 1),
('Track Pants',                 'Retro side stripe, lightweight. 90s athletic revival.',                     'Joggers',  1499.00,  35, 'S,M,L,XL,XXL', 1),

-- ACCESSORIES
('Streetwear Snapback Cap',     '6-panel snapback with flat brim. Embroidered logo patch.',                  'Accessories', 699.00, 60, 'One Size',     1),
('Bucket Hat',                  'Reversible canvas/mesh bucket. Summer essential.',                          'Accessories', 599.00, 55, 'One Size',     1),
('Chunky Chain Necklace',       'Silver-tone stainless steel chunky link. Streetwear staple.',               'Accessories', 499.00, 80, 'One Size',     1),
('Crossbody Sling Bag',         '4L compact sling. Adjustable strap, multiple pockets. Y2K vibes.',         'Accessories', 899.00, 30, 'One Size',     1);

-- ============================================================
-- TABLE: Bills
-- ============================================================
CREATE TABLE IF NOT EXISTS Bills (
    bill_id         INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    customer_name   VARCHAR(120)   NOT NULL DEFAULT 'Walk-in Customer',
    customer_phone  VARCHAR(20)             DEFAULT NULL,
    customer_email  VARCHAR(180)            DEFAULT NULL,
    total_amount    DECIMAL(12,2)  NOT NULL DEFAULT 0.00,
    payment_method  VARCHAR(40)    NOT NULL DEFAULT 'Cash',
    bill_date       DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    notes           TEXT                    DEFAULT NULL,
    PRIMARY KEY (bill_id),
    INDEX idx_date (bill_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: Bill_Items
-- ============================================================
CREATE TABLE IF NOT EXISTS Bill_Items (
    id          INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    bill_id     INT UNSIGNED   NOT NULL,
    stock_id    INT UNSIGNED            DEFAULT NULL,
    item_name   VARCHAR(180)   NOT NULL,
    size_chosen VARCHAR(10)             DEFAULT NULL,
    unit_price  DECIMAL(10,2)  NOT NULL,
    quantity    INT            NOT NULL DEFAULT 1,
    line_total  DECIMAL(12,2)  NOT NULL,
    PRIMARY KEY (id),
    INDEX idx_bill (bill_id),
    CONSTRAINT fk_bill_items_bill
        FOREIGN KEY (bill_id) REFERENCES Bills (bill_id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
