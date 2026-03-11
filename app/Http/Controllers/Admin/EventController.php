<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EventCategory;
use App\Models\Event;
use App\Models\VenueType;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EventController extends Controller
{
    public function index(Request $request): Response
    {
        $modelClass = Event::class;
        $resource = 'events';

        $query = Event::query()
            ->with(['category:id,name', 'venueType:id,name'])
            ->withCount('queueEntries');

        $query = $modelClass::applyTableFilters($query, $request->query());
        $sort = $modelClass::parseTableSort($request->query());

        if ($sort['field'] !== null) {
            $query = $modelClass::applyTableSort($query, $sort['field'], $sort['dir']);
        }

        $columns = $modelClass::tableColumns();
        foreach ($columns as &$column) {
            if ($column['field_name'] === 'category.name') {
                $column['options'] = EventCategory::query()
                    ->orderBy('name')
                    ->pluck('name', 'name')
                    ->map(static fn (string $name): array => [
                        'value' => $name,
                        'label' => $name,
                    ])
                    ->values()
                    ->toArray();
            }

            if ($column['field_name'] === 'venueType.name') {
                $column['options'] = VenueType::query()
                    ->orderBy('name')
                    ->pluck('name', 'name')
                    ->map(static fn (string $name): array => [
                        'value' => $name,
                        'label' => $name,
                    ])
                    ->values()
                    ->toArray();
            }
        }

        return Inertia::render('admin/events/Index', [
            'resource' => $resource,
            'resources' => [
                ['id' => $resource, 'label' => 'Eventi'],
            ],
            'columns' => $columns,
            'rows' => $query->paginate($this->resolvePerPage($request))->withQueryString(),
            'sort' => $sort,
            'filters' => $modelClass::requestedTableFilters($request->query()),
        ]);
    }

    private function resolvePerPage(Request $request): int
    {
        $allowed = [12, 24, 32, 48];
        $perPage = (int) $request->query('per_page', 24);

        return in_array($perPage, $allowed, true) ? $perPage : 24;
    }
}
