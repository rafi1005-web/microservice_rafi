const productModel = require('../models/productModel');

// Validasi Base64 dan ukuran maksimal 2 MB
function validateImage(image) {
    // Cek wajib diisi
    if (!image || image.trim() === '') {
        return {
            valid: false,
            message: "Field image wajib diisi"
        };
    }

    // Jika menggunakan Data URI, hapus prefix-nya
    let base64Data = image;

    if (image.startsWith('data:image/')) {
        const parts = image.split(',');

        if (parts.length !== 2) {
            return {
                valid: false,
                message: "Field image harus berupa Base64 yang valid"
            };
        }

        base64Data = parts[1];
    }

    // Cek format Base64
    const base64Regex = /^[A-Za-z0-9+/]+={0,2}$/;

    if (
        !base64Regex.test(base64Data) ||
        base64Data.length % 4 !== 0
    ) {
        return {
            valid: false,
            message: "Field image harus berupa Base64 yang valid"
        };
    }

    // Decode Base64 untuk mengecek ukuran file sebenarnya
    const imageBuffer = Buffer.from(base64Data, 'base64');

    const maxSize = 2 * 1024 * 1024; // 2 MB

    if (imageBuffer.length > maxSize) {
        return {
            valid: false,
            message: "Ukuran image maksimal 2 MB"
        };
    }

    return {
        valid: true,
        image: base64Data
    };
}


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
        const {
            name,
            description,
            price,
            stack,
            image
        } = req.body;

        // Validasi data produk
        if (!name || price === undefined || stack === undefined) {
            return res.status(400).json({
                message: "name, price, dan stack wajib diisi"
            });
        }

        // Validasi image
        const imageValidation = validateImage(image);

        if (!imageValidation.valid) {
            return res.status(400).json({
                message: imageValidation.message
            });
        }

        const product = await productModel.createProduct({
            name,
            description,
            price,
            stack,
            image: imageValidation.image
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
        const {
            name,
            description,
            price,
            stack,
            image
        } = req.body;

        // Cek produk terlebih dahulu
        const existingProduct = await productModel.getProductById(req.params.id);

        if (!existingProduct) {
            return res.status(404).json({
                message: "Produk tidak ditemukan"
            });
        }

        // Validasi image
        const imageValidation = validateImage(image);

        if (!imageValidation.valid) {
            return res.status(400).json({
                message: imageValidation.message
            });
        }

        const product = await productModel.updateProduct(
            req.params.id,
            {
                name,
                description,
                price,
                stack,
                image: imageValidation.image
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