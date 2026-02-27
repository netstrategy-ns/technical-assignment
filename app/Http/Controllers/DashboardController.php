<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $featuredEvents = Event::with('category')
            ->featured()
            ->upcoming()
            ->orderBy('starts_at')
            ->take(8)
            ->get();

        $upcomingEvents = Event::with('category')
            ->upcoming()
            ->orderBy('starts_at')
            ->take(4)
            ->get();

        return Inertia::render('Dashboard', [
            'featuredEvents' => $featuredEvents,
            'upcomingEvents' => $upcomingEvents,
        ]);
    }
}
