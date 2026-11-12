-- DormBook — Жатақхана бөлмелерін брондау жүйесі
-- Дерекқор мен кестелерді құру скрипті (phpMyAdmin → Import арқылы жүктеледі)
-- Ескерту: скрипт ескі dormbook дерекқорын өшіріп, қайта құрады.

DROP DATABASE IF EXISTS dormbook;

CREATE DATABASE dormbook
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE dormbook;

-- 1. Пайдаланушылар (студенттер мен әкімшілер)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    student_id VARCHAR(20) NOT NULL UNIQUE,
    phone VARCHAR(20) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('student', 'admin') NOT NULL DEFAULT 'student',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Жатақханалар
CREATE TABLE IF NOT EXISTS dormitories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    address VARCHAR(255) NOT NULL,
    floors INT NOT NULL
);

-- 3. Бөлмелер
CREATE TABLE IF NOT EXISTS rooms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    dormitory_id INT NOT NULL,
    room_number VARCHAR(10) NOT NULL,
    floor INT NOT NULL,
    capacity INT NOT NULL,
    free_places INT NOT NULL,
    price_per_month DECIMAL(10, 2) NOT NULL,
    description TEXT,
    UNIQUE (dormitory_id, room_number),
    FOREIGN KEY (dormitory_id) REFERENCES dormitories(id) ON DELETE CASCADE
);

-- 4. Брондаулар
CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    room_id INT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    status ENUM('pending', 'approved', 'rejected', 'cancelled') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE
);

-- Тестілік деректер
INSERT INTO dormitories (name, address, floors) VALUES
('№1 жатақхана', 'Алматы қ., Гоголь к-сі, 114', 5),
('№2 жатақхана', 'Алматы қ., Абай д-лы, 50', 6),
('№3 жатақхана', 'Алматы қ., Сейфуллин д-лы, 200', 5);

INSERT INTO rooms (dormitory_id, room_number, floor, capacity, free_places, price_per_month, description) VALUES
(1, '101', 1, 2, 1, 25000, 'Екі орындық бөлме, жеке жуынатын бөлмесі бар'),
(1, '205', 2, 3, 2, 20000, 'Үш орындық бөлме, терезесі оңтүстікке қарайды'),
(2, '312', 3, 4, 0, 18000, 'Төрт орындық бөлме'),
(2, '118', 1, 2, 2, 27000, 'Екі орындық, жөндеуден кейін'),
(3, '407', 4, 3, 1, 21000, 'Үш орындық бөлме, оқу үстелдері бар'),
(3, '220', 2, 4, 3, 17000, 'Төрт орындық бөлме');

-- Тестілік пайдаланушы (email: test@dormbook.kz, құпиясөз: 12345678)
INSERT INTO users (full_name, email, student_id, phone, password_hash, role) VALUES
('Тест Студент', 'test@dormbook.kz', '22010001', '+77001112233',
 '$2y$10$oqimcsOWo9oTPzgv308XMei2EfWZssDALZ0UEyOR534f6aAmP9VL6', 'student');

-- Әкімші аккаунты (email: admin@dormbook.kz, құпиясөз: admin12345)
INSERT INTO users (full_name, email, student_id, phone, password_hash, role) VALUES
('Әкімші', 'admin@dormbook.kz', '00000001', '+77000000000',
 '$2y$10$QuzRj5WxrhJJPRHMpxBlZ.1esZWuTAoepyQlJTj/G7cDMlMmlaJdO', 'admin');
