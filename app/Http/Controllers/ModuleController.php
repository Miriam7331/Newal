<?php

namespace App\Http\Controllers;

use App\Models\Module;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Inertia\Inertia;
use Illuminate\Validation\Rule;

class ModuleController extends Controller
{

    public function loadAutocompleteItems()
    {
        $search = Request::get('search', '');

        $items = Module::whereRaw("CONCAT(name, ' (', code, ')') LIKE '%$search%'")->limit(6)->get();

        return ['autocompleteItems' => $items];
    }

    public function loadItems()
    {
        $itemsPerPage = Request::get('itemsPerPage', 10);
        $sortBy = json_decode(Request::get('sortBy', '[]'), true);
        $search = json_decode(Request::get('search', '[]'), true);
        $deleted = filter_var(Request::get('deleted', 'false'), FILTER_VALIDATE_BOOLEAN);

        $query = Module::query();;

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
        $module = Module::create(
            Request::validate([
                'code' => ['required', 'max:191', 'unique:modules,code'],
                'name' => ['required', 'max:191'],
            ])
        );

        return Redirect::back()->with(['success' => 'Módulo creado.', 'item' => $module, 'itemType' => 'create-module']);
    }

    public function update(Module $module)
    {
        $module->update(
            Request::validate([
                'code' => [
                    'required',
                    'max:191',
                    Rule::unique('modules', 'code')->ignore($module->id),
                ],
                'name' => ['required', 'max:191'],
            ])
        );

        return Redirect::back()->with(['success' => 'Módulo editado.', 'item' => $module, 'itemType' => 'update-module']);
    }

    public function destroy(Module $module)
    {
        $module->delete();

        return Redirect::back()->with('success', 'Módulo movido a la papelera.');
    }

    public function destroyPermanent($id)
    {
        $module = Module::onlyTrashed()->findOrFail($id);
        $module->forceDelete();

        return Redirect::back()->with('success', 'Módulo eliminado de forma permanente.');
    }

    public function restore($id)
    {
        $module = Module::onlyTrashed()->findOrFail($id);
        $module->restore();

        return Redirect::back()->with('success', 'Módulo restaurado.');
    }

    public function exportExcel()
    {
        $items = Module::all();

        return  ['itemsExcel' => $items];
    }
}
