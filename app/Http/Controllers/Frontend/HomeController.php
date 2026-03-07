<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventCategory;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Fortify\Features;

class HomeController extends Controller
{
    /**
     * Home: eventi in evidenza per hero (max 15) + eventi raggruppati per categoria.
     */
    public function __invoke(): Response
    {
        $featuredEvents = Event::query()
            ->filterByActive()
            ->filterByFeatured()
            ->with(['category', 'venueType'])
            ->orderByStartDate()
            ->take(15)
            ->get();

        $categoriesWithEvents = EventCategory::query()
            ->whereHas('events', fn ($q) => $q->filterByActive())
            ->orderBy('name')
            ->with(['events' => fn ($q) => $q->filterByActive()->with(['category', 'venueType'])->orderByStartDate()])
            ->get()
            ->map(fn (EventCategory $cat) => [
                'category' => ['id' => $cat->id, 'name' => $cat->name, 'slug' => $cat->slug],
                'events' => $cat->events->values()->all(),
            ])
            ->values()
            ->all();

        return Inertia::render('frontend/home/Index', [
            'featuredEvents' => $featuredEvents,
            'eventsByCategory' => $categoriesWithEvents,
            'canRegister' => Features::enabled(Features::registration()),
        ]);
    }
}
