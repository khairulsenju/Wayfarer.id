<?php

namespace App\Http\Controllers;

use App\Services\OrderService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Throwable;

class MidtransWebhookController extends Controller
{
    public function __construct(private OrderService $orders)
    {
    }

    public function handle(Request $request): JsonResponse
    {
        $payload = $request->all();

        try {
            $order = $this->orders->handleMidtransNotification($payload);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Pesanan tidak ditemukan.'], 404);
        } catch (InvalidArgumentException $e) {
            Log::warning('Notifikasi Midtrans ditolak: ' . $e->getMessage(), [
                'order_id' => $payload['order_id'] ?? null,
            ]);

            return response()->json(['message' => 'Notifikasi tidak valid.'], 403);
        } catch (Throwable $e) {
            Log::error('Gagal memproses notifikasi Midtrans: ' . $e->getMessage(), [
                'order_id' => $payload['order_id'] ?? null,
            ]);

            return response()->json(['message' => 'Terjadi kesalahan server.'], 500);
        }

        return response()->json([
            'message' => 'OK',
            'order_code' => $order->order_code,
            'payment_status' => $order->payment_status,
            'order_status' => $order->order_status,
        ]);
    }
}