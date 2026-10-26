<?php

namespace App\Http\Controllers;

use App\Exceptions\EmptyCartException;
use App\Exceptions\InsufficientStockException;
use App\Exceptions\ProductNotFoundException;
use App\Exceptions\ServiceUnavailableException;
use App\Http\Requests\CreateOrderRequest;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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

    public function show(int $id): JsonResponse
    {
        $order = Order::with('items')->find($id);

        if (! $order) {
            return response()->json([
                'message' => "Pesanan dengan id {$id} tidak ditemukan",
            ], 404);
        }

        return response()->json([
            'message' => 'Berhasil mengambil pesanan',
            'data' => $order,
        ]);
    }

    public function byUser(Request $request, int $userId): JsonResponse
    {
        $status = $request->query('status');

        if ($status !== null && ! in_array($status, ['PENDING', 'PAID'], true)) {
            return response()->json([
                'message' => 'Data yang dikirim tidak valid',
                'errors' => ['status' => ['Status harus PENDING atau PAID']],
            ], 422);
        }

        $orders = Order::with('items')
            ->where('user_id', $userId)
            ->when($status, fn ($query) => $query->where('status', $status))
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'message' => 'Berhasil mengambil daftar pesanan',
            'data' => $orders,
        ]);
    }

    public function pay(int $id): JsonResponse
    {
        $order = Order::with('items')->find($id);

        if (! $order) {
            return response()->json([
                'message' => "Pesanan dengan id {$id} tidak ditemukan",
            ], 404);
        }

        if ($order->status !== 'PENDING') {
            return response()->json([
                'message' => "Pesanan berstatus {$order->status}, hanya pesanan PENDING yang dapat dibayar",
            ], 409);
        }

        $order->update(['status' => 'PAID']);

        return response()->json([
            'message' => 'Pesanan berhasil dibayar',
            'data' => $order,
        ]);
    }
}
