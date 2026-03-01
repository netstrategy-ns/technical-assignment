<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Services\TicketService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function __construct(
        private readonly TicketService $tickets,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $tickets = $this->tickets->listForUserWithDetails($request->user()->id);

        return response()->json($tickets);
    }
}
