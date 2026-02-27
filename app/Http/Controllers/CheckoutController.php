<?php

namespace App\Http\Controllers;

use App\Http\Requests\Checkout\StoreCheckoutRequest;
use App\Services\CheckoutService;
use Illuminate\Http\JsonResponse;
use RuntimeException;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly CheckoutService $checkout,
    ) {
    }

    public function store(StoreCheckoutRequest $request): JsonResponse
    {
        $user = $request->user();
        $idempotencyKey = $request->header('Idempotency-Key');

        try {
            $order = $this->checkout->checkout(
                $user->id,
                (int) $request->input('event_id'),
                $idempotencyKey,
            );

            return response()->json($order, 201);
        } catch (RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }
    }
}
