<?php

namespace App\Http\Controllers;

use App\Http\Requests\Hold\StoreHoldRequest;
use App\Services\HoldService;
use App\Models\Hold;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class HoldController extends Controller
{
    public function __construct(
        private readonly HoldService $holds,
    ) {
    }

    public function store(StoreHoldRequest $request): JsonResponse
    {
        $user = $request->user();

        try {
            $hold = $this->holds->createHold(
                $user->id,
                (int) $request->input('event_id'),
                (int) $request->input('ticket_type_id'),
                (int) $request->input('quantity'),
            );

            return response()->json($hold, 201);
        } catch (RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }
    }

    public function index(Request $request): JsonResponse
    {
        $holds = $this->holds->listActiveForUser($request->user()->id);

        return response()->json($holds);
    }
}
