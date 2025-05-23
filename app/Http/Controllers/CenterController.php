<?php

namespace App\Http\Controllers;

use App\Models\Center;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Inertia\Inertia;

class CenterController extends Controller
{
    public function loadItems() 
    {
        $itemsPerPage = Request::get('itemsPerPage', 10);
        $sortBy = json_decode(Request::get('sortBy', '[]'), true);
        $search = json_decode(Request::get('search', '[]'), true);
        $deleted = filter_var(Request::get('deleted', 'false'), FILTER_VALIDATE_BOOLEAN);

        $query = Center::with('users');

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
        $center = Center::create(
            Request::validate([
                'name' => ['required', 'max:191'],
                'entities_id' => ['required', 'exists:entities,id'],
                'island' => ['required', 'max:191'],
            ])
        );

        $center->load(['users']);

        return Redirect::back()->with(['success' => 'Centro creado.', 'item' => $center, 'itemType' => 'center']);
    }

    public function update(Center $center)
    {
        $center->update(
            Request::validate([
                'name' => ['required', 'max:191'],
                'entities_id' => ['required', 'exists:entities,id'],
                'island' => ['required', 'max:191'],
            ])
        );

        $center->load(['users']);

        return Redirect::back()->with(['success' => 'Centro actualizado.', 'item' => $center, 'itemType' => 'center']);
    }

    public function destroy(Center $center)
    {
        $center->delete();

        return Redirect::back()->with('success', 'Centro movido a la papelera.');
    }

    public function destroyPermanent($id)
    {
        $center = Center::onlyTrashed()->findOrFail($id);
        $center->forceDelete();

        return Redirect::back()->with('success', 'Centro eliminado permanentemente.');
    }

    public function restore($id)
    {
        $center = Center::onlyTrashed()->findOrFail($id);
        $center->restore();

        return Redirect::back()->with('success', 'Centro restaurado.');
    }

    public function exportExcel()
    {
        $items = Center::all();

        return  [ 'itemsExcel' => $items ];
    }

    public function addUser($id)
    {
        try {
            $request = Request::all();

            $center = Center::find($id);
            $center->users()->attach($request["users_id"]);

            $center->load('users', 'entity');

            return Redirect::back()->with(['success' => 'Usuario añadido.', 'item' => $center, 'itemType' => 'center']);
        } catch (\Throwable $th) {
            return Redirect::back()->with('error', $th->getMessage());
        }
    }

    public function removeUser($id)
    {
        try {
            $center = Center::findOrFail($id);
            $center->users()->detach(Request::validate([
                'users_id' => ['required', 'exists:users,id'],
            ]));

            $center->load(['users']);

            return Redirect::back()->with(['success' => 'Usuario desvinculado.', 'item' => $center, 'itemType' => 'center']);

            return Redirect::back()->with('success', 'Usuario eliminado.');
        } catch (\Throwable $th) {
            return Redirect::back()->with('error', 'Error al eliminar usuario.');
        }
    }
}