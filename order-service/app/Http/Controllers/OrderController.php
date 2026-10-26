<?php

namespace App\Http\Controllers;

use App\Exceptions\EmptyCartException;
use App\Exceptions\InsufficientStockException;
use App\Exceptions\ProductNotFoundException;
use App\Exceptions\ServiceUnavailableException;
use App\Http\Requests\CreateOrderRequest;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Throwable;

class OrderController extends Controller
{
    public function __construct(private OrderService $orderService) {}

    public function health(): JsonResponse
    {
        return response()->json(['status' => 'ok', 'service' => 'order-service']);
    }

    public function store(CreateOrderRequest $request): JsonResponse
    {
        try {
            $order = $this->orderService->createOrder($request->integer('user_id'));

            return response()->json([
                'message' => 'Pesanan berhasil dibuat',
                'data' => $order,
            ], 201);
        } catch (EmptyCartException | ProductNotFoundException | InsufficientStockException $e) {
           return response()->json(['message' => $e->getMessage()], 409);
        } catch (ServiceUnavailableException $e) {
            return response()->json(['message' => $e->getMessage()], 503);
        } catch (Throwable $e) {
            Log::error('Error tak terduga saat membuat pesanan', ['error' => $e->getMessage()]);

            $body = ['message' => 'Terjadi kesalahan pada server'];
            if (config('app.debug')) {
                $body['error'] = $e->getMessage();
            }

            return response()->json($body, 500);
        }
    }
}
