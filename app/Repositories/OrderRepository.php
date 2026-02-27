<?php

namespace App\Repositories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Collection;

class OrderRepository
{
    public function forUser(int $userId): Collection
    {
        return Order::query()
            ->where('user_id', $userId)
            ->orderByDesc('created_at')
            ->get();
    }

    public function loadDetails(Order $order): Order
    {
        $order->load(['items', 'tickets']);

        return $order;
    }
}
