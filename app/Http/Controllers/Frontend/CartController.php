<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class CartController extends Controller
{
    /**
     * Pagina carrello (contenuto gestito client-side da useCart; in modulo 04 si potranno passare hold dal backend).
     */
    public function index(): Response
    {
        return Inertia::render('frontend/cart/Index');
    }
}
