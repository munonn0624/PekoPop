-- --------------------------------------------------
-- PekoPop Blindbox Store - SQL Schema Dump
-- --------------------------------------------------
-- Created: 2025-10-12
-- Database: pekopop
-- --------------------------------------------------

CREATE DATABASE IF NOT EXISTS pekopop
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE pekopop;

-- Users Table
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100),
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO users(full_name,email,password_hash,phone) VALUE('Jayden Lee','munonn1234@gmail.com','$2y$10$oQ1dzxAcm0KazJ8Tlp2bDuMvtl5RGtkP9k8khEq6Xy1hMWqaOXotu','60123456789');

CREATE TABLE addresses (
    address_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    recipient_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    address_line1 VARCHAR(255) NOT NULL,
    address_line2 VARCHAR(255),
    city VARCHAR(100) NOT NULL,
    state VARCHAR(100) NOT NULL,
    postal_code VARCHAR(20) NOT NULL,
    country VARCHAR(100) DEFAULT 'Malaysia',
    is_default BOOLEAN DEFAULT FALSE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

-- Admins Table
CREATE TABLE admins (
    admin_id INT AUTO_INCREMENT PRIMARY KEY,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Categories Table
CREATE TABLE categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL
);

INSERT INTO categories(name) VALUES ("Twinkle");
INSERT INTO categories(name) VALUES ("Labubu");

-- Products Table
CREATE TABLE products (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(category_id)
);

CREATE TABLE product_images (
    image_id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    image_url VARCHAR(255) NOT NULL,
    is_main BOOLEAN DEFAULT FALSE,
    is_detail BOOLEAN DEFAULT FALSE,        
    set_type ENUM('Single', 'Whole') DEFAULT NULL,

    FOREIGN KEY (product_id) REFERENCES products(product_id)
);

INSERT INTO products(category_id,name,description,price) VALUES (1,"Twinkle Twinkle Create Your Taste Series Figures","Testing 123", 59.90);
INSERT INTO products(category_id,name,description,price) VALUES (1,"We are Twinkle Twinkle Series - Plush Pendant Blind Box","Testing 456", 49.90);
INSERT INTO products(category_id,name,description,price) VALUES (1,"Twinkle Twinkle Be a Little Star Series - Plush Pendant Blind Box","Testing 789", 54.99);
INSERT INTO products(category_id,name,description,price) VALUES (2,"THE LABUBU - Pin for Love Series - Vinyl Plush Pendant Blind Box","Testing abc", 39.90);
INSERT INTO products(category_id,name,description,price) VALUES (2,"THE LABUBU - Have a Seat Vinyl PLush Blind Box","Testing fgk", 79.90);

INSERT INTO product_images(product_id,image_url,is_main) VALUES (1,"/PekoPop/static/images/blindbox/twinkle/twinkle1/main.jpg",TRUE);
INSERT INTO product_images(product_id,image_url) VALUES (1,"/PekoPop/static/images/blindbox/twinkle/twinkle1/1.jpg");
INSERT INTO product_images(product_id,image_url) VALUES (1,"/PekoPop/static/images/blindbox/twinkle/twinkle1/2.jpg");
INSERT INTO product_images(product_id,image_url) VALUES (1,"/PekoPop/static/images/blindbox/twinkle/twinkle1/3.jpg");
INSERT INTO product_images(product_id,image_url) VALUES (1,"/PekoPop/static/images/blindbox/twinkle/twinkle1/4.jpg");
INSERT INTO product_images(product_id,image_url,is_detail) VALUES (1,"/PekoPop/static/images/blindbox/twinkle/twinkle1/info1.jpg",TRUE);
INSERT INTO product_images(product_id,image_url,is_detail) VALUES (1,"/PekoPop/static/images/blindbox/twinkle/twinkle1/info2.jpg",TRUE);
INSERT INTO product_images(product_id,image_url,is_detail) VALUES (1,"/PekoPop/static/images/blindbox/twinkle/twinkle1/info3.jpg",TRUE);
INSERT INTO product_images(product_id,image_url,is_detail) VALUES (1,"/PekoPop/static/images/blindbox/twinkle/twinkle1/info4.jpg",TRUE);
INSERT INTO product_images(product_id,image_url,set_type) VALUES (1,"/PekoPop/static/images/blindbox/twinkle/twinkle1/single.jpg",'Single');
INSERT INTO product_images(product_id,image_url,set_type) VALUES (1,"/PekoPop/static/images/blindbox/twinkle/twinkle1/whole.jpg",'Whole');

INSERT INTO product_images(product_id,image_url,is_main) VALUES (2,"/PekoPop/static/images/blindbox/twinkle/twinkle2/main.jpg",TRUE);
INSERT INTO product_images(product_id,image_url) VALUES (2,"/PekoPop/static/images/blindbox/twinkle/twinkle2/1.jpg");
INSERT INTO product_images(product_id,image_url) VALUES (2,"/PekoPop/static/images/blindbox/twinkle/twinkle2/2.jpg");
INSERT INTO product_images(product_id,image_url) VALUES (2,"/PekoPop/static/images/blindbox/twinkle/twinkle2/3.jpg");
INSERT INTO product_images(product_id,image_url) VALUES (2,"/PekoPop/static/images/blindbox/twinkle/twinkle2/4.jpg");
INSERT INTO product_images(product_id,image_url,is_detail) VALUES (2,"/PekoPop/static/images/blindbox/twinkle/twinkle2/info1.jpg",TRUE);
INSERT INTO product_images(product_id,image_url,is_detail) VALUES (2,"/PekoPop/static/images/blindbox/twinkle/twinkle2/info2.jpg",TRUE);
INSERT INTO product_images(product_id,image_url,is_detail) VALUES (2,"/PekoPop/static/images/blindbox/twinkle/twinkle2/info3.jpg",TRUE);
INSERT INTO product_images(product_id,image_url,is_detail) VALUES (2,"/PekoPop/static/images/blindbox/twinkle/twinkle2/info4.jpg",TRUE);
INSERT INTO product_images(product_id,image_url,is_detail) VALUES (2,"/PekoPop/static/images/blindbox/twinkle/twinkle2/info5.jpg",TRUE);
INSERT INTO product_images(product_id,image_url,set_type) VALUES (2,"/PekoPop/static/images/blindbox/twinkle/twinkle2/single.jpg", 'Single');
INSERT INTO product_images(product_id,image_url,set_type) VALUES (2,"/PekoPop/static/images/blindbox/twinkle/twinkle2/whole.jpg", 'Whole');

-- Product 3
INSERT INTO product_images(product_id, image_url, is_main) VALUES (3, "/PekoPop/static/images/blindbox/twinkle/twinkle3/main.jpg", TRUE);
INSERT INTO product_images(product_id, image_url) VALUES (3, "/PekoPop/static/images/blindbox/twinkle/twinkle3/1.jpg");
INSERT INTO product_images(product_id, image_url) VALUES (3, "/PekoPop/static/images/blindbox/twinkle/twinkle3/2.jpg");
INSERT INTO product_images(product_id, image_url) VALUES (3, "/PekoPop/static/images/blindbox/twinkle/twinkle3/3.jpg");
INSERT INTO product_images(product_id, image_url) VALUES (3, "/PekoPop/static/images/blindbox/twinkle/twinkle3/4.jpg");
INSERT INTO product_images(product_id, image_url, is_detail) VALUES (3, "/PekoPop/static/images/blindbox/twinkle/twinkle3/info1.jpg", TRUE);
INSERT INTO product_images(product_id, image_url, is_detail) VALUES (3, "/PekoPop/static/images/blindbox/twinkle/twinkle3/info2.jpg", TRUE);
INSERT INTO product_images(product_id, image_url, is_detail) VALUES (3, "/PekoPop/static/images/blindbox/twinkle/twinkle3/info3.jpg", TRUE);
INSERT INTO product_images(product_id, image_url, is_detail) VALUES (3, "/PekoPop/static/images/blindbox/twinkle/twinkle3/info4.jpg", TRUE);
INSERT INTO product_images(product_id, image_url, set_type) VALUES (3, "/PekoPop/static/images/blindbox/twinkle/twinkle3/single.jpg", 'Single');
INSERT INTO product_images(product_id, image_url, set_type) VALUES (3, "/PekoPop/static/images/blindbox/twinkle/twinkle3/whole.jpg", 'Whole');

-- Product 4
INSERT INTO product_images(product_id, image_url, is_main) VALUES (4, "/PekoPop/static/images/blindbox/labubu/labubu1/main.jpg", TRUE);
INSERT INTO product_images(product_id, image_url) VALUES (4, "/PekoPop/static/images/blindbox/labubu/labubu1/1.jpg");
INSERT INTO product_images(product_id, image_url) VALUES (4, "/PekoPop/static/images/blindbox/labubu/labubu1/2.jpg");
INSERT INTO product_images(product_id, image_url) VALUES (4, "/PekoPop/static/images/blindbox/labubu/labubu1/3.jpg");
INSERT INTO product_images(product_id, image_url) VALUES (4, "/PekoPop/static/images/blindbox/labubu/labubu1/4.jpg");
INSERT INTO product_images(product_id, image_url) VALUES (4, "/PekoPop/static/images/blindbox/labubu/labubu1/5.jpg");
INSERT INTO product_images(product_id, image_url, is_detail) VALUES (4, "/PekoPop/static/images/blindbox/labubu/labubu1/info1.jpg", TRUE);
INSERT INTO product_images(product_id, image_url, is_detail) VALUES (4, "/PekoPop/static/images/blindbox/labubu/labubu1/info2.jpg", TRUE);
INSERT INTO product_images(product_id, image_url, is_detail) VALUES (4, "/PekoPop/static/images/blindbox/labubu/labubu1/info3.jpg", TRUE);
INSERT INTO product_images(product_id, image_url, is_detail) VALUES (4, "/PekoPop/static/images/blindbox/labubu/labubu1/info4.jpg", TRUE);
INSERT INTO product_images(product_id, image_url, is_detail) VALUES (4, "/PekoPop/static/images/blindbox/labubu/labubu1/info5.jpg", TRUE);
INSERT INTO product_images(product_id, image_url, set_type) VALUES (4, "/PekoPop/static/images/blindbox/labubu/labubu1/single.jpg", 'Single');
INSERT INTO product_images(product_id, image_url, set_type) VALUES (4, "/PekoPop/static/images/blindbox/labubu/labubu1/whole.jpg", 'Whole');

-- Product 5
INSERT INTO product_images(product_id, image_url, is_main) VALUES (5, "/PekoPop/static/images/blindbox/labubu/labubu2/main.jpg", TRUE);
INSERT INTO product_images(product_id, image_url) VALUES (5, "/PekoPop/static/images/blindbox/labubu/labubu2/1.jpg");
INSERT INTO product_images(product_id, image_url) VALUES (5, "/PekoPop/static/images/blindbox/labubu/labubu2/2.jpg");
INSERT INTO product_images(product_id, image_url) VALUES (5, "/PekoPop/static/images/blindbox/labubu/labubu2/3.jpg");
INSERT INTO product_images(product_id, image_url) VALUES (5, "/PekoPop/static/images/blindbox/labubu/labubu2/4.jpg");
INSERT INTO product_images(product_id, image_url) VALUES (5, "/PekoPop/static/images/blindbox/labubu/labubu2/5.jpg");
INSERT INTO product_images(product_id, image_url, is_detail) VALUES (5, "/PekoPop/static/images/blindbox/labubu/labubu2/info1.jpg", TRUE);
INSERT INTO product_images(product_id, image_url, is_detail) VALUES (5, "/PekoPop/static/images/blindbox/labubu/labubu2/info2.jpg", TRUE);
INSERT INTO product_images(product_id, image_url, is_detail) VALUES (5, "/PekoPop/static/images/blindbox/labubu/labubu2/info3.jpg", TRUE);
INSERT INTO product_images(product_id, image_url, is_detail) VALUES (5, "/PekoPop/static/images/blindbox/labubu/labubu2/info4.jpg", TRUE);
INSERT INTO product_images(product_id, image_url, set_type) VALUES (5, "/PekoPop/static/images/blindbox/labubu/labubu2/single.jpg", 'Single');
INSERT INTO product_images(product_id, image_url, set_type) VALUES (5, "/PekoPop/static/images/blindbox/labubu/labubu2/whole.jpg", 'Whole');

CREATE TABLE product_details (
    product_id INT,
    stock INT DEFAULT 0,
    set_type ENUM('Single', 'Whole') NOT NULL, 
    is_new BOOLEAN DEFAULT FALSE, 
    total_sold INT DEFAULT 0,       
    status ENUM('Active', 'Archived') DEFAULT 'Active',

    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE,
    UNIQUE(product_id, set_type) 
);

INSERT INTO product_details(product_id,stock,set_type,total_sold) VALUES (1,50,'Single',50);
INSERT INTO product_details(product_id,stock,set_type,total_sold) VALUES (1,50,'Whole',200);

INSERT INTO product_details(product_id, stock, set_type, is_new, total_sold) VALUES (2, 50, 'Single', FALSE, 0);
INSERT INTO product_details(product_id, stock, set_type, is_new, total_sold) VALUES (2, 50, 'Whole', FALSE, 0);

INSERT INTO product_details(product_id, stock, set_type, is_new, total_sold, status) VALUES (3, 50, 'Single', FALSE, 0, 'Archived');
INSERT INTO product_details(product_id, stock, set_type, is_new, total_sold, status) VALUES (3, 50, 'Whole', FALSE, 0, 'Archived');

INSERT INTO product_details(product_id, stock, set_type, is_new, total_sold) VALUES (4, 20, 'Single', TRUE, 0);
INSERT INTO product_details(product_id, stock, set_type, is_new, total_sold) VALUES (4, 5, 'Whole', TRUE, 0);

INSERT INTO product_details(product_id, stock, set_type, is_new, total_sold) VALUES (5, 5, 'Single', FALSE, 80);
INSERT INTO product_details(product_id, stock, set_type, is_new, total_sold) VALUES (5, 5, 'Whole', FALSE, 80);



-- Cart Items Table
CREATE TABLE cart_items (
    cart_item_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT DEFAULT 1,
    set_type ENUM('Single', 'Whole') NOT NULL, 
    added_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (product_id) REFERENCES products(product_id),
    UNIQUE KEY unique_cart_item (user_id, product_id, set_type, delivery_type)
);

-- Orders Table
CREATE TABLE orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    address_id INT NULL,
    pickup_id INT NULL,
    order_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    total_amount DECIMAL(10,2),
    payment_method ENUM('CreditCard', 'eWallet') NOT NULL,
    status ENUM('Pending', 'Paid', 'Shipped', 'Completed', 'Cancelled') DEFAULT 'Pending',
    delivery_type ENUM('pickup', 'delivery') NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (address_id) REFERENCES addresses(address_id),
    FOREIGN KEY (pickup_id) REFERENCES pickup_locations(pickup_id)
);

-- Order Items Table
CREATE TABLE order_items (
    order_item_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2),
    delivery_type ENUM('pickup', 'delivery') NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(order_id),
    FOREIGN KEY (product_id) REFERENCES products(product_id)
);

CREATE TABLE pickup_locations (
    pickup_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    address_line1 VARCHAR(255) NOT NULL,
    address_line2 VARCHAR(255) NOT NULL,
    city VARCHAR(100) NOT NULL,
    state VARCHAR(100) NOT NULL,
    postal_code VARCHAR(20) NOT NULL,
    country VARCHAR(100) DEFAULT 'Malaysia',
    is_active BOOLEAN DEFAULT TRUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Password Resets Table
CREATE TABLE password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100),
    reset_code VARCHAR(10),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
