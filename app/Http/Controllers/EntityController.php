<?php

namespace App\Http\Controllers;

use App\Models\Entity;
use App\Models\User;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Inertia\Inertia;

class EntityController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Inertia::render('Dashboard/Entity', [
            'entities' => Inertia::lazy(fn () => Entity::all()),
            'users' => Inertia::lazy(fn () => User::all()),
        ]);
    }

    public function loadItems() 
    {
        $itemsPerPage = Request::get('itemsPerPage', 10);
        $sortBy = json_decode(Request::get('sortBy', '[]'), true);
        $search = json_decode(Request::get('search', '[]'), true);
        $deleted = filter_var(Request::get('deleted', 'false'), FILTER_VALIDATE_BOOLEAN);

        $query = Entity::query();

        if ($deleted) {
            $query->onlyTrashed();
        }

        if (!empty($search)) {
            foreach ($search as $key => $value) {
                if (!empty($value)) {
                    $query->where($key, 'LIKE', '%' . $value . '%');
                }
            }
        }

        if (!empty($sortBy)) {
            foreach ($sortBy as $sort) {
                if (isset($sort['key']) && isset($sort['order'])) {
                    $query->orderBy($sort['key'], $sort['order']);
                }
            }
        } else {
            $query->orderBy("id", "desc");
        }

        if ($itemsPerPage == -1) {
            $itemsPerPage = $query->count();
        }

        $items = $query->paginate($itemsPerPage);

        return [
            'tableData' => [
                'items' => $items->items(),
                'itemsLength' => $items->total(),
                'itemsPerPage' => $items->perPage(),
                'page' => $items->currentPage(),
                'sortBy' => $sortBy,
                'search' => $search, 
                'deleted' => $deleted,
            ]
        ];
    }

    public function store()
    {
        Entity::create(
            Request::validate([
                'name' => ['required', 'max:191'],
            ])
        );

        return Redirect::back()->with('success', 'Entidad creada.');
    }

    public function update(Entity $entity)
    {
        $entity->update(
            Request::validate([
                'name' => ['required', 'max:191'],
            ])
        );

        return Redirect::back()->with('success', 'Entidad actualizada.');
    }

    public function destroy(Entity $entity)
    {
        $entity->delete();

        return Redirect::back()->with('success', 'Entidad movida a la papelera.');
    }

    public function destroyPermanent($id)
    {
        $entity = Entity::onlyTrashed()->findOrFail($id);
        $entity->forceDelete();

        return Redirect::back()->with('success', 'Entidad eliminada permanentemente.');
    }

    public function restore($id)
    {
        $entity = Entity::onlyTrashed()->findOrFail($id);
        $entity->restore();

        return Redirect::back()->with('success', 'Entidad restaurada.');
    }

    public function exportExcel()
    {
        $items = Entity::all();

        return  [ 'itemsExcel' => $items ];
    } 
}