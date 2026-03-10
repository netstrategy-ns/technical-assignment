<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Checkout\StoreCheckoutRequest;
use App\Services\CheckoutService;
use App\Services\CartHoldService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CheckoutController extends Controller
{
    public function index(Request $request, CartHoldService $cartHoldService): Response
    {
        return Inertia::render('app/checkout/Index', [
            'cart' => $cartHoldService->buildCartPayload($request->user()),
        ]);
    }

    public function store(StoreCheckoutRequest $request, CheckoutService $checkoutService): RedirectResponse
    {
        $order = $checkoutService->checkout(
            $request->user(),
            (array) $request->validated('hold_ids', []),
        );

        return redirect()->route('orders.show', $order);
    }
}
