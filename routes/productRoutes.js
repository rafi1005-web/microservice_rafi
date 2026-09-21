const express = require('express');

const router = express.Router();

const productController = require('../product_service/controllers/productController');

// GET semua produk
router.get('/', productController.index);

// GET produk berdasarkan ID
router.get('/:id', productController.getById);

// POST tambah produk
router.post('/', productController.create);

// PUT update produk berdasarkan ID
router.put('/:id', productController.update);

// DELETE hapus produk berdasarkan ID
router.delete('/:id', productController.remove);

module.exports = router;