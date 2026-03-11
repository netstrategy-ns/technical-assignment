<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VenueType;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class VenueTypeController extends Controller
{
    public function index(Request $request): Response
    {
        $modelClass = VenueType::class;
        $resource = 'venue-types';

        $query = VenueType::query()->with('events:id,venue_type_id');
        $query = $modelClass::applyTableFilters($query, $request->query());
        $sort = $modelClass::parseTableSort($request->query());

        if ($sort['field'] !== null) {
            $query = $modelClass::applyTableSort($query, $sort['field'], $sort['dir']);
        }

        return Inertia::render('admin/venue-types/Index', [
            'resource' => $resource,
            'resources' => [
                ['id' => $resource, 'label' => 'Tipologie luogo'],
            ],
            'columns' => $modelClass::tableColumns(),
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
