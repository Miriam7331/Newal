<?php

namespace App\Http\Controllers;

use App\Models\ProfessionalFamily;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Inertia\Inertia;

class ProfessionalFamilyController extends Controller
{
    public function loadItems() 
    {
        $itemsPerPage = Request::get('itemsPerPage', 10);
        $sortBy = json_decode(Request::get('sortBy', '[]'), true);
        $search = json_decode(Request::get('search', '[]'), true);
        $deleted = filter_var(Request::get('deleted', 'false'), FILTER_VALIDATE_BOOLEAN);

        $query = ProfessionalFamily::query();

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
        $professionalFamily = ProfessionalFamily::create(
            Request::validate([
                'name' => ['required', 'max:191'],
            ])
        );

        return Redirect::back()->with(['success' => 'Familia Profesional creada.', 'item' => $professionalFamily, 'itemType' => 'ProfessionalFamily']);
    }

    public function update(ProfessionalFamily $professionalFamily)
    {
        $professionalFamily->update(
            Request::validate([
                'name' => ['required', 'max:191'],
            ])
        );

        return Redirect::back()->with('success', 'Familia Profesional editada.');
    }

    public function destroy(ProfessionalFamily $professionalFamily)
    {
        $professionalFamily->delete();

        return Redirect::back()->with('success', 'Familia Profesional movida a la papelera.');
    }

    public function destroyPermanent($id)
    {
        $professionalFamily = ProfessionalFamily::onlyTrashed()->findOrFail($id);
        $professionalFamily->forceDelete();

        return Redirect::back()->with('success', 'Familia Profesional eliminada de forma permanente.');
    }

    public function restore($id)
    {
        $professionalFamily = ProfessionalFamily::onlyTrashed()->findOrFail($id);
        $professionalFamily->restore();

        return Redirect::back()->with('success', 'Familia Profesional restaurada.');
    }

    public function exportExcel()
    {
        $items = ProfessionalFamily::all();

        return  [ 'itemsExcel' => $items ];
    } 
}
