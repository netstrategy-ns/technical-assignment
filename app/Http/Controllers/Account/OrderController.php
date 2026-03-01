<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(
        private readonly OrderService $orders,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $orders = $this->orders->listForUser($request->user()->id);

        return response()->json($orders);
    }

    public function show(Order $order): JsonResponse
    {
        $this->authorize('view', $order);

        $order = $this->orders->loadDetails($order);

        return response()->json($order);
    }
}
