CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    stack VARCHAR(255) NOT NULL,
    image MEDIUMTEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Data Contoh

INSERT INTO products (name, description, price, stack, image) VALUES
(
    'Keyboard Mekanikal',
    'Keyboard mekanikal switch blue',
    500000,
    'Mechanical Keyboard',
    'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg=='
),
(
    'Mouse Wireless',
    'Mouse wireless dengan sensor optical',
    350000,
    'Wireless Mouse',
    'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg=='
),
(
    'Headset Gaming',
    'Headset gaming dengan kualitas audio terbaik',
    800000,
    'Gaming Headset',
    'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg=='
);