<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(Request $request): Response
    {
        $modelClass = User::class;
        $resource = 'users';

        $query = User::query()->withTrashed();
        $query = $modelClass::applyTableFilters($query, $request->query());
        $sort = $modelClass::parseTableSort($request->query());

        if ($sort['field'] !== null) {
            $query = $modelClass::applyTableSort($query, $sort['field'], $sort['dir']);
        }

        $rows = $query->paginate($this->resolvePerPage($request));
        $rows->getCollection()->transform(fn (User $user): User => $user->makeVisible('is_admin'));

        return Inertia::render('admin/users/Index', [
            'resource' => $resource,
            'resources' => [
                ['id' => $resource, 'label' => 'Utenti'],
            ],
            'columns' => $modelClass::tableColumns(),
            'rows' => $rows->withQueryString(),
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
