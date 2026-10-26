const express = require('express');
const cors = require('cors');
const productRoutes = require('./routes/productRoutes');

const app = express();

app.use(cors());

// Naikkan batas JSON agar image Base64 > 2 MB bisa diterima
// oleh Express dan divalidasi oleh aplikasi.
app.use(express.json({ limit: '4mb' }));

// endpoint for healthcheck
app.get('/health', (req, res) => {
    res.json({
        status: "ok",
        service: "product-service"
    });
});

app.use("/products", productRoutes);

// unknown path
app.use((req, res) => {
    res.status(404).json({
        message: "Endpoint tidak dikenal"
    });
});

module.exports = app;