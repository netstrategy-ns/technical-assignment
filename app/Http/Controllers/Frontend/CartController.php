<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CartController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('app/cart/Index', [
            'cart' => app(\App\Services\CartHoldService::class)->buildCartPayload($request->user()),
        ]);
    }
}
