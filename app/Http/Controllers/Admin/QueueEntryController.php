<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QueueEntry;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class QueueEntryController extends Controller
{
    public function index(Request $request): Response
    {
        $modelClass = QueueEntry::class;
        $resource = 'queue-entries';

        $query = QueueEntry::query()
            ->with('event:id,title')
            ->with('user:id,name');
        $query = $modelClass::applyTableFilters($query, $request->query());
        $sort = $modelClass::parseTableSort($request->query());

        if ($sort['field'] !== null) {
            $query = $modelClass::applyTableSort($query, $sort['field'], $sort['dir']);
        }

        return Inertia::render('admin/queue-entries/Index', [
            'resource' => $resource,
            'resources' => [
                ['id' => $resource, 'label' => 'Coda eventi'],
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
