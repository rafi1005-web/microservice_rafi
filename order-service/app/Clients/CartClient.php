<?php

namespace App\Clients;

use App\Exceptions\ServiceUnavailableException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CartClient
{
    private function http(): PendingRequest
    {
        return Http::baseUrl(config('services.cart.url'))
            ->timeout(config('services.cart.timeout'));
    }

    public function getCart(int $userId): array
    {
        try {
           $response = $this->http()->get("/carts/{$userId}");
        } catch (ConnectionException $e) {
            Log::error('Cart-service tidak dapat dihubungi', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);

            throw new ServiceUnavailableException('Cart service', $e);
        }

        if (! $response->successful()) {
            Log::error('Cart-service membalas dengan error', [
                'user_id' => $userId,
                'status' => $response->status(),
            ]);

            throw new ServiceUnavailableException('Cart service');
        }

        return $response->json('data');
    }

    public function clearCart(int $userId): void
{
    try {
        $response = $this->http()->delete("/carts/{$userId}");
    } catch (ConnectionException $e) {
        Log::error('Cart-service tidak dapat dihubungi saat mengosongkan keranjang', [
            'user_id' => $userId,
            'error' => $e->getMessage(),
        ]);

        throw new ServiceUnavailableException('Cart service', $e);
    }

    if (! $response->successful()) {
        Log::error('Cart-service gagal mengosongkan keranjang', [
            'user_id' => $userId,
            'status' => $response->status(),
        ]);

        throw new ServiceUnavailableException('Cart service');
    }
}
}
