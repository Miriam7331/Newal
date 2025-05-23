<?php

namespace App\Http\Controllers;

use App\Models\Sector;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Inertia\Inertia;

class SectorController extends Controller
{
    public function loadItems() 
    {
        $itemsPerPage = Request::get('itemsPerPage', 10);
        $sortBy = json_decode(Request::get('sortBy', '[]'), true);
        $search = json_decode(Request::get('search', '[]'), true);
        $deleted = filter_var(Request::get('deleted', 'false'), FILTER_VALIDATE_BOOLEAN);

        $query = Sector::query();

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
        $sector = Sector::create(
            Request::validate([
                'name' => ['required', 'max:191'],
            ])
        );

        return Redirect::back()->with(['success' => 'Sector creado.', 'item' => $sector, 'itemType' => 'sector']);
    }

    public function update(Sector $sector)
    {
        $sector->update(
            Request::validate([
                'name' => ['required', 'max:191'],
            ])
        );

        return Redirect::back()->with('success', 'Sector editado.');
    }

    public function destroy(Sector $sector)
    {
        $sector->delete();

        return Redirect::back()->with('success', 'Sector movido a la papelera.');
    }

    public function destroyPermanent($id)
    {
        $sector = Sector::onlyTrashed()->findOrFail($id);
        $sector->forceDelete();

        return Redirect::back()->with('success', 'Sector eliminado de forma permanente.');
    }

    public function restore($id)
    {
        $sector = Sector::onlyTrashed()->findOrFail($id);
        $sector->restore();

        return Redirect::back()->with('success', 'Sector restaurado.');
    }

    public function exportExcel()
    {
        $items = Sector::all();

        return  [ 'itemsExcel' => $items ];
    } 
}
