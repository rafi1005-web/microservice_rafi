# Microservice Toko Online

Sistem toko online berbasis microservice: empat service dengan database masing-masing, dijalankan bersama lewat Docker Compose. Service `order-service` (Laravel + MySQL) dibuat untuk UTS, sedangkan `product-service`, `cart-service`, dan `review-service` berasal dari kelas.

## Arsitektur

| Service | Teknologi | Database | Port di komputer |
|---|---|---|---|
| product-service | Node.js (Express) | MySQL | 3000 |
| cart-service | Laravel | PostgreSQL | 3001 |
| review-service | Python (Flask) | MongoDB | 3002 |
| order-service | Laravel | MySQL | 3003 (database: 3308) |

Setiap service memiliki database sendiri. Antar-service hanya berkomunikasi lewat HTTP, memakai nama service di jaringan Docker (misalnya `http://product-service:3000`).

Alur `POST /orders` pada order-service:

1. Ambil keranjang user dari cart-service.
2. Jika keranjang kosong, tolak (409).
3. Untuk tiap item, ambil produk dari product-service lalu periksa ketersediaan dan stok.
4. Simpan pesanan dan item dalam satu transaksi database, dengan nama dan harga produk disalin ke item (snapshot).
5. Kosongkan keranjang di cart-service. Kegagalan di langkah ini tidak membatalkan pesanan yang sudah tersimpan.

Lapisan di order-service: route -> `OrderController` -> `OrderService` (aturan bisnis) -> `ProductClient` dan `CartClient` (komunikasi HTTP) -> model `Order` dan `OrderItem`.

## Menjalankan Sistem

Prasyarat: Docker dan Docker Compose.

```
git clone https://github.com/rafi1005-web/microservice_rafi.git
cd microservice_rafi
docker compose up --build
```

Tidak ada langkah manual lain. Migrasi database dijalankan otomatis saat service menyala, dan data awal produk dimuat dari `product-service/database/init.sql`. Build pertama bisa memakan waktu cukup lama (hingga puluhan menit pada koneksi lambat) karena mengunduh image dasar. Tunggu sampai semua container berstatus `Up`, lalu cek:

```
curl http://localhost:3003/health
```

Untuk menghentikan sistem dan menghapus data: `docker compose down -v`.

## Data Awal Produk

| id | Nama | Harga | Stok |
|---|---|---|---|
| 1 | Keyboard Mekanikal | 450000 | 25 |
| 2 | Mouse Wireless | 150000 | 40 |
| 3 | Monitor 24 Inch | 1800000 | 10 |

## Endpoint order-service (http://localhost:3003)

| Method | Path | Keterangan | Status |
|---|---|---|---|
| GET | `/health` | Cek hidup | 200 |
| POST | `/orders` | Buat pesanan dari keranjang | 201, 422, 409, 503, 500 |
| GET | `/orders/{id}` | Detail pesanan | 200, 404 |
| GET | `/orders/user/{userId}?status=` | Daftar pesanan user, filter opsional `PENDING` atau `PAID` | 200, 422 |
| PATCH | `/orders/{id}/pay` | Bayar pesanan `PENDING` | 200, 404, 409 |

Id pada path harus berupa angka. Selain itu dibalas 404.

Contoh request:

```
# isi keranjang (cart-service)
curl -X POST http://localhost:3001/carts -H "Content-Type: application/json" -d '{"user_id":7,"product_id":1,"quantity":2}'

# buat pesanan
curl -X POST http://localhost:3003/orders -H "Content-Type: application/json" -d '{"user_id":7}'

# daftar pesanan user, hanya yang sudah dibayar
curl "http://localhost:3003/orders/user/7?status=PAID"

# bayar pesanan
curl -X PATCH http://localhost:3003/orders/1/pay
```

Pada PowerShell gunakan `curl.exe` dan simpan body JSON ke file (`-d "@body.json"`).

Kasus gagal `POST /orders`:

| Kondisi | Status |
|---|---|
| `user_id` tidak ada atau bukan angka | 422 |
| Keranjang kosong | 409 |
| Produk di keranjang sudah tidak ada | 409 |
| Stok tidak mencukupi | 409 |
| product-service atau cart-service tidak dapat dihubungi, atau melewati timeout | 503 |
| Kesalahan tak terduga | 500 |

Detail teknis pada respons error hanya muncul bila `APP_DEBUG=true`.

## Environment Variable order-service

| Variabel | Fungsi | Nilai di compose |
|---|---|---|
| `APP_NAME` | Nama aplikasi | `order-service` |
| `APP_ENV` | Lingkungan | `local` |
| `APP_KEY` | Kunci aplikasi Laravel (kunci khusus pengembangan, bukan rahasia) | tercantum di compose |
| `APP_DEBUG` | Menampilkan detail teknis pada error | `false` |
| `DB_CONNECTION`, `DB_HOST`, `DB_PORT` | Koneksi database | `mysql`, `order-db`, `3306` |
| `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` | Akses database | `orderdb`, `appuser`, `apppassword` |
| `PRODUCT_SERVICE_URL` | Alamat product-service | `http://product-service:3000` |
| `CART_SERVICE_URL` | Alamat cart-service | `http://cart-service:3001` |
| `HTTP_TIMEOUT` | Batas waktu panggilan antar-service (detik) | `5` |
| `LOG_CHANNEL` | Log ke stderr agar terbaca lewat `docker compose logs` | `stderr` |

Contoh nilai tersedia di `order-service/.env.example`. File `.env` tidak ikut repo.

## Perbaikan pada Service dari Kelas

Kontrak API ketiga service tidak diubah. Hanya bug dan kekurangan konfigurasi yang diperbaiki:

- **cart-service:** `GET /carts/{userId}` gagal 500 karena memanggil relasi `product` yang tidak ada pada model `CartItem`. Pemanggilan itu dihapus.
- **cart-service:** `PRODUCT_SERVICE_URL` ditambahkan di compose supaya cart-service menemukan product-service di dalam Docker, dan file `dockerfile` diganti namanya menjadi `Dockerfile`.
- **product-service:** ditambahkan kolom `stock` (angka) pada tabel `products`, karena dibutuhkan untuk memeriksa stok. Kolom `stack` yang lama tetap ada. Data awal disesuaikan dengan data contoh di soal.
- **compose:** healthcheck database memakai `127.0.0.1` supaya status sehat berarti database benar-benar menerima koneksi jaringan.
- **order-service:** `CartClient` menerima data keranjang berupa daftar item langsung (bentuk yang dikirim cart-service dari kelas).

## Keputusan Desain dan Keterbatasan

### Snapshot harga

Saat pesanan dibuat, nama produk, harga satuan, dan subtotal disalin ke tabel `order_items`. Pesanan yang sudah ada dibaca hanya dari database order-service, tanpa memanggil product-service lagi. Akibatnya harga di pesanan lama tidak berubah walaupun harga produk diubah kemudian (terbukti dengan mengubah harga Mouse Wireless setelah pesanan dibuat). Trade-off: data produk tersimpan ganda, dan perubahan nama atau harga produk tidak tercermin di pesanan lama. Itu memang yang diinginkan untuk catatan transaksi.

### Kegagalan mengosongkan keranjang

Pengosongan keranjang adalah panggilan HTTP ke service lain, jadi tidak bisa dibatalkan oleh transaksi database. Pemanggilannya ditaruh sesudah transaksi pesanan selesai. Kalau gagal, pesanan tetap tersimpan dan hanya dicatat peringatan di log (berisi `order_id` dan `user_id`). Pilihan ini menghindari kehilangan pesanan yang sudah sah. Risikonya, keranjang masih berisi item sehingga user bisa membuat pesanan ganda, dan tidak ada retry otomatis.

### Pemisahan lapisan

Controller hanya menerima request, memanggil `OrderService`, lalu menerjemahkan exception menjadi status HTTP. Aturan bisnis (keranjang kosong, pemeriksaan stok, transaksi, snapshot) ada di `OrderService`. `ProductClient` dan `CartClient` hanya melempar exception khusus dan tidak membalas HTTP, sehingga lapisan service tidak bergantung pada bentuk respons dan penerjemahan kode status terpusat di satu tempat.

### Keterbatasan yang disadari

- Stok di product-service tidak dikurangi setelah pesanan dibuat, karena product-service dari kelas tidak menyediakan endpoint untuk mengubah stok.
- Pemeriksaan stok dan penyimpanan pesanan tidak atomik antar-service, sehingga dua pesanan bersamaan bisa sama-sama lolos pemeriksaan stok.
- Tidak ada autentikasi: `user_id` dipercaya apa adanya dari body request.
- Total harga dihitung dengan `float` pada kode (walaupun disimpan sebagai `decimal` di database), yang bisa menimbulkan galat pembulatan untuk harga berdesimal.
- Pesanan `PENDING` tidak kedaluwarsa, dan tidak ada retry saat service lain gagal.
- Product-service dipanggil satu per satu untuk tiap item keranjang, jadi jumlah panggilan bertambah seiring jumlah item.