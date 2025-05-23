<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;

class ProjectController extends Controller
{

    public function all()
    {
        $items = Project::all();

        return $items;
    }

    public function loadItems()
    {
        $itemsPerPage = Request::get('itemsPerPage', 10);
        $sortBy = json_decode(Request::get('sortBy', '[]'), true);
        $search = json_decode(Request::get('search', '[]'), true);
        $deleted = filter_var(Request::get('deleted', 'false'), FILTER_VALIDATE_BOOLEAN);

        $query = Project::query();

        if ($deleted) {
            $query->onlyTrashed();
        }

        if (!empty($search)) {
            foreach ($search as $key => $value) {
                if (!empty($value)) {
                    if ($key == "active") {
                        if (strpos('activo', strtolower($value)) !== false) {
                            $query->where($key, '=', 1);
                        } else if (strpos('inactivo', strtolower($value)) !== false) {
                            $query->where($key, '=', 0);
                        } else {
                            $query->whereRaw('false');
                        }
                    } else {
                        $query->where($key, 'LIKE', '%' . $value . '%');
                    }
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
        $project = Project::create(
            Request::validate([
                'name' => ['required', 'max:191'],
                'active' => ['required'],
            ])
        );

        return Redirect::back()->with(['success' => 'Projecto creado.', 'item' => $project, 'itemType' => 'project']);
    }

    public function update(Project $project)
    {
        $project->update(
            Request::validate([
                'name' => ['required', 'max:191'],
                'active' => ['required'],
            ])
        );

        return Redirect::back()->with('success', 'Projecto editado.');
    }

    public function destroy(Project $project)
    {
        $project->delete();

        return Redirect::back()->with('success', 'Projecto movido a la papelera.');
    }

    public function destroyPermanent($id)
    {
        $project = Project::onlyTrashed()->findOrFail($id);
        $project->forceDelete();

        return Redirect::back()->with('success', 'Projecto eliminado de forma permanente.');
    }

    public function restore($id)
    {
        $project = Project::onlyTrashed()->findOrFail($id);
        $project->restore();

        return Redirect::back()->with('success', 'Projecto restaurado.');
    }

    public function exportExcel()
    {
        $items = Project::all();

        return  ['itemsExcel' => $items];
    }
}
