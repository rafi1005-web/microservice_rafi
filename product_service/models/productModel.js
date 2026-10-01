const pool = require('../config/db');

// Ambil semua produk
async function getAllProducts() {
    const [rows] = await pool.query(
        'SELECT * FROM products ORDER BY created_at DESC'
    );
    return rows;
}

// Ambil satu produk berdasarkan ID
async function getProductById(id) {
    const [rows] = await pool.query(
        'SELECT * FROM products WHERE id = ?',
        [id]
    );
    return rows[0];
}

// Simpan produk ke database
async function createProduct(product) {
    const {
        name,
        description,
        price,
        stack,
        image
    } = product;

    const [result] = await pool.query(
        'INSERT INTO products (name, description, price, stack, image) VALUES (?, ?, ?, ?, ?)',
        [name, description, price, stack, image]
    );

    return getProductById(result.insertId);
}

// Update produk berdasarkan ID
async function updateProduct(id, product) {
    const {
        name,
        description,
        price,
        stack,
        image
    } = product;

    await pool.query(
        'UPDATE products SET name = ?, description = ?, price = ?, stack = ?, image = ? WHERE id = ?',
        [name, description, price, stack, image, id]
    );

    return getProductById(id);
}

// Hapus produk berdasarkan ID
async function deleteProduct(id) {
    const [result] = await pool.query(
        'DELETE FROM products WHERE id = ?',
        [id]
    );

    return result.affectedRows > 0;
}

module.exports = {
    getAllProducts,
    getProductById,
    createProduct,
    updateProduct,
    deleteProduct
};