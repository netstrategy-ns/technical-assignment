<?php

namespace App\Http\Controllers\Events;

use App\Http\Controllers\Controller;
use App\Http\Requests\VenueTypes\StoreVenueTypeRequest;
use App\Http\Requests\VenueTypes\UpdateVenueTypeRequest;
use App\Models\VenueType;

class VenueTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreVenueTypeRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(VenueType $venueType)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(VenueType $venueType)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateVenueTypeRequest $request, VenueType $venueType)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(VenueType $venueType)
    {
        //
    }
}
