<?php

namespace App\Clients;

use App\Exceptions\ProductNotFoundException;
use App\Exceptions\ServiceUnavailableException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProductClient
{
    public function getProduct(int $productId): array
    {
        try {
            $response = Http::baseUrl(config('services.product.url'))
                ->timeout(config('services.product.timeout'))
                ->get("/products/{$productId}");
        } catch (ConnectionException $e) {
            Log::error('Product-service tidak dapat dihubungi', [
                'product_id' => $productId,
                'error' => $e->getMessage(),
            ]);

            throw new ServiceUnavailableException('Product service', $e);
        }

       if ($response->status() === 404) {
            throw new ProductNotFoundException($productId);
        }

        if (! $response->successful()) {
            Log::error('Product-service membalas dengan error', [
                'product_id' => $productId,
                'status' => $response->status(),
            ]);

            throw new ServiceUnavailableException('Product service');
        }

        return $response->json('data');
    }
}
