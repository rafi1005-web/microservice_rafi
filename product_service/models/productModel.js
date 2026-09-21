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
  const { name, description, price, stack } = product;

  const [result] = await pool.query(
    'INSERT INTO products (name, description, price, stack) VALUES (?, ?, ?, ?)',
    [name, description, price, stack]
  );

  return getProductById(result.insertId);
}

// Update produk berdasarkan ID
async function updateProduct(id, product) {
  const { name, description, price, stack } = product;

  await pool.query(
    'UPDATE products SET name = ?, description = ?, price = ?, stack = ? WHERE id = ?',
    [name, description, price, stack, id]
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