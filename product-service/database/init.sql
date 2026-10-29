CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    stack VARCHAR(255) NOT NULL,
    image MEDIUMTEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Data Contoh

INSERT INTO products (name, description, price, stock, stack, image) VALUES
(
    'Keyboard Mekanikal',
    'Keyboard mekanikal switch blue',
    450000,
    25,
    'Mechanical Keyboard',
    'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg=='
),
(
    'Mouse Wireless',
    'Mouse wireless dengan sensor optical',
    150000,
    40,
    'Wireless Mouse',
    'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg=='
),
(
    'Monitor 24 Inch',
    'Monitor 24 inch Full HD',
    1800000,
    10,
    'Monitor',
    'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg=='
);