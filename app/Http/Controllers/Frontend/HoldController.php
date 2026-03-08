<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Holds\StoreHoldRequest;
use App\Http\Requests\Holds\UpdateHoldRequest;
use App\Models\Hold;
use App\Services\CartHoldService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class HoldController extends Controller
{
    public function store(StoreHoldRequest $request, CartHoldService $cartHoldService): RedirectResponse
    {
        $cartHoldService->placeHold(
            $request->user(),
            (int) $request->integer('ticket_id'),
            (int) $request->integer('quantity'),
        );

        return redirect()
            ->back()
            ->with('message', 'Biglietti aggiunti al carrello.');
    }

    public function destroy(Hold $hold, Request $request, CartHoldService $cartHoldService): RedirectResponse
    {
        $cartHoldService->releaseHold($request->user(), $hold);

        return redirect()
            ->back()
            ->with('message', 'Biglietti rimossi dal carrello.');
    }

    public function update(Hold $hold, UpdateHoldRequest $request, CartHoldService $cartHoldService): RedirectResponse
    {
        $cartHoldService->updateHoldQuantity(
            $request->user(),
            $hold,
            (int) $request->integer('quantity'),
        );

        return redirect()
            ->back()
            ->with('message', 'Quantita biglietti aggiornata.');
    }
}
