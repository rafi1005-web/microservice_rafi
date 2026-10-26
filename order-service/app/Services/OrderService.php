<?php

namespace App\Services;

use App\Clients\CartClient;
use App\Clients\ProductClient;
use App\Exceptions\EmptyCartException;
use App\Exceptions\InsufficientStockException;
use App\Exceptions\ServiceUnavailableException;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderService
{
    public function __construct(
        private CartClient $cartClient,
        private ProductClient $productClient,
    ) {}

    public function createOrder(int $userId): Order
    {
        // 1. Ambil keranjang dari cart-service.
        $cart = $this->cartClient->getCart($userId);

        // 2. Keranjang kosong -> EmptyCartException.
        if (empty($cart['items'])) {
            throw new EmptyCartException($userId);
        }

        // 3. Per item: ambil produk, cek stok, siapkan snapshot.
        $lines = [];
        $total = 0;

        foreach ($cart['items'] as $item) {
            $product = $this->productClient->getProduct($item['product_id']);

            if ($product['stock'] < $item['quantity']) {
                throw new InsufficientStockException(
                    $product['name'], $item['quantity'], $product['stock']
                );
            }

            $unitPrice = (float) $product['price'];
            $subtotal = $unitPrice * $item['quantity'];

            $lines[] = [
                'product_id' => $item['product_id'],
                'product_name' => $product['name'],
                'unit_price' => $unitPrice,
                'quantity' => $item['quantity'],
                'subtotal' => $subtotal,
            ];

            $total += $subtotal;
        }

        // 4. Simpan pesanan dan item dalam SATU transaksi.
        $order = DB::transaction(function () use ($userId, $total, $lines) {
            $order = Order::create([
                'user_id' => $userId,
                'status' => 'PENDING',
                'total_amount' => $total,
            ]);

            $order->items()->createMany($lines);

            return $order;
        });

        // 5. Kosongkan keranjang. Kalau gagal, pesanan TIDAK dibatalkan.
        try {
            $this->cartClient->clearCart($userId);
        } catch (ServiceUnavailableException $e) {
            Log::warning('Pesanan tersimpan tetapi keranjang gagal dikosongkan', [
                'order_id' => $order->id,
                'user_id' => $userId,
            ]);
        }

        return $order->load('items');
    }
}
