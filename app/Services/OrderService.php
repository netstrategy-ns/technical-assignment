<?php

namespace App\Services;

use App\Models\Order;
use App\Repositories\OrderRepository;
use Illuminate\Database\Eloquent\Collection;

class OrderService
{
    public function __construct(
        private readonly OrderRepository $orders,
    ) {}

    public function listForUser(int $userId): Collection
    {
        return $this->orders->forUser($userId);
    }

    public function loadDetails(Order $order): Order
    {
        return $this->orders->loadDetails($order);
    }
}
