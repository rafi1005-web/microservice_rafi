const productModel = require('../models/productModel');

// GET semua produk
async function index(req, res) {
    try {
        const products = await productModel.getAllProducts();

        res.json({
            message: "Berhasil mengambil data produk",
            data: products
        });

    } catch (error) {
        res.status(500).json({
            message: "Gagal mengambil data produk",
            error: error.message
        });
    }
}

// GET produk berdasarkan ID
async function getById(req, res) {
    try {
        const product = await productModel.getProductById(req.params.id);

        if (!product) {
            return res.status(404).json({
                message: "Produk tidak ditemukan"
            });
        }

        res.json({
            message: "Berhasil mengambil data produk",
            data: product
        });

    } catch (error) {
        res.status(500).json({
            message: "Gagal mengambil data produk",
            error: error.message
        });
    }
}

// POST tambah produk
async function create(req, res) {
    try {
        const { name, description, price, stack } = req.body;

        if (!name || price === undefined || stack === undefined) {
            return res.status(400).json({
                message: "name, price, dan stack wajib diisi"
            });
        }

        const product = await productModel.createProduct({
            name,
            description,
            price,
            stack
        });

        res.status(201).json({
            message: "Produk berhasil ditambahkan",
            data: product
        });

    } catch (error) {
        res.status(500).json({
            message: "Gagal menambahkan produk",
            error: error.message
        });
    }
}

// PUT update produk
async function update(req, res) {
    try {
        const { name, description, price, stack } = req.body;

        const existingProduct = await productModel.getProductById(req.params.id);

        if (!existingProduct) {
            return res.status(404).json({
                message: "Produk tidak ditemukan"
            });
        }

        const product = await productModel.updateProduct(
            req.params.id,
            {
                name,
                description,
                price,
                stack
            }
        );

        res.json({
            message: "Produk berhasil diperbarui",
            data: product
        });

    } catch (error) {
        res.status(500).json({
            message: "Gagal memperbarui produk",
            error: error.message
        });
    }
}

// DELETE hapus produk
async function remove(req, res) {
    try {
        const deleted = await productModel.deleteProduct(req.params.id);

        if (!deleted) {
            return res.status(404).json({
                message: "Produk tidak ditemukan"
            });
        }

        res.json({
            message: "Produk berhasil dihapus"
        });

    } catch (error) {
        res.status(500).json({
            message: "Gagal menghapus produk",
            error: error.message
        });
    }
}

module.exports = {
    index,
    getById,
    create,
    update,
    remove
};